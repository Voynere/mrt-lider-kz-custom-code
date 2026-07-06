<?php
/*
Template Name: specialists
*/

// --- Список валидных слагов городов ---
$known_city_slugs = array(
    'almaty', 'astana', 'karaganda', 'taldykorgan', 'almaty_aubakirova'
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

// --- Параметры пагинации ---
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$posts_per_page = 6;

// --- Запрос специалистов ---
$args = array(
    'post_type'      => 'post',
    'posts_per_page' => $posts_per_page,
    'paged'          => $paged,
    'tax_query'      => array(
        'relation' => 'AND',
        array(
            'taxonomy' => 'category',
            'field'    => 'slug',
            'terms'    => $selected_city,
        ),
        array(
            'taxonomy' => 'category',
            'field'    => 'slug',
            'terms'    => 'specialisty',
        )
    )
);

$specialists_query = new WP_Query($args);

get_header();
?>

<main class="main">
    <div class="main-background">
        <?php 
            if (!is_front_page()) {
                custom_breadcrumbs();
            }
        ?>
        <section class="page-specialists">
            <div class="container">
                <div class="page-specialists__inner">
                    <div class="page-specialists__top">
                        <h2 class="page-title">НАШИ ВРАЧИ</h2>
                    </div>
                    
                    <div class="page-specialists__content">
                        <?php if ($specialists_query->have_posts()) : ?>
                            <?php while ($specialists_query->have_posts()) : $specialists_query->the_post(); ?>
                                <a class="page-specialists__item" href="<?php the_permalink(); ?>">
                                    <div class="page-specialists__item-img">
                                        <?php 
                                        $image = get_field('specialists_image');
                                        if ($image) : 
                                        ?>
                                            <img src="<?php echo esc_url($image); ?>" alt="<?php the_title_attribute(); ?>">
                                        <?php endif; ?>
                                    </div>
                                    <h5 class="page-specialists__item-name"><?php the_title(); ?></h5>
                                    <div class="page-specialists__item-info">
                                        <p class="page-specialists__item-exp">
                                            Опыт работы: <?php the_field('specialists_exp'); ?>
                                        </p>
                                        <p class="page-specialists__item-spec">
                                            <?php the_field('specialists_job'); ?>
                                        </p>
                                        <div class="page-specialists__item-stars">
                                            <?php for ($i = 0; $i < 5; $i++) : ?>
                                                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/star.png'); ?>" alt="Рейтинг">
                                            <?php endfor; ?>
                                        </div>
                                    </div>

                                    <div class="page-specialists__item-arrow">
                                        <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M2.08108 1H9V7.91892M7.81081 2.18919L1 9" stroke="#6180A1" stroke-width="1.5" stroke-linecap="round"/>
                                        </svg>
                                    </div>
                                    <div class="page-specialists__item-corners">
                                        <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/rating_corner.svg'); ?>" class="raiting__corner left-top" alt="">
                                        <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/rating_corner.svg'); ?>" class="raiting__corner left-bottom" alt="">
                                        <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/rating_corner.svg'); ?>" class="raiting__corner right-top" alt="">
                                        <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/rating_corner.svg'); ?>" class="raiting__corner right-bottom" alt="">
                                    </div>
                                </a>
                            <?php endwhile; ?>
                        <?php else : ?>
                            <p class="no-specialists">Врачи в выбранном городе не найдены</p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Пагинация -->
                    <?php if ($specialists_query->max_num_pages > 1) : ?>
                    <div class="page-specialists__nav">
                        <?php
                        $big = 999999999;
                        $svg_arrow = '<svg class="pagination-arrow" width="8" height="17" viewBox="0 0 8 17" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M0 1.47L0.8625 0.5L8 8.5L0.8625 16.5L0 15.535L6.27083 8.5L0 1.47Z" fill="#404040"/></svg>';
                        
                        echo paginate_links(array(
                            'base'      => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
                            'format'    => '?paged=%#%',
                            'current'   => max(1, $paged),
                            'total'     => $specialists_query->max_num_pages,
                            'prev_text' => '<span class="pagination-arrow-wrapper prev-arrow">' . $svg_arrow . '</span>',
                            'next_text' => '<span class="pagination-arrow-wrapper next-arrow">' . $svg_arrow . '</span>',
                            'mid_size'  => 2,
                        ));
                        ?>
                    </div>
                    <?php endif; ?>

                    <?php wp_reset_postdata(); ?>
                </div>
            </div>
        </section>

        <div class="tour">
            <div class="container">
                <div class="tour__inner">
                    <img src="<?php bloginfo('template_url')?>/assets/img/3d_tour.jpg" alt="">
                    <a href="#" class="tour__content">
                        <h2 class="tour__title"><span>ПРОЙДИТЕ 3D ТУР</span> <span>ПО КЛИНИКЕ</span> <span>«МРТ ЛИДЕР»</span></h2>
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