#!/usr/bin/env php
<?php
/**
 * KZ Yandex Direct optimization: archive duplicates, suspend legacy animals, fix Metrika counters.
 *
 * Usage:
 *   php optimize-kz-campaigns.php --dry-run
 *   php optimize-kz-campaigns.php --apply
 */

declare(strict_types=1);

$dir = __DIR__;
require_once $dir . '/bootstrap-env.php';
require_once $dir . '/direct-lib.php';

/** @var array<int, int> campaignId => Metrika counter (from mrt-city-config.php) */
const KZ_COUNTER_BY_CAMPAIGN = [
    61448203 => 110465113, // Алматы
    61448204 => 110466202, // Астана
    61448205 => 110468879, // Караганда
    65889007 => 110469944, // Талдыкорган
];

/** Duplicate search campaigns → archive */
const KZ_ARCHIVE_CAMPAIGN_IDS = [
    61448215, // Поиск - Алматы2
    61448225, // Поиск - Астана3
];

/** Legacy animals search → suspend (new MRI Animal campaigns stay ON) */
const KZ_SUSPEND_CAMPAIGN_IDS = [
    712211671, // Поиск - Алматы МРТ животным
];

$apply = in_array('--apply', $argv ?? [], true);

$env = yandexDirectLoadEnv();
if ($env === null) {
    fwrite(STDERR, "ERROR: No credentials\n");
    exit(1);
}

$tokens = yandexDirectUniqueAccountTokens(['KAZAKHSTAN_MRT' => 'TOKEN_KAZAKHSTAN_MRT'], $env);
$token = $tokens['KAZAKHSTAN_MRT'] ?? '';
if ($token === '') {
    fwrite(STDERR, "ERROR: TOKEN_KAZAKHSTAN_MRT missing\n");
    exit(1);
}

$allIds = array_values(array_unique(array_merge(
    KZ_ARCHIVE_CAMPAIGN_IDS,
    KZ_SUSPEND_CAMPAIGN_IDS,
    array_keys(KZ_COUNTER_BY_CAMPAIGN)
)));

$campaignsById = [];
foreach (array_chunk($allIds, 10) as $chunk) {
    $result = directLibApi($token, 'campaigns', [
        'SelectionCriteria' => ['Ids' => $chunk],
        'FieldNames' => ['Id', 'Name', 'State', 'Status', 'Type'],
        'TextCampaignFieldNames' => ['CounterIds'],
    ], $env);
    if (isset($result['error'])) {
        fwrite(STDERR, "ERROR fetching campaigns: {$result['error']}\n");
        exit(1);
    }
    foreach ($result['Campaigns'] ?? [] as $campaign) {
        $id = (int) ($campaign['Id'] ?? 0);
        if ($id > 0) {
            $campaignsById[$id] = $campaign;
        }
    }
}

function kzCounterItems(array $campaign): array
{
    $items = $campaign['TextCampaign']['CounterIds']['Items']
        ?? $campaign['CounterIds']['Items']
        ?? $campaign['CounterIds']
        ?? [];
    if (!is_array($items)) {
        return [];
    }
    return array_map('intval', $items);
}

function kzOptimizeApiErrors(array $result, string $action): ?string
{
    if (isset($result['error'])) {
        return "{$action}: {$result['error']}";
    }
    $key = match ($action) {
        'update' => 'UpdateResults',
        'suspend', 'archive' => 'ArchiveResults',
        default => 'Results',
    };
    if ($action === 'suspend') {
        $key = 'SuspendResults';
    }
    if ($action === 'archive') {
        $key = 'ArchiveResults';
    }
    $errors = [];
    foreach ($result[$key] ?? [] as $item) {
        if (!empty($item['Errors'])) {
            foreach ($item['Errors'] as $e) {
                $errors[] = ($e['Message'] ?? '') . ' ' . ($e['Details'] ?? '');
            }
        }
    }
    return $errors === [] ? null : implode('; ', $errors);
}

$reportsDir = $dir . '/reports';
if (!is_dir($reportsDir) && !mkdir($reportsDir, 0755, true) && !is_dir($reportsDir)) {
    fwrite(STDERR, "ERROR: Cannot create reports directory\n");
    exit(1);
}
$ts = date('Y-m-d-His');
$logPath = $reportsDir . "/optimize-kz-{$ts}.log";

$plan = [
    'archive' => [],
    'suspend' => [],
    'counter_update' => [],
];
$warnings = [];

foreach (KZ_ARCHIVE_CAMPAIGN_IDS as $id) {
    if (!isset($campaignsById[$id])) {
        $warnings[] = "Archive id={$id}: campaign not found";
        continue;
    }
    $c = $campaignsById[$id];
    $plan['archive'][] = [
        'id' => $id,
        'name' => (string) ($c['Name'] ?? ''),
        'state' => (string) ($c['State'] ?? ''),
    ];
}

foreach (KZ_SUSPEND_CAMPAIGN_IDS as $id) {
    if (!isset($campaignsById[$id])) {
        $warnings[] = "Suspend id={$id}: campaign not found";
        continue;
    }
    $c = $campaignsById[$id];
    $state = (string) ($c['State'] ?? '');
    if ($state === 'SUSPENDED' || $state === 'OFF') {
        $warnings[] = "Suspend id={$id}: already {$state}";
        continue;
    }
    $plan['suspend'][] = [
        'id' => $id,
        'name' => (string) ($c['Name'] ?? ''),
        'state' => $state,
    ];
}

foreach (KZ_COUNTER_BY_CAMPAIGN as $id => $targetCounter) {
    if (!isset($campaignsById[$id])) {
        $warnings[] = "Counter id={$id}: campaign not found";
        continue;
    }
    $c = $campaignsById[$id];
    $current = kzCounterItems($c);
    $target = [$targetCounter];
    sort($current);
    $targetSorted = $target;
    sort($targetSorted);
    if ($current === $targetSorted) {
        continue;
    }
    $plan['counter_update'][] = [
        'id' => $id,
        'name' => (string) ($c['Name'] ?? ''),
        'from' => $current,
        'to' => $target,
    ];
}

echo 'KZ Yandex Direct optimize' . ($apply ? ' (APPLY)' : ' (dry-run)') . "\n";
echo str_repeat('=', 60) . "\n\n";

echo "Archive (" . count($plan['archive']) . "):\n";
foreach ($plan['archive'] as $row) {
    echo sprintf("  id=%d state=%s %s\n", $row['id'], $row['state'], $row['name']);
}

echo "\nSuspend (" . count($plan['suspend']) . "):\n";
foreach ($plan['suspend'] as $row) {
    echo sprintf("  id=%d state=%s %s\n", $row['id'], $row['state'], $row['name']);
}

echo "\nCounterIds update (" . count($plan['counter_update']) . "):\n";
foreach ($plan['counter_update'] as $row) {
    $from = $row['from'] === [] ? '(none)' : implode(',', $row['from']);
    $to = implode(',', $row['to']);
    echo sprintf("  id=%d %s: [%s] → [%s]\n", $row['id'], $row['name'], $from, $to);
}

if ($warnings !== []) {
    echo "\nWarnings:\n";
    foreach ($warnings as $w) {
        echo "  {$w}\n";
    }
}

$apiErrors = [];

if ($apply) {
    $needSuspendBeforeArchive = [];
    foreach ($plan['archive'] as $row) {
        if ($row['state'] === 'ON') {
            $needSuspendBeforeArchive[] = $row['id'];
        }
    }

    foreach (array_chunk($needSuspendBeforeArchive, 10) as $chunk) {
        $result = directLibApi($token, 'campaigns', [
            'SelectionCriteria' => ['Ids' => $chunk],
        ], $env, 'suspend');
        $err = kzOptimizeApiErrors($result, 'suspend');
        if ($err !== null) {
            $apiErrors[] = "pre-archive suspend: {$err}";
            file_put_contents($logPath, date('c') . " SUSPEND ERROR: {$err}\n", FILE_APPEND);
        } else {
            file_put_contents($logPath, date('c') . ' suspended for archive: ' . implode(',', $chunk) . "\n", FILE_APPEND);
        }
        usleep(300000);
    }

    $archiveIds = array_map(static fn(array $r): int => $r['id'], $plan['archive']);
    foreach (array_chunk($archiveIds, 10) as $chunk) {
        $result = directLibApi($token, 'campaigns', [
            'SelectionCriteria' => ['Ids' => $chunk],
        ], $env, 'archive');
        $err = kzOptimizeApiErrors($result, 'archive');
        if ($err !== null) {
            $apiErrors[] = "archive: {$err}";
            file_put_contents($logPath, date('c') . " ARCHIVE ERROR: {$err}\n", FILE_APPEND);
        } else {
            file_put_contents($logPath, date('c') . ' archived: ' . implode(',', $chunk) . "\n", FILE_APPEND);
        }
        usleep(300000);
    }

    $suspendIds = array_map(static fn(array $r): int => $r['id'], $plan['suspend']);
    foreach (array_chunk($suspendIds, 10) as $chunk) {
        if ($chunk === []) {
            continue;
        }
        $result = directLibApi($token, 'campaigns', [
            'SelectionCriteria' => ['Ids' => $chunk],
        ], $env, 'suspend');
        $err = kzOptimizeApiErrors($result, 'suspend');
        if ($err !== null) {
            $apiErrors[] = "suspend: {$err}";
            file_put_contents($logPath, date('c') . " SUSPEND ERROR: {$err}\n", FILE_APPEND);
        } else {
            file_put_contents($logPath, date('c') . ' suspended: ' . implode(',', $chunk) . "\n", FILE_APPEND);
        }
        usleep(300000);
    }

    $updates = [];
    foreach ($plan['counter_update'] as $row) {
        $updates[] = [
            'Id' => $row['id'],
            'TextCampaign' => [
                'CounterIds' => ['Items' => $row['to']],
            ],
        ];
    }
    foreach (array_chunk($updates, 10) as $chunk) {
        $result = directLibApi($token, 'campaigns', [
            'Campaigns' => $chunk,
        ], $env, 'update');
        $err = kzOptimizeApiErrors($result, 'update');
        if ($err !== null) {
            $apiErrors[] = "update CounterIds: {$err}";
            file_put_contents($logPath, date('c') . " UPDATE ERROR: {$err}\n", FILE_APPEND);
        } else {
            $ids = array_map(static fn(array $c): int => (int) $c['Id'], $chunk);
            file_put_contents($logPath, date('c') . ' updated CounterIds: ' . implode(',', $ids) . "\n", FILE_APPEND);
        }
        usleep(300000);
    }

    echo "\nLog: {$logPath}\n";
}

if ($apiErrors !== []) {
    echo "\nAPI errors:\n";
    foreach ($apiErrors as $e) {
        echo "  {$e}\n";
    }
    exit(1);
}

exit(0);
