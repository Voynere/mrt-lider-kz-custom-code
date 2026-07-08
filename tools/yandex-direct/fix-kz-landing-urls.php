#!/usr/bin/env php
<?php
/**
 * Replace mrt-lider.ru → mrt-lider.kz in Href for legacy KZ search campaigns only.
 *
 * Usage:
 *   php fix-kz-landing-urls.php --dry-run
 *   php fix-kz-landing-urls.php --apply
 */

declare(strict_types=1);

const KZ_LEGACY_CAMPAIGN_IDS = [
    61448203, // Поиск - Алматы
    61448215, // Алматы2
    61448204, // Астана
    61448225, // Астана3
    61448205, // Караганда
    65889007, // Талдыкорган
];

const KZ_EXCLUDED_CAMPAIGN_IDS = [
    712360075,
    712360098,
    712360119,
    712360139,
    712211671,
];

const BATCH_SIZE = 100;
const BATCH_SLEEP_US = 500000;

$dir = __DIR__;
require_once $dir . '/bootstrap-env.php';
require_once $dir . '/direct-lib.php';

$apply = in_array('--apply', $argv ?? [], true);
$dryRun = !$apply;
if (in_array('--dry-run', $argv ?? [], true)) {
    $dryRun = true;
    $apply = false;
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

$campaignIds = array_values(array_diff(KZ_LEGACY_CAMPAIGN_IDS, KZ_EXCLUDED_CAMPAIGN_IDS));
if ($campaignIds === []) {
    fwrite(STDERR, "ERROR: No target campaigns\n");
    exit(1);
}

$campaignsResult = directLibApi($token, 'campaigns', [
    'SelectionCriteria' => ['Ids' => $campaignIds],
    'FieldNames' => ['Id', 'Name', 'State'],
], $env);

if (isset($campaignsResult['error'])) {
    fwrite(STDERR, "ERROR: {$campaignsResult['error']}\n");
    exit(1);
}

$campaignNames = [];
foreach ($campaignsResult['Campaigns'] ?? [] as $c) {
    $campaignNames[(int) ($c['Id'] ?? 0)] = (string) ($c['Name'] ?? '');
}

$missing = array_diff($campaignIds, array_keys($campaignNames));
if ($missing !== []) {
    fwrite(STDERR, 'WARNING: campaigns not found: ' . implode(', ', $missing) . "\n");
}

$adsResult = directLibFetchAllAds($token, $campaignIds, $env);
if (isset($adsResult['error'])) {
    fwrite(STDERR, "ERROR: {$adsResult['error']}\n");
    exit(1);
}

$wouldChange = 0;
$updated = 0;
$errors = 0;
$skipped = 0;
$pending = [];
$examples = [];

foreach ($adsResult['ads'] as $ad) {
    $href = directLibExtractHref($ad);
    if ($href === '') {
        continue;
    }
    $newHref = kzFixTransformHref($href);
    if ($newHref === null) {
        $skipped++;
        continue;
    }
    if (!directLibIsWorkableAdState((string) ($ad['State'] ?? ''))) {
        $skipped++;
        continue;
    }

    $wouldChange++;
    $adId = (int) ($ad['Id'] ?? 0);
    $campaignId = (int) ($ad['CampaignId'] ?? 0);
    if (count($examples) < 5) {
        $examples[] = ['ad' => $adId, 'campaign' => $campaignNames[$campaignId] ?? (string) $campaignId, 'before' => $href, 'after' => $newHref];
    }

    if ($apply) {
        $pending[] = [
            'Id' => $adId,
            'TextAd' => ['Href' => $newHref],
            '_meta' => ['campaignId' => $campaignId, 'href' => $href, 'newHref' => $newHref],
        ];
    }
}

echo 'KZ landing URL fix (' . ($dryRun ? 'dry-run' : 'apply') . ")\n";
echo 'Target campaigns: ' . implode(', ', $campaignIds) . "\n";
echo "Ads scanned: " . count($adsResult['ads']) . "\n";
echo "Would change: {$wouldChange}\n";

foreach ($examples as $ex) {
    echo "  Example ad {$ex['ad']} ({$ex['campaign']}):\n";
    echo "    before: {$ex['before']}\n";
    echo "    after:  {$ex['after']}\n";
}

if ($apply && $pending !== []) {
    foreach (array_chunk($pending, BATCH_SIZE) as $chunk) {
        $payload = [];
        $metaById = [];
        foreach ($chunk as $item) {
            $meta = $item['_meta'];
            unset($item['_meta']);
            $payload[] = $item;
            $metaById[(int) $item['Id']] = $meta;
        }
        $result = directLibApi($token, 'ads', ['Ads' => $payload], $env, 'update');
        if (isset($result['error'])) {
            $errors += count($chunk);
            fwrite(STDERR, "ERROR batch update: {$result['error']}\n");
            usleep(BATCH_SLEEP_US);
            continue;
        }
        foreach ($result['UpdateResults'] ?? [] as $i => $ur) {
            $adId = (int) ($payload[$i]['Id'] ?? 0);
            if (!empty($ur['Errors'])) {
                $errors++;
                fwrite(STDERR, 'ERROR ad ' . $adId . ': ' . json_encode($ur['Errors'], JSON_UNESCAPED_UNICODE) . "\n");
                continue;
            }
            $updated++;
        }
        usleep(BATCH_SLEEP_US);
    }
    echo "Updated: {$updated}\n";
    echo "Errors: {$errors}\n";
}

exit($errors > 0 ? 2 : 0);

function kzFixTransformHref(string $href): ?string
{
    if (!str_contains(strtolower($href), 'mrt-lider.ru')) {
        return null;
    }
    $new = preg_replace('#^https?://(?:www\.)?mrt-lider\.ru#i', 'https://mrt-lider.kz', $href);
    if (!is_string($new) || $new === $href) {
        return null;
    }
    return $new;
}
