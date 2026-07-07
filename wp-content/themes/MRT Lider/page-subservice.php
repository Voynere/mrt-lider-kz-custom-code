<?php
/*
Template Name: subservice
*/

// Force OPcache refresh on redeploy
if (function_exists('opcache_invalidate')) {
    opcache_invalidate(__FILE__, true);
}

// Load subservice-specific content data
require_once __DIR__ . '/subservice-content.php';

$selected_city = mrt_get_selected_city_slug();
$path_parts = mrt_get_request_path_parts();

// --- Телефон города ---
$mrt_city_phone = function_exists('mrt_get_city_phone') ? mrt_get_city_phone($selected_city) : ['raw' => '+7 (3452) 500-735', 'href' => 'tel:+73452500735'];
$mrt_phone_raw = $mrt_city_phone['raw'] ?: '+7 (3452) 500-735';
$mrt_phone_href = $mrt_city_phone['href'] ?: 'tel:+73452500735';

// --- Маппинг: url-slug → название подкатегории → ключевые слова для поиска в si_category ---
$subcategory_map = [
    'mrt' => [
        'title' => 'МРТ',
        'h1' => 'Магнитно-резонансная томография',
        'si_category_keywords' => ['мрт', 'магнитно-резонансн', 'томограф'],
        'description' => 'МРТ (магнитно-резонансная томография) различных областей тела.',
        'image' => '',
    ],
    'mrt-golovy' => [
        'title' => 'МРТ Головы',
        'h1' => 'МРТ Головы',
        'si_category_keywords' => ['голов', 'головн', 'гипофиз', 'орбит', 'придаточ', 'пазух', 'ангиограф', 'онкопоиск', 'программ', 'комплексн'],
        'description' => 'МРТ головного мозга, гипофиза, орбит, придаточных пазух носа, ангиография сосудов, онкопоиск, комплексные программы.',
        'image' => '',
    ],
    'mrt-pozvonochnika' => [
        'title' => 'МРТ Позвоночника',
        'h1' => 'МРТ Позвоночника',
        'si_category_keywords' => ['позвоноч', 'спин', 'кресцов', 'копчик', 'крестц'],
        'description' => 'МРТ шейного, грудного, поясничного отделов позвоночника, крестца и копчика.',
        'image' => '',
    ],
    'mrt-sustavov' => [
        'title' => 'МРТ Суставов',
        'h1' => 'МРТ Суставов',
        'si_category_keywords' => ['сустав', 'колен', 'плеч', 'тазобедр', 'локт', 'голеностоп', 'кист', 'стоп'],
        'description' => 'МРТ коленного, плечевого, тазобедренного, локтевого, голеностопного суставов.',
        'image' => '',
    ],
    'mrt-brushnoy-polosti' => [
        'title' => 'МРТ Брюшной полости',
        'h1' => 'МРТ Брюшной полости',
        'si_category_keywords' => ['брюшн', 'живот', 'печен', 'желч', 'поджелуд', 'селезен', 'поче', 'надпочеч'],
        'description' => 'МРТ органов брюшной полости: печень, желчный пузырь, поджелудочная, селезенка, почки.',
        'image' => '',
    ],
    'mrt-malogo-taza' => [
        'title' => 'МРТ Малого таза',
        'h1' => 'МРТ Малого таза',
        'si_category_keywords' => ['малого таза', 'мочевого', 'предстательной', 'яични', 'матк', 'мошонк'],
        'si_category_exclude' => ['тазобедр', 'сустав'],
        'description' => 'МРТ органов малого таза: мочевой пузырь, предстательная железа, яичники, матка.',
        'image' => '',
    ],
    'mrt-serdca-i-sosudov' => [
        'title' => 'МРТ Сердца и сосудов',
        'h1' => 'МРТ Сердца и сосудов',
        'si_category_keywords' => ['сердц', 'сосуд', 'аорт', 'коронар', 'миокард', 'вен'],
        'description' => 'МРТ сердца, аорты, коронарных артерий, венозной системы.',
        'image' => '',
    ],
    'mrt-grudnoy-kletki' => [
        'title' => 'МРТ Грудной клетки',
        'h1' => 'МРТ Грудной клетки',
        'si_category_keywords' => ['грудн', 'легк', 'средостен', 'ребер', 'молоч', 'молочн'],
        'description' => 'МРТ органов грудной клетки: легкие, средостение, ребра, молочные железы, мягкие ткани грудной стенки.',
        'image' => '',
    ],
    'mrt-myagkih-tkaney' => [
        'title' => 'МРТ Мягких тканей',
        'h1' => 'МРТ Мягких тканей',
        'si_category_keywords' => ['мягк', 'мышц', 'связ', 'сухожил', 'жиров', 'онкопоиск'],
        'description' => 'МРТ мягких тканей: мышцы, связки, сухожилия, жировая клетчатка.',
        'image' => '',
    ],
    'kt' => [
        'title' => 'КТ',
        'h1' => 'Компьютерная томография',
        'si_category_keywords' => ['кт', 'компьютерн', 'томограф'],
        'description' => 'Компьютерная томография (КТ) различных областей тела.',
        'image' => '',
    ],
    // --- КТ по анатомическим областям ---
    'kt-golovy' => [
        'title' => 'КТ Головы',
        'h1' => 'КТ Головы',
        'si_category_keywords' => ['голов', 'головн', 'гипофиз', 'орбит', 'пазух', 'ангиограф', 'челюст', 'ше'],
        'description' => 'КТ головного мозга, лицевого черепа, придаточных пазух носа, орбит, височных костей, шеи.',
        'image' => '',
    ],
    'kt-gortani' => [
        'title' => 'КТ Гортани',
        'h1' => 'КТ Гортани',
        'si_category_keywords' => ['гортан'],
        'description' => 'КТ гортани: структуры гортани, надгортанник, хрящи гортани, складки, голосовые связки, трахея.',
        'image' => '',
    ],
    'kt-pozvonochnika' => [
        'title' => 'КТ Позвоночника',
        'h1' => 'КТ Позвоночника',
        'si_category_keywords' => ['позвоноч', 'спин', 'кресцов', 'копчик', 'крестц'],
        'description' => 'КТ шейного, грудного, поясничного отделов позвоночника, крестца и копчика.',
        'image' => '',
    ],
    'kt-sustavov' => [
        'title' => 'КТ Суставов и костей',
        'h1' => 'КТ Суставов и костей',
        'si_category_keywords' => ['сустав', 'колен', 'плеч', 'локт', 'голеностоп', 'кист', 'стоп', 'кост'],
        'description' => 'КТ суставов и костей: коленного, плечевого, тазобедренного, локтевого, голеностопного.',
        'image' => '',
    ],
    'kt-brushnoy-polosti' => [
        'title' => 'КТ Брюшной полости',
        'h1' => 'КТ Брюшной полости',
        'si_category_keywords' => ['брюшн', 'живот', 'печен', 'желч', 'поджелуд', 'селезен', 'поче', 'надпочеч'],
        'description' => 'КТ органов брюшной полости и забрюшинного пространства: печень, почки, поджелудочная, селезенка.',
        'image' => '',
    ],
    'kt-malogo-taza' => [
        'title' => 'КТ Малого таза',
        'h1' => 'КТ Малого таза',
        'si_category_keywords' => ['малого таза', 'мочевого', 'предстательной', 'яични', 'матк', 'мошонк'],
        'si_category_exclude' => ['тазобедр', 'сустав'],
        'description' => 'КТ органов малого таза: мочевой пузырь, предстательная железа, яичники, матка.',
        'image' => '',
    ],
    'kt-serdca-i-sosudov' => [
        'title' => 'КТ Сердца и сосудов',
        'h1' => 'КТ Сердца и сосудов',
        'si_category_keywords' => ['сердц', 'сосуд', 'аорт', 'коронар', 'миокард', 'вен'],
        'description' => 'КТ сердца, аорты, коронарных артерий, сосудов различных областей.',
        'image' => '',
    ],
    'kt-grudnoy-kletki' => [
        'title' => 'КТ Грудной клетки',
        'h1' => 'КТ Грудной клетки',
        'si_category_keywords' => ['грудн', 'легк', 'средостен', 'ребер', 'молоч'],
        'description' => 'КТ органов грудной клетки: легкие, средостение, ребра.',
        'image' => '',
    ],
    'kt-myagkih-tkaney' => [
        'title' => 'КТ Мягких тканей',
        'h1' => 'КТ Мягких тканей',
        'si_category_keywords' => ['мягк', 'мышц', 'связ', 'сухожил', 'жиров'],
        'description' => 'КТ мягких тканей: мышцы, связки, сухожилия, жировая клетчатка.',
        'image' => '',
    ],
    'kt-kompleksnye' => [
        'title' => 'КТ Комплексные программы',
        'h1' => 'КТ Комплексные программы',
        'si_category_keywords' => ['программ', 'комплексн', 'всего тела', 'энтерограф', 'колоноскоп', 'пантомограф'],
        'description' => 'КТ комплексные программы: КТ всего тела, энтерография, виртуальная колоноскопия.',
        'image' => '',
    ],
    'uzi' => [
        'title' => 'УЗИ',
        'h1' => 'Ультразвуковая диагностика',
        'si_category_keywords' => ['узи', 'ультразвук'],
        'description' => 'УЗИ органов и систем организма.',
        'image' => '',
    ],
    // --- УЗИ по анатомическим областям ---
    'uzi-golovy' => [
        'title' => 'УЗИ Головы и шеи',
        'h1' => 'УЗИ Головы и шеи',
        'si_category_keywords' => ['голов', 'головн', 'глаз', 'зритель', 'орбит', 'слюн', 'щитовид', 'паращитовид', 'брахиоцефаль', 'ше', 'гортан'],
        'description' => 'УЗИ головы и шеи: щитовидной железы, слюнных желез, брахиоцефальных артерий, глазных орбит.',
        'image' => '',
    ],
    'uzi-serdca-i-sosudov' => [
        'title' => 'УЗИ Сердца и сосудов',
        'h1' => 'УЗИ Сердца и сосудов',
        'si_category_keywords' => ['сердц', 'сосуд', 'аорт', 'вен', 'артери', 'дуплекс', 'доплер', 'допплер'],
        'description' => 'УЗИ сердца (эхокардиография), дуплексное сканирование сосудов, допплерография артерий и вен.',
        'image' => '',
    ],
    'uzi-brushnoy-polosti' => [
        'title' => 'УЗИ Брюшной полости',
        'h1' => 'УЗИ Брюшной полости',
        'si_category_keywords' => ['брюшн', 'живот', 'печен', 'желч', 'поджелуд', 'селезен', 'поче', 'надпочеч', 'кишеч', 'желуд', 'пищевод'],
        'description' => 'УЗИ органов брюшной полости и забрюшинного пространства: печень, желчный пузырь, поджелудочная, почки, селезенка.',
        'image' => '',
    ],
    'uzi-malogo-taza' => [
        'title' => 'УЗИ Малого таза',
        'h1' => 'УЗИ Малого таза',
        'si_category_keywords' => ['малого таза', 'мочевого', 'предстательной', 'простат', 'яични', 'матк', 'мошонк', 'беремен'],
        'si_category_exclude' => ['тазобедр', 'сустав'],
        'description' => 'УЗИ органов малого таза: мочевой пузырь, простата, матка, яичники, мошонка.',
        'image' => '',
    ],
    'uzi-molochnykh-zhelez' => [
        'title' => 'УЗИ Молочных желез',
        'h1' => 'УЗИ Молочных желез',
        'si_category_keywords' => ['молоч', 'грудн', 'желез'],
        'si_category_exclude' => ['плевр', 'легк', 'средостен'],
        'description' => 'УЗИ молочных (грудных) желез.',
        'image' => '',
    ],
    'uzi-sustavov' => [
        'title' => 'УЗИ Суставов',
        'h1' => 'УЗИ Суставов',
        'si_category_keywords' => ['сустав', 'колен', 'плеч', 'локт', 'голеностоп', 'кист', 'стоп', 'лучезапяс'],
        'description' => 'УЗИ суставов: коленного, плечевого, локтевого, лучезапястного, голеностопного.',
        'image' => '',
    ],
    'uzi-myagkih-tkaney' => [
        'title' => 'УЗИ Мягких тканей',
        'h1' => 'УЗИ Мягких тканей',
        'si_category_keywords' => ['мягк', 'мышц', 'лимф', 'подкож'],
        'description' => 'УЗИ мягких тканей, лимфатических узлов, подкожной клетчатки.',
        'image' => '',
    ],
    'uzi-grudnoy-kletki' => [
        'title' => 'УЗИ Грудной клетки',
        'h1' => 'УЗИ Грудной клетки',
        'si_category_keywords' => ['грудн', 'плевр', 'легк', 'средостен', 'ребер'],
        'si_category_exclude' => ['желез', 'молоч'],
        'description' => 'УЗИ грудной клетки: плевральных полостей, легких, средостения.',
        'image' => '',
    ],
    'uzi-nervnoj-sistemy' => [
        'title' => 'УЗИ Нервной системы',
        'h1' => 'УЗИ Нервной системы',
        'si_category_keywords' => ['нерв', 'сплетен', 'карпаль', 'средин'],
        'description' => 'УЗИ периферической нервной системы: срединного, локтевого, лучевого нервов, плечевого сплетения.',
        'image' => '',
    ],
    'densitometriya' => [
        'title' => 'Денситометрия',
        'h1' => 'Денситометрия',
        'si_category_keywords' => ['денситометр', 'плотност', 'кост'],
        'description' => 'Денситометрия — измерение плотности костной ткани.',
        'image' => '',
    ],
];

// --- Определяем slug подкатегории из URL ---
$subcat_slug = '';
$idx = array_search('uslugi-i-ceny', $path_parts);
if ($idx !== false && isset($path_parts[$idx + 1]) && !empty($path_parts[$idx + 1]) && $path_parts[$idx + 1] !== 'price') {
    $subcat_slug = sanitize_text_field($path_parts[$idx + 1]);
}

if (!isset($subcategory_map[$subcat_slug])) {
    get_header();
    echo '<main class="main"><div class="container"><p>Категория не найдена</p></div></main>';
    get_footer();
    exit;
}

if (mrt_is_subcategory_hidden_for_city($subcat_slug, $selected_city)) {
    global $wp_query;
    $wp_query->set_404();
    status_header(404);
    nocache_headers();
    get_header();
    echo '<main class="main"><div class="container"><p>Услуга не найдена.</p></div></main>';
    get_footer();
    exit;
}

$subcat_data = $subcategory_map[$subcat_slug];
$subcat_title = $subcat_data['h1'];
$subcat_desc = $subcat_data['description'];

// Load extended content for this subcategory
$subsvc_content = mrt_get_subservice_content($subcat_slug);

// Add tomograph type hint from query param (?type=1.5 or ?type=3)
$tomograph_type = $_GET['type'] ?? '';
$type_suffix = '';
if ($tomograph_type === '1.5' || $tomograph_type === '3') {
    $type_suffix = ' ' . $tomograph_type . ' Тесла';
}
$keywords = $subcat_data['si_category_keywords'];
$exclude_words = $subcat_data['si_category_exclude'] ?? [];

// --- Ищем услуги для города по si_category ---
$branch_term = mrt_get_branch_term_for_city($selected_city);
if (!$branch_term) {
    get_header();
    echo '<main class="main"><div class="container"><p>Для данного филиала данных не найдено</p></div></main>';
    get_footer();
    exit;
}

// --- Запрос услуг ---
$all_services = get_posts([
    'post_type' => 'service',
    'posts_per_page' => -1,
    'post_status' => 'publish',
    'tax_query' => [[
        'taxonomy' => 'branch',
        'field' => 'slug',
        'terms' => $branch_term->slug,
    ]],
]);

// --- Определяем допустимые service_type для подкатегории ---
// Берём из глобальной переменной, установленной в seo-config.php (надёжнее чем локальное вычисление)
$allowed_service_type_pattern = $GLOBALS['mrt_subcat_allowed_svc_type'] ?? null;
if ($allowed_service_type_pattern === null) {
    // Fallback: локальное вычисление
    if (strpos($subcat_slug, 'mrt-') === 0) {
        $allowed_service_type_pattern = 'мрт';
    } elseif ($subcat_slug === 'kt') {
        $allowed_service_type_pattern = 'кт';
    } elseif ($subcat_slug === 'uzi') {
        $allowed_service_type_pattern = 'узи';
    } elseif ($subcat_slug === 'densitometriya') {
        $allowed_service_type_pattern = 'денситометр';
    }
}

// Фильтруем по si_category + service_type (exclude first, then include; use unique IDs)
$matching_services = [];
foreach ($all_services as $svc) {
    $cat = get_post_meta($svc->ID, 'si_category', true);
    if (!$cat) continue;
    $cat_lower = mb_strtolower($cat, 'UTF-8');

    // Check exclusion keywords — if any match, skip
    $excluded = false;
    foreach ($exclude_words as $ex) {
        if (strpos($cat_lower, $ex) !== false) { $excluded = true; break; }
    }
    if ($excluded) continue;

    // Check category keyword match
    $cat_match = false;
    foreach ($keywords as $kw) {
        if (strpos($cat_lower, $kw) !== false) { $cat_match = true; break; }
    }
    if (!$cat_match) continue;

    // Check service_type filter (КТ/УЗИ не должны попадать в МРТ и наоборот)
    if ($allowed_service_type_pattern !== null) {
        $svc_types = wp_get_post_terms($svc->ID, 'service_type');
        $svc_type_name = !empty($svc_types) ? mb_strtolower($svc_types[0]->name, 'UTF-8') : '';
        if (strpos($svc_type_name, $allowed_service_type_pattern) === false) {
            continue; // тип услуги не соответствует подкатегории — пропускаем
        }
    }

    $matching_services[$svc->ID] = $svc;
}
$matching_services = array_values($matching_services);
$grouped = [];
$all_types = [];
foreach ($matching_services as $svc) {
    $types = wp_get_post_terms($svc->ID, 'service_type');
    $type_name = !empty($types) ? $types[0]->name : 'Прочее';
    if (!isset($grouped[$type_name])) $grouped[$type_name] = [];
    $grouped[$type_name][] = $svc;
    if (!in_array($type_name, $all_types)) $all_types[] = $type_name;
}

// Сортировка услуг внутри каждой группы: простые выше, комплексы/программы ниже
foreach ($grouped as $type_name => &$items) {
    usort($items, function($a, $b) {
        $a_title = mb_strtolower($a->post_title, 'UTF-8');
        $b_title = mb_strtolower($b->post_title, 'UTF-8');
        $a_is_complex = (strpos($a_title, 'комплекс') !== false || strpos($a_title, 'программ') !== false);
        $b_is_complex = (strpos($b_title, 'комплекс') !== false || strpos($b_title, 'программ') !== false);
        if ($a_is_complex && !$b_is_complex) return 1;
        if (!$a_is_complex && $b_is_complex) return -1;
        return strcmp($a_title, $b_title);
    });
}
unset($items);

// Сортировка service_type
usort($all_types, function($a, $b) {
    $order = ['МРТ 1.5 тесла' => 1, 'МРТ 1.5 Т' => 1, 'МРТ 3 тесла' => 2, 'МРТ 3.0 Т' => 2, 'МРТ 3 Т' => 2, 'КТ' => 3, 'УЗИ' => 4, 'Денситометрия' => 5];
    $pa = $order[$a] ?? 999;
    $pb = $order[$b] ?? 999;
    return $pa - $pb;
});

// --- Город в предложном падеже для H1 ---
$city_gen_h1 = mrt_seo_city_genitive($selected_city);

// --- тенге ---
$kazakhstan_cities = mrt_get_kz_cities();
$use_tenge = in_array($selected_city, $kazakhstan_cities, true);
$currency_symbol = $use_tenge ? '₸' : '₽';
$show_concessional_notice = mrt_should_show_concessional_price_notice(
    $selected_city,
    mrt_posts_have_discounted_price($matching_services, $selected_city)
);

get_header();
?>

<main class="main">
    <div class="main-background">
        <?php if (!is_front_page()) custom_breadcrumbs(); ?>

        <section class="subservice-section" style="padding: 40px 0;">
            <div class="container">
                <h1 class="subservice-section__title">
                    <?php echo esc_html($subcat_title . $type_suffix . ' в ' . $city_gen_h1 . ' — цены, запись в МРТ Лидер'); ?>
                </h1>

                <?php if ($show_concessional_notice) {
                    echo mrt_render_concessional_price_notice($selected_city);
                } ?>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:32px;align-items:start;margin-bottom:32px;">
                    <div>
                        <p style="font-size:16px;color:#4b5563;line-height:1.7;">
                            <?php
                            $subsvc_intro = $subsvc_content['seo_text'] ?: $subcat_desc;
                            echo esc_html(mrt_apply_city_equipment_branding($subsvc_intro, $selected_city));
                            ?>
                        </p>
                    </div>
                    <div style="background:#f0f9ff;border-radius:16px;padding:24px;text-align:center;">
                        <div style="font-size:14px;color:#6b7280;margin-bottom:8px;">Запись на исследование</div>
                        <a href="<?php echo esc_url($mrt_phone_href); ?>" style="font-size:22px;font-weight:700;color:#2563eb;text-decoration:none;display:block;margin-bottom:12px;"><?php echo esc_html($mrt_phone_raw); ?></a>
                        <a href="#" class="booking-btn"
                            data-service-category="<?php echo esc_attr($subcat_title . $type_suffix); ?>"
                            data-service-oblast="<?php echo esc_attr($subcat_title); ?>"
                            data-city="<?php echo esc_attr($selected_city); ?>"
                            style="display:inline-block;background:#2563eb;color:#fff;padding:12px 32px;border-radius:12px;text-decoration:none;font-weight:600;margin-bottom:8px;">Записаться онлайн</a>
                        <div style="font-size:12px;color:#9ca3af;">Звонок по всей России</div>
                    </div>
                </div>

                <!-- Что показывает исследование -->
                <?php if (!empty($subsvc_content['what_shows'])): ?>
                <div style="margin-bottom:32px;padding:24px;background:#f9fafb;border-radius:16px;">
                    <h2 style="font-size:20px;font-weight:700;color:#1f2937;margin-bottom:16px;">Что показывает <?php echo esc_html($subcat_title); ?>?</h2>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                        <?php foreach ($subsvc_content['what_shows'] as $ws): ?>
                        <div style="display:flex;align-items:flex-start;gap:8px;padding:8px 12px;background:#fff;border-radius:8px;border:1px solid #e5e7eb;">
                            <span style="color:#22c55e;font-size:16px;line-height:1.4;">✓</span>
                            <span style="font-size:14px;color:#374151;line-height:1.4;"><?php echo esc_html($ws); ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($all_types)): ?>
                    <?php foreach ($all_types as $type_name): ?>
                        <?php $items = $grouped[$type_name] ?? []; if (empty($items)) continue; ?>
                        <div style="margin-bottom:24px;">
                            <h2 style="font-size:20px;font-weight:600;color:#374151;margin-bottom:12px;border-bottom:2px solid #2563eb;padding-bottom:8px;">
                                <?php echo esc_html($type_name); ?>
                            </h2>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                                <?php foreach ($items as $item):
                                    $price_raw = get_post_meta($item->ID, 'si_price', true);
                                    $discount_raw = get_post_meta($item->ID, 'si_discount', true);
                                    $landing_url = home_url('/' . $selected_city . '/services/' . $item->post_name . '/');
                                    $type_concessional = mrt_city_uses_concessional_pricing($selected_city, '', $type_name);
                                    $price_parts = mrt_service_price_parts_from_meta($price_raw, $discount_raw, $type_concessional);
                                ?>
                                    <a href="<?php echo esc_url($landing_url); ?>" style="display:flex;justify-content:space-between;align-items:center;padding:10px 16px;background:#fff;border:1px solid #e5e7eb;border-radius:10px;text-decoration:none;color:inherit;transition:border-color 0.15s,box-shadow 0.15s;" onmouseover="this.style.borderColor='#2563eb';this.style.boxShadow='0 2px 8px rgba(37,99,235,0.12)'" onmouseout="this.style.borderColor='#e5e7eb';this.style.boxShadow='none'">
                                        <span style="font-size:14px;color:#374151;"><?php echo esc_html(mrt_get_service_oblast_display($item->ID)); ?></span>
                                        <span style="font-weight:600;color:#2563eb;white-space:nowrap;text-align:right;">
                                            <?php
                                            if (($price_parts['mode'] ?? '') === 'text'):
                                                echo esc_html((string) ($price_parts['text'] ?? '—'));
                                            elseif ($type_concessional):
                                            ?>
                                                <?php
                                                $base = (int) ($price_parts['base'] ?? 0);
                                                if ($base > 0) {
                                                    echo esc_html(mrt_format_price_amount($base, $currency_symbol));
                                                    if (!empty($price_parts['has_concessional'])) {
                                                        echo '<br><span style="font-size:12px;font-weight:500;color:#6b7280;">от '
                                                            . esc_html(mrt_format_price_amount((int) $price_parts['concessional'], $currency_symbol))
                                                            . ' <span class="price__badge">льготная</span></span>';
                                                    }
                                                } else {
                                                    echo '—';
                                                }
                                                ?>
                                            <?php else: ?>
                                                <?php
                                                $show_crossed = (int) ($price_parts['old'] ?? 0);
                                                $show_current = (int) ($price_parts['current'] ?? 0);
                                                if ($show_crossed > 0 && $show_crossed !== $show_current): ?>
                                                    <span style="text-decoration:line-through;color:#9ca3af;margin-right:6px;font-size:13px;"><?php echo esc_html(mrt_format_price_amount($show_crossed, $currency_symbol)); ?></span>
                                                <?php endif; ?>
                                                <?php echo esc_html($show_current ? mrt_format_price_amount($show_current, $currency_symbol) : '—'); ?>
                                            <?php endif; ?>
                                        </span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="text-align:center;padding:40px;color:#9ca3af;">
                        Услуги в данной категории временно недоступны. Позвоните по телефону <a href="<?php echo esc_url($mrt_phone_href); ?>" style="color:#2563eb;font-weight:600;"><?php echo esc_html($mrt_phone_raw); ?></a> для уточнения.
                    </div>
                <?php endif; ?>

                <!-- Связанные категории (внутренние ссылки) -->
                <?php if (!empty($subsvc_content['related'])): ?>
                <div style="margin-top:40px;padding:24px;background:#eff6ff;border-radius:16px;border:1px solid #bfdbfe;">
                    <h2 style="font-size:20px;font-weight:700;color:#1e40af;margin-bottom:16px;">См. также</h2>
                    <div style="display:flex;flex-wrap:wrap;gap:12px;">
                        <?php foreach ($subsvc_content['related'] as $rel_slug):
                            if (!isset($subcategory_map[$rel_slug])) continue;
                            $rel_data = $subcategory_map[$rel_slug];
                            $rel_url = home_url('/' . $selected_city . '/uslugi-i-ceny/' . $rel_slug . '/');
                        ?>
                        <a href="<?php echo esc_url($rel_url); ?>" style="display:inline-block;padding:10px 20px;background:#fff;border:1px solid #93c5fd;border-radius:10px;text-decoration:none;color:#1e40af;font-weight:600;font-size:14px;transition:border-color 0.15s;" onmouseover="this.style.borderColor='#2563eb'" onmouseout="this.style.borderColor='#93c5fd'">
                            <?php echo esc_html($rel_data['title']); ?> в <?php echo esc_html($city_gen_h1); ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </section>

        <?php get_template_part('template-parts/tour-or-animals-map'); ?>

    </div>
</main>

<?php include __DIR__ . '/template-parts/booking-modal.php'; ?>
<?php get_footer(); ?>
