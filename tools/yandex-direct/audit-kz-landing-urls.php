#!/usr/bin/env php
<?php
/**
 * Audit KAZAKHSTAN_MRT ad links — flag mrt-lider.ru and missing .kz host.
 *
 * Usage: php audit-kz-landing-urls.php
 */

declare(strict_types=1);

$dir = __DIR__;
require_once $dir . '/bootstrap-env.php';
require_once $dir . '/direct-lib.php';

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

$campaignsResult = directLibApi($token, 'campaigns', [
    'SelectionCriteria' => ['States' => directLibWorkableCampaignStates()],
    'FieldNames' => ['Id', 'Name', 'State'],
], $env);

if (isset($campaignsResult['error'])) {
    fwrite(STDERR, "ERROR: {$campaignsResult['error']}\n");
    exit(1);
}

$campaigns = $campaignsResult['Campaigns'] ?? [];
$campaignIds = array_map(static fn(array $c): int => (int) ($c['Id'] ?? 0), $campaigns);
$campaignNames = [];
foreach ($campaigns as $c) {
    $campaignNames[(int) ($c['Id'] ?? 0)] = (string) ($c['Name'] ?? '');
}

if ($campaignIds === []) {
    echo "No workable campaigns.\n";
    exit(0);
}

$adsResult = directLibFetchAllAds($token, $campaignIds, $env);
if (isset($adsResult['error'])) {
    fwrite(STDERR, "ERROR: {$adsResult['error']}\n");
    exit(1);
}

$issues = 0;
echo str_repeat('-', 80) . "\n";
printf("%-10s %-30s %-8s %s\n", 'Campaign', 'Ad', 'State', 'Issue / Href');
echo str_repeat('-', 80) . "\n";

foreach ($adsResult['ads'] as $ad) {
    $href = (string) ($ad['TextAd']['Href'] ?? '');
    if ($href === '') {
        continue;
    }
    $campaignId = (int) ($ad['CampaignId'] ?? 0);
    $adId = (int) ($ad['Id'] ?? 0);
    $state = (string) ($ad['State'] ?? '');
    $issue = '';

    if (str_contains($href, 'mrt-lider.ru')) {
        $issue = 'wrong_host: .ru instead of .kz';
    } elseif (!str_contains($href, 'mrt-lider.kz')) {
        $issue = 'wrong_host: not mrt-lider.kz';
    } elseif (str_starts_with($href, 'http://')) {
        $issue = 'http_not_https';
    }

    if ($issue !== '') {
        $issues++;
        $cname = mb_substr($campaignNames[$campaignId] ?? (string) $campaignId, 0, 28);
        printf("%-10s %-30s %-8s %s\n", $cname, (string) $adId, $state, $issue);
        echo "  {$href}\n";
    }
}

echo str_repeat('-', 80) . "\n";
echo "Issues: {$issues}\n";
exit($issues > 0 ? 1 : 0);
