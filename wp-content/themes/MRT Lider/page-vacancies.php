<?php
/*
Template Name: vacancies
*/

// --- ЛОГИКА ОПРЕДЕЛЕНИЯ ГОРОДА ---
$known_city_slugs_vacancies_php = array(
    'almaty', 'astana', 'karaganda', 'taldykorgan', 'almaty_aubakirova'
);

// Функция для определения города из URL запроса (дублируем из header.php)
if (!function_exists('get_city_slug_from_request_path_vacancies')) {
    function get_city_slug_from_request_path_vacancies($known_slugs) {
        $request_uri = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '/';
        $path = explode('?', $request_uri)[0];
        $path_parts = array_filter(explode('/', trim($path, '/')));
        
        if (!empty($path_parts)) {
            $first_part = strtolower(reset($path_parts));
            if (in_array($first_part, $known_slugs, true)) {
                return $first_part;
            }
        }
        return false;
    }
}

$city_slug_from_url_vacancies = get_city_slug_from_request_path_vacancies($known_city_slugs_vacancies_php);

if ($city_slug_from_url_vacancies !== false) {
    $selected_city_slug_vacancies = $city_slug_from_url_vacancies;
    // Устанавливаем/обновляем cookie только если он отличается
    if (!headers_sent()) {
        if (!isset($_COOKIE['selected_city']) || $_COOKIE['selected_city'] !== $selected_city_slug_vacancies) {
            setcookie('selected_city', $selected_city_slug_vacancies, time() + 30 * DAY_IN_SECONDS, '/', $_SERVER['HTTP_HOST'], is_ssl(), true);
        }
    }
} else {
    $selected_city_cookie_value_vacancies = isset($_COOKIE['selected_city']) ? sanitize_text_field($_COOKIE['selected_city']) : 'almaty';
    if (in_array($selected_city_cookie_value_vacancies, $known_city_slugs_vacancies_php, true)) {
         $selected_city_slug_vacancies = $selected_city_cookie_value_vacancies;
    } else {
         $selected_city_slug_vacancies = 'almaty';
         if (!headers_sent()) {
            setcookie('selected_city', $selected_city_slug_vacancies, time() + 30 * DAY_IN_SECONDS, '/', $_SERVER['HTTP_HOST'], is_ssl(), true);
         }
    }
}
// --- КОНЕЦ ЛОГИКИ ОПРЕДЕЛЕНИЯ ГОРОДА ---

get_header();
?>

<main class="main">
    <div class="main-background">
        <?php 
            if (!is_front_page()) {
                custom_breadcrumbs();
            }
        ?>

        <section class="vacancies">
            <div class="container">
                <div class="vacancies__inner">
                    <h1 class="page-title vacancies__title">НАШИ ВАКАНСИИ</h1>
                    <div class="vacancies__content">
                        <?php
                        // Запрос вакансий для выбранного города
                        $vacancies_args = array(
                            'post_type' => 'vacancy',
                            'posts_per_page' => -1,
                            'tax_query' => array(
                                array(
                                    'taxonomy' => 'vacancy_city',
                                    'field'    => 'slug',
                                    'terms'    => $selected_city_slug_vacancies,
                                ),
                            ),
                        );
                        $vacancies_query = new WP_Query($vacancies_args);
    
                        if ($vacancies_query->have_posts()) :
                            while ($vacancies_query->have_posts()) : $vacancies_query->the_post();
                                // Получаем метаданные вакансии
                                $salary = get_post_meta(get_the_ID(), '_vacancy_salary', true);
                                $payments_per_month = get_post_meta(get_the_ID(), '_vacancy_payments_per_month', true);
                                $experience = get_post_meta(get_the_ID(), '_vacancy_experience', true);
                                $employment_type = get_post_meta(get_the_ID(), '_vacancy_employment_type', true);
                                $working_hours = get_post_meta(get_the_ID(), '_vacancy_working_hours', true);
                                $work_format = get_post_meta(get_the_ID(), '_vacancy_work_format', true);
                                ?>
                                <div class="vacancies__item">
                                    <h3 class="vacancies__item-title"><?php the_title(); ?></h3>
                                    <div class="vacancies__item-container">
                                        <?php if (!empty($salary)) : ?>
                                            <p class="vacancies__item-text">от <?php echo esc_html($salary); ?>₽ за месяц, до вычета налогов</p>
                                        <?php endif; ?>
                                        <?php if (!empty($payments_per_month)) : ?>
                                            <p class="vacancies__item-text">Выплаты: <?php echo esc_html($payments_per_month); ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($experience)) : ?>
                                            <p class="vacancies__item-text">Опыт работы: <?php echo esc_html($experience); ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($employment_type)) : ?>
                                            <p class="vacancies__item-text"><?php echo esc_html($employment_type); ?> занятость</p>
                                        <?php endif; ?>
                                        <?php if (!empty($working_hours)) : ?>
                                            <p class="vacancies__item-text">Рабочие часы: <?php echo esc_html($working_hours); ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($work_format)) : ?>
                                            <p class="vacancies__item-text">Формат работы: <?php echo esc_html($work_format); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                            <?php wp_reset_postdata(); ?>
                        <?php else : ?>
                            <p>Для выбранного города вакансий пока нет.</p>
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