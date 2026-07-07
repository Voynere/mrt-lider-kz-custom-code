<?php
/**
 * MRT Lider KZ — Custom SEO Module (standalone, no AIOSEO)
 * 
 * Полная замена AIOSEO. Без внешних зависимостей.
 * 
 * Функции:
 * - Title, Description, Canonical, Robots
 * - Open Graph + Twitter Cards
 * - Schema.org JSON-LD (Organization, WebSite, MedicalClinic, BreadcrumbList, FAQPage, MedicalProcedure)
 * - Sitemap XML (индекс + подкарты)
 * - robots.txt
 * - Preconnect
 */

// ============================================================
//  SECURITY — убираем WordPress version из meta generator
// ============================================================
remove_action('wp_head', 'wp_generator');
add_filter('the_generator', '__return_empty_string');

// Force OPcache refresh for this file (re-deploy safety)
if (function_exists('opcache_invalidate')) {
    opcache_invalidate(__FILE__, true);
}

// Auto-flush rewrite rules once (on first load after deploy)
add_action('init', function() {
    if (get_option('mrt_rewrite_flush_kz_v1') !== '6') {
        flush_rewrite_rules(false);
        update_option('mrt_rewrite_flush_kz_v1', '6');
    }
}, 99);

// ============================================================
//  ГОРОДА — общий справочник (единый массив)
// ============================================================
function mrt_seo_get_cities() {
    return function_exists('mrt_get_city_map') ? mrt_get_city_map() : [];
}

/**
 * Reverse-поиск: имя города → slug.
 * Пример: mrt_get_city_slug_by_name('Тюмень') → 'tumen'
 * Регистронезависимый. Возвращает slug или '' если не найдено.
 */
function mrt_get_city_slug_by_name(string $name): string {
    $cities = mrt_seo_get_cities();
    $name_lower = mb_strtolower($name, 'UTF-8');
    foreach ($cities as $slug => $city_name) {
        if (mb_strtolower($city_name, 'UTF-8') === $name_lower) {
            return $slug;
        }
    }
    return '';
}

/**
 * Currency symbol by city: KZT for Kazakhstan cities, RUB for Russian.
 */
function mrt_seo_currency(string $city = ''): string {
    return '₸';
}

/**
 * Branch ratings from Яндекс Справочник (data/branch-ratings.json).
 * Returns ['rating' => 5.0, 'count' => 850] or null if not found.
 */
function mrt_seo_get_branch_rating(string $city = ''): ?array {
    static $ratings = null;
    if ($ratings === null) {
        $json_file = get_template_directory() . '/data/branch-ratings.json';
        if (file_exists($json_file)) {
            $data = json_decode(file_get_contents($json_file), true);
            $ratings = $data ?: [];
        } else {
            $ratings = [];
        }
    }
    if (!$city) $city = mrt_seo_current_city();
    return $ratings[$city] ?? null;
}

function mrt_seo_current_city() {
    return mrt_get_selected_city_slug();
}

/**
 * Get the page slug from URL (not WordPress post context).
 * For /novosibirsk/uslugi-i-ceny/ → returns 'uslugi-i-ceny'
 */
function mrt_seo_url_page_slug() {
    $cities = mrt_seo_get_cities();
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $path = parse_url($uri, PHP_URL_PATH);
    $path = trim((string) $path, '/');
    $parts = $path ? explode('/', $path) : [];
    // Skip city part and return the next segment
    if (count($parts) >= 2 && array_key_exists($parts[0], $cities)) {
        return $parts[1];
    }
    return '';
}

/**
 * Получить данные контактов города из ACF (адрес, телефон, часы работы).
 * Возвращает ['address' => '', 'telephone' => '', 'openingHours' => []] или пустой массив.
 */
function mrt_seo_get_city_contacts(string $city): array {
    static $cache = [];
    if (isset($cache[$city])) return $cache[$city];

    $city_name_map = mrt_seo_get_cities();
    $branch_name = $city_name_map[$city] ?? '';
    if (!$branch_name) { $cache[$city] = []; return []; }

    $args = [
        'post_type'      => 'post',
        'posts_per_page' => 1,
        'tax_query'      => [
            'relation' => 'AND',
            ['taxonomy' => 'category', 'field' => 'slug', 'terms' => $city],
            ['taxonomy' => 'category', 'field' => 'slug', 'terms' => 'contacty'],
        ],
    ];
    $q = new WP_Query($args);
    $result = ['address' => '', 'telephone' => '', 'openingHours' => []];

    if ($q->have_posts()) {
        $q->the_post();
        $pid = get_the_ID();

        // Адрес (первый непустой)
        $addresses = get_field('contacts_addresses', $pid) ?: [];
        for ($i = 1; $i <= 10; $i++) {
            $key = 'contacts_address_' . $i;
            if (!empty($addresses[$key])) {
                $result['address'] = $addresses[$key];
                break;
            }
        }

        // Телефон (первый непустой)
        $phones = get_field('contacts_phones', $pid) ?: [];
        for ($i = 1; $i <= 10; $i++) {
            $key = 'contacts_phone_' . $i;
            if (!empty($phones[$key])) {
                $raw = $phones[$key];
                // Убираем пробелы, скобки, дефисы для schema.org telephone
                $result['telephone'] = preg_replace('/[^\d+]/', '', $raw);
                if (strpos($result['telephone'], '+') !== 0) {
                    $result['telephone'] = '+7' . $result['telephone'];
                }
                break;
            }
        }

        // Часы работы
        $hours = get_field('contacts_opening_hours', $pid) ?: [];
        $hours_str = $hours['contacts_opening_days'] ?? '';
        if ($hours_str) {
            $result['openingHours'] = [$hours_str];
        }
        for ($i = 1; $i <= 10; $i++) {
            $key = 'contacts_opening_hours_' . $i;
            if (!empty($hours[$key])) {
                $result['openingHours'][] = $hours[$key];
            }
        }

        wp_reset_postdata();
    }

    $cache[$city] = $result;
    return $result;
}

function mrt_seo_is_city_home() {
    $cities = mrt_seo_get_cities();
    $request_uri = $_SERVER['REQUEST_URI'] ?? '/';
    $path = parse_url($request_uri, PHP_URL_PATH);
    $path = trim((string) $path, '/');
    $parts = $path ? explode('/', $path) : [];
    return !empty($parts[0]) && array_key_exists($parts[0], $cities) && count($parts) <= 1;
}

function mrt_seo_is_animals_context(): bool {
    return function_exists('mrt_is_animals_branch') && mrt_is_animals_branch(mrt_seo_current_city());
}

function mrt_seo_animals_home_title(): string {
    return 'МРТ для животных в Алматы — от 50 000 ₸ | MRI Animal';
}

function mrt_seo_animals_home_description(): string {
    return 'MRI Animal — мрт животным. Ветеринарное МРТ для собак и кошек. Philips 1,5 Т, заключение в день. с. Отеген батыра, ул. Аубакирова 17/1. Запись онлайн и WhatsApp.';
}


/**
 * Проверяет, является ли текущая страница дочерней от «uslugi-i-ceny».
 * Обрабатывает URL вида /{city}/uslugi-i-ceny/price/{service}/
 */
function mrt_seo_is_uslugi_child() {
    if (!is_page()) return false;
    $ancestors = get_post_ancestors(get_the_ID());
    foreach ($ancestors as $ancestor_id) {
        $ancestor = get_post($ancestor_id);
        if ($ancestor && $ancestor->post_name === 'uslugi-i-ceny') return true;
    }
    return false;
}

function mrt_seo_city_genitive($slug) {
    $map = [
        'almaty' => 'Алматы',
        'astana' => 'Астане',
        'karaganda' => 'Караганде',
        'taldykorgan' => 'Талдыкоргане',
        'almaty_aubakirova' => 'Отеген батыра',
    ];
    return $map[$slug] ?? ($slug ?: 'Алматы');
}

/**
 * Предлог «в» или «во» перед названием города в предложном падеже.
 * Правило: «во» перед словами на «в» или «ф» (во Владивостоке, во Фрунзе).
 */
function mrt_seo_city_in(string $city_genitive): string {
    $first = mb_strtolower(mb_substr($city_genitive, 0, 1, 'UTF-8'), 'UTF-8');
    return ($first === 'в' || $first === 'ф') ? 'во' : 'в';
}

// ============================================================
// 1b. CITY-SPECIFIC DATA — уникальный контент per город
// ============================================================
function mrt_seo_city_branch_count($slug) {
    global $wpdb;
    $count = wp_cache_get('mrt_branch_count_' . $slug, 'mrt_seo');
    if ($count !== false) return $count;
    $count = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(DISTINCT p.ID) FROM {$wpdb->posts} p
         INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
         INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
         INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
         WHERE p.post_type = 'service' AND p.post_status = 'publish'
         AND tt.taxonomy = 'branch' AND t.slug = %s",
        $slug
    ));
    wp_cache_set('mrt_branch_count_' . $slug, $count, 'mrt_seo', 3600);
    return $count;
}

function mrt_seo_city_meta($slug) {
    $cities = mrt_seo_get_cities();
    $city_name = $cities[$slug] ?? '';
    $city_gen = mrt_seo_city_genitive($slug);
    $branches = mrt_seo_city_branch_count($slug);
    $mri_brand = function_exists('mrt_city_mri_equipment_brand') ? mrt_city_mri_equipment_brand($slug) : 'Siemens';

    $home_phrases = [
        "Современные томографы {$mri_brand}, опытные врачи-рентгенологи",
        'Оборудование экспертного класса, быстрые результаты',
        "Диагностика на аппаратах нового поколения {$mri_brand}",
        'Высокоточные исследования в комфортных условиях',
        'Профессиональная диагностика, современное оборудование',
        'Квалифицированные специалисты, передовое оборудование',
    ];
    $branch_features = function_exists('mrt_city_branch_features') ? mrt_city_branch_features($slug) : ['oms' => true, 'dms' => true];
    $svc_phrases = [
        'Актуальные цены, запись онлайн',
        !empty($branch_features['oms'])
            ? 'Скидки пенсионерам, ОМС, быстрая запись'
            : 'Скидки пенсионерам, быстрая запись',
        'Без очередей, результаты за час',
        'Удобное время, онлайн-запись',
        'Доступные цены, комфортные условия',
    ];
    $idx_home = crc32($slug) % count($home_phrases);
    $idx_svc = crc32($slug . 'svc') % count($svc_phrases);

    return [
        'home_desc' => "МРТ диагностика в {$city_gen}. {$home_phrases[$idx_home]}. {$branches} филиалов, запись онлайн.",
        'services_desc' => "Полный прайс-лист МРТ, КТ, УЗИ и денситометрии в {$city_gen}. {$svc_phrases[$idx_svc]}. {$branches} центров МРТ Лидер.",
        'contacts_desc' => "Адреса, телефоны и режим работы {$branches} МРТ центров в {$city_gen}. Как добраться, запись на исследование в МРТ Лидер.",
        'specialists_desc' => "Врачи-рентгенологи и специалисты МРТ центров в {$city_gen}. Опытные доктора, современное оборудование.",
        'faq_desc' => "Ответы на частые вопросы о МРТ, КТ, УЗИ в {$city_gen}. Подготовка, противопоказания, результаты.",
        'branch_count' => $branches,
    ];
}

function mrt_seo_service_type_label($post_id) {
    $terms = wp_get_post_terms($post_id, 'service_type', ['orderby' => 'name', 'order' => 'ASC']);
    if (!empty($terms) && !is_wp_error($terms)) {
        return $terms[0]->name;
    }
    return '';
}

// ============================================================
// 1. TITLE — динамические заголовки
// ============================================================
add_filter('document_title_parts', 'mrt_seo_title_parts', 20);
function mrt_seo_title_parts($title) {
    $cities = mrt_seo_get_cities();
    $city = mrt_seo_current_city();
    $city_name = $cities[$city] ?? '';

    // Helper: checks if a page is one of the known city subpages
    $is_city_subpage = function($slug) { return mrt_seo_url_page_slug() === $slug; };
    $page_slug = mrt_seo_url_page_slug();
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    $path_parts = explode('/', trim((string) parse_url($uri, PHP_URL_PATH), '/'));
    $is_price_child = count($path_parts) >= 4 && ($path_parts[1] ?? '') === 'uslugi-i-ceny';

    if (mrt_seo_is_animals_context() && mrt_seo_is_city_home()) {
        $title['title'] = mrt_seo_animals_home_title();
        $title['tagline'] = '';
        $title['site'] = '';
        return $title;
    }
    if (mrt_seo_is_animals_context() && $page_slug === 'kontakty') {
        $title['title'] = 'Контакты MRI Animal — мрт животным | МРТ Лидер';
        $title['tagline'] = ''; $title['site'] = '';
        return $title;
    }
    if (mrt_seo_is_animals_context() && $page_slug === 'uslugi-i-ceny') {
        $title['title'] = 'Цены на МРТ для животных — MRI Animal | МРТ Лидер';
        $title['tagline'] = ''; $title['site'] = '';
        return $title;
    }
    if (mrt_seo_is_city_home() && $city_name) {
        $city_gen = mrt_seo_city_genitive($city);
        $title['title'] = "МРТ в {$city_gen} — сеть диагностических центров МРТ Лидер";
        $title['tagline'] = '';
        $title['site'] = '';
        return $title;
    }
    if (($is_city_subpage('uslugi-i-ceny') && !$is_price_child) && $city_name) {
        $city_gen = mrt_seo_city_genitive($city);
        $title['title'] = "Услуги и цены на МРТ в {$city_gen} — МРТ Лидер";
        $title['tagline'] = ''; $title['site'] = '';
    }
    // Price children (/{city}/uslugi-i-ceny/price/{service}/)
    if ($is_price_child && $city_name) {
        $service_title = get_the_title();
        $city_gen = mrt_seo_city_genitive($city);
        $title['title'] = "{$service_title} в {$city_gen} — цены, запись онлайн | МРТ Лидер";
        $title['tagline'] = ''; $title['site'] = '';
    }
    if ($is_city_subpage('specialisty') && $city_name) {
        $city_gen = mrt_seo_city_genitive($city);
        $title['title'] = "Специалисты МРТ в {$city_gen} — врачи МРТ Лидер";
        $title['tagline'] = ''; $title['site'] = '';
    }
    if ($is_city_subpage('kontakty') && $city_name) {
        $city_gen = mrt_seo_city_genitive($city);
        $title['title'] = "Контакты МРТ центров в {$city_gen} — адреса, телефоны | МРТ Лидер";
        $title['tagline'] = ''; $title['site'] = '';
    }
    if ($is_city_subpage('vopros-otvet') && $city_name) {
        $city_gen = mrt_seo_city_genitive($city);
        $title['title'] = "Вопросы и ответы о МРТ в {$city_gen} — МРТ Лидер";
        $title['tagline'] = ''; $title['site'] = '';
    }
    if (is_singular('service') && $city_name) {
        $mrt_city = get_query_var('mrt_city') ?: $city;
        $city_for_title = $cities[$mrt_city] ?? $city_name;
        $type_label = mrt_seo_service_type_label(get_the_ID());
        $suffix = $type_label ? " — {$type_label}" : '';
        $title['title'] = get_the_title() . " в {$city_for_title}{$suffix} | МРТ Лидер";
        $title['tagline'] = ''; $title['site'] = '';
    }
    // Child pages of uslugi-i-ceny (/{city}/uslugi-i-ceny/price/{service}/)
    if (is_page() && mrt_seo_is_uslugi_child() && $city_name) {
        $service_title = get_the_title();
        $title['title'] = "{$service_title} в {$city_name} — цены, запись онлайн | МРТ Лидер";
        $title['tagline'] = ''; $title['site'] = '';
    }
    // Service landing page (/{city}/services/{slug}/)
    if (mrt_seo_is_service_landing() && $city_name) {
        $svc = get_page_by_path(get_query_var('mrt_service_landing'), OBJECT, 'service');
        if ($svc) {
            $svc_title = mrt_get_service_oblast_display($svc->ID);
            $city_gen = mrt_seo_city_genitive($city);
            $title['title'] = "{$svc_title} в {$city_gen} — цена, запись онлайн | МРТ Лидер";
            $title['tagline'] = ''; $title['site'] = '';
        }
    }
    // Articles (blog)
    if (is_singular('article')) {
        $title['title'] = get_the_title() . ' — статья о МРТ | МРТ Лидер';
        $title['tagline'] = ''; $title['site'] = '';
    }
    if (is_post_type_archive('article')) {
        $title['title'] = 'Статьи о МРТ и диагностике — блог МРТ Лидер';
        $title['tagline'] = ''; $title['site'] = '';
    }
    return $title;
}

// Backup: pre_get_document_title (runs before document_title_parts)
add_filter('pre_get_document_title', 'mrt_seo_pre_title', 20);
function mrt_seo_pre_title($title) {
    $cities = mrt_seo_get_cities();
    $city = mrt_seo_current_city();
    $city_name = $cities[$city] ?? '';
    if (!$city_name) return $title;

    $page_slug = mrt_seo_url_page_slug();
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    $path = parse_url($uri, PHP_URL_PATH);
    $path_parts = explode('/', trim((string) $path, '/'));
    // If path has 4+ parts (/city/uslugi-i-ceny/price/uzi/) — it's a child service
    $is_price_child = count($path_parts) >= 4;

    // Subcategory pages (/city/uslugi-i-ceny/mrt-golovy/)
    $subcat = get_query_var('mrt_subcat');
    // Also check URL as fallback (query var may be empty on first load)
    if (!$subcat) {
        $uri2 = $_SERVER['REQUEST_URI'] ?? '';
        $path2 = trim(parse_url($uri2, PHP_URL_PATH), '/');
        $parts2 = explode('/', $path2);
        if (count($parts2) >= 3 && $parts2[1] === 'uslugi-i-ceny' && $parts2[2] !== 'price') {
            $subcat = $parts2[2];
        }
    }
    if ($subcat && strpos($subcat, 'price_') !== 0) {
        $subcat_titles = [
            'mrt' => 'МРТ',
            'mrt-golovy' => 'МРТ Головы', 'mrt-pozvonochnika' => 'МРТ Позвоночника',
            'mrt-sustavov' => 'МРТ Суставов', 'mrt-brushnoy-polosti' => 'МРТ Брюшной полости',
            'mrt-malogo-taza' => 'МРТ Малого таза', 'mrt-serdca-i-sosudov' => 'МРТ Сердца и сосудов',
            'mrt-grudnoy-kletki' => 'МРТ Грудной клетки', 'mrt-myagkih-tkaney' => 'МРТ Мягких тканей',
            'kt' => 'КТ', 'kt-golovy' => 'КТ Головы', 'kt-pozvonochnika' => 'КТ Позвоночника',
            'kt-sustavov' => 'КТ Суставов и костей', 'kt-brushnoy-polosti' => 'КТ Брюшной полости',
            'kt-malogo-taza' => 'КТ Малого таза', 'kt-serdca-i-sosudov' => 'КТ Сердца и сосудов',
            'kt-grudnoy-kletki' => 'КТ Грудной клетки', 'kt-myagkih-tkaney' => 'КТ Мягких тканей',
            'kt-gortani' => 'КТ Гортани',
            'kt-kompleksnye' => 'КТ Комплексные программы',
            'uzi' => 'УЗИ', 'uzi-golovy' => 'УЗИ Головы и шеи',
            'uzi-serdca-i-sosudov' => 'УЗИ Сердца и сосудов',
            'uzi-brushnoy-polosti' => 'УЗИ Брюшной полости',
            'uzi-malogo-taza' => 'УЗИ Малого таза',
            'uzi-molochnykh-zhelez' => 'УЗИ Молочных желез',
            'uzi-grudnoy-kletki' => 'УЗИ Грудной клетки',
            'uzi-sustavov' => 'УЗИ Суставов', 'uzi-myagkih-tkaney' => 'УЗИ Мягких тканей',
            'uzi-nervnoj-sistemy' => 'УЗИ Нервной системы',
            'densitometriya' => 'Денситометрия',
        ];
        $cat_name = $subcat_titles[$subcat] ?? $subcat;
        $tomograph_type = $_GET['type'] ?? '';
        $type_suffix_title = ($tomograph_type === '1.5' || $tomograph_type === '3') ? ' ' . $tomograph_type . ' Тесла' : '';
        $city_gen = mrt_seo_city_genitive($city);
        return "{$cat_name}{$type_suffix_title} в {$city_gen} — цены, запись онлайн | МРТ Лидер";
    }

    if (mrt_seo_is_animals_context() && mrt_seo_is_city_home()) {
        return mrt_seo_animals_home_title();
    }
    if (mrt_seo_is_animals_context() && $page_slug === 'kontakty') {
        return 'Контакты MRI Animal — мрт животным | МРТ Лидер';
    }
    if (mrt_seo_is_animals_context() && $page_slug === 'uslugi-i-ceny') {
        return 'Цены на МРТ для животных — MRI Animal | МРТ Лидер';
    }
    if (mrt_seo_is_city_home()) {
        $city_gen = mrt_seo_city_genitive($city);
        return "МРТ в {$city_gen} — сеть диагностических центров МРТ Лидер";
    }
    // Only match uslugi-i-ceny if it's NOT a price child
    if ($page_slug === 'uslugi-i-ceny' && !$is_price_child) {
        $city_gen = mrt_seo_city_genitive($city);
        return "Услуги и цены на МРТ в {$city_gen} — МРТ Лидер";
    }
    if ($page_slug === 'specialisty' || $page_slug === 'kontakty' || $page_slug === 'vopros-otvet') {
        $city_gen = mrt_seo_city_genitive($city);
        $labels = ['specialisty' => "Специалисты МРТ в {$city_gen} — врачи МРТ Лидер",
                   'kontakty' => "Контакты МРТ центров в {$city_gen} — адреса, телефоны | МРТ Лидер",
                   'vopros-otvet' => "Вопросы и ответы о МРТ в {$city_gen} — МРТ Лидер"];
        return $labels[$page_slug] ?? $title;
    }
    // Price children (uslugi-i-ceny/price/...) or any child page
    if ($is_price_child || mrt_seo_is_uslugi_child()) {
        $city_gen = mrt_seo_city_genitive($city);
        return get_the_title() . " в {$city_gen} — цены, запись онлайн | МРТ Лидер";
    }
    if (is_singular('service')) {
        $mrt_city = get_query_var('mrt_city') ?: $city;
        $city_svc = $cities[$mrt_city] ?? $city_name;
        return get_the_title() . " в {$city_svc} | МРТ Лидер";
    }
    // Service landing page (/{city}/services/{slug}/)
    if (mrt_seo_is_service_landing() && $city_name) {
        $svc = get_page_by_path(get_query_var('mrt_service_landing'), OBJECT, 'service');
        if ($svc) {
            $svc_title = mrt_get_service_oblast_display($svc->ID);
            $city_gen = mrt_seo_city_genitive($city);
            return "{$svc_title} в {$city_gen} — цена, запись онлайн | МРТ Лидер";
        }
    }
    return $title;
}

// ============================================================
// 2. META TAGS — description, canonical, robots, OG, Twitter, verification
// ============================================================
add_action('wp_head', 'mrt_seo_meta_tags', 1);
function mrt_seo_meta_tags() {
    $cities = mrt_seo_get_cities();
    $city = mrt_seo_current_city();
    $city_name = $cities[$city] ?? '';
    $city_genitive = mrt_seo_city_genitive($city);

    // --- Description ---
    $description = '';
    $city_meta = $city_name ? mrt_seo_city_meta($city) : null;
    $page_slug = mrt_seo_url_page_slug();

    $uri = $_SERVER['REQUEST_URI'] ?? '';
    $path_parts = explode('/', trim((string) parse_url($uri, PHP_URL_PATH), '/'));
    $is_price_child = count($path_parts) >= 4 && ($path_parts[1] ?? '') === 'uslugi-i-ceny';
    $subcat_meta = get_query_var('mrt_subcat');

    if (mrt_seo_is_animals_context() && mrt_seo_is_city_home()) {
        $description = mrt_seo_animals_home_description();
    } elseif (mrt_seo_is_city_home() && $city_meta) {
        $description = $city_meta['home_desc'];
    } elseif ($subcat_meta && strpos($subcat_meta, 'price_') !== 0) {
        $sub_descs = [
            'mrt-golovy' => 'МРТ головного мозга, гипофиза, орбит, придаточных пазух носа, онкопоиск, комплексные программы',
            'mrt-pozvonochnika' => 'МРТ шейного, грудного, поясничного отделов позвоночника',
            'mrt-sustavov' => 'МРТ коленного, плечевого, тазобедренного суставов',
            'mrt-brushnoy-polosti' => 'МРТ органов брюшной полости',
            'mrt-malogo-taza' => 'МРТ органов малого таза',
            'mrt-serdca-i-sosudov' => 'МРТ сердца и сосудов',
            'mrt-grudnoy-kletki' => 'МРТ органов грудной клетки: легкие, средостение, молочные железы',
            'mrt-myagkih-tkaney' => 'МРТ мягких тканей, мышц, связок, сухожилий',
            'kt' => 'Компьютерная томография',
            'kt-golovy' => 'КТ головного мозга, лицевого черепа, придаточных пазух носа, орбит',
            'kt-pozvonochnika' => 'КТ шейного, грудного, поясничного отделов позвоночника',
            'kt-sustavov' => 'КТ суставов и костей',
            'kt-brushnoy-polosti' => 'КТ органов брюшной полости и забрюшинного пространства',
            'kt-malogo-taza' => 'КТ органов малого таза',
            'kt-serdca-i-sosudov' => 'КТ сердца и сосудов',
            'kt-grudnoy-kletki' => 'КТ органов грудной клетки',
            'kt-myagkih-tkaney' => 'КТ мягких тканей',
            'kt-gortani' => 'КТ гортани: структуры гортани, надгортанник, хрящи, голосовые связки',
            'kt-kompleksnye' => 'КТ комплексные программы',
            'uzi' => 'УЗИ органов и систем',
            'uzi-golovy' => 'УЗИ головы и шеи: щитовидная железа, брахиоцефальные артерии',
            'uzi-serdca-i-sosudov' => 'УЗИ сердца и сосудов: эхокардиография, дуплексное сканирование',
            'uzi-brushnoy-polosti' => 'УЗИ органов брюшной полости и забрюшинного пространства',
            'uzi-malogo-taza' => 'УЗИ органов малого таза',
            'uzi-molochnykh-zhelez' => 'УЗИ молочных желез',
            'uzi-sustavov' => 'УЗИ суставов',
            'uzi-myagkih-tkaney' => 'УЗИ мягких тканей и лимфатических узлов',
            'uzi-nervnoj-sistemy' => 'УЗИ периферической нервной системы',
            'densitometriya' => 'Денситометрия'];        $sub_d = $sub_descs[$subcat_meta] ?? '';
        $description = "{$sub_d} в {$city_genitive}. Актуальные цены, запись онлайн. Центры МРТ Лидер в {$city_genitive}.";
    } elseif ($page_slug === 'uslugi-i-ceny' && !$is_price_child && $city_meta) {
        $stats = mrt_seo_city_service_stats($city);
        $cur = mrt_seo_currency($city);
        $price_range = '';
        if ($stats['price_min'] && $stats['price_max']) {
            $price_range = " Цены от {$stats['price_min']} {$cur} до {$stats['price_max']} {$cur}.";
        } elseif ($stats['price_min']) {
            $price_range = " Цены от {$stats['price_min']} {$cur}.";
        }
        $type_list = !empty($stats['types']) ? ' ' . implode(', ', array_slice($stats['types'], 0, 5)) . ' и другие исследования.' : '';
        $description = "Услуги и цены на МРТ, КТ, УЗИ и денситометрию в {$city_genitive}. {$stats['count']} видов диагностики.{$price_range}{$type_list} Запись онлайн, сеть центров МРТ Лидер.";
    } elseif ($page_slug === 'specialisty' && $city_meta) {
        $description = $city_meta['specialists_desc'];
    } elseif ($page_slug === 'kontakty' && $city_meta) {
        $description = $city_meta['contacts_desc'];
    } elseif ($page_slug === 'vopros-otvet' && $city_meta) {
        $description = $city_meta['faq_desc'];
    } elseif (is_post_type_archive('article')) {
        $description = 'Статьи о МРТ, КТ, УЗИ и диагностике. Полезные советы, подготовка к исследованиям. Запись онлайн, сеть центров МРТ Лидер.';
    } elseif (is_singular('article')) {
        $title = get_the_title();
        $description = "{$title} — статья о МРТ и диагностике. Полезная информация о МРТ, КТ, УЗИ. Запись онлайн, сеть центров МРТ Лидер.";
    } elseif (is_singular('service') && $city_name) {
        $mrt_city = get_query_var('mrt_city') ?: $city;
        $city_gen_svc = mrt_seo_city_genitive($mrt_city);
        $branches_svc = mrt_seo_city_branch_count($mrt_city);
        $service_price = get_field('service_price', get_the_ID());
        $service_type = mrt_seo_service_type_label(get_the_ID());
        $cur_svc = mrt_seo_currency($mrt_city);
        $price_str = ($service_price && is_numeric($service_price)) ? " от {$service_price} {$cur_svc}." : '.';
        $type_str = $service_type ? " ({$service_type})" : '';
        $mri_brand = function_exists('mrt_city_mri_equipment_brand') ? mrt_city_mri_equipment_brand($mrt_city) : 'Siemens';
        $branch_features = function_exists('mrt_city_branch_features') ? mrt_city_branch_features($mrt_city) : ['oms' => true, 'dms' => true];
        $insurance_label = function_exists('mrt_city_insurance_label') ? mrt_city_insurance_label($branch_features) : 'ОМС и ДМС';
        $insurance_meta = $insurance_label !== '' ? "{$insurance_label}, " : '';
        $svc_phrases = [
            "Быстрая запись онлайн, результаты за 1 час. {$branches_svc} центров МРТ Лидер в {$city_gen_svc}.",
            "Скидки пенсионерам и льготникам. {$branches_svc} филиалов сети МРТ Лидер в {$city_gen_svc}.",
            "Современное оборудование {$mri_brand}, опытные врачи-рентгенологи. {$branches_svc} центров МРТ Лидер.",
            "Приём без очередей, удобное время записи. {$branches_svc} филиалов МРТ Лидер в {$city_gen_svc}.",
            "{$insurance_meta}онлайн-запись. Результаты за 1 час. {$branches_svc} диагностических центров в {$city_gen_svc}.",
        ];
        $svc_idx = crc32($mrt_city . get_the_ID()) % count($svc_phrases);
        $description = get_the_title() . "{$type_str} в {$city_gen_svc}{$price_str} {$svc_phrases[$svc_idx]}";
    } elseif (is_page() && mrt_seo_is_uslugi_child() && $city_name) {
        $service_title = get_the_title();
        $branches = mrt_seo_city_branch_count($city);
        $mri_brand = function_exists('mrt_city_mri_equipment_brand') ? mrt_city_mri_equipment_brand($city) : 'Siemens';
        $description = "{$service_title} в {$city_genitive}. Запись онлайн, современное оборудование {$mri_brand}. {$branches} центров МРТ Лидер в {$city_genitive}.";
    } elseif (mrt_seo_is_service_landing() && $city_name) {
        $svc = get_page_by_path(get_query_var('mrt_service_landing'), OBJECT, 'service');
        if ($svc) {
            $svc_title = mrt_get_service_oblast_display($svc->ID);
            $svc_price = get_post_meta($svc->ID, 'si_price', true);
            $cur_landing = mrt_seo_currency($city);
            $price_str = ($svc_price && is_numeric($svc_price)) ? ' Цена: ' . number_format((int)$svc_price, 0, '', ' ') . ' ' . $cur_landing . '.' : '';
            $branches = mrt_seo_city_branch_count($city);
            $mri_brand = function_exists('mrt_city_mri_equipment_brand') ? mrt_city_mri_equipment_brand($city) : 'Siemens';
            $description = "{$svc_title} в {$city_genitive}.{$price_str} Запись онлайн, современное оборудование {$mri_brand}. {$branches} центров МРТ Лидер в {$city_genitive}.";
        }
    } elseif (is_singular()) {
        $description = wp_trim_words(get_the_excerpt() ?: get_the_content(), 30, '...');
    }
    if ($description) {
        echo '<meta name="description" content="' . esc_attr($description) . '" />' . "\n";
    }

    // --- Canonical ---
    $canonical = mrt_seo_canonical_url();
    echo '<link rel="canonical" href="' . esc_url($canonical) . '" />' . "\n";

    // --- Open Graph ---
    $og_title = mrt_seo_og_title($city_name);
    $og_desc = $description ?: get_bloginfo('description');
    $og_image = mrt_seo_og_image();
    $og_img_dims = mrt_seo_og_image_dims();

    echo '<meta property="og:locale" content="ru_RU" />' . "\n";
    echo '<meta property="og:site_name" content="МРТ Лидер — диагностический центр" />' . "\n";
    echo '<meta property="og:type" content="website" />' . "\n";
    echo '<meta property="og:title" content="' . esc_attr($og_title) . '" />' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($og_desc) . '" />' . "\n";
    echo '<meta property="og:url" content="' . esc_url($canonical) . '" />' . "\n";
    echo '<meta property="og:image" content="' . esc_url($og_image) . '" />' . "\n";
    if ($og_img_dims) {
        echo '<meta property="og:image:width" content="' . $og_img_dims[0] . '" />' . "\n";
        echo '<meta property="og:image:height" content="' . $og_img_dims[1] . '" />' . "\n";
    }
    // --- Twitter Cards ---
    echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
    echo '<meta name="twitter:title" content="' . esc_attr($og_title) . '" />' . "\n";
    echo '<meta name="twitter:description" content="' . esc_attr($og_desc) . '" />' . "\n";
    echo '<meta name="twitter:image" content="' . esc_url($og_image) . '" />' . "\n";

    // --- Hreflang для городов (антиканнибализация) ---
    if (is_singular('service')) {
        $service_title = get_the_title();
        $sibling_ids = get_posts([
            'post_type' => 'service',
            'post_status' => 'publish',
            'title' => $service_title,
            'posts_per_page' => -1,
            'fields' => 'ids',
        ]);
        if (!empty($sibling_ids)) {
            $hreflang_by_city = [];
            foreach ($sibling_ids as $sid) {
                $branches = wp_get_post_terms($sid, 'branch', ['fields' => 'slugs']);
                if (!empty($branches) && !is_wp_error($branches) && array_key_exists($branches[0], $cities)) {
                    $city_slug = $branches[0];
                    // Keep only one URL per city (prefer current post)
                    if ($sid === get_the_ID() || !isset($hreflang_by_city[$city_slug])) {
                        $slug = get_post_field('post_name', $sid);
                        $hreflang_by_city[$city_slug] = $slug;
                    }
                }
            }
            foreach ($hreflang_by_city as $h_city => $h_slug) {
                echo '<link rel="alternate" hreflang="ru-' . esc_attr($h_city) . '" href="' . esc_url(home_url('/' . $h_city . '/services/' . $h_slug . '/')) . '" />' . "\n";
            }
            $current_slug = get_post_field('post_name', get_the_ID());
            $current_city_href = get_query_var('mrt_city') ?: mrt_seo_current_city();
            echo '<link rel="alternate" hreflang="x-default" href="' . esc_url(home_url('/' . $current_city_href . '/services/' . $current_slug . '/')) . '" />' . "\n";
        }
    } elseif (mrt_seo_is_city_home()) {
        foreach ($cities as $slug => $name) {
            echo '<link rel="alternate" hreflang="ru-' . esc_attr($slug) . '" href="' . esc_url(home_url('/' . $slug . '/')) . '" />' . "\n";
        }
        echo '<link rel="alternate" hreflang="x-default" href="' . esc_url(home_url('/almaty/')) . '" />' . "\n";
    } elseif (mrt_seo_is_service_landing()) {
        // Hreflang for service landing pages — same service across cities
        $landing_slug = get_query_var('mrt_service_landing');
        $svc = get_page_by_path($landing_slug, OBJECT, 'service');
        if ($svc) {
            $svc_title = $svc->post_title;
            $sibling_ids = get_posts([
                'post_type' => 'service',
                'post_status' => 'publish',
                'title' => $svc_title,
                'posts_per_page' => -1,
                'fields' => 'ids',
            ]);
            $hreflang_by_city_landing = [];
            foreach ($sibling_ids as $sid) {
                $branches = wp_get_post_terms($sid, 'branch', ['fields' => 'slugs']);
                if (!empty($branches) && !is_wp_error($branches) && array_key_exists($branches[0], $cities)) {
                    $city_slug = $branches[0];
                    $post_name = get_post_field('post_name', $sid);
                    $hreflang_by_city_landing[$city_slug] = $post_name;
                }
            }
            foreach ($hreflang_by_city_landing as $h_city => $h_slug) {
                echo '<link rel="alternate" hreflang="ru-' . esc_attr($h_city) . '" href="' . esc_url(home_url('/' . $h_city . '/services/' . $h_slug . '/')) . '" />' . "\n";
            }
            echo '<link rel="alternate" hreflang="x-default" href="' . esc_url(home_url('/' . $city . '/services/' . $landing_slug . '/')) . '" />' . "\n";
        }
    } elseif (get_query_var('mrt_subcat')) {
        // Hreflang для подкатегорий: /{city}/uslugi-i-ceny/{subcat}/
        $subcat = get_query_var('mrt_subcat');
        foreach ($cities as $slug => $name) {
            if (mrt_is_subcategory_hidden_for_city($subcat, $slug)) {
                continue;
            }
            echo '<link rel="alternate" hreflang="ru-' . esc_attr($slug) . '" href="' . esc_url(home_url('/' . $slug . '/uslugi-i-ceny/' . $subcat . '/')) . '" />' . "\n";
        }
        echo '<link rel="alternate" hreflang="x-default" href="' . esc_url(home_url('/' . $city . '/uslugi-i-ceny/' . $subcat . '/')) . '" />' . "\n";
    }

    // --- Верификация ---
    echo '<meta name="google-site-verification" content="dcqWRwuvLVi3WRSf2pNFUyszhMFKWv35DAkTwpz6cZo" />' . "\n";
    echo '<meta name="yandex-verification" content="35c851145e0692d2" />' . "\n";
    echo '<meta name="facebook-domain-verification" content="baawbxh7yddo6g4edqe7wqeclmm57y" />' . "\n";

    // --- Keywords (для Яндекса) ---
    $keywords = mrt_seo_keywords($city_name, $city_genitive);
    if ($keywords) {
        echo '<meta name="keywords" content="' . esc_attr($keywords) . '" />' . "\n";
    }

    // --- lastmod для свежести ---
    if (is_singular()) {
        $modified = get_the_modified_time('c');
        if ($modified) {
            echo '<meta property="article:modified_time" content="' . esc_attr($modified) . '" />' . "\n";
        }
    }

    // --- Robots meta ---
    $robots_content = [];
    $robots_content[] = 'max-image-preview:large';
    if (is_category() || is_tag() || is_author() || is_date() || is_search() || is_404() || is_paged()) {
        $robots_content[] = 'noindex';
        $robots_content[] = 'follow';
    }
    echo '<meta name="robots" content="' . esc_attr(implode(', ', $robots_content)) . '" />' . "\n";
}

// ============================================================
// 2b. KEYWORDS — meta keywords для Яндекса (per city/service)
// ============================================================
function mrt_seo_keywords($city_name, $city_genitive) {
    $page_slug = mrt_seo_url_page_slug();
    if (mrt_seo_is_city_home() && $city_name) {
        return "МРТ в {$city_genitive}, МРТ Лидер {$city_name}, сделать МРТ {$city_genitive}, МРТ центр {$city_name}, диагностика МРТ {$city_genitive}";
    }
    if ($page_slug === 'uslugi-i-ceny' && $city_name) {
        return "МРТ {$city_name} цена, КТ {$city_name} стоимость, УЗИ {$city_genitive} цена, МРТ Лидер прайс, МРТ головы {$city_name} цена, МРТ позвоночника {$city_genitive}";
    }
    if (is_singular('service') && $city_name) {
        $mrt_city = get_query_var('mrt_city') ?: mrt_seo_current_city();
        $city_gen_svc = mrt_seo_city_genitive($mrt_city);
        $title = get_the_title();
        $type_label = mrt_seo_service_type_label(get_the_ID());
        $kw = "{$title} {$city_gen_svc} цена, {$title} {$city_gen_svc} сделать, ";
        if ($type_label) $kw .= "{$type_label} {$city_gen_svc} стоимость, ";
        $kw .= "МРТ Лидер {$city_gen_svc}, записаться на {$title} {$city_gen_svc}";
        return $kw;
    }
    if (is_page() && mrt_seo_is_uslugi_child() && $city_name) {
        $title = get_the_title();
        $mrt_city = mrt_seo_current_city();
        $city_gen_svc = mrt_seo_city_genitive($mrt_city);
        return "{$title} {$city_gen_svc} цена, {$title} {$city_gen_svc} сделать, {$title} в {$city_gen_svc} стоимость, МРТ Лидер{$city_gen_svc}";
    }
    if (mrt_seo_is_service_landing() && $city_name) {
        $svc = get_page_by_path(get_query_var('mrt_service_landing'), OBJECT, 'service');
        if ($svc) {
            $svc_title = mrt_get_service_oblast_display($svc->ID);
            $type_label = mrt_seo_service_type_label($svc->ID);
            $kw = "{$svc_title} {$city_genitive} цена, {$svc_title} {$city_genitive} сделать, ";
            if ($type_label) $kw .= "{$type_label} {$city_genitive} стоимость, ";
            $kw .= "МРТ Лидер {$city_genitive}, записаться на {$svc_title} {$city_genitive}";
            return $kw;
        }
    }
    return '';
}

// ============================================================
// 2c. CITY SERVICE STATS — количество услуг + диапазон цен
// ============================================================
function mrt_seo_city_service_stats($slug) {
    $cache_key = 'mrt_svc_stats_' . $slug;
    $cached = wp_cache_get($cache_key, 'mrt_seo');
    if ($cached !== false) return $cached;

    global $wpdb;
    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT p.ID, pm.meta_value as price
         FROM {$wpdb->posts} p
         INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
         INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
         INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
         LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'service_price'
         WHERE p.post_type = 'service' AND p.post_status = 'publish'
         AND tt.taxonomy = 'branch' AND t.slug = %s",
        $slug
    ));

    $stats = ['count' => 0, 'price_min' => null, 'price_max' => null, 'types' => []];
    $types = [];
    foreach ($rows as $r) {
        $stats['count']++;
        $price = $r->price ? (int) $r->price : null;
        if ($price && $price > 0) {
            $stats['price_min'] = $stats['price_min'] === null ? $price : min($stats['price_min'], $price);
            $stats['price_max'] = $stats['price_max'] === null ? $price : max($stats['price_max'], $price);
        }
        $terms = wp_get_post_terms($r->ID, 'service_type');
        if (!empty($terms) && !is_wp_error($terms)) {
            foreach ($terms as $t) {
                if (!in_array($t->name, $types, true)) $types[] = $t->name;
            }
        }
    }
    $stats['types'] = $types;

    wp_cache_set($cache_key, $stats, 'mrt_seo', 3600);
    return $stats;
}

// ============================================================
// 3. ROBOTS — через стандартный wp_robots фильтр
// ============================================================
add_filter('wp_robots', 'mrt_seo_wp_robots', 20);
function mrt_seo_wp_robots($robots) {
    $robots['max-image-preview'] = 'large';

    if (is_category() || is_tag() || is_author() || is_date() || is_search() || is_404() || is_paged()) {
        $robots['noindex'] = true;
        $robots['follow'] = true;
    }

    return $robots;
}

// ============================================================
// 4. CANONICAL URL
// ============================================================
function mrt_seo_canonical_url() {
    $mrt_city = get_query_var('mrt_city');
    $city = mrt_seo_current_city();

    // City-service CPT: canonical → landing page (/{city}/services/{slug}/)
    // чтобы избежать каннибализации: landing содержит цену, FAQ, rating.
    if (is_singular('service') && $mrt_city) {
        $svc_slug = get_post_field('post_name', get_the_ID());
        return home_url('/' . $mrt_city . '/services/' . $svc_slug . '/');
    }

    // Service landing: /{city}/services/{slug}/
    if (mrt_seo_is_service_landing()) {
        $landing_slug = get_query_var('mrt_service_landing');
        if (!$landing_slug) {
            // Fallback: extract from URL
            $uri_l = $_SERVER['REQUEST_URI'] ?? '';
            $path_l = trim(parse_url($uri_l, PHP_URL_PATH), '/');
            $parts_l = explode('/', $path_l);
            $landing_slug = $parts_l[2] ?? '';
        }
        return home_url('/' . $mrt_city . '/services/' . $landing_slug . '/');
    }

    // City home: /{city}/
    if (mrt_seo_is_city_home()) {
        return home_url('/' . $city . '/');
    }

    // Any page under a city path: use clean REQUEST_URI
    // WordPress permalinks don't include city, so we reconstruct from the URL
    if ($city) {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $path = parse_url($uri, PHP_URL_PATH);
        $path = rtrim((string) $path, '/') . '/';
        if (strpos($path, '/' . $city . '/') === 0 || $path === '/' . $city . '/') {
            return home_url($path);
        }
    }

    if (is_singular()) {
        return get_permalink();
    }
    $protocol = is_ssl() ? 'https://' : 'http://';
    return $protocol . ($_SERVER['HTTP_HOST'] ?? 'mrt-lider.kz') . ($_SERVER['REQUEST_URI'] ?? '/');
}

// ============================================================
// 5. OG TITLE
// ============================================================
function mrt_seo_og_title($city_name) {
    if (mrt_seo_is_city_home() && $city_name) {
        $city = mrt_seo_current_city();
        $city_gen = mrt_seo_city_genitive($city);
        return "МРТ в {$city_gen} — сеть диагностических центров МРТ Лидер";
    }
    if (is_singular('service') && $city_name) {
        $mrt_city = get_query_var('mrt_city') ?: mrt_seo_current_city();
        $city_for_og = mrt_seo_get_cities()[$mrt_city] ?? $city_name;
        return get_the_title() . " в {$city_for_og} | МРТ Лидер";
    }
    // Service landing
    if (mrt_seo_is_service_landing() && $city_name) {
        $svc = get_page_by_path(get_query_var('mrt_service_landing'), OBJECT, 'service');
        if ($svc) {
            $svc_title = mrt_get_service_oblast_display($svc->ID);
            $city_gen = mrt_seo_city_genitive(get_query_var('mrt_city'));
            return "{$svc_title} в {$city_gen} | МРТ Лидер";
        }
    }
    // City sub-pages (uslugi-i-ceny, kontakty, speciality, vopros-otvet)
    if (is_page() && $city_name) {
        return get_the_title() . " в {$city_name} | МРТ Лидер";
    }
    if (is_singular('article')) {
        return get_the_title() . ' — статья о МРТ | МРТ Лидер';
    }
    if (is_singular()) return get_the_title();
    return get_bloginfo('name');
}

// ============================================================
// 6. OG IMAGE
// ============================================================
function mrt_seo_og_image() {
    if (is_singular() && has_post_thumbnail()) {
        return get_the_post_thumbnail_url(get_the_ID(), 'large');
    }
    // Landing-страница: пробуем thumbnail связанной услуги
    if (mrt_seo_is_service_landing()) {
        $landing_slug = get_query_var('mrt_service_landing');
        if (!$landing_slug) {
            $uri_l = $_SERVER['REQUEST_URI'] ?? '';
            $path_l = trim(parse_url($uri_l, PHP_URL_PATH), '/');
            $parts_l = explode('/', $path_l);
            $landing_slug = $parts_l[2] ?? '';
        }
        if ($landing_slug) {
            $svc = get_page_by_path($landing_slug, OBJECT, 'service');
            if ($svc && has_post_thumbnail($svc->ID)) {
                return get_the_post_thumbnail_url($svc->ID, 'large');
            }
        }
    }
    return get_template_directory_uri() . '/assets/img/Logo-wth-text.png';
}

function mrt_seo_og_image_dims() {
    if (is_singular() && has_post_thumbnail()) {
        $thumb = wp_get_attachment_image_src(get_post_thumbnail_id(get_the_ID()), 'large');
        if ($thumb) return [$thumb[1], $thumb[2]];
    }
    // Landing-страница: пробуем thumbnail связанной услуги
    if (mrt_seo_is_service_landing()) {
        $landing_slug = get_query_var('mrt_service_landing');
        if (!$landing_slug) {
            $uri_l = $_SERVER['REQUEST_URI'] ?? '';
            $path_l = trim(parse_url($uri_l, PHP_URL_PATH), '/');
            $parts_l = explode('/', $path_l);
            $landing_slug = $parts_l[2] ?? '';
        }
        if ($landing_slug) {
            $svc = get_page_by_path($landing_slug, OBJECT, 'service');
            if ($svc && has_post_thumbnail($svc->ID)) {
                $thumb = wp_get_attachment_image_src(get_post_thumbnail_id($svc->ID), 'large');
                if ($thumb) return [$thumb[1], $thumb[2]];
            }
        }
    }
    return [5000, 5000];
}

// ============================================================
// 7. SCHEMA.ORG JSON-LD (все типы в одном @graph)
// ============================================================
add_action('wp_head', 'mrt_seo_schema_jsonld', 2);
function mrt_seo_schema_jsonld() {
    $cities = mrt_seo_get_cities();
    $city = mrt_seo_current_city();
    $city_name = $cities[$city] ?? 'Тюмень';

    $schema = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'Organization',
                '@id' => home_url('/#organization'),
                'name' => 'МРТ Лидер',
                'url' => home_url('/'),
                'logo' => ['@type' => 'ImageObject', 'url' => mrt_logo_url()],
                'foundingDate' => '2012-01-18',
                'description' => 'Сеть диагностических центров МРТ Лидер в Казахстане',
            ],
            [
                '@type' => 'WebSite',
                '@id' => home_url('/#website'),
                'url' => home_url('/'),
                'name' => 'МРТ Лидер',
                'inLanguage' => 'ru-RU',
                'publisher' => ['@id' => home_url('/#organization')],
            ],
        ],
    ];

    // MedicalClinic на главных страницах городов
    if (mrt_seo_is_animals_context() && mrt_seo_is_city_home()) {
        $branch = function_exists('mrt_get_branch') ? mrt_get_branch($city) : [];
        $city_contacts = mrt_seo_get_city_contacts($city);
        $clinic = [
            '@type' => 'VeterinaryCare',
            '@id' => home_url('/' . $city . '/#medicalclinic'),
            'name' => 'MRI Animal — мрт животным',
            'description' => mrt_seo_animals_home_description(),
            'url' => home_url('/' . $city . '/'),
            'logo' => ['@type' => 'ImageObject', 'url' => mrt_logo_url()],
            'image' => mrt_logo_url(),
            'medicalSpecialty' => 'Radiology',
            'availableService' => [
                ['@type' => 'MedicalProcedure', 'name' => 'МРТ для собак'],
                ['@type' => 'MedicalProcedure', 'name' => 'МРТ для кошек'],
            ],
            'parentOrganization' => ['@id' => home_url('/#organization')],
            'areaServed' => ['@type' => 'City', 'name' => 'Алматы'],
        ];
        if (!empty($branch['address_full'])) {
            $clinic['address'] = [
                '@type' => 'PostalAddress',
                'streetAddress' => $branch['address_short'] ?? $branch['address_full'],
                'addressLocality' => 'Отеген батыра',
                'addressRegion' => 'Алматинская область',
                'addressCountry' => 'KZ',
            ];
        }
        if (!empty($city_contacts['telephone'])) {
            $clinic['telephone'] = $city_contacts['telephone'];
        }
        if (!empty($city_contacts['openingHours'])) {
            $clinic['openingHours'] = $city_contacts['openingHours'];
        }
        $schema['@graph'][] = $clinic;
    } elseif (mrt_seo_is_city_home()) {
        $city_genitive = mrt_seo_city_genitive($city);
        $branch_count_clinic = mrt_seo_city_branch_count($city);
        $clinic = [
            '@type' => 'MedicalClinic',
            '@id' => home_url('/' . $city . '/#medicalclinic'),
            'name' => "МРТ Лидер — {$city_name}",
            'description' => "Сеть диагностических центров МРТ Лидер. МРТ, КТ, УЗИ, денситометрия в {$city_genitive}. {$branch_count_clinic} филиалов.",
            'url' => home_url('/' . $city . '/'),
            'logo' => ['@type' => 'ImageObject', 'url' => mrt_logo_url()],
            'image' => mrt_logo_url(),
            'medicalSpecialty' => 'Radiology',
            'availableService' => [
                ['@type' => 'MedicalProcedure', 'name' => 'МРТ'],
                ['@type' => 'MedicalProcedure', 'name' => 'КТ'],
                ['@type' => 'MedicalProcedure', 'name' => 'УЗИ'],
                ['@type' => 'MedicalProcedure', 'name' => 'Денситометрия'],
            ],
            'parentOrganization' => ['@id' => home_url('/#organization')],
            'areaServed' => ['@type' => 'City', 'name' => $city_name],
        ];
        // Добавляем данные контактов из ACF
        $city_contacts = mrt_seo_get_city_contacts($city);
        if ($city_contacts['address']) {
            $clinic['address'] = [
                '@type' => 'PostalAddress',
                'streetAddress' => $city_contacts['address'],
                'addressLocality' => $city_name,
                'addressCountry' => 'KZ',
            ];
        }
        if ($city_contacts['telephone']) {
            $clinic['telephone'] = $city_contacts['telephone'];
        }
        if (!empty($city_contacts['openingHours'])) {
            $clinic['openingHours'] = $city_contacts['openingHours'];
        }
        $schema['@graph'][] = $clinic;
    }

    // ItemList для страницы услуг и цен (расширенные сниппеты в Google)
    $page_slug = mrt_seo_url_page_slug();
    if ($page_slug === 'uslugi-i-ceny' && $city_name) {
        $svc_items = [];
        $svc_posts = get_posts([
            'post_type' => 'service',
            'post_status' => 'publish',
            'posts_per_page' => 30,
            'tax_query' => [['taxonomy' => 'branch', 'field' => 'slug', 'terms' => $city]],
            'orderby' => 'title',
            'order' => 'ASC',
        ]);

        // Service Schema для главной страницы услуг
        $city_genitive = mrt_seo_city_genitive($city);
        $schema['@graph'][] = [
            '@type' => 'Service',
            'name' => "Услуги и цены МРТ Лидер в {$city_name}",
            'description' => "Полный прайс-лист МРТ, КТ, УЗИ и денситометрии в {$city_genitive}. Запись онлайн, сеть центров МРТ Лидер.",
            'url' => mrt_seo_canonical_url(),
            'serviceType' => 'Диагностические услуги',
            'areaServed' => ['@type' => 'City', 'name' => $city_name],
            'provider' => [
                '@type' => 'MedicalClinic',
                '@id' => home_url('/' . $city . '/#medicalclinic'),
                'name' => "МРТ Лидер — {$city_name}",
                'url' => home_url('/' . $city . '/'),
            ],
        ];
        $pos = 0;
        foreach ($svc_posts as $svc_post) {
            $pos++;
            $svc_price = get_field('service_price', $svc_post->ID);
            $svc_type = mrt_seo_service_type_label($svc_post->ID);
            $item = [
                '@type' => 'ListItem',
                'position' => $pos,
                'name' => get_the_title($svc_post->ID) . ($svc_type ? " — {$svc_type}" : ''),
                'url' => home_url('/' . $city . '/services/' . $svc_post->post_name . '/'),
            ];
            $svc_items[] = $item;
        }
        if (!empty($svc_items)) {
            $schema['@graph'][] = [
                '@type' => 'ItemList',
                '@id' => home_url('/' . $city . '/uslugi-i-ceny/#itemlist'),
                'name' => "Услуги МРТ Лидер в {$city_name}",
                'numberOfItems' => count($svc_items),
                'itemListElement' => $svc_items,
            ];
        }
    }

    // Service Schema для подкатегорий (/{city}/uslugi-i-ceny/{subcat}/)
    $subcat_schema = get_query_var('mrt_subcat');
    if ($subcat_schema && $city_name) {
        $subcat_titles_map = [
            'mrt-golovy' => 'МРТ Головы', 'mrt-pozvonochnika' => 'МРТ Позвоночника',
            'mrt-sustavov' => 'МРТ Суставов', 'mrt-brushnoy-polosti' => 'МРТ Брюшной полости',
            'mrt-malogo-taza' => 'МРТ Малого таза', 'mrt-serdca-i-sosudov' => 'МРТ Сердца и сосудов',
            'mrt-grudnoy-kletki' => 'МРТ Грудной клетки', 'mrt-myagkih-tkaney' => 'МРТ Мягких тканей',
            'kt' => 'КТ', 'kt-golovy' => 'КТ Головы', 'kt-pozvonochnika' => 'КТ Позвоночника',
            'kt-sustavov' => 'КТ Суставов', 'kt-brushnoy-polosti' => 'КТ Брюшной полости',
            'kt-malogo-taza' => 'КТ Малого таза', 'kt-serdca-i-sosudov' => 'КТ Сердца и сосудов',
            'kt-grudnoy-kletki' => 'КТ Грудной клетки', 'kt-myagkih-tkaney' => 'КТ Мягких тканей',
            'kt-kompleksnye' => 'КТ Комплексные программы',
            'uzi' => 'УЗИ', 'uzi-golovy' => 'УЗИ Головы и шеи',
            'uzi-serdca-i-sosudov' => 'УЗИ Сердца и сосудов',
            'uzi-brushnoy-polosti' => 'УЗИ Брюшной полости',
            'uzi-malogo-taza' => 'УЗИ Малого таза',
            'uzi-molochnykh-zhelez' => 'УЗИ Молочных желез',
            'uzi-sustavov' => 'УЗИ Суставов', 'uzi-myagkih-tkaney' => 'УЗИ Мягких тканей',
            'uzi-nervnoj-sistemy' => 'УЗИ Нервной системы',
            'densitometriya' => 'Денситометрия',
        ];
        $subcat_name = $subcat_titles_map[$subcat_schema] ?? $subcat_schema;
        $city_genitive = mrt_seo_city_genitive($city);
        $schema['@graph'][] = [
            '@type' => 'Service',
            'name' => "{$subcat_name} в {$city_name}",
            'description' => "{$subcat_name} в {$city_genitive}. Актуальные цены, запись онлайн. Центры МРТ Лидер.",
            'url' => mrt_seo_canonical_url(),
            'serviceType' => $subcat_name,
            'areaServed' => ['@type' => 'City', 'name' => $city_name],
            'provider' => [
                '@type' => 'MedicalClinic',
                '@id' => home_url('/' . $city . '/#medicalclinic'),
                'name' => "МРТ Лидер — {$city_name}",
                'url' => home_url('/' . $city . '/'),
            ],
        ];
    }

    // BreadcrumbList
    $canonical = mrt_seo_canonical_url();
    $breadcrumbs = mrt_seo_get_breadcrumbs_data();
    if (!empty($breadcrumbs)) {
        $items = [];
        foreach ($breadcrumbs as $i => $bc) {
            $item = ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $bc['name']];
            if (!empty($bc['url'])) $item['item'] = $bc['url'];
            $items[] = $item;
        }
        $schema['@graph'][] = [
            '@type' => 'BreadcrumbList',
            '@id' => $canonical . '#breadcrumblist',
            'itemListElement' => $items,
        ];
    }

    // WebPage
    $schema['@graph'][] = [
        '@type' => 'WebPage',
        '@id' => $canonical . '#webpage',
        'url' => $canonical,
        'name' => mrt_seo_og_title($city_name),
        'inLanguage' => 'ru-RU',
        'isPartOf' => ['@id' => home_url('/#website')],
        'breadcrumb' => ['@id' => $canonical . '#breadcrumblist'],
    ];

    // MedicalProcedure для страниц услуг
    if (is_singular('service')) {
        $mrt_city_svc = get_query_var('mrt_city') ?: $city;
        $mrt_city_name = $cities[$mrt_city_svc] ?? $city_name;
        $mrt_city_gen = mrt_seo_city_genitive($mrt_city_svc);
        $service_type = mrt_seo_service_type_label(get_the_ID());
        $price = get_field('service_price');
        $canonical_svc = mrt_seo_canonical_url();
        $proc = [
            '@type' => 'MedicalProcedure',
            'name' => get_the_title() . " в {$mrt_city_name}",
            'alternateName' => $service_type ?: null,
            'description' => get_the_title() . " в {$mrt_city_gen}. " . wp_trim_words(get_the_excerpt() ?: get_the_content(), 30, '...'),
            'url' => $canonical_svc,
            'procedureType' => 'Diagnostic',
            'howPerformed' => function_exists('mrt_city_mri_equipment_how_performed') ? mrt_city_mri_equipment_how_performed($mrt_city_svc) : 'На аппарате МРТ Siemens',
            'provider' => [
                '@type' => 'MedicalClinic',
                '@id' => home_url('/' . $mrt_city_svc . '/#medicalclinic'),
                'name' => "МРТ Лидер — {$mrt_city_name}",
                'url' => home_url('/' . $mrt_city_svc . '/'),
            ],
        ];
        if ($price && is_numeric($price)) {
            $proc['offers'] = [
                '@type' => 'Offer',
                'price' => (int) $price,
                'priceCurrency' => 'KZT',
                'availability' => 'https://schema.org/InStock',
            ];
        }
        $schema['@graph'][] = $proc;
    }

    // MedicalProcedure для дочерних страниц uslugi-i-ceny
    if (is_page() && mrt_seo_is_uslugi_child() && $city_name) {
        $city_genitive = mrt_seo_city_genitive($city);
        $canonical_child = mrt_seo_canonical_url();
        $child_price = get_field('service_price', get_the_ID());
        $proc = [
            '@type' => 'MedicalProcedure',
            'name' => get_the_title() . " в {$city_name}",
            'description' => get_the_title() . " в {$city_genitive}. " . wp_trim_words(get_the_excerpt() ?: get_the_content(), 30, '...'),
            'url' => $canonical_child,
            'procedureType' => 'Diagnostic',
            'howPerformed' => function_exists('mrt_city_mri_equipment_how_performed') ? mrt_city_mri_equipment_how_performed($city) : 'На аппарате МРТ Siemens',
            'provider' => [
                '@type' => 'MedicalClinic',
                '@id' => home_url('/' . $city . '/#medicalclinic'),
                'name' => "МРТ Лидер — {$city_name}",
                'url' => home_url('/' . $city . '/'),
            ],
        ];
        if ($child_price && is_numeric($child_price)) {
            $proc['offers'] = [
                '@type' => 'Offer',
                'price' => (int) $child_price,
                'priceCurrency' => 'KZT',
                'availability' => 'https://schema.org/InStock',
            ];
        }
        $schema['@graph'][] = $proc;
    }

    // MedicalProcedure для service landing страниц (/{city}/services/{slug}/)
    if (mrt_seo_is_service_landing() && $city_name) {
        $landing_slug = get_query_var('mrt_service_landing');
        $svc = get_page_by_path($landing_slug, OBJECT, 'service');
        if ($svc) {
            $city_genitive = mrt_seo_city_genitive($city);
            $canonical_landing = mrt_seo_canonical_url();
            $svc_title = mrt_get_service_oblast_display($svc->ID);
            $svc_price = get_post_meta($svc->ID, 'si_price', true);
            $svc_type = mrt_seo_service_type_label($svc->ID);
            $kz_cities = mrt_get_kz_cities();
            $price_currency = in_array($city, $kz_cities, true) ? 'KZT' : 'RUB';
            $landing_mri_brand = function_exists('mrt_city_mri_equipment_brand') ? mrt_city_mri_equipment_brand($city) : 'Siemens';
            $proc = [
                '@type' => 'MedicalProcedure',
                'name' => "{$svc_title} в {$city_name}",
                'alternateName' => $svc_type ?: null,
                'description' => "{$svc_title} в {$city_genitive}. Современное оборудование {$landing_mri_brand}, запись онлайн, результат за 1 час. Сеть центров МРТ Лидер.",
                'url' => $canonical_landing,
                'procedureType' => 'Diagnostic',
                'howPerformed' => function_exists('mrt_city_mri_equipment_how_performed') ? mrt_city_mri_equipment_how_performed($city) : 'На аппарате МРТ Siemens',
                'areaServed' => ['@type' => 'City', 'name' => $city_name],
                'provider' => [
                    '@type' => 'MedicalClinic',
                    '@id' => home_url('/' . $city . '/#medicalclinic'),
                    'name' => "МРТ Лидер — {$city_name}",
                    'url' => home_url('/' . $city . '/'),
                ],
            ];
            if ($svc_price && is_numeric($svc_price)) {
                $proc['offers'] = [
                    '@type' => 'Offer',
                    'price' => (int) $svc_price,
                    'priceCurrency' => $price_currency,
                    'availability' => 'https://schema.org/InStock',
                ];
            }
            // AggregateRating для MedicalProcedure из Яндекс Справочника
            $landing_rating = mrt_seo_get_branch_rating($city);
            if ($landing_rating) {
                $proc['aggregateRating'] = [
                    '@type' => 'AggregateRating',
                    'ratingValue' => $landing_rating['rating'],
                    'bestRating' => 5,
                    'worstRating' => 1,
                    'ratingCount' => $landing_rating['count'],
                    'reviewCount' => $landing_rating['count'],
                ];
            }
            $schema['@graph'][] = $proc;

            // FAQPage Schema для service landing (данные из service-content.php)
            require_once __DIR__ . '/service-content.php';
            $svc_category = get_post_meta($svc->ID, 'si_category', true);
            $svc_oblast_display = mrt_get_service_oblast_display($svc->ID);
            $svc_type_terms = wp_get_post_terms($svc->ID, 'service_type');
            $svc_type_name = !empty($svc_type_terms) ? $svc_type_terms[0]->name : '';
            $svc_faq_data = mrt_finalize_service_content(
                mrt_get_service_content($svc_category ?: $svc_oblast_display, $svc_type_name, $svc_oblast_display),
                $svc_oblast_display,
                $svc_category ?: ''
            );
            if (!empty($svc_faq_data['faq'])) {
                $faq_entities = [];
                foreach ($svc_faq_data['faq'] as $faq_item) {
                    $faq_entities[] = [
                        '@type' => 'Question',
                        'name' => $faq_item['q'],
                        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq_item['a']],
                    ];
                }
                $schema['@graph'][] = ['@type' => 'FAQPage', 'mainEntity' => $faq_entities];
            }
        }
    }

    // FAQPage
    if (mrt_seo_url_page_slug() === 'vopros-otvet') {
        $parent_cat = get_term_by('slug', 'answers', 'category');
        if ($parent_cat) {
            $faq_posts = get_posts([
                'post_type' => 'post',
                'category' => $parent_cat->term_id,
                'posts_per_page' => -1,
                'post_status' => 'publish',
            ]);
            $faq_items = [];
            foreach ($faq_posts as $fp) {
                $answer = wp_strip_all_tags($fp->post_content);
                $answer = wp_trim_words($answer, 60, '...');
                if (!empty($fp->post_title) && !empty($answer)) {
                    $faq_items[] = [
                        '@type' => 'Question',
                        'name' => $fp->post_title,
                        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $answer],
                    ];
                }
            }
            if (!empty($faq_items)) {
                $schema['@graph'][] = ['@type' => 'FAQPage', 'mainEntity' => $faq_items];
            }
        }
    }

    // BlogPosting для статей /stati/
    if (is_singular('article')) {
        $article_id = get_the_ID();
        $blog_post = [
            '@type' => 'BlogPosting',
            '@id' => get_permalink($article_id) . '#article',
            'headline' => get_the_title($article_id),
            'description' => wp_strip_all_tags(get_the_excerpt($article_id)),
            'url' => get_permalink($article_id),
            'datePublished' => get_the_date('c', $article_id),
            'dateModified' => get_the_modified_date('c', $article_id),
            'author' => [
                '@type' => 'Organization',
                'name' => 'МРТ Лидер',
                '@id' => home_url('/#organization'),
            ],
            'publisher' => ['@id' => home_url('/#organization')],
            'inLanguage' => 'ru-RU',
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => get_permalink($article_id),
            ],
        ];
        if (has_post_thumbnail($article_id)) {
            $blog_post['image'] = get_the_post_thumbnail_url($article_id, 'large');
        }
        $schema['@graph'][] = $blog_post;
    }

    // FAQPage for articles (detect FAQ sections in content)
    if (is_singular('article')) {
        $post_content = get_the_content();
        // Simple detection: look for a heading containing "FAQ" or class "faq"
        if (preg_match('/<h[2-4][^>]*>.*?FAQ.*?<\/h[2-4]>/i', $post_content) || strpos($post_content, 'class="faq"') !== false) {
            // Extract FAQ items: assume each question is an h3 followed by a paragraph answer
            preg_match_all('/<h[3][^>]*>(.*?)<\/h[3]>\s*<p>(.*?)<\/p>/is', $post_content, $matches, PREG_SET_ORDER);
            $faq_items = [];
            foreach ($matches as $match) {
                $question = wp_strip_all_tags($match[1]);
                $answer = wp_strip_all_tags($match[2]);
                if ($question && $answer) {
                    $faq_items[] = [
                        '@type' => 'Question',
                        'name' => $question,
                        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $answer],
                    ];
                }
            }
            if (!empty($faq_items)) {
                $schema['@graph'][] = ['@type' => 'FAQPage', 'mainEntity' => $faq_items];
            }
        }
    }

    // AggregateRating из Яндекс Справочника — добавляем к существующему MedicalClinic
    $city = mrt_seo_current_city();
    $branch_rating = mrt_seo_get_branch_rating($city);
    if ($branch_rating) {
        foreach ($schema['@graph'] as &$item) {
            if (isset($item['@type']) && $item['@type'] === 'MedicalClinic'
                && isset($item['@id']) && strpos($item['@id'], $city . '/#medicalclinic') !== false) {
                $item['aggregateRating'] = [
                    '@type' => 'AggregateRating',
                    'ratingValue' => $branch_rating['rating'],
                    'bestRating' => 5,
                    'worstRating' => 1,
                    'ratingCount' => $branch_rating['count'],
                    'reviewCount' => $branch_rating['count'],
                ];
                break;
            }
        }
        unset($item);
    }

    echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
}

// ============================================================
// 8. BREADCRUMBS — данные для Schema и HTML
// ============================================================
function mrt_seo_get_breadcrumbs_data() {
    $cities = mrt_seo_get_cities();
    $city = mrt_seo_current_city();
    $city_name = $cities[$city] ?? '';
    $breadcrumbs = [['name' => $city_name ?: 'Главная', 'url' => home_url('/' . $city . '/')]];

    if (is_singular('service')) {
        $mrt_city_bc = get_query_var('mrt_city') ?: $city;
        $breadcrumbs = [['name' => $cities[$mrt_city_bc] ?? $city_name ?: 'Главная', 'url' => home_url('/' . $mrt_city_bc . '/')]];
        $uslugi_page = get_page_by_path('uslugi-i-ceny');
        $breadcrumbs[] = ['name' => $uslugi_page ? $uslugi_page->post_title : 'Услуги и цены', 'url' => home_url('/' . $mrt_city_bc . '/uslugi-i-ceny/')];
        $breadcrumbs[] = ['name' => get_the_title(), 'url' => null];
    } elseif (is_page() && !mrt_seo_is_city_home()) {
        if (get_page_template_slug() === 'page-service-item.php') {
            $uslugi_page = get_page_by_path('uslugi-i-ceny');
            $breadcrumbs[] = ['name' => $uslugi_page ? $uslugi_page->post_title : 'Услуги и цены', 'url' => home_url('/' . $city . '/uslugi-i-ceny/')];
            $breadcrumbs[] = ['name' => get_the_title(), 'url' => null];
        } elseif (is_singular() && has_term('specialisty', 'category')) {
            $breadcrumbs[] = ['name' => 'Специалисты', 'url' => home_url('/' . $city . '/specialisty/')];
            $breadcrumbs[] = ['name' => get_the_title(), 'url' => null];
        } else {
            $breadcrumbs[] = ['name' => get_the_title(), 'url' => null];
        }
    } elseif (is_singular() && !mrt_seo_is_city_home()) {
        $breadcrumbs[] = ['name' => get_the_title(), 'url' => null];
    } elseif (mrt_seo_is_service_landing()) {
        $landing_slug = get_query_var('mrt_service_landing');
        if (!$landing_slug) {
            $uri_l = $_SERVER['REQUEST_URI'] ?? '';
            $path_l = trim(parse_url($uri_l, PHP_URL_PATH), '/');
            $parts_l = explode('/', $path_l);
            $landing_slug = $parts_l[2] ?? '';
        }
        $svc = get_page_by_path($landing_slug, OBJECT, 'service');
        if ($svc) {
            $svc_name = mrt_get_service_oblast_display($svc->ID);
            $si_cat = get_post_meta($svc->ID, 'si_category', true);
            $uslugi_page = get_page_by_path('uslugi-i-ceny');
            $breadcrumbs[] = ['name' => $uslugi_page ? $uslugi_page->post_title : 'Услуги и цены', 'url' => home_url('/' . $city . '/uslugi-i-ceny/')];
            if ($si_cat) {
                $subcat_slug_bc = mrt_category_to_subcat_slug($si_cat);
                if ($subcat_slug_bc) {
                    $subcat_names_bc = ['mrt-golovy' => 'МРТ Головы', 'mrt-pozvonochnika' => 'МРТ Позвоночника', 'mrt-sustavov' => 'МРТ Суставов', 'mrt-brushnoy-polosti' => 'МРТ Брюшной полости', 'mrt-malogo-taza' => 'МРТ Малого таза', 'mrt-serdca-i-sosudov' => 'МРТ Сердца и сосудов', 'mrt-grudnoy-kletki' => 'МРТ Грудной клетки', 'mrt-myagkih-tkaney' => 'МРТ Мягких тканей', 'kt' => 'КТ', 'uzi' => 'УЗИ', 'densitometriya' => 'Денситометрия'];
                    $subcat_name = $subcat_names_bc[$subcat_slug_bc] ?? '';
                    if ($subcat_name) {
                        $breadcrumbs[] = ['name' => $subcat_name, 'url' => home_url('/' . $city . '/uslugi-i-ceny/' . $subcat_slug_bc . '/')];
                    }
                }
            }
            $breadcrumbs[] = ['name' => $svc_name, 'url' => null];
        }
    }

    return $breadcrumbs;
}

// ============================================================
// 9. SITEMAP XML
// ============================================================
// Отключаем встроенный WordPress sitemap
add_filter('wp_sitemaps_enabled', '__return_false');

add_action('init', 'mrt_seo_sitemap_init', 0); // priority 0 — раньше WP
function mrt_seo_sitemap_init() {
    add_rewrite_rule('^sitemap\.xml$', 'index.php?mrt_sitemap=index', 'top');
    add_rewrite_rule('^sitemap-posts\.xml$', 'index.php?mrt_sitemap=posts', 'top');
    add_rewrite_rule('^sitemap-pages\.xml$', 'index.php?mrt_sitemap=pages', 'top');
    add_rewrite_rule('^sitemap-taxonomies\.xml$', 'index.php?mrt_sitemap=taxonomies', 'top');
    add_rewrite_rule('^sitemap-services-([^/]+)\.xml$', 'index.php?mrt_sitemap=services&city=$matches[1]', 'top');
    add_rewrite_rule('^sitemap-hubs\.xml$', 'index.php?mrt_sitemap=hubs', 'top');
    add_rewrite_rule('^sitemap-landings\.xml$', 'index.php?mrt_sitemap=landings', 'top');
    add_rewrite_rule('^sitemap-subcats\.xml$', 'index.php?mrt_sitemap=subcats', 'top');
    add_rewrite_rule('^sitemap-articles\.xml$', 'index.php?mrt_sitemap=articles', 'top');
}

add_filter('query_vars', 'mrt_seo_sitemap_query_vars');
function mrt_seo_sitemap_query_vars($vars) {
    $vars[] = 'mrt_sitemap';
    $vars[] = 'city';
    return $vars;
}

add_action('template_redirect', 'mrt_seo_sitemap_render');
function mrt_seo_sitemap_render() {
    $type = get_query_var('mrt_sitemap');
    if (!$type) return;

    header('Content-Type: application/xml; charset=UTF-8');
    header('X-Robots-Tag: noindex, follow');

    if ($type === 'index') mrt_seo_sitemap_index();
    elseif ($type === 'posts') mrt_seo_sitemap_posts();
    elseif ($type === 'pages') mrt_seo_sitemap_pages();
    elseif ($type === 'services') mrt_seo_sitemap_city_services();
    elseif ($type === 'taxonomies') mrt_seo_sitemap_taxonomies();
    elseif ($type === 'hubs') mrt_seo_sitemap_hubs();
    elseif ($type === 'landings') mrt_seo_sitemap_landings();
    elseif ($type === 'subcats') mrt_seo_sitemap_subcats();
    elseif ($type === 'articles') mrt_seo_sitemap_articles();

    exit;
}

function mrt_seo_sitemap_index() {
    $lastmod = date('c');
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach (['posts', 'pages'] as $type) {
        $lm = mrt_seo_sitemap_lastmod($type);
        echo '<sitemap><loc>' . home_url("/sitemap-{$type}.xml") . '</loc><lastmod>' . ($lm ?: $lastmod) . '</lastmod></sitemap>' . "\n";
    }
    echo '<sitemap><loc>' . home_url('/sitemap-landings.xml') . '</loc><lastmod>' . $lastmod . '</lastmod></sitemap>' . "\n";
    echo '<sitemap><loc>' . home_url('/sitemap-hubs.xml') . '</loc><lastmod>' . $lastmod . '</lastmod></sitemap>' . "\n";
    echo '<sitemap><loc>' . home_url('/sitemap-subcats.xml') . '</loc><lastmod>' . $lastmod . '</lastmod></sitemap>' . "\n";
    echo '<sitemap><loc>' . home_url('/sitemap-taxonomies.xml') . '</loc><lastmod>' . $lastmod . '</lastmod></sitemap>' . "\n";
    $lm_articles = mrt_seo_sitemap_lastmod_articles();
    echo '<sitemap><loc>' . home_url("/sitemap-articles.xml") . '</loc><lastmod>' . ($lm_articles ?: $lastmod) . '</lastmod></sitemap>' . "\n";
    echo '</sitemapindex>';
}

function mrt_seo_sitemap_posts() {
    $posts = get_posts(['post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'modified', 'order' => 'DESC']);
    mrt_seo_sitemap_output($posts, 'post');
}

function mrt_seo_sitemap_pages() {
    $pages = get_posts(['post_type' => 'page', 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'modified', 'order' => 'DESC']);

    // Для городских страниц (/{city}/) lastmod = max(page modified, theme modified)
    // т.к. контент генерируется шаблонами, а не из БД
    $theme_mtime = filemtime(get_template_directory() . '/assets/css/style.min.css');
    $theme_date = date('c', $theme_mtime);

    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach ($pages as $item) {
        $page_date = get_the_modified_date('c', $item->ID);
        // Для страниц с URL-роутингом (города) берём свежую дату
        $lastmod = max($page_date, $theme_date);
        echo '<url><loc>' . esc_url(get_permalink($item->ID)) . '</loc>';
        echo '<lastmod>' . $lastmod . '</lastmod>';
        echo '<changefreq>monthly</changefreq>';
        echo '<priority>0.7</priority></url>' . "\n";
    }
    echo '</urlset>';
}

function mrt_seo_sitemap_city_services() {
    // Legacy sitemap — redirect to canonical landings map.
    wp_redirect(home_url('/sitemap-landings.xml'), 301);
    exit;
}

function mrt_seo_sitemap_lastmod_city_services($city) {
    global $wpdb;
    $date = $wpdb->get_var($wpdb->prepare(
        "SELECT MAX(p.post_modified_gmt) FROM {$wpdb->posts} p
         INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
         INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
         INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
         WHERE p.post_type = 'service' AND p.post_status = 'publish'
         AND tt.taxonomy = 'branch' AND t.slug = %s",
        $city
    ));
    return $date ? date('c', strtotime($date)) : null;
}

function mrt_seo_sitemap_taxonomies() {
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    $cities = mrt_seo_get_cities();
    foreach ($cities as $slug => $name) {
        echo '<url><loc>' . home_url("/{$slug}/") . '</loc><changefreq>daily</changefreq><priority>0.8</priority></url>' . "\n";
    }
    echo '</urlset>';
}

function mrt_seo_sitemap_output($items, $type) {
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    $priority = $type === 'page' ? '0.7' : ($type === 'service' ? '0.8' : ($type === 'article' ? '0.7' : '0.6'));
    $freq = $type === 'service' ? 'weekly' : ($type === 'article' ? 'weekly' : 'monthly');
    foreach ($items as $item) {
        echo '<url><loc>' . esc_url(get_permalink($item->ID)) . '</loc>';
        echo '<lastmod>' . get_the_modified_date('c', $item->ID) . '</lastmod>';
        echo '<changefreq>' . $freq . '</changefreq>';
        echo '<priority>' . $priority . '</priority></url>' . "\n";
    }
    echo '</urlset>';
}

function mrt_seo_sitemap_lastmod($type) {
    global $wpdb;
    $post_type = $type === 'pages' ? 'page' : 'post';
    $date = $wpdb->get_var($wpdb->prepare(
        "SELECT MAX(post_modified_gmt) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish'",
        $post_type
    ));
    return $date ? date('c', strtotime($date)) : null;
}

function mrt_seo_sitemap_lastmod_articles() {
    global $wpdb;
    $date = $wpdb->get_var(
        "SELECT MAX(post_modified_gmt) FROM {$wpdb->posts} WHERE post_type = 'article' AND post_status = 'publish'"
    );
    return $date ? date('c', strtotime($date)) : null;
}

/**
 * Sitemap: Landing-страницы услуг /{city}/services/{slug}/
 * Только услуги, доступные в городе (taxonomy branch).
 */
function mrt_seo_sitemap_landings() {
    $cities = mrt_seo_get_cities();
    $cache_key = 'mrt_sitemap_landings_v3';
    $cached = wp_cache_get($cache_key, 'mrt_seo');
    if ($cached !== false) {
        echo $cached;
        return;
    }

    global $wpdb;
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    foreach ($cities as $city_slug => $city_name) {
        $services = $wpdb->get_results($wpdb->prepare(
            "SELECT DISTINCT p.ID, p.post_name, p.post_modified_gmt FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
             INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
             INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
             WHERE p.post_type = 'service' AND p.post_status = 'publish'
             AND tt.taxonomy = 'branch' AND t.slug = %s
             ORDER BY p.post_name",
            $city_slug
        ));

        foreach ($services as $svc) {
            if (mrt_is_service_hidden_for_city((int) $svc->ID, $city_slug)) {
                continue;
            }
            $xml .= '<url>';
            $xml .= '<loc>' . esc_url(home_url('/' . $city_slug . '/services/' . $svc->post_name . '/')) . '</loc>';
            $xml .= '<lastmod>' . date('c', strtotime($svc->post_modified_gmt)) . '</lastmod>';
            $xml .= '<changefreq>weekly</changefreq>';
            $xml .= '<priority>0.7</priority>';
            $xml .= '</url>' . "\n";
        }
    }

    $xml .= '</urlset>';

    wp_cache_set($cache_key, $xml, 'mrt_seo', 3600);
    echo $xml;
}

function mrt_seo_sitemap_hubs() {
    $cities = mrt_seo_get_cities();
    $hub_slugs = ['uslugi-i-ceny', 'kontakty', 'specialisty', 'vopros-otvet'];

    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    foreach ($cities as $city_slug => $city_name) {
        foreach ($hub_slugs as $hub) {
            echo '<url>';
            echo '<loc>' . esc_url(home_url('/' . $city_slug . '/' . $hub . '/')) . '</loc>';
            echo '<changefreq>weekly</changefreq>';
            echo '<priority>0.75</priority>';
            echo '</url>' . "\n";
        }
    }

    echo '</urlset>';
}

/**
 * Sitemap: Подкатегории /{city}/uslugi-i-ceny/{subcat}/
 */
function mrt_seo_sitemap_subcats() {
    $cities = mrt_seo_get_cities();
    $subcats = mrt_get_subcat_slugs();

    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    foreach ($cities as $city_slug => $city_name) {
        foreach ($subcats as $subcat) {
            if (mrt_is_subcategory_hidden_for_city($subcat, $city_slug)) {
                continue;
            }
            $xml .= '<url>';
            $xml .= '<loc>' . esc_url(home_url('/' . $city_slug . '/uslugi-i-ceny/' . $subcat . '/')) . '</loc>';
            $xml .= '<changefreq>weekly</changefreq>';
            $xml .= '<priority>0.7</priority>';
            $xml .= '</url>' . "\n";
        }
    }

    $xml .= '</urlset>';
    echo $xml;
}

/**
 * Sitemap: Articles (blog posts) /stati/
 */
function mrt_seo_sitemap_articles() {
    $posts = get_posts(['post_type' => 'article', 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'modified', 'order' => 'DESC']);
    mrt_seo_sitemap_output($posts, 'article');
}

// ============================================================
// 10. ROBOTS.TXT
// ============================================================
add_filter('robots_txt', 'mrt_seo_robots_txt', 999, 2);
function mrt_seo_robots_txt($output, $public) {
    $output = "User-agent: *\n";
    $output .= "Disallow: /wp-admin/\n";
    $output .= "Allow: /wp-admin/admin-ajax.php\n";
    $output .= "Disallow: /xmlrpc.php\n";
    $output .= "Disallow: /wp-login.php\n";
    $output .= "Disallow: /wp-signup.php\n";
    $output .= "Disallow: /wp-activate.php\n";
    $output .= "Disallow: /wp-trackback.php\n";
    $output .= "Disallow: /wp-links-opml.php\n";
    $output .= "Disallow: /readme.html\n";
    $output .= "Disallow: /license.txt\n";
    $output .= "Disallow: /wp-includes/\n";
    $output .= "Disallow: /wp-content/plugins/\n";
    $output .= "Disallow: /wp-content/uploads/cache/\n";
    $output .= "Disallow: /helium/\n";
    $output .= "Disallow: /page/\n";
    $output .= "\n";
    $output .= "Sitemap: " . home_url('/sitemap.xml') . "\n";
    return $output;
}

// ============================================================
// 11. REMOVE DUPLICATE WP META (canonical handled by our module)
// ============================================================
add_action('init', 'mrt_seo_remove_wp_canonical', 100);
function mrt_seo_remove_wp_canonical() {
    remove_action('wp_head', 'rel_canonical');
    remove_action('wp_head', 'wp_shortlink_wp_head');
}

// ============================================================
// 12. PRECONNECT
// ============================================================
add_action('wp_head', 'mrt_seo_preconnect', 1);
function mrt_seo_preconnect() {
    echo '<link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>' . "\n";
    echo '<link rel="preconnect" href="https://unpkg.com" crossorigin>' . "\n";
    echo '<link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>' . "\n";
}

// ============================================================
// 13. FLUSH REWRITE RULES (для sitemap endpoints)
// ============================================================
add_action('after_switch_theme', 'mrt_seo_flush_rewrite');
function mrt_seo_flush_rewrite() {
    mrt_seo_sitemap_init();
    flush_rewrite_rules();
}
