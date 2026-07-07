<?php
/**
 * Template Name: Специалист
 * Template Post Type: post
 */

get_header(); ?>

<main class="main">
    <div class="main-background">
        <div class="specialist">
            <div class="container">
                <div class="specialist__inner">
                    <?php 
                        if (!is_front_page()) {
                            custom_breadcrumbs();
                        }
                    ?>
                    <?php if (have_posts()) : while (have_posts()) : the_post(); 
                        $image = get_field('specialists_image');
                        $post_id = get_the_ID();
                        ?>
                        <article id="post-<?php the_ID(); ?>" <?php post_class('specialist__post'); ?>>
                            
                            <div class="specialist__head">
                                <div class="specialist__avatar">
                                    <img src="<?php echo esc_url($image); ?>" alt="<?php the_title(); ?>" class="specialist__avatar-img">
                                    <img src="<?php bloginfo('template_url')?>/assets/img/rating_corner.svg" class="raiting__corner left-top" alt="">
                                    <img src="<?php bloginfo('template_url')?>/assets/img/rating_corner.svg" class="raiting__corner left-bottom" alt="">
                                    <img src="<?php bloginfo('template_url')?>/assets/img/rating_corner.svg" class="raiting__corner right-top" alt="">
                                    <img src="<?php bloginfo('template_url')?>/assets/img/rating_corner.svg" class="raiting__corner right-bottom" alt="">
                                </div>
                                <div class="specialist__head-container">
                                    <h1 class="specialist__title grey-line">
                                        <?php the_title(); ?>
                                    </h1>
                                    <div class="specialist__head-info">
                                        <p class="specialist__head-text bold grey-line">
                                            <?php echo esc_html(get_field('specialists_job')); ?>
                                        </p>
                                        <p class="specialist__head-text grey-line">
                                            Специализация: <?php echo esc_html(get_field('specialists_nazvanie_specializacii')); ?>
                                        </p>
                                        <p class="specialist__head-text grey-line">
                                            Стаж работы: <?php echo esc_html(get_field('specialists_exp')); ?>
                                        </p>
                                        <p class="specialist__head-raiting grey-line">
                                            Рейтинг: 
                                        </p>
                                    </div>
                                    <div class="specialist__head-buttons">
                                        <button class="specialist__head-btn btn-blue">
                                            Записаться на приём
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <rect x="0.5" y="0.5" width="23" height="23" rx="11.5" stroke="white"/>
                                                <path d="M9.08108 8H16V14.9189M14.8108 9.18919L8 16" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                                            </svg>
                                        </button>
                                        <button class="specialist__head-btn btn-white">
                                            Посмотреть отзывы
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <rect x="0.5" y="0.5" width="23" height="23" rx="11.5" stroke="#404040"/>
                                                <path d="M9.08108 8H16V14.9189M14.8108 9.18919L8 16" stroke="#404040" stroke-width="1.5" stroke-linecap="round"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="specialist__content">
                                <h2 class="page-title">ОБРАЗОВАНИЕ</h2>
                                <div class="specialist__content-container specialist__education">
                                    <div class="specialist__education-wrapper">
                                        <?php
                                        // Получаем группу образования
                                        $education_group = get_field('specialists_obrazovanie', $post_id) ?: [];
                                        $education_items = [];
                                        $edu_index = 1;
                                        
                                        while ($edu_index <= 8) {
                                            $year_key = "specialists_data_obuchenija_{$edu_index}";
                                            $place_key = "specialists_mesto_obuchenija_{$edu_index}";
                                            
                                            $year = $education_group[$year_key] ?? '';
                                            $place = $education_group[$place_key] ?? '';
                                            
                                            if (!empty($year) || !empty($place)) {
                                                $education_items[] = [
                                                    'year' => $year,
                                                    'place' => $place
                                                ];
                                            }
                                            $edu_index++;
                                        }
                                        
                                        // Выводим записи об образовании
                                        if (!empty($education_items)) {
                                            foreach ($education_items as $item) {
                                                echo '<div class="specialist__education-item">';
                                                if (!empty($item['year'])) {
                                                    echo '<p class="specialist__education-year specialist__content-bold">' 
                                                         . esc_html($item['year']) . '</p>';
                                                }
                                                if (!empty($item['place'])) {
                                                    echo '<p class="specialist__education-text specialist__content-text">' 
                                                         . esc_html($item['place']) . '</p>';
                                                }
                                                echo '</div>';
                                            }
                                        } else {
                                            echo '<div class="specialist__education-item">';
                                            echo '<p>Информация об образовании отсутствует</p>';
                                            echo '</div>';
                                        }
                                        ?>
                                    </div>

                                    <div class="specialist__education-wrapper">
                                        <?php
                                        // Получаем группу специализации
                                        $specialization_group = get_field('osnovnaja_specializacija', $post_id) ?: [];
                                        $specialization_name = $specialization_group['specialists_nazvanie_specializacii'] ?? '';
                                        ?>
                                        <h4 class="specialist__education-title">
                                            Основная специализация: <?php echo esc_html($specialization_name); ?>
                                        </h4>
                                        
                                        <?php
                                        $activity_index = 1;
                                        $activities = [];
                                        
                                        while ($activity_index <= 8) {
                                            $activity_key = "specialists_vid_dejatelnosti_{$activity_index}";
                                            $activity = $specialization_group[$activity_key] ?? '';
                                            
                                            if (!empty($activity)) {
                                                $activities[] = $activity;
                                            }
                                            $activity_index++;
                                        }
                                        
                                        // виды деятельности
                                        if (!empty($activities)) {
                                            foreach ($activities as $activity) {
                                                echo '<p class="specialist__education-text">— ' . esc_html($activity) . '</p>';
                                            }
                                        } else {
                                            echo '<p class="specialist__education-text">Информация отсутсвует</p>';
                                        }
                                        ?>
                                    </div>

                                    <img src="<?php bloginfo('template_url')?>/assets/img/rating_corner.svg" class="raiting__corner left-top" alt="">
                                    <img src="<?php bloginfo('template_url')?>/assets/img/rating_corner.svg" class="raiting__corner left-bottom" alt="">
                                    <img src="<?php bloginfo('template_url')?>/assets/img/rating_corner.svg" class="raiting__corner right-top" alt="">
                                    <img src="<?php bloginfo('template_url')?>/assets/img/rating_corner.svg" class="raiting__corner right-bottom" alt="">
                                </div>
                            </div>

                            <div class="specialist__content">
                                <h2 class="page-title">СЕРТИФИКАТЫ</h2>
                                <div class="specialist__content-container specialist__сertificates">
                                    <div class="specialist__сertificates-container">
                                        <img src="<?php bloginfo('template_url')?>/assets/img/certificate_1.jpg" class="specialist__сertificates-img" alt="">
                                        <img src="<?php bloginfo('template_url')?>/assets/img/rating_corner.svg" class="raiting__corner left-top" alt="">
                                        <img src="<?php bloginfo('template_url')?>/assets/img/rating_corner.svg" class="raiting__corner left-bottom" alt="">
                                        <img src="<?php bloginfo('template_url')?>/assets/img/rating_corner.svg" class="raiting__corner right-top" alt="">
                                        <img src="<?php bloginfo('template_url')?>/assets/img/rating_corner.svg" class="raiting__corner right-bottom" alt="">
                                    </div>
                                    <div class="specialist__сertificates-container">
                                        <img src="<?php bloginfo('template_url')?>/assets/img/certificate_1.jpg" class="specialist__сertificates-img" alt="">
                                        <img src="<?php bloginfo('template_url')?>/assets/img/rating_corner.svg" class="raiting__corner left-top" alt="">
                                        <img src="<?php bloginfo('template_url')?>/assets/img/rating_corner.svg" class="raiting__corner left-bottom" alt="">
                                        <img src="<?php bloginfo('template_url')?>/assets/img/rating_corner.svg" class="raiting__corner right-top" alt="">
                                        <img src="<?php bloginfo('template_url')?>/assets/img/rating_corner.svg" class="raiting__corner right-bottom" alt="">
                                    </div>
                                    <div class="specialist__сertificates-container">
                                        <img src="<?php bloginfo('template_url')?>/assets/img/certificate_1.jpg" class="specialist__сertificates-img" alt="">
                                        <img src="<?php bloginfo('template_url')?>/assets/img/rating_corner.svg" class="raiting__corner left-top" alt="">
                                        <img src="<?php bloginfo('template_url')?>/assets/img/rating_corner.svg" class="raiting__corner left-bottom" alt="">
                                        <img src="<?php bloginfo('template_url')?>/assets/img/rating_corner.svg" class="raiting__corner right-top" alt="">
                                        <img src="<?php bloginfo('template_url')?>/assets/img/rating_corner.svg" class="raiting__corner right-bottom" alt="">
                                    </div>
                                </div>
                            </div>

                            <div class="specialist__reviews">
                                <div class="specialist__reviews-top">
                                    <h2 class="page-title">СТАТЬИ</h2>
                                    <div class="specialist__buttons slider-buttons">
                                        <button class="articles__buttons-arrow reviewsSwiper-prev slider-buttons__item prev">
                                            <svg width="16" height="16" viewBox="0 0 11 10" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path d="M10 1L2 5L10 9" stroke="#404040" />
                                            </svg>
                                        </button>
                                        <button class="articles__buttons-arrow reviewsSwiper-next slider-buttons__item next">
                                            <svg width="16" height="16" viewBox="0 0 11 10" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path d="M10 1L2 5L10 9" stroke="#404040" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="swiper reviewsSwiper">
                                    <div class="swiper-wrapper">
                                        <div class="swiper-slide">
                                            <div class="specialist__reviews-item">
                                                <div class="specialist__reviews-head">
                                                    <div class="specialist__reviews-img">
                                                        <img src="<?php bloginfo('template_url')?>/assets/img/reviews_1.jpg" alt="">
                                                    </div>
                                                    <p class="specialist__reviews-name">Короткова Инна Валерьевна</p>
                                                </div>
                                                <p class="specialist__reviews-text">
                                                    Мне надо было быстро сделать рентгеновский снимок органов
                                                    грудной клетки по назначению врача терапевта. Я обратилась в клинику
                                                    первый доктор в Отрадном. Мне быстро сделали исследования и описали
                                                    снимок в течение 15 минут. Врач рентгенолог Фунтова Татьяна
                                                    Михайловна мне все объяснила и рассказала по снимку. Я теперь узнала
                                                    где у меня расположено сердце ))) Все очень быстро чётко и
                                                    качественно!!!
                                                </p>
                                            </div>
                                        </div>
                                        <div class="swiper-slide">
                                            <div class="specialist__reviews-item">
                                                <div class="specialist__reviews-head">
                                                    <div class="specialist__reviews-img">
                                                        <img src="<?php bloginfo('template_url')?>/assets/img/reviews_2.jpg" alt="">
                                                    </div>
                                                    <p class="specialist__reviews-name">Петров Игорь Владимирович</p>
                                                </div>
                                                <p class="specialist__reviews-text">
                                                    Выражаю огромную благодарность Кузьмину МВ! Врач МРТ от
                                                    Бога! Огромное спасибо за индивидуальный подход, за разъяснение
                                                    причин боли, за точную постановку диагноза, за профессионализм и
                                                    грамотность, вот с кого надо брать пример! Обязательно последую его
                                                    рекомендациям, такому грамотному врачу можно довериться. Спасибо!
                                                    Так же хочу отметить четкую работу администраторов на ресепшене,
                                                    вежливость и грамотность.
                                                </p>
                                            </div>
                                        </div>
                                        <div class="swiper-slide">
                                            <div class="specialist__reviews-item">
                                                <div class="specialist__reviews-head">
                                                    <div class="specialist__reviews-img">
                                                        <img src="<?php bloginfo('template_url')?>/assets/img/reviews_1.jpg" alt="">
                                                    </div>
                                                    <p class="specialist__reviews-name">Иванов Леонид Петрович</p>
                                                </div>
                                                <p class="specialist__reviews-text">
                                                    Выражаю огромную благодарность Кузьмину МВ! Врач МРТ от
                                                    Бога! Огромное спасибо за индивидуальный подход, за разъяснение
                                                    причин боли, за точную постановку диагноза, за профессионализм и
                                                    грамотность, вот с кого надо брать пример!
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="specialist-meta">
                                <?php if ($position = get_field('position')) : ?>
                                    <p><strong>Должность:</strong> <?php echo esc_html($position); ?></p>
                                <?php endif; ?>
                                
                                <?php if ($experience = get_field('experience')) : ?>
                                    <p><strong>Стаж:</strong> <?php echo esc_html($experience); ?> года</p>
                                <?php endif; ?>
                            </div>
                        </article>
                        
                    <?php endwhile; endif; ?>
                </div>
            </div>
        </div>
        
        <div class="question">
            <div class="container">
                <div class="question__inner">
                    <div class="question__mobile">
                        <div class="question__mobile-item top">
                            <img src="<?php bloginfo('template_url')?>/assets/img/semicircle_mobile.svg" alt="">
                        </div>
                        <div class="question__mobile-item bottom">
                            <img src="<?php bloginfo('template_url')?>/assets/img/semicircle_mobile.svg" alt="">
                        </div>
                        <div class="question__mobile-tg">
                            <div class="question__tg">
                                <img src="<?php bloginfo('template_url')?>/assets/img/tg.svg" alt="">
                            </div>
                        </div>
                    </div>
                    <div class="question__images">
                        <div class="question__semicircle">
                            <img src="<?php bloginfo('template_url')?>/assets/img/semicircle.svg" alt="">
                        </div>
                        <div class="question__tg">
                            <img src="<?php bloginfo('template_url')?>/assets/img/tg.svg" alt="">
                        </div>
                    </div>
                    <div class="question__content">
                        <a href="#" class="question__info">
                            <p class="question__title">ОСТАЛИСЬ ВОПРОСЫ?</p>
                            <p class="question__text">Звоните! +7(3952)47-15-12</p>
                        </a>
                        <img src="<?php bloginfo('template_url')?>/assets/img/any_questions_left.jpg" alt="">
                    </div>
                </div>
            </div>
        </div>

        <?php get_template_part('template-parts/tour-or-animals-map'); ?>

    </div>
</main>

<?php get_footer(); ?>
<!-- Слайдер отзывов -->
<script>
    var swiper = new Swiper(".reviewsSwiper", {
        slidesPerView: 1,
        spaceBetween: 24,
        loop: true,
        navigation: {
            nextEl: ".reviewsSwiper-next",
            prevEl: ".reviewsSwiper-prev",
        },
        breakpoints: {
            867: {
                slidesPerView: 2,
                spaceBetween: 24
            },
            578: {
                slidesPerView: 2,
                spaceBetween: 24
            },
        }
    });
</script>