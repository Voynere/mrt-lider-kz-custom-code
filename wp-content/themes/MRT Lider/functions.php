<?php

require get_template_directory() . '/inc/mrt-city-config.php';
require get_template_directory() . '/inc/mrt-header-helpers.php';
require get_template_directory() . '/inc/mrt-city-routing.php';

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
    }

    wp_localize_script('main', 'mrtCityConfig', array(
        'cityMap'       => mrt_get_city_map(),
        'knownSlugs'    => mrt_get_known_city_slugs(),
        'animalsSlugs'  => array_values(array_filter(mrt_get_known_city_slugs(), 'mrt_is_animals_branch')),
        'citySpecificPages' => mrt_get_city_specific_page_slugs(),
    ));
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

    echo '<div class="breadcrumbs"><div class="container"><ul class="breadcrumbs__list">';

    // Главная страница выбранного города
    echo '<li><a href="' . esc_url($city_base_url) . '">Главная</a></li>';

    $post = get_post();

    $show_uslugi_parent = false;
    if (is_page() && get_page_template_slug($post) === 'page-service-item.php') {
        $show_uslugi_parent = true;
    }

    // Формируем URL и заголовок страницы "Услуги и цены" с учётом города
    $uslugi_page_url = trailingslashit($city_base_url . 'uslugi-i-ceny');
    $uslugi_page = get_page_by_path('uslugi-i-ceny');
    $uslugi_page_title = $uslugi_page ? $uslugi_page->post_title : 'Услуги и цены';

    if ($uslugi_page && $show_uslugi_parent) {
        echo '<li><svg width="8" height="17" viewBox="0 0 8 17" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M0 1.47L0.8625 0.5L8 8.5L0.8625 16.5L0 15.535L6.27083 8.5L0 1.47Z" fill="#404040"/></svg></li>';
        echo '<li><a href="' . esc_url($uslugi_page_url) . '">' . esc_html($uslugi_page_title) . '</a></li>';
    }

    // === Основная логика для разных типов страниц ===
    if (is_single()) {
        // Для записей: рубрика -> заголовок
        $categories = get_the_category();
        if (!empty($categories)) {
            $first_category = $categories[0];
            echo '<li><svg width="8" height="17" viewBox="0 0 8 17" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M0 1.47L0.8625 0.5L8 8.5L0.8625 16.5L0 15.535L6.27083 8.5L0 1.47Z" fill="#404040"/></svg></li>';
            echo '<li><a href="' . esc_url(get_category_link($first_category->term_id)) . '">' . esc_html($first_category->name) . '</a></li>';
        }
        echo '<li><svg width="8" height="17" viewBox="0 0 8 17" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M0 1.47L0.8625 0.5L8 8.5L0.8625 16.5L0 15.535L6.27083 8.5L0 1.47Z" fill="#404040"/></svg></li>';
        echo '<li>' . esc_html(get_the_title()) . '</li>';

    } else {
        // Для обычных страниц (не дочерних)
        if (!is_front_page()) {
            echo '<li><svg width="8" height="17" viewBox="0 0 8 17" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M0 1.47L0.8625 0.5L8 8.5L0.8625 16.5L0 15.535L6.27083 8.5L0 1.47Z" fill="#404040"/></svg></li>';
            echo '<li>' . esc_html(get_the_title()) . '</li>';
        }
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
        if (!is_wp_error($service_terms) && !empty($service_terms)) {
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
    register_setting('city_metrics_settings_group', 'city_metrics_data');
}

// --- Функция отрисовки страницы настроек ---
function render_city_metrics_settings_page() {
    // текущие данные
    $options = get_option('city_metrics_data', array());

    // Список городов
    $cities_list = array(
        'almaty' => 'Алматы',
        'angarsk' => 'Ангарск',
        'astana' => 'Астана',
        'achinsk' => 'Ачинск',
        'blagoveshhensk' => 'Благовещенск',
        'bratsk' => 'Братск',
        'vladivostok' => 'Владивосток',
        'volgodonsk' => 'Волгодонск',
        'irkutsk' => 'Иркутск',
        'karaganda' => 'Караганда',
        'kemerovo' => 'Кемерово',
        'kirov' => 'Киров',
        'komsomolsk' => 'Комсомольск-на-Амуре',
        'krasnoyarsk' => 'Красноярск',
        'kurgan' => 'Курган',
        'magadan' => 'Магадан',
        'murmansk' => 'Мурманск',
        'nahodka' => 'Находка',
        'nizhnekamsk' => 'Нижнекамск',
        'nizhnij_novgorod' => 'Нижний Новгород',
        'nizhnij_tagil' => 'Нижний Тагил',
        'novosibirsk' => 'Новосибирск',
        'petropavlovsk_kamchatskij' => 'Петропавловск-Камчатский',
        'rostov' => 'Ростов-на-Дону',
        'samara' => 'Самара',
        'taldykorgan' => 'Талдыкорган',
        'tomsk' => 'Томск',
        'tumen' => 'Тюмень',
        'ussurijsk' => 'Уссурийск',
        'khabarovsk' => 'Хабаровск'
    );
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

    $current_city_slug = isset($_COOKIE['selected_city']) ? sanitize_text_field($_COOKIE['selected_city']) : 'khabarovsk';

    // --- Получение метрик из wp_options ---
    $options = get_option('city_metrics_data', array());
    
    $yandex_code_key = 'yandex_' . $current_city_slug;
    $ga_code_key = 'ga_' . $current_city_slug;

    $yandex_code = isset($options[$yandex_code_key]) ? $options[$yandex_code_key] : '';
    $ga_code = isset($options[$ga_code_key]) ? $options[$ga_code_key] : '';

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
}
// --- Конец метрики ---