<?php

require get_template_directory() . '/inc/mrt-city-config.php';
require get_template_directory() . '/inc/mrt-header-helpers.php';
require get_template_directory() . '/inc/mrt-service-helpers.php';
require get_template_directory() . '/inc/mrt-city-routing.php';
require get_template_directory() . '/inc/mrt-service-routing.php';
require get_template_directory() . '/seo-config.php';

add_theme_support('title-tag');

add_filter('body_class', function ($classes) {
    $slug = mrt_resolve_selected_city();
    if (mrt_is_animals_branch($slug)) {
        $classes[] = 'mrt-animals-branch';
    }
    return $classes;
});

add_action( 'wp_enqueue_scripts', function () {

	$style_path = get_template_directory() . '/assets/css/style.min.css';
	$style_uri  = get_template_directory_uri() . '/assets/css/style.min.css';
	$version    = file_exists($style_path) ? filemtime($style_path) : null;
	wp_enqueue_style( 'style', $style_uri, [], $version );
    $header_ru_css = get_template_directory() . '/assets/css/header-ru.css';
    if (file_exists($header_ru_css)) {
        wp_enqueue_style(
            'mrt-header-ru',
            get_template_directory_uri() . '/assets/css/header-ru.css',
            array('style'),
            filemtime($header_ru_css)
        );
    }
    wp_enqueue_style( 'complect-style', 'https://cdn.jsdelivr.net/npm/@fancyapps/ui@6.0/dist/fancybox/fancybox.css', array(), '6.0' );
    wp_enqueue_script( 'fancybox-script', 'https://cdn.jsdelivr.net/npm/@fancyapps/ui@6.0/dist/fancybox/fancybox.umd.js', array(), '6.0', true );

    // Привязываем bind 
    $init_js = <<<JS
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof Fancybox !== 'undefined' && Fancybox.bind) {
            Fancybox.bind('[data-fancybox]', {
            });
        }
    });
    JS;

    wp_add_inline_script( 'fancybox-script', $init_js );
	wp_enqueue_script( 'imask', 'https://unpkg.com/imask', array(), null, true );
	wp_enqueue_script( 'imask', 'https://cdnjs.cloudflare.com/ajax/libs/imask/7.5.3/imask.min.js', array(), '7.5.3', true );
	wp_enqueue_script( 'main', get_template_directory_uri() . '/assets/js/main.js', array('jquery'), '1.6.5', true );
	wp_enqueue_script( 'cookie', get_template_directory_uri() . '/assets/js/cookie.js', array('jquery'), '1.2.3', true );
	wp_enqueue_script( 'city-chosen', get_template_directory_uri() . '/assets/js/city-chosen.js', array('jquery'), '1.0.3', true );
	wp_enqueue_script( 'tax', get_template_directory_uri() . '/assets/js/tax.js', array('jquery'), 'null', true );
	wp_enqueue_script( 'booking', get_template_directory_uri() . '/assets/js/booking.js', array('jquery'), 'null', true );
    if (is_page('vopros-otvet')) {
        wp_enqueue_script(
            'answers-script',
            get_template_directory_uri() . '/assets/js/answers.js',
            array(),
            'null',
            true 
        );
    }
    if (is_page('pravovaja-i-juridicheskaja-informacija')) {
        wp_enqueue_script(
            'license',
            get_template_directory_uri() . '/assets/js/license.js',
            array(),
            '1.3',
            true 
        );
    }

    if (mrt_page_needs_closing_countdown()) {
        wp_enqueue_script(
            'mrt-closing-countdown',
            get_template_directory_uri() . '/assets/js/closing-countdown.js',
            array(),
            file_exists(get_template_directory() . '/assets/js/closing-countdown.js')
                ? filemtime(get_template_directory() . '/assets/js/closing-countdown.js')
                : null,
            true
        );
    }

    $search_js = get_template_directory() . '/assets/js/search.js';
    wp_enqueue_script(
        'mrt-search',
        get_template_directory_uri() . '/assets/js/search.js',
        array(),
        file_exists($search_js) ? filemtime($search_js) : '1.0.0',
        true
    );

    $selected_slug = mrt_resolve_selected_city();
    if (mrt_is_animals_branch($selected_slug) || is_page_template('home-animals.php')) {
        wp_enqueue_style(
            'mrt-animals',
            get_template_directory_uri() . '/assets/css/animals.css',
            array('mrt-header-ru'),
            filemtime(get_template_directory() . '/assets/css/animals.css')
        );
        $metrika_js = get_template_directory() . '/assets/js/mrt-metrika.js';
        wp_enqueue_script(
            'mrt-metrika',
            get_template_directory_uri() . '/assets/js/mrt-metrika.js',
            array(),
            file_exists($metrika_js) ? filemtime($metrika_js) : '1.0.0',
            true
        );
    }

    wp_localize_script('main', 'mrtCityConfig', array(
        'cityMap'       => mrt_get_city_map(),
        'knownSlugs'    => mrt_get_known_city_slugs(),
        'animalsSlugs'  => array_values(array_filter(mrt_get_known_city_slugs(), 'mrt_is_animals_branch')),
        'citySpecificPages' => mrt_get_city_specific_page_slugs(),
    ));

    $service_landing_css = get_template_directory() . '/assets/css/service-landing.css';
    if (file_exists($service_landing_css) && (mrt_seo_is_service_landing() || !empty(get_query_var('mrt_subcat')))) {
        wp_enqueue_style(
            'mrt-service-landing',
            get_template_directory_uri() . '/assets/css/service-landing.css',
            array('style'),
            filemtime($service_landing_css)
        );
    }

    $request_uri_for_assets = $_SERVER['REQUEST_URI'] ?? '';
    if (preg_match('#/uslugi-i-ceny/price/#', $request_uri_for_assets)) {
        $price_js = get_template_directory() . '/assets/js/price.js';
        wp_enqueue_script(
            'price',
            get_template_directory_uri() . '/assets/js/price.js',
            array(),
            file_exists($price_js) ? filemtime($price_js) : '1.0.0',
            true
        );
    }
});

require get_template_directory() . '/vacancies.php';


# Добавляем SVG в список разрешенных для загрузки файлов
function allow_svg_upload($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    $mimes['svgz'] = 'image/svg+xml';
    return $mimes;
}
add_filter('upload_mimes', 'allow_svg_upload');


// Хлебные крошки
function custom_breadcrumbs() {
    $selected_city = mrt_get_selected_city_slug();
    $city_base_url = mrt_get_city_base_url($selected_city);

    $request_uri = $_SERVER['REQUEST_URI'] ?? '/';
    $path = parse_url($request_uri, PHP_URL_PATH);
    $path = trim((string) $path, '/');
    $path_parts = $path ? explode('/', $path) : array();

    $post = get_post();
    $is_uslugi_itself = ($post && $post->post_name === 'uslugi-i-ceny');
    $is_uslugi_child = $post && $post->post_parent && ($parent = get_post($post->post_parent)) && $parent->post_name === 'uslugi-i-ceny';
    $is_service_page = ($post && get_page_template_slug($post) === 'page-service-item.php');

    echo '<div class="breadcrumbs"><div class="container"><ul class="breadcrumbs__list">';
    echo '<li><a href="' . esc_url($city_base_url) . '">Главная</a></li>';

    $uslugi_page_url = trailingslashit($city_base_url . 'uslugi-i-ceny');
    $uslugi_title = 'Услуги и цены';

    $show_uslugi_link = ($is_service_page || $is_uslugi_child || (count($path_parts) >= 3 && ($path_parts[1] ?? '') === 'uslugi-i-ceny')) && !$is_uslugi_itself;

    if ($show_uslugi_link) {
        echo '<li><svg width="8" height="17" viewBox="0 0 8 17" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M0 1.47L0.8625 0.5L8 8.5L0.8625 16.5L0 15.535L6.27083 8.5L0 1.47Z" fill="#404040"/></svg></li>';
        echo '<li><a href="' . esc_url($uslugi_page_url) . '">' . esc_html($uslugi_title) . '</a></li>';
    }

    $current_title = get_the_title();

    if (count($path_parts) >= 3 && ($path_parts[1] ?? '') === 'uslugi-i-ceny' && ($path_parts[2] ?? '') !== 'price') {
        $subcat_slug = $path_parts[2];
        $subcat_names = array(
            'mrt-golovy' => 'МРТ Головы', 'mrt-pozvonochnika' => 'МРТ Позвоночника',
            'mrt-sustavov' => 'МРТ Суставов', 'mrt-brushnoy-polosti' => 'МРТ Брюшной полости',
            'mrt-malogo-taza' => 'МРТ Малого таза', 'mrt-serdca-i-sosudov' => 'МРТ Сердца и сосудов',
            'mrt-grudnoy-kletki' => 'МРТ Грудной клетки', 'mrt-myagkih-tkaney' => 'МРТ Мягких тканей',
            'kt' => 'КТ', 'uzi' => 'УЗИ', 'densitometriya' => 'Денситометрия',
        );
        $current_title = $subcat_names[$subcat_slug] ?? $subcat_slug;
    } elseif (count($path_parts) >= 4 && ($path_parts[1] ?? '') === 'uslugi-i-ceny' && ($path_parts[2] ?? '') === 'price') {
        $price_slug = $path_parts[3];
        $service_term = get_term_by('slug', $price_slug, 'service_type');
        if ($service_term) {
            $current_title = $service_term->name;
        }
    }

    if ($is_service_page || $is_uslugi_child || (count($path_parts) >= 2 && ($path_parts[1] ?? '') === 'uslugi-i-ceny')) {
        echo '<li><svg width="8" height="17" viewBox="0 0 8 17" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M0 1.47L0.8625 0.5L8 8.5L0.8625 16.5L0 15.535L6.27083 8.5L0 1.47Z" fill="#404040"/></svg></li>';
        echo '<li>' . esc_html($current_title) . '</li>';
    } elseif (is_single()) {
        $categories = get_the_category();
        if (!empty($categories)) {
            $first_category = $categories[0];
            echo '<li><svg width="8" height="17" viewBox="0 0 8 17" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M0 1.47L0.8625 0.5L8 8.5L0.8625 16.5L0 15.535L6.27083 8.5L0 1.47Z" fill="#404040"/></svg></li>';
            echo '<li><a href="' . esc_url(get_category_link($first_category->term_id)) . '">' . esc_html($first_category->name) . '</a></li>';
        }
        echo '<li><svg width="8" height="17" viewBox="0 0 8 17" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M0 1.47L0.8625 0.5L8 8.5L0.8625 16.5L0 15.535L6.27083 8.5L0 1.47Z" fill="#404040"/></svg></li>';
        echo '<li>' . esc_html($current_title) . '</li>';
    } elseif (!is_front_page()) {
        echo '<li><svg width="8" height="17" viewBox="0 0 8 17" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M0 1.47L0.8625 0.5L8 8.5L0.8625 16.5L0 15.535L6.27083 8.5L0 1.47Z" fill="#404040"/></svg></li>';
        echo '<li>' . esc_html($current_title) . '</li>';
    }

    echo '</ul></div></div>';
}


// Для карточки специалиста
add_filter('single_template', 'custom_single_template');
function custom_single_template($template) {
    global $post;
    
    if (has_term('specialisty', 'category', $post)) {
        $new_template = locate_template('single-specialist.php');
        if ($new_template) return $new_template;
    }
    
    return $template;
}

function services_rewrite_rules() {
    add_rewrite_rule(
        '^uslugi-i-ceny/price/([^/]+)/?',
        'index.php?pagename=uslugi-i-ceny/price&service_type=$matches[1]',
        'top'
    );
}
add_action('init', 'services_rewrite_rules');
function services_query_vars($vars) {
    $vars[] = 'service_type';
    return $vars;
}
add_filter('query_vars', 'services_query_vars');


// Подключение обработчика формы
add_action('wp_ajax_send_contact_form', 'handle_contact_form');
add_action('wp_ajax_nopriv_send_contact_form', 'handle_contact_form');
function handle_contact_form() {
    include get_template_directory() . '/template-parts/send-contact-form.php';
}
// Подключение обработчика формы главной страницы
add_action('wp_ajax_send_main_form', 'handle_main_form');
add_action('wp_ajax_nopriv_send_main_form', 'handle_main_form');
function handle_main_form() {
    include get_template_directory() . '/template-parts/send-home-form.php';
}
// Подключение обработчика формы модального окна
add_action('wp_ajax_send_booking_form', 'handle_booking_form');
add_action('wp_ajax_nopriv_send_booking_form', 'handle_booking_form');
function handle_booking_form() {
    include get_template_directory() . '/template-parts/send-popup-form.php';
}
// Подключение обработчика формы модального окна с услугой
add_action('wp_ajax_send_booking_form_with_service', 'handle_booking_form_with_service');
add_action('wp_ajax_nopriv_send_booking_form_with_service', 'handle_booking_form_with_service');
function handle_booking_form_with_service() {
    include get_template_directory() . '/template-parts/send-service-form.php';
}
// Подключение обработчика налоговой формы
add_action('wp_ajax_send_tax_form', 'handle_tax_form');
add_action('wp_ajax_nopriv_send_tax_form', 'handle_tax_form');
function handle_tax_form() {
    include get_template_directory() . '/template-parts/send-tax-form.php';
}

/**
 * AJAX: site search (services in city, articles, FAQ).
 */
add_action('wp_ajax_mrt_site_search', 'mrt_handle_site_search');
add_action('wp_ajax_nopriv_mrt_site_search', 'mrt_handle_site_search');
function mrt_handle_site_search(): void {
    $q = isset($_GET['q']) ? sanitize_text_field(wp_unslash($_GET['q'])) : '';
    $city = isset($_GET['city']) ? sanitize_text_field(wp_unslash($_GET['city'])) : '';
    $cities = mrt_seo_get_cities();
    if ($city === '' || !array_key_exists($city, $cities)) {
        $city = mrt_get_selected_city_slug();
    }

    if (mb_strlen($q, 'UTF-8') < 2) {
        wp_send_json_success(array('services' => array(), 'articles' => array(), 'faq' => array()));
    }

    $branch = mrt_get_branch($city);
    $branch_slug = !empty($branch['branch_taxonomy']) ? $branch['branch_taxonomy'] : $city;

    $services = array();
    $svc_query = new WP_Query(array(
        'post_type'      => 'service',
        'post_status'    => 'publish',
        'posts_per_page' => 8,
        's'              => $q,
        'tax_query'      => array(array(
            'taxonomy' => 'branch',
            'field'    => 'slug',
            'terms'    => $branch_slug,
        )),
    ));
    while ($svc_query->have_posts()) {
        $svc_query->the_post();
        $post_id = get_the_ID();
        $service_url = trailingslashit(mrt_get_city_nav_url('uslugi-i-ceny', $city));
        $service_terms = wp_get_post_terms($post_id, 'service_type');
        $post_name = get_post_field('post_name', $post_id);
        if ($post_name) {
            $service_url = mrt_get_service_landing_url($city, $post_name);
        } elseif (!is_wp_error($service_terms) && !empty($service_terms)) {
            $service_url = trailingslashit(home_url('/' . $city . '/uslugi-i-ceny/price/' . $service_terms[0]->slug));
        }
        $services[] = array(
            'title' => get_the_title(),
            'url'   => $service_url,
        );
    }
    wp_reset_postdata();

    $articles = array();
    if (post_type_exists('article')) {
        $art_query = new WP_Query(array(
            'post_type'      => 'article',
            'post_status'    => 'publish',
            'posts_per_page' => 5,
            's'              => $q,
        ));
        while ($art_query->have_posts()) {
            $art_query->the_post();
            $articles[] = array(
                'title' => get_the_title(),
                'url'   => get_permalink(),
            );
        }
        wp_reset_postdata();
    }

    $faq = array();
    $parent_answers = get_term_by('slug', 'answers', 'category');
    if ($parent_answers && !is_wp_error($parent_answers)) {
        $faq_posts = get_posts(array(
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => 20,
            'tax_query'      => array(
                'relation' => 'AND',
                array(
                    'taxonomy' => 'category',
                    'field'    => 'slug',
                    'terms'    => $city,
                ),
                array(
                    'taxonomy'         => 'category',
                    'field'            => 'term_id',
                    'terms'            => (int) $parent_answers->term_id,
                    'include_children' => true,
                ),
            ),
        ));
        foreach ($faq_posts as $faq_post) {
            for ($i = 1; $i <= 50; $i++) {
                $vopros = get_field('know_vopros_' . $i, $faq_post->ID);
                if (!$vopros) {
                    break;
                }
                if (mb_stripos($vopros, $q, 0, 'UTF-8') !== false) {
                    $faq[] = array(
                        'title' => wp_strip_all_tags($vopros),
                        'url'   => mrt_get_city_nav_url('vopros-otvet', $city),
                    );
                    if (count($faq) >= 5) {
                        break 2;
                    }
                }
            }
        }
    }

    wp_send_json_success(array(
        'services' => $services,
        'articles' => $articles,
        'faq'      => $faq,
    ));
}

add_action('wp_footer', function () {
    get_template_part('template-parts/search-overlay');
}, 5);

add_action('wp_ajax_mrt_resolve_city_switch', 'mrt_ajax_resolve_city_switch');
add_action('wp_ajax_nopriv_mrt_resolve_city_switch', 'mrt_ajax_resolve_city_switch');
function mrt_ajax_resolve_city_switch(): void {
    $service_slug = sanitize_text_field(wp_unslash($_REQUEST['service'] ?? ''));
    $target_city = sanitize_text_field(wp_unslash($_REQUEST['city'] ?? ''));
    if ($service_slug === '' || $target_city === '') {
        wp_send_json_error(array('message' => 'Missing params'), 400);
    }
    wp_send_json_success(array('url' => mrt_resolve_service_city_switch_url($service_slug, $target_city)));
}


// --- Метрика ---
// --- Добавление пункта меню и страницы настроек ---
add_action('admin_menu', 'add_city_metrics_settings_page');
function add_city_metrics_settings_page() {
    add_management_page(
        'Метрики для городов',           // заголовок страницы
        'Метрики городов',              // название в меню
        'manage_options',               
        'city-metrics-settings',        // slug страницы
        'render_city_metrics_settings_page', // функция отрисовки страницы
        999 
    );
}

// --- регистрация настроек ---
add_action('admin_init', 'register_city_metrics_settings');
function register_city_metrics_settings() {
    register_setting(
        'city_metrics_settings_group',
        'city_metrics_data',
        array(
            'type'              => 'array',
            'sanitize_callback' => 'mrt_sanitize_city_metrics_data',
        )
    );
}

if (!function_exists('mrt_sanitize_city_metrics_data')) {
    /** Оставляет только метрики KZ-филиалов; RU-slug'и отбрасываются при сохранении. */
    function mrt_sanitize_city_metrics_data($input) {
        if (!is_array($input)) {
            return array();
        }

        $allowed_slugs = array_keys(mrt_get_metrics_cities_list());
        $sanitized = array();

        foreach ($allowed_slugs as $slug) {
            foreach (array('yandex_', 'ga_') as $prefix) {
                $key = $prefix . $slug;
                if (array_key_exists($key, $input)) {
                    $sanitized[$key] = wp_unslash($input[$key]);
                }
            }
        }

        return $sanitized;
    }
}

// --- Функция отрисовки страницы настроек ---
function render_city_metrics_settings_page() {
    // текущие данные
    $options = get_option('city_metrics_data', array());

    // Только KZ-филиалы (country=kz в mrt-city-config.php)
    $cities_list = mrt_get_metrics_cities_list();
    ?>
    <div class="wrap">
        <h1>Метрики для городов</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('city_metrics_settings_group');
            do_settings_sections('city_metrics_settings_group');
            // Дублируем кнопку сохранения сверху
            submit_button();
            ?>
            <table class="form-table" role="presentation">
                <?php foreach ($cities_list as $city_slug => $city_name) : ?>
                    <tr valign="top">
                        <th scope="row"><?php echo esc_html($city_name); ?></th>
                        <td>
                            <h4>Яндекс.Метрика</h4>
                            <textarea 
                                name="city_metrics_data[yandex_<?php echo esc_attr($city_slug); ?>]" 
                                rows="10" 
                                cols="50" 
                                class="large-text"
                                placeholder="Вставьте полный код счетчика Яндекс.Метрики для <?php echo esc_attr($city_name); ?>."
                            ><?php echo esc_textarea(isset($options['yandex_' . $city_slug]) ? $options['yandex_' . $city_slug] : ''); ?></textarea>

                            <h4>Google Analytics (gtag)</h4>
                            <textarea 
                                name="city_metrics_data[ga_<?php echo esc_attr($city_slug); ?>]" 
                                rows="10" 
                                cols="50" 
                                class="large-text"
                                placeholder="Вставьте полный код Google Analytics (gtag) для <?php echo esc_attr($city_name); ?>."
                            ><?php echo esc_textarea(isset($options['ga_' . $city_slug]) ? $options['ga_' . $city_slug] : ''); ?></textarea>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// --- Вставка метрики в head ---
add_action('wp_head', 'insert_city_specific_metrics_from_options', 99); 
function insert_city_specific_metrics_from_options() {

    $known = mrt_get_known_city_slugs();
    $current_city_slug = mrt_resolve_selected_city('almaty', false);
    if (!in_array($current_city_slug, $known, true)) {
        $current_city_slug = 'almaty';
    }

    // --- Получение метрик из wp_options ---
    $options = get_option('city_metrics_data', array());
    
    $yandex_code_key = 'yandex_' . $current_city_slug;
    $ga_code_key = 'ga_' . $current_city_slug;

    $yandex_code = isset($options[$yandex_code_key]) ? $options[$yandex_code_key] : '';
    $ga_code = isset($options[$ga_code_key]) ? $options[$ga_code_key] : '';

    // Филиал животных: fallback на счётчик Алматы, если свой не задан
    if ($yandex_code === '' && $current_city_slug === 'almaty_aubakirova') {
        $yandex_code = isset($options['yandex_almaty']) ? $options['yandex_almaty'] : '';
    }
    if ($ga_code === '' && $current_city_slug === 'almaty_aubakirova') {
        $ga_code = isset($options['ga_almaty']) ? $options['ga_almaty'] : '';
    }

    // --- Вывод метрик ---
    if ($yandex_code) {
        // комментарий для отладки (виден только в исходном коде страницы)
        echo "<!-- Yandex.Metrika code for city: $current_city_slug -->\n";
        // Выводим код Яндекс.Метрики
        echo $yandex_code . "\n"; 
    }

    if ($ga_code) {
        // комментарий для отладки
        echo "<!-- Google Analytics code for city: $current_city_slug -->\n";
        // Выводим код Google Analytics
        echo $ga_code . "\n";
    }

    $metrika_id = '';
    if ($yandex_code && preg_match('/ym\s*\(\s*(\d+)/', $yandex_code, $m)) {
        $metrika_id = $m[1];
    }
    echo '<script>window.mrtMetrikaId=' . ($metrika_id ? (int) $metrika_id : 'null') . ';</script>' . "\n";
}
// --- Конец метрики ---