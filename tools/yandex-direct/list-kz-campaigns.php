#!/usr/bin/env php
<?php
/**
 * List campaigns in KAZAKHSTAN_MRT cabinet (connection test).
 *
 * Usage: php list-kz-campaigns.php
 */

declare(strict_types=1);

$dir = __DIR__;
require_once $dir . '/bootstrap-env.php';
require_once $dir . '/direct-lib.php';

$env = yandexDirectLoadEnv();
if ($env === null) {
    fwrite(STDERR, "ERROR: No credentials. Copy .env.example → .env or create wp-content/mrt-secrets/yandex-direct.env\n");
    exit(1);
}

$tokens = yandexDirectUniqueAccountTokens(['KAZAKHSTAN_MRT' => 'TOKEN_KAZAKHSTAN_MRT'], $env);
$token = $tokens['KAZAKHSTAN_MRT'] ?? '';
if ($token === '') {
    fwrite(STDERR, "ERROR: TOKEN_KAZAKHSTAN_MRT missing\n");
    exit(1);
}

$client = yandexDirectFetchClientInfo($token, $env);
if ($client !== null) {
    echo "Client: {$client['login']} ({$client['type']})\n";
} else {
    echo "WARN: could not fetch client info\n";
}

$result = directLibApi($token, 'campaigns', [
    'SelectionCriteria' => (object) [],
    'FieldNames' => ['Id', 'Name', 'State', 'Status', 'Type'],
], $env);

if (isset($result['error'])) {
    fwrite(STDERR, "ERROR: {$result['error']}\n");
    exit(1);
}

$campaigns = $result['Campaigns'] ?? [];
echo 'Campaigns: ' . count($campaigns) . "\n";
echo str_repeat('-', 72) . "\n";

usort($campaigns, static fn(array $a, array $b): int => strcmp((string) ($a['Name'] ?? ''), (string) ($b['Name'] ?? '')));

foreach ($campaigns as $campaign) {
    $id = (int) ($campaign['Id'] ?? 0);
    $name = (string) ($campaign['Name'] ?? '');
    $state = (string) ($campaign['State'] ?? '');
    $status = (string) ($campaign['Status'] ?? '');
    $type = (string) ($campaign['Type'] ?? '');
    echo sprintf("%-10d %-12s %-12s %-8s %s\n", $id, $state, $status, $type, $name);
}

exit(0);
