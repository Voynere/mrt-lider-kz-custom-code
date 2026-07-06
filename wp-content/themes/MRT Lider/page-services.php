<?php
/*
Template Name: services
*/

// --- Список валидных слагов городов ---
$known_city_slugs = array(
    'almaty', 'astana', 'karaganda', 'taldykorgan'
);

// --- Определяем город: URL > кука > fallback ---
$selected_city = 'almaty'; // fallback

$request_uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($request_uri, PHP_URL_PATH);
$path = trim($path, '/');
$path_parts = $path ? explode('/', $path) : array();
$url_city = !empty($path_parts[0]) ? sanitize_text_field($path_parts[0]) : '';

if ($url_city && in_array($url_city, $known_city_slugs, true)) {
    $selected_city = $url_city;
} elseif (isset($_COOKIE['selected_city'])) {
    $cookie_city = sanitize_text_field($_COOKIE['selected_city']);
    if (in_array($cookie_city, $known_city_slugs, true)) {
        $selected_city = $cookie_city;
    }
}

// --- Вспомогательные функции ---
function si_get_term_image_url($term) {
    if (is_object($term)) {
        $term_id = isset($term->term_id) ? (int)$term->term_id : 0;
    } else {
        $term_id = (int)$term;
    }
    if (!$term_id) return false;

    if (function_exists('z_taxonomy_image_url')) {
        $url = z_taxonomy_image_url($term_id);
        if (!empty($url)) return $url;
    }

    $meta_keys = array('category_image','category-image','cat_image','image','image_id','wp_term_image','term_image','thumbnail_id');
    foreach ($meta_keys as $k) {
        $val = get_term_meta($term_id, $k, true);
        if (empty($val)) continue;
        if (is_numeric($val) && intval($val) > 0) {
            $url = wp_get_attachment_url(intval($val));
            if ($url) return $url;
        }
        if (is_array($val) && !empty($val['id'])) {
            $url = wp_get_attachment_url(intval($val['id']));
            if ($url) return $url;
        }
        if (is_string($val) && preg_match('#^https?://#i', $val)) {
            return $val;
        }
    }

    $try_funcs = array('get_taxonomy_image_url','get_term_image_url','taxonomy_image_plugin_get_image_url','z_taxonomy_image_url_by_id');
    foreach ($try_funcs as $fn) {
        if (function_exists($fn)) {
            $maybe = call_user_func($fn, $term_id);
            if (!empty($maybe)) return $maybe;
        }
    }

    $thumb = get_term_meta($term_id, 'thumbnail_id', true);
    if (!empty($thumb) && is_numeric($thumb)) {
        $url = wp_get_attachment_url(intval($thumb));
        if ($url) return $url;
    }

    return false;
}

function si_sort_service_types($a, $b) {
    $order = array(
        'МРТ 1.5 тесла' => 1,
        'МРТ 1.5 Т' => 1,
        'МРТ 3 тесла' => 2,
        'МРТ 3.0 Т' => 2,
        'МРТ 3 Т' => 2,
        'КТ' => 3,
        'УЗИ' => 4,
        'Денситометрия' => 5,
        'Денсиотометрия' => 5
    );
    
    $name_a = $a->name;
    $name_b = $b->name;
    
    $priority_a = isset($order[$name_a]) ? $order[$name_a] : 999;
    $priority_b = isset($order[$name_b]) ? $order[$name_b] : 999;
    
    if ($priority_a == $priority_b) {
        return strcasecmp($name_a, $name_b);
    }
    
    return $priority_a - $priority_b;
}

// --- Получаем услуги по филиалу ---
$service_posts = get_posts(array(
    'post_type' => 'service',
    'posts_per_page' => -1,
    'fields' => 'ids',
    'tax_query' => array(
        array(
            'taxonomy' => 'branch',
            'field'    => 'slug',
            'terms'    => $selected_city,
        )
    )
));

$service_types = array();
if (!empty($service_posts)) {
    $service_types = get_terms(array(
        'taxonomy'     => 'service_type',
        'hide_empty'   => false,
        'object_ids'   => $service_posts,
    ));
}

get_header();
?>

<main class="main">
    <div class="main-background">
        <?php 
            if (!is_front_page()) {
                custom_breadcrumbs();
            }
        ?>

        <section class="services">
            <div class="container">
                <div class="services__inner">
                    <h1 class="services__title page-title">УСЛУГИ И ЦЕНЫ</h1>

                    <div class="services__content">
                        <?php if (!empty($service_types) && !is_wp_error($service_types)) : 
                            usort($service_types, 'si_sort_service_types');
                            foreach ($service_types as $term) : 
                                $term_slug = $term->slug;
                                $url = home_url('/') . $selected_city . '/uslugi-i-ceny/price/' . $term_slug . '/';
                                $img_url = si_get_term_image_url($term);
                                ?>
                                <a class="services__item" href="<?php echo esc_url($url); ?>">
                                    <div class="services__item-image-wrapper">
                                        <?php if ($img_url) : ?>
                                            <img src="<?php echo esc_url($img_url); ?>" alt="<?php echo esc_attr($term->name); ?>" class="services__item-img" />
                                        <?php endif; ?>
                                    </div>
                                    <div class="services__item-container">
                                        <h3 class="services__item-title"><?php echo esc_html($term->name); ?></h3>
                                    </div>
                                    <div class="services__item-arrow">
                                        <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M2.08108 1H9V7.91892M7.81081 2.18919L1 9" stroke="#6180A1" stroke-width="1.5" stroke-linecap="round" />
                                        </svg>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <p>Для данного филиала данные по видам услуг не найдены.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <div class="tour">
            <div class="container">
                <div class="tour__inner">
                    <img src="<?php bloginfo('template_url')?>/assets/img/3d_tour.jpg" alt="">
                    <a href="#" class="tour__content">
                        <h3 class="tour__title">
                            <span>ПРОЙДИТЕ 3D ТУР</span> 
                            <span>ПО КЛИНИКЕ</span> 
                            <span>«МРТ ЛИДЕР»</span>
                        </h3>
                        <div class="tour__play">
                            <img src="<?php bloginfo('template_url')?>/assets/img/play_video.svg" alt="">
                        </div>
                    </a>
                </div>
            </div>
        </div>

    </div>
</main>

<?php get_footer(); ?>