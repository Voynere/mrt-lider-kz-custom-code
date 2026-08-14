<?php
/**
 * KZ cities: replace Кларискан → Дотагита (weight tiers) and add
 * «Головной мозг + холестеатома» from the new MRT price list.
 *
 * Usage:
 *   php cli-update-kz-cholesteatoma-dotagita.php
 *   php cli-update-kz-cholesteatoma-dotagita.php --dry-run
 *   php cli-update-kz-cholesteatoma-dotagita.php --branch=almaty
 */
if (php_sapi_name() !== 'cli') {
    exit(1);
}

$wp_root = realpath(__DIR__ . '/../../..');
if (!$wp_root || !is_file($wp_root . '/wp-load.php')) {
    fwrite(STDERR, "WordPress root not found\n");
    exit(1);
}

require $wp_root . '/wp-load.php';

$dry_run = in_array('--dry-run', $argv, true);
$only_branch = '';
foreach (array_slice($argv, 1) as $arg) {
    if (str_starts_with($arg, '--branch=')) {
        $only_branch = sanitize_key(substr($arg, 9));
    }
}

$city_slugs = ['almaty', 'astana', 'karaganda', 'taldykorgan'];
if ($only_branch !== '') {
    if (!in_array($only_branch, $city_slugs, true)) {
        fwrite(STDERR, "Unknown branch slug: {$only_branch}\n");
        exit(1);
    }
    $city_slugs = [$only_branch];
}

const MRT_KZ_CHOL_PRICE = '40500';
const MRT_KZ_CHOL_DISCOUNT = '34500';
const MRT_KZ_DOTA_LT90 = '16000';
const MRT_KZ_DOTA_GE90 = '32000';

/**
 * @return WP_Term|null
 */
function mrt_kz_resolve_branch_term(string $slug): ?WP_Term {
    $term = get_term_by('slug', $slug, 'branch');
    if ($term instanceof WP_Term) {
        return $term;
    }

    $labels = [
        'almaty' => 'Алматы',
        'astana' => 'Астана',
        'karaganda' => 'Караганда',
        'taldykorgan' => 'Талдыкорган',
    ];
    if (!empty($labels[$slug])) {
        $term = get_term_by('name', $labels[$slug], 'branch');
        if ($term instanceof WP_Term) {
            return $term;
        }
    }

    return null;
}

/**
 * @return list<int>
 */
function mrt_kz_branch_service_ids(int $term_id): array {
    $ids = get_posts([
        'post_type' => 'service',
        'post_status' => ['publish', 'draft', 'pending', 'private'],
        'posts_per_page' => -1,
        'fields' => 'ids',
        'tax_query' => [[
            'taxonomy' => 'branch',
            'field' => 'term_id',
            'terms' => $term_id,
        ]],
    ]);

    return array_map('intval', $ids);
}

function mrt_kz_oblast(int $post_id): string {
    return trim((string) get_post_meta($post_id, 'si_oblast', true));
}

function mrt_kz_norm(string $s): string {
    $s = mb_strtolower($s, 'UTF-8');
    $s = str_replace(['ё', '®', '«', '»', '"', "'"], ['е', '', '', '', '', ''], $s);
    $s = preg_replace('/\s+/u', ' ', $s) ?? $s;
    return trim($s);
}

function mrt_kz_has_cholesteatoma(array $ids): bool {
    foreach ($ids as $id) {
        if (mb_stripos(mrt_kz_oblast($id), 'холестеатом', 0, 'UTF-8') !== false) {
            return true;
        }
    }
    return false;
}

/**
 * @return int|null template post id
 */
function mrt_kz_find_neurovascular_template(array $ids): ?int {
    foreach ($ids as $id) {
        $oblast = mrt_kz_norm(mrt_kz_oblast($id));
        if (str_contains($oblast, 'нейроваскуляр')) {
            return $id;
        }
    }
    foreach ($ids as $id) {
        $oblast = mrt_kz_norm(mrt_kz_oblast($id));
        if (str_contains($oblast, 'головной мозг') && str_contains($oblast, 'паркинсон')) {
            return $id;
        }
    }
    return null;
}

/**
 * @return int|null
 */
function mrt_kz_find_contrast_template(array $ids): ?int {
    foreach ($ids as $id) {
        $oblast = mrt_kz_norm(mrt_kz_oblast($id));
        if (str_contains($oblast, 'контрастирование') && (str_contains($oblast, 'гадовист') || str_contains($oblast, 'омнискан') || str_contains($oblast, 'кларискан') || str_contains($oblast, 'дотагит'))) {
            return $id;
        }
    }
    return null;
}

function mrt_kz_cholesteatoma_oblast_from_template(string $template_oblast): string {
    $trimmed = trim($template_oblast);
    $has_mrt_prefix = (bool) preg_match('/^мрт\s+/iu', $trimmed);

    if (preg_match('/нейроваскуляр/iu', $trimmed)) {
        $replacement = $has_mrt_prefix
            ? 'МРТ Головной мозг + холестеатома'
            : 'Головной мозг + холестеатома';
        return $replacement;
    }

    return $has_mrt_prefix
        ? 'МРТ Головной мозг + холестеатома'
        : 'Головной мозг + холестеатома';
}

function mrt_kz_clone_service(int $source_id, string $oblast, string $price, string $discount, bool $dry_run): int {
    $category = (string) get_post_meta($source_id, 'si_category', true);
    $type = (string) get_post_meta($source_id, 'si_type', true);
    $title = function_exists('si_truncate_for_title')
        ? si_truncate_for_title(($category !== '' ? $category . ' — ' : '') . $oblast)
        : mb_substr(($category !== '' ? $category . ' — ' : '') . $oblast, 0, 140, 'UTF-8');

    echo "  + create: {$oblast} | price={$price} discount={$discount}\n";
    if ($dry_run) {
        return 0;
    }

    $post_id = wp_insert_post([
        'post_type' => 'service',
        'post_title' => $title,
        'post_status' => 'publish',
        'post_name' => '',
    ], true);

    if (is_wp_error($post_id)) {
        fwrite(STDERR, '    ERROR create: ' . $post_id->get_error_message() . "\n");
        return 0;
    }

    $branches = wp_get_object_terms($source_id, 'branch', ['fields' => 'ids']);
    if (!is_wp_error($branches) && $branches) {
        wp_set_object_terms((int) $post_id, array_map('intval', $branches), 'branch', false);
    }

    $types = wp_get_object_terms($source_id, 'service_type', ['fields' => 'ids']);
    if (!is_wp_error($types) && $types) {
        wp_set_object_terms((int) $post_id, array_map('intval', $types), 'service_type', false);
    }

    update_post_meta((int) $post_id, 'si_category', $category);
    update_post_meta((int) $post_id, 'si_oblast', $oblast);
    update_post_meta((int) $post_id, 'si_price', $price);
    update_post_meta((int) $post_id, 'si_discount', $discount);
    if ($type !== '') {
        update_post_meta((int) $post_id, 'si_type', $type);
    }

    $code = (string) get_post_meta($source_id, 'si_code', true);
    if ($code !== '') {
        // Do not copy unique code; leave empty for new row.
    }

    return (int) $post_id;
}

function mrt_kz_rename_clariscan_to_dotagita(int $post_id, bool $dry_run): string {
    $oblast = mrt_kz_oblast($post_id);
    $new_oblast = preg_replace('/Кларискан/iu', 'Дотагита', $oblast) ?? $oblast;
    $new_oblast = trim(preg_replace('/\s+/u', ' ', $new_oblast) ?? $new_oblast);

    $norm = mrt_kz_norm($new_oblast);
    $price = (string) get_post_meta($post_id, 'si_price', true);
    $discount = (string) get_post_meta($post_id, 'si_discount', true);

    if (preg_match('/от\s*90/u', $norm)) {
        $price = MRT_KZ_DOTA_GE90;
        $discount = MRT_KZ_DOTA_GE90;
        if (!preg_match('/дотагита\s+от/u', $norm)) {
            $new_oblast = 'Дополнительное контрастирование: Дотагита от 90 кг';
        }
    } elseif (preg_match('/до\s*90/u', $norm)) {
        $price = MRT_KZ_DOTA_LT90;
        $discount = MRT_KZ_DOTA_LT90;
        if (!preg_match('/дотагита\s+до/u', $norm)) {
            $new_oblast = 'Дополнительное контрастирование: Дотагита до 90 кг';
        }
    } else {
        // Plain "Кларискан" → до 90 кг tier (companion "от 90" created separately).
        $new_oblast = 'Дополнительное контрастирование: Дотагита до 90 кг';
        $price = MRT_KZ_DOTA_LT90;
        $discount = MRT_KZ_DOTA_LT90;
    }

    $old_title = get_the_title($post_id);
    $category = (string) get_post_meta($post_id, 'si_category', true);
    $new_title = function_exists('si_truncate_for_title')
        ? si_truncate_for_title(($category !== '' ? $category . ' — ' : '') . $new_oblast)
        : mb_substr(($category !== '' ? $category . ' — ' : '') . $new_oblast, 0, 140, 'UTF-8');

    echo "  ~ rename #{$post_id}: {$oblast} → {$new_oblast} | {$price}\n";
    if ($dry_run) {
        return $new_oblast;
    }

    wp_update_post([
        'ID' => $post_id,
        'post_title' => $new_title !== '' ? $new_title : $old_title,
    ]);
    update_post_meta($post_id, 'si_oblast', $new_oblast);
    update_post_meta($post_id, 'si_price', $price);
    update_post_meta($post_id, 'si_discount', $discount);

    return $new_oblast;
}

function mrt_kz_ensure_dotagita_tiers(array $ids, ?int $template_id, bool $dry_run): void {
    $have_lt90 = false;
    $have_ge90 = false;
    foreach ($ids as $id) {
        $norm = mrt_kz_norm(mrt_kz_oblast($id));
        if (!str_contains($norm, 'дотагит')) {
            continue;
        }
        if (preg_match('/от\s*90/u', $norm)) {
            $have_ge90 = true;
        }
        if (preg_match('/до\s*90/u', $norm) || !preg_match('/от\s*90/u', $norm)) {
            $have_lt90 = true;
        }
    }

    if ($template_id === null) {
        echo "  ! no contrast template to clone Dotagita tiers\n";
        return;
    }

    if (!$have_lt90) {
        mrt_kz_clone_service(
            $template_id,
            'Дополнительное контрастирование: Дотагита до 90 кг',
            MRT_KZ_DOTA_LT90,
            MRT_KZ_DOTA_LT90,
            $dry_run
        );
    }
    if (!$have_ge90) {
        mrt_kz_clone_service(
            $template_id,
            'Дополнительное контрастирование: Дотагита от 90 кг',
            MRT_KZ_DOTA_GE90,
            MRT_KZ_DOTA_GE90,
            $dry_run
        );
    }
}

$stats = [
    'cities' => 0,
    'renamed' => 0,
    'created_chol' => 0,
    'created_dota' => 0,
];

foreach ($city_slugs as $slug) {
    $term = mrt_kz_resolve_branch_term($slug);
    if (!$term) {
        echo "SKIP {$slug}: branch term not found\n";
        continue;
    }

    $stats['cities']++;
    echo "\n=== {$slug} ({$term->name} #{$term->term_id})" . ($dry_run ? ' [DRY]' : '') . " ===\n";
    $ids = mrt_kz_branch_service_ids((int) $term->term_id);

    $clariscan_ids = [];
    foreach ($ids as $id) {
        if (mb_stripos(mrt_kz_oblast($id), 'Кларискан', 0, 'UTF-8') !== false) {
            $clariscan_ids[] = $id;
        }
    }

    foreach ($clariscan_ids as $id) {
        mrt_kz_rename_clariscan_to_dotagita($id, $dry_run);
        $stats['renamed']++;
    }

    // Refresh ids/oblasts after renames for tier ensure.
    $ids = mrt_kz_branch_service_ids((int) $term->term_id);
    $before = count($ids);
    $contrast_template = mrt_kz_find_contrast_template($ids);
    $dota_before = 0;
    foreach ($ids as $id) {
        if (str_contains(mrt_kz_norm(mrt_kz_oblast($id)), 'дотагит')) {
            $dota_before++;
        }
    }
    mrt_kz_ensure_dotagita_tiers($ids, $contrast_template, $dry_run);
    $ids = mrt_kz_branch_service_ids((int) $term->term_id);
    $dota_after = 0;
    foreach ($ids as $id) {
        if (str_contains(mrt_kz_norm(mrt_kz_oblast($id)), 'дотагит')) {
            $dota_after++;
        }
    }
    $stats['created_dota'] += max(0, $dota_after - $dota_before);

    if (!mrt_kz_has_cholesteatoma($ids)) {
        $template = mrt_kz_find_neurovascular_template($ids);
        if ($template === null) {
            echo "  ! no neurovascular template for cholesteatoma\n";
        } else {
            $oblast = mrt_kz_cholesteatoma_oblast_from_template(mrt_kz_oblast($template));
            mrt_kz_clone_service($template, $oblast, MRT_KZ_CHOL_PRICE, MRT_KZ_CHOL_DISCOUNT, $dry_run);
            $stats['created_chol']++;
        }
    } else {
        echo "  = cholesteatoma already present\n";
    }

    unset($before);
}

echo "\nDONE cities={$stats['cities']} renamed_clariscan={$stats['renamed']} created_chol={$stats['created_chol']} created_dota_delta={$stats['created_dota']}"
    . ($dry_run ? " (dry-run)\n" : "\n");

if (!$dry_run) {
    if (function_exists('mrt_bump_page_cache_version')) {
        mrt_bump_page_cache_version();
        echo "page cache version bumped\n";
    }
    if (function_exists('wp_cache_flush')) {
        wp_cache_flush();
        echo "wp_cache_flush done\n";
    }
    foreach (['/var/cache/pagespeed/cache.flush', '/var/cache/mod_pagespeed/cache.flush'] as $flush_file) {
        if (@touch($flush_file)) {
            echo "pagespeed flush touched: {$flush_file}\n";
        }
    }
}

exit(0);
