<?php
/**
 * Shared helpers for Yandex Direct CLI tools (reports API, money parsing).
 */

declare(strict_types=1);

const DIRECT_LIB_API_URL = 'https://api.direct.yandex.com/json/v5/';
const DIRECT_LIB_REPORTS_URL = 'https://api.direct.yandex.com/json/v5/reports';

/**
 * Кампании в работе: активные и остановленные. ARCHIVED и прочие — не трогаем.
 *
 * @return list<string>
 */
function directLibWorkableCampaignStates(): array
{
    return ['ON', 'SUSPENDED'];
}

/**
 * Объявления, которые можно читать и править. ARCHIVED — не трогаем.
 *
 * @return list<string>
 */
function directLibWorkableAdStates(): array
{
    return ['ON', 'OFF', 'SUSPENDED', 'OFF_BY_MONITORING'];
}

function directLibIsWorkableCampaignState(string $state): bool
{
    return in_array($state, directLibWorkableCampaignStates(), true);
}

function directLibIsWorkableAdState(string $state): bool
{
    return in_array($state, directLibWorkableAdStates(), true);
}

/** @return list<string> */
function directLibReportHeaders(string $token, array $env): array
{
    $headers = [
        'Authorization: Bearer ' . $token,
        'Accept-Language: ru',
        'Content-Type: application/json; charset=utf-8',
        'processingMode: auto',
        'returnMoneyInMicros: false',
        'skipReportHeader: true',
        'skipColumnHeader: false',
        'skipReportSummary: true',
    ];
    $clientLogin = trim($env['DIRECT_CLIENT_LOGIN'] ?? '');
    if ($clientLogin !== '') {
        $headers[] = 'Client-Login: ' . $clientLogin;
    }
    return $headers;
}

function directLibParseMoney(string $value): float
{
    $value = str_replace([' ', ','], ['', '.'], trim($value));
    if (!is_numeric($value)) {
        return 0.0;
    }
    $num = (float) $value;
    if ($num >= 1000000 && floor($num) == $num) {
        return $num / 1000000;
    }
    return $num;
}

function directLibParseFloat(string $value): float
{
    $value = str_replace(',', '.', trim($value));
    return is_numeric($value) ? (float) $value : 0.0;
}

/** @return array{summary:array<string,float|int>,rows:list<array<string,mixed>>}|array{error:string} */
function directLibFetchCampaignPerformance(string $token, array $env, int $days): array
{
    $dateTo = date('Y-m-d');
    $dateFrom = date('Y-m-d', strtotime("-{$days} days"));

    $body = [
        'params' => [
            'SelectionCriteria' => [
                'DateFrom' => $dateFrom,
                'DateTo' => $dateTo,
            ],
            'FieldNames' => [
                'CampaignId', 'CampaignName', 'Impressions', 'Clicks', 'Cost',
                'Conversions', 'Ctr', 'AvgCpc', 'CostPerConversion',
            ],
            'ReportName' => 'MRT Lider stop-list ' . date('Y-m-d H:i'),
            'ReportType' => 'CAMPAIGN_PERFORMANCE_REPORT',
            'DateRangeType' => 'CUSTOM_DATE',
            'Format' => 'TSV',
            'IncludeVAT' => 'YES',
            'IncludeDiscount' => 'NO',
        ],
    ];

    $tsv = directLibFetchReportTsv($token, $env, $body);
    if (isset($tsv['error'])) {
        return ['error' => $tsv['error']];
    }

    $rows = directLibParseCampaignTsv($tsv['content']);
    $summary = [
        'impressions' => 0,
        'clicks' => 0,
        'cost' => 0.0,
        'conversions' => 0.0,
    ];
    foreach ($rows as $row) {
        $summary['impressions'] += $row['impressions'];
        $summary['clicks'] += $row['clicks'];
        $summary['cost'] += $row['cost'];
        $summary['conversions'] += $row['conversions'];
    }

    return ['summary' => $summary, 'rows' => $rows];
}

/** @return array{content:string}|array{error:string} */
function directLibFetchReportTsv(string $token, array $env, array $body): array
{
    $json = json_encode($body, JSON_UNESCAPED_UNICODE);
    if ($json === false) {
        return ['error' => 'Failed to encode report request'];
    }

    $headers = directLibReportHeaders($token, $env);
    $maxAttempts = 30;

    for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
        $ch = curl_init(DIRECT_LIB_REPORTS_URL);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_TIMEOUT => 120,
        ]);
        $response = curl_exec($ch);
        if ($response === false) {
            $err = curl_error($ch);
            curl_close($ch);
            return ['error' => 'curl: ' . $err];
        }

        $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $rawHeaders = substr($response, 0, $headerSize);
        $content = substr($response, $headerSize);
        curl_close($ch);

        if ($httpCode === 200) {
            return ['content' => $content];
        }
        if ($httpCode === 201 || $httpCode === 202) {
            $retrySec = 5;
            if (preg_match('/retryIn:\s*(\d+)/i', $rawHeaders, $m)) {
                $retrySec = max(1, (int) $m[1]);
            }
            sleep($retrySec);
            continue;
        }

        return ['error' => "Report HTTP {$httpCode}: " . trim(substr($content, 0, 500))];
    }

    return ['error' => 'Report generation timeout'];
}

/** @return list<array<string,mixed>> */
function directLibParseCampaignTsv(string $tsv): array
{
    $lines = preg_split('/\r\n|\r|\n/', trim($tsv)) ?: [];
    if (count($lines) < 2) {
        return [];
    }

    $rows = [];
    foreach ($lines as $i => $line) {
        if ($i === 0 || str_starts_with($line, 'Total rows') || trim($line) === '') {
            continue;
        }
        $cols = explode("\t", $line);
        if (count($cols) < 6 || !is_numeric($cols[0])) {
            continue;
        }

        $rows[] = [
            'campaign_id' => (int) $cols[0],
            'campaign_name' => $cols[1],
            'impressions' => (int) str_replace(' ', '', $cols[2]),
            'clicks' => (int) str_replace(' ', '', $cols[3]),
            'cost' => directLibParseMoney($cols[4]),
            'conversions' => directLibParseFloat($cols[5]),
            'ctr' => isset($cols[6]) ? directLibParseFloat(str_replace('%', '', $cols[6])) : 0.0,
            'avg_cpc' => isset($cols[7]) ? directLibParseMoney($cols[7]) : 0.0,
            'cpa' => isset($cols[8]) ? directLibParseMoney($cols[8]) : 0.0,
        ];
    }

    return $rows;
}

/** @return array{ads:list<array<string,mixed>>}|array{error:string} */
function directLibFetchAllAds(string $token, array $campaignIds, array $env): array
{
    $allAds = [];
    $limit = 10000;
    $apiUrl = DIRECT_LIB_API_URL;

    foreach (array_chunk($campaignIds, 10) as $chunk) {
        $offset = 0;
        while (true) {
            $result = directLibApi($token, 'ads', [
                'SelectionCriteria' => [
                    'CampaignIds' => $chunk,
                    'States' => directLibWorkableAdStates(),
                ],
                'FieldNames' => ['Id', 'CampaignId', 'State', 'Type'],
                'TextAdFieldNames' => ['Href', 'Title', 'Text'],
                'Page' => ['Limit' => $limit, 'Offset' => $offset],
            ], $env);
            if (isset($result['error'])) {
                return ['error' => $result['error']];
            }
            $batch = $result['Ads'] ?? [];
            if ($batch === []) {
                break;
            }
            $allAds = array_merge($allAds, $batch);
            if (count($batch) < $limit) {
                break;
            }
            $offset += $limit;
        }
    }

    return ['ads' => $allAds];
}

/** @return array<string,mixed> */
function directLibApi(string $token, string $service, array $params, array $env, string $method = 'get'): array
{
    $url = DIRECT_LIB_API_URL . $service;
    $body = json_encode(['method' => $method, 'params' => $params], JSON_UNESCAPED_UNICODE);
    if ($body === false) {
        return ['error' => 'Failed to encode JSON request'];
    }

    $headers = [
        'Authorization: Bearer ' . $token,
        'Accept-Language: ru',
        'Content-Type: application/json; charset=utf-8',
    ];
    $clientLogin = trim($env['DIRECT_CLIENT_LOGIN'] ?? '');
    if ($clientLogin !== '') {
        $headers[] = 'Client-Login: ' . $clientLogin;
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $body,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 120,
    ]);
    $response = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        return ['error' => 'curl: ' . $curlError];
    }
    $data = json_decode($response, true);
    if (!is_array($data)) {
        return ['error' => "Invalid JSON (HTTP {$httpCode})"];
    }
    if (isset($data['error'])) {
        $err = $data['error'];
        $detail = $err['error_detail'] ?? ($err['error_string'] ?? json_encode($err, JSON_UNESCAPED_UNICODE));
        return ['error' => (string) $detail];
    }
    return $data['result'] ?? [];
}

function directLibExtractHref(array $ad): string
{
    return !empty($ad['TextAd']['Href']) ? trim((string) $ad['TextAd']['Href']) : '';
}

function directLibClassifyLandingUrl(string $href): string
{
    if (!str_contains($href, 'mrt-lider.ru')) {
        if (str_contains($href, 'med-sol.ru')) {
            return 'legacy_med_sol';
        }
        return 'external_host';
    }
    if (preg_match('#/services/[^/]+/#', $href)) {
        return 'service_landing';
    }
    if (preg_match('#mrt-lider\.ru/[^/]+/[^/]+/#', $href) && !str_contains($href, '/services/')) {
        return 'legacy_flat_service';
    }
    if (preg_match('#mrt-lider\.ru/[^/]+/?$#', $href)) {
        return 'city_home';
    }
    return 'other';
}

function directLibRecommendAction(string $account, array $row): string
{
    if ($account === 'KAZAKHSTAN_MRT') {
        if ($row['conversions'] > 0 && str_contains((string) ($row['campaign_name'] ?? ''), 'Поиск')) {
            return 'LAUNCH: поиск с посадочными mrt-lider.ru/{city}/ — не РСЯ';
        }
        if (str_contains((string) ($row['campaign_name'] ?? ''), 'РСЯ') || str_contains((string) ($row['campaign_name'] ?? ''), 'Ретаргет')) {
            return 'HOLD: РСЯ/ретаргет KZ — 0 conv, только поиск';
        }
        return 'AUDIT: fix-links mrt-lider.kz → mrt-lider.ru, затем resume';
    }
    if ($row['clicks'] >= 50 && $row['conversions'] <= 0) {
        return 'STOP или пересборка: много кликов без заявок — проверить посадочную /services/, минус-слова';
    }
    if ($row['cost'] >= 10000) {
        return 'STOP: высокий расход без конверсий';
    }
    return 'PAUSE + аудит: расход без заявок — audit-rk + fix-links --services-only';
}

/** Yandex Direct rejects some Unicode punctuation and currency symbols in Title/Text. */
function directLibSanitizeYandexAdField(string $text): string
{
    $text = str_replace(["\u{20B8}", "\u{20BD}", '₸'], ' тг', $text);
    $text = str_replace(["\u{2014}", "\u{2013}", "\u{2212}", '—', '–', '−'], '-', $text);
    $text = str_replace("\u{00B7}", '. ', $text);
    $text = str_replace('~', '', $text);
    $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

    return trim($text);
}
