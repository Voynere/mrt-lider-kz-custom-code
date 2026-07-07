<?php
/**
 * Load Yandex Direct .env from first available path.
 *
 * @return array<string, string>|null
 */
function yandexDirectLoadEnv(): ?array
{
    static $cached = null;
    if ($cached !== null) {
        return $cached === [] ? null : $cached;
    }

    $paths = [
        __DIR__ . '/.env',
        __DIR__ . '/../../wp-content/mrt-secrets/yandex-direct.env',
    ];

    foreach ($paths as $path) {
        $env = yandexDirectParseEnvFile($path);
        if ($env !== null) {
            $cached = $env;
            return $env;
        }
    }

    $cached = [];
    return null;
}

/** @return array<string, string>|null */
function yandexDirectParseEnvFile(string $path): ?array
{
    if (!is_file($path)) {
        return null;
    }

    $env = [];
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $env[trim($key)] = trim($value, " \t\"'");
    }

    return $env === [] ? null : $env;
}

/**
 * Resolve account tokens from env, skip duplicate OAuth tokens (same cabinet).
 *
 * @param array<string, string> $labelToEnvKey e.g. ['LIDER_MRT' => 'TOKEN_LIDER_MRT']
 * @param array<string, string> $env
 * @return array<string, string> label => token
 */
function yandexDirectUniqueAccountTokens(array $labelToEnvKey, array $env): array
{
    $result = [];
    $seen = [];

    foreach ($labelToEnvKey as $label => $key) {
        $token = trim($env[$key] ?? '');
        if ($token === '') {
            continue;
        }
        $hash = hash('sha256', $token);
        if (isset($seen[$hash])) {
            fwrite(STDERR, "WARN: {$label} — тот же OAuth-токен, что и {$seen[$hash]}; кабинет один, пропуск дубля.\n");
            continue;
        }
        $seen[$hash] = $label;
        $result[$label] = $token;
    }

    return $result;
}

/**
 * Direct API client info for token (Login, ClientId, Type).
 *
 * @return array{login:string,client_id:string,type:string}|null
 */
function yandexDirectFetchClientInfo(string $token, array $env = []): ?array
{
    $body = json_encode([
        'method' => 'get',
        'params' => ['FieldNames' => ['Login', 'ClientId', 'Type', 'ClientInfo']],
    ], JSON_THROW_ON_ERROR);

    $headers = [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json; charset=utf-8',
        'Accept-Language: ru',
    ];
    $clientLogin = trim($env['DIRECT_CLIENT_LOGIN'] ?? '');
    if ($clientLogin !== '') {
        $headers[] = 'Client-Login: ' . $clientLogin;
    }

    $ch = curl_init('https://api.direct.yandex.com/json/v5/clients');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $body,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
    ]);
    $raw = curl_exec($ch);
    if ($raw === false) {
        return null;
    }
    $data = json_decode($raw, true);
    $client = $data['result']['Clients'][0] ?? null;
    if (!is_array($client)) {
        return null;
    }

    return [
        'login' => (string) ($client['Login'] ?? ''),
        'client_id' => (string) ($client['ClientId'] ?? ''),
        'type' => (string) ($client['Type'] ?? ''),
    ];
}
