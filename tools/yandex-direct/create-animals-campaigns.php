#!/usr/bin/env php
<?php
/**
 * Create MRI Animal search campaigns in KAZAKHSTAN_MRT from seov/yandex-direct-ads.csv.
 *
 * Usage:
 *   php create-animals-campaigns.php --dry-run
 *   php create-animals-campaigns.php --apply
 */

declare(strict_types=1);

const KZ_METRIKA_COUNTER = 110465113;
const KZ_ALMATY_REGION_ID = 29406;
const KZ_WEEKLY_SPEND_LIMIT = 1500000000; // micro-units, ~1500 ₸/week baseline per group

$dir = __DIR__;
$repoRoot = dirname($dir, 2);
require_once $dir . '/bootstrap-env.php';
require_once $dir . '/direct-lib.php';

$apply = in_array('--apply', $argv ?? [], true);
$csvPath = $repoRoot . '/seov/yandex-direct-ads.csv';
$minusPath = $repoRoot . '/seov/minus-words.txt';
$semanticPath = $repoRoot . '/seov/semantic-core.csv';

if (!is_file($csvPath)) {
    fwrite(STDERR, "ERROR: Missing {$csvPath} (seov/ is local; see docs/rag/chunks/08-animals-contextual-ads.md)\n");
    exit(1);
}

/** @return list<string> */
function kzAnimalsLoadMinusWords(string $path): array
{
    if (!is_file($path)) {
        return ['человек', 'людям', 'беременным', 'детям'];
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    return array_values(array_filter(array_map('trim', $lines)));
}

/** @return array<string, list<string>> campaign utm slug => keywords */
function kzAnimalsLoadSemanticByCampaign(string $path): array
{
    if (!is_file($path)) {
        return [];
    }
    $map = [];
    $fh = fopen($path, 'rb');
    if ($fh === false) {
        return [];
    }
    $header = fgetcsv($fh);
    while (($row = fgetcsv($fh)) !== false) {
        if (count($row) < 2) {
            continue;
        }
        $keyword = trim($row[0]);
        $group = trim($row[1] ?? '');
        if ($keyword === '') {
            continue;
        }
        $campaign = match ($group) {
            'commercial', 'brand', 'geo' => 'animals_mrt_commercial',
            'zones' => 'animals_mrt_zones',
            'symptoms' => 'animals_mrt_symptoms',
            'b2b' => 'animals_mrt_b2b',
            default => 'animals_mrt_commercial',
        };
        $map[$campaign][] = $keyword;
    }
    fclose($fh);
    foreach ($map as $campaign => $keywords) {
        $map[$campaign] = array_values(array_unique($keywords));
    }
    return $map;
}

/** @return array<string, array{name:string,groups:array<string, list<array<string,string>>>}> */
function kzAnimalsParseAdsCsv(string $path): array
{
    $campaigns = [];
    $fh = fopen($path, 'rb');
    if ($fh === false) {
        return [];
    }
    $header = fgetcsv($fh);
    while (($row = fgetcsv($fh)) !== false) {
        if (count($row) < 8) {
            continue;
        }
        [$campaignSlug, $groupName, , $title1, $title2, $text, $urlPath] = $row;
        $campaignSlug = trim((string) $campaignSlug);
        $groupName = trim((string) $groupName);
        if ($campaignSlug === '' || $groupName === '') {
            continue;
        }
        if (!isset($campaigns[$campaignSlug])) {
            $campaigns[$campaignSlug] = [
                'name' => kzAnimalsCampaignDisplayName($campaignSlug),
                'groups' => [],
            ];
        }
        $href = kzAnimalsAbsoluteHref(trim((string) $urlPath), $campaignSlug);
        $campaigns[$campaignSlug]['groups'][$groupName][] = [
            'title' => trim((string) $title1),
            'title2' => trim((string) $title2),
            'text' => trim((string) $text),
            'href' => $href,
        ];
    }
    fclose($fh);
    return $campaigns;
}

function kzAnimalsCampaignDisplayName(string $slug): string
{
    return match ($slug) {
        'animals_mrt_commercial' => 'KZ Поиск — MRI Animal commercial',
        'animals_mrt_zones' => 'KZ Поиск — MRI Animal zones',
        'animals_mrt_symptoms' => 'KZ Поиск — MRI Animal symptoms',
        'animals_mrt_b2b' => 'KZ Поиск — MRI Animal B2B',
        default => 'KZ Поиск — MRI Animal ' . $slug,
    };
}

function kzAnimalsAbsoluteHref(string $urlPath, string $campaignSlug): string
{
    $path = $urlPath;
    if ($path === '' || $path[0] !== '/') {
        $path = '/almaty_aubakirova/';
    }
    $base = 'https://mrt-lider.kz' . rtrim($path, '/') . '/';
    $sep = str_contains($base, '?') ? '&' : '?';
    return $base . $sep . 'utm_source=yandex&utm_medium=cpc&utm_campaign=' . rawurlencode($campaignSlug)
        . '&utm_content={ad_id}_{source}&utm_term={keyword}';
}

function kzAnimalsApiErrors(array $result, string $label): ?string
{
    if (isset($result['error'])) {
        return "{$label}: {$result['error']}";
    }
    foreach ($result['AddResults'] ?? [] as $item) {
        if (!empty($item['Errors'])) {
            $msgs = array_map(static fn(array $e): string => ($e['Message'] ?? '') . ' ' . ($e['Details'] ?? ''), $item['Errors']);
            return "{$label}: " . implode('; ', $msgs);
        }
    }
    return null;
}

function kzAnimalsFindCampaignId(string $token, array $env, string $name): ?int
{
    $existing = directLibApi($token, 'campaigns', [
        'SelectionCriteria' => (object) [],
        'FieldNames' => ['Id', 'Name', 'State'],
    ], $env);
    foreach ($existing['Campaigns'] ?? [] as $campaign) {
        if ((string) ($campaign['Name'] ?? '') === $name && (string) ($campaign['State'] ?? '') !== 'ARCHIVED') {
            return (int) ($campaign['Id'] ?? 0);
        }
    }
    return null;
}

function kzAnimalsEnsureCampaign(string $token, array $env, string $name, string $startDate): int
{
    $found = kzAnimalsFindCampaignId($token, $env, $name);
    if ($found !== null && $found > 0) {
        return $found;
    }

    $result = directLibApi($token, 'campaigns', [
        'Campaigns' => [[
            'Name' => $name,
            'StartDate' => $startDate,
            'TextCampaign' => [
                'BiddingStrategy' => [
                    'Search' => [
                        'BiddingStrategyType' => 'WB_MAXIMUM_CLICKS',
                        'WbMaximumClicks' => ['WeeklySpendLimit' => KZ_WEEKLY_SPEND_LIMIT],
                    ],
                    'Network' => ['BiddingStrategyType' => 'SERVING_OFF'],
                ],
                'CounterIds' => ['Items' => [KZ_METRIKA_COUNTER]],
                'Settings' => [
                    ['Option' => 'ADD_METRICA_TAG', 'Value' => 'YES'],
                    ['Option' => 'ENABLE_EXTENDED_AD_TITLE', 'Value' => 'YES'],
                    ['Option' => 'ALTERNATIVE_TEXTS_ENABLED', 'Value' => 'YES'],
                ],
            ],
        ]],
    ], $env, 'add');

    $err = kzAnimalsApiErrors($result, 'campaigns');
    if ($err !== null) {
        throw new RuntimeException($err);
    }
    $id = (int) ($result['AddResults'][0]['Id'] ?? 0);
    if ($id <= 0) {
        throw new RuntimeException('No campaign id returned');
    }
    return $id;
}

function kzAnimalsEnsureAdGroup(string $token, array $env, int $campaignId, string $groupName, array $negativeKeywords): int
{
    $groups = directLibApi($token, 'adgroups', [
        'SelectionCriteria' => ['CampaignIds' => [$campaignId]],
        'FieldNames' => ['Id', 'Name'],
    ], $env);
    foreach ($groups['AdGroups'] ?? [] as $group) {
        if ((string) ($group['Name'] ?? '') === $groupName) {
            return (int) ($group['Id'] ?? 0);
        }
    }

    $result = directLibApi($token, 'adgroups', [
        'AdGroups' => [[
            'Name' => $groupName,
            'CampaignId' => $campaignId,
            'RegionIds' => [KZ_ALMATY_REGION_ID],
            'NegativeKeywords' => ['Items' => $negativeKeywords],
        ]],
    ], $env, 'add');

    $err = kzAnimalsApiErrors($result, 'adgroups');
    if ($err !== null) {
        throw new RuntimeException($err);
    }
    $id = (int) ($result['AddResults'][0]['Id'] ?? 0);
    if ($id <= 0) {
        throw new RuntimeException('No ad group id returned');
    }
    return $id;
}

function kzAnimalsEnsureAds(string $token, array $env, int $adGroupId, array $ads): array
{
    $existing = directLibApi($token, 'ads', [
        'SelectionCriteria' => ['AdGroupIds' => [$adGroupId]],
        'FieldNames' => ['Id', 'Type'],
        'TextAdFieldNames' => ['Title', 'Href'],
    ], $env);

    $existingCount = count(array_filter($existing['Ads'] ?? [], static fn(array $ad): bool => ($ad['Type'] ?? '') === 'TEXT_AD'));
    if ($existingCount > 0) {
        return array_map(
            static fn(array $ad): int => (int) ($ad['Id'] ?? 0),
            array_filter($existing['Ads'] ?? [], static fn(array $ad): bool => ($ad['Type'] ?? '') === 'TEXT_AD'),
        );
    }

    $payload = [];
    foreach ($ads as $ad) {
        $payload[] = [
            'AdGroupId' => $adGroupId,
            'TextAd' => [
                'Title' => $ad['title'],
                'Title2' => $ad['title2'],
                'Text' => $ad['text'],
                'Href' => $ad['href'],
                'Mobile' => 'NO',
            ],
        ];
    }

    $result = directLibApi($token, 'ads', ['Ads' => $payload], $env, 'add');
    $err = kzAnimalsApiErrors($result, 'ads');
    if ($err !== null) {
        throw new RuntimeException($err);
    }

    return array_map(static fn(array $r): int => (int) ($r['Id'] ?? 0), $result['AddResults'] ?? []);
}

function kzAnimalsEnsureKeywords(string $token, array $env, int $adGroupId, array $keywords): int
{
    $existing = directLibApi($token, 'keywords', [
        'SelectionCriteria' => ['AdGroupIds' => [$adGroupId]],
        'FieldNames' => ['Id', 'Keyword'],
    ], $env);

    $set = [];
    foreach ($existing['Keywords'] ?? [] as $kw) {
        $keyword = trim((string) ($kw['Keyword'] ?? ''));
        if ($keyword !== '' && $keyword !== '---autotargeting') {
            $set[$keyword] = true;
        }
    }

    $payload = [];
    foreach ($keywords as $keyword) {
        if (!isset($set[$keyword])) {
            $payload[] = ['AdGroupId' => $adGroupId, 'Keyword' => $keyword];
        }
    }

    if ($payload === []) {
        return count($set);
    }

    $result = directLibApi($token, 'keywords', ['Keywords' => $payload], $env, 'add');
    $err = kzAnimalsApiErrors($result, 'keywords');
    if ($err !== null) {
        throw new RuntimeException($err);
    }

    return count($set) + count($result['AddResults'] ?? []);
}

$campaigns = kzAnimalsParseAdsCsv($csvPath);
$semantic = kzAnimalsLoadSemanticByCampaign($semanticPath);
$minusWords = kzAnimalsLoadMinusWords($minusPath);

echo 'MRI Animal — Yandex Direct KZ' . ($apply ? ' (APPLY)' : ' (dry-run)') . "\n";
echo str_repeat('=', 60) . "\n";
echo 'CSV: ' . $csvPath . "\n";
echo 'Campaigns in CSV: ' . count($campaigns) . "\n";
echo 'Metrika counter: ' . KZ_METRIKA_COUNTER . "\n\n";

foreach ($campaigns as $slug => $data) {
    $kwCount = count($semantic[$slug] ?? []);
    $adCount = array_sum(array_map('count', $data['groups']));
    echo "- {$slug} ({$data['name']}): " . count($data['groups']) . " groups, {$adCount} ads, {$kwCount} semantic keywords\n";
}

if (!$apply) {
    echo "\nRun with --apply to create/update in KAZAKHSTAN_MRT cabinet.\n";
    exit(0);
}

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

$reportsDir = $dir . '/reports';
if (!is_dir($reportsDir)) {
    mkdir($reportsDir, 0755, true);
}
$logPath = $reportsDir . '/create-animals-' . date('Y-m-d-His') . '.log';
$startDate = date('Y-m-d');

foreach ($campaigns as $slug => $data) {
    try {
        $campaignId = kzAnimalsEnsureCampaign($token, $env, $data['name'], $startDate);
        file_put_contents($logPath, date('c') . " campaign {$slug} id={$campaignId}\n", FILE_APPEND);

        $keywordsForCampaign = $semantic[$slug] ?? [];
        foreach ($data['groups'] as $groupName => $ads) {
            $adGroupId = kzAnimalsEnsureAdGroup($token, $env, $campaignId, $groupName, $minusWords);
            $adIds = kzAnimalsEnsureAds($token, $env, $adGroupId, $ads);
            $kwTotal = kzAnimalsEnsureKeywords($token, $env, $adGroupId, $keywordsForCampaign);

            if ($adIds !== []) {
                directLibApi($token, 'ads', ['SelectionCriteria' => ['Ids' => $adIds]], $env, 'moderate');
            }

            file_put_contents(
                $logPath,
                date('c') . " group \"{$groupName}\" id={$adGroupId} ads=" . count($adIds) . " kw={$kwTotal}\n",
                FILE_APPEND
            );
            echo "OK {$slug} / {$groupName}: campaign={$campaignId} group={$adGroupId}\n";
        }

        directLibApi($token, 'campaigns', ['SelectionCriteria' => ['Ids' => [$campaignId]]], $env, 'resume');
    } catch (Throwable $e) {
        fwrite(STDERR, "ERROR {$slug}: {$e->getMessage()}\n");
        file_put_contents($logPath, date('c') . " ERROR {$slug}: {$e->getMessage()}\n", FILE_APPEND);
    }
}

echo "\nLog: {$logPath}\n";
exit(0);
