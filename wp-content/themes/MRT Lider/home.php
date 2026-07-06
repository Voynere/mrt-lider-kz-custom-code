<?php
/*
Template Name: home
*/

// --- ПЕРЕОПРЕДЕЛЕНИЕ $selected_city аналогично header.php---
$known_city_slugs_home_php = array(
    'almaty', 'astana', 'karaganda', 'taldykorgan', 'almaty_aubakirova'
);

if (!function_exists('get_city_slug_from_request_path_home_selected')) {
    function get_city_slug_from_request_path_home_selected($known_slugs) {
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

$city_slug_from_url_home_selected = get_city_slug_from_request_path_home_selected($known_city_slugs_home_php);

if ($city_slug_from_url_home_selected !== false) {
    $selected_city = $city_slug_from_url_home_selected;
    if (!headers_sent()) {
        if (!isset($_COOKIE['selected_city']) || $_COOKIE['selected_city'] !== $selected_city) {
            setcookie('selected_city', $selected_city, time() + 30 * DAY_IN_SECONDS, '/', $_SERVER['HTTP_HOST'], is_ssl(), true);
        }
    }
} else {

    $selected_city_cookie_value = isset($_COOKIE['selected_city']) ? sanitize_text_field($_COOKIE['selected_city']) : 'almaty';
    if (in_array($selected_city_cookie_value, $known_city_slugs_home_php, true)) {
        $selected_city = $selected_city_cookie_value;
    } else {
        $selected_city = 'almaty';
        if (!headers_sent()) {
            setcookie('selected_city', $selected_city, time() + 30 * DAY_IN_SECONDS, '/', $_SERVER['HTTP_HOST'], is_ssl(), true);
        }
    }
}
// --- КОНЕЦ $selected_city ---

// --- ФУНКЦИИ ДЛЯ ПОЛУЧЕНИЯ КОНТАКТНЫХ ДАННЫХ ---
function get_whatsapp_number_home_template($city_slug) {
    $args = array(
        'post_type'      => 'post',
        'posts_per_page' => 1,
        'tax_query'      => array(
            'relation' => 'AND',
            array(
                'taxonomy' => 'category',
                'field'    => 'slug',
                'terms'    => $city_slug,
            ),
            array(
                'taxonomy' => 'category',
                'field'    => 'slug',
                'terms'    => 'contacty',
            ),
        ),
    );

    $contacts_query = new WP_Query($args);
    $whatsapp_digits = '';

    if ($contacts_query->have_posts()) {
        while ($contacts_query->have_posts()) {
            $contacts_query->the_post();
            $whatsapp_field = get_field('contacts_whatsapp');
            if (!empty($whatsapp_field)) {
                $whatsapp_digits = preg_replace('/\D+/', '', $whatsapp_field);
                break; 
            }
        }
        wp_reset_postdata();
    }

    return $whatsapp_digits;
}

function get_contact_phone_number_home_template($city_slug) {
    $args = array(
        'post_type'      => 'post',
        'posts_per_page' => 1,
        'tax_query'      => array(
            'relation' => 'AND',
            array(
                'taxonomy' => 'category',
                'field'    => 'slug',
                'terms'    => $city_slug,
            ),
            array(
                'taxonomy' => 'category',
                'field'    => 'slug',
                'terms'    => 'contacty',
            ),
        ),
    );

    $contacts_query = new WP_Query($args);
    $phone_number = '';
    $phone_href = '#';

    if ($contacts_query->have_posts()) {
        while ($contacts_query->have_posts()) {
            $contacts_query->the_post();
            $phones_field = get_field('contacts_phones');
            if (!empty($phones_field)) {
                // Ищем первый доступный номер телефона
                for ($i = 1; $i <= 3; $i++) {
                    $field_key = 'contacts_phone_' . $i;
                    if (!empty($phones_field[$field_key])) {
                        $phone_number = $phones_field[$field_key];
                        $phone_clean = str_replace([' ', '(', ')', '-'], '', $phone_number);
                        $phone_href = 'tel:' . esc_attr($phone_clean);
                        break 2; // Выходим из обоих циклов
                    }
                }
            }
        }
        wp_reset_postdata();
    }

    return array('number' => $phone_number, 'href' => $phone_href);
}
// --- КОНЕЦ ФУНКЦИЙ ДЛЯ ПОЛУЧЕНИЯ КОНТАКТНЫХ ДАННЫХ ---

// Получаем WhatsApp-номер заранее
$whatsapp_digits = get_whatsapp_number_home_template($selected_city);
$whatsapp_href = $whatsapp_digits ? 'https://wa.me/' . $whatsapp_digits : '#';
$whatsapp_attrs = $whatsapp_digits ? ' target="_blank" rel="noopener noreferrer"' : ' aria-disabled="true"';

// Получаем контактный телефон заранее
$contact_phone_data = get_contact_phone_number_home_template($selected_city);
$contact_phone_href = $contact_phone_data['href'];
$contact_phone_number = $contact_phone_data['number'];

get_header();
?>

<section class="home-hero">
    <div class="container">
        <div class="home-hero__inner">
            <div class="home-hero__info">
                <h4>
                    Пройдите обследование и узнайте причину беспокойства в течение 3 часов. Точные и безопасные исследования с заключением врача.
                </h4>
                <ul class="home-hero__options">
                    <?php
                    // Получаем все уникальные типы услуг для выбранного филиала
                    $service_posts = get_posts(array(
                        'post_type' => 'service',
                        'posts_per_page' => -1,
                        'fields' => 'ids', 
                        'tax_query' => array(
                            array(
                                'taxonomy' => 'branch',
                                'field' => 'slug',
                                'terms' => $selected_city,
                            )
                        )
                    ));

                    $service_types = array();
                    if (!empty($service_posts)) {
                        foreach ($service_posts as $post_id) {
                            $types = wp_get_post_terms($post_id, 'service_type');
                            if (!is_wp_error($types) && !empty($types)) {
                                foreach ($types as $type) {
                                    // Избегаем дубликатов по term_id
                                    if (!isset($service_types[$type->term_id])) {
                                        $service_types[$type->term_id] = $type;
                                    }
                                }
                            }
                        }
                    }

                    // Функция для сортировки типов услуг (лучше вынести в functions.php)
                    function sort_hero_service_types($a, $b) {
                        $order = array(
                            'МРТ 1.5 тесла' => 1,
                            'МРТ 3 тесла' => 2,
                            'КТ' => 3,
                            'УЗИ' => 4,
                            'Денситометрия' => 5
                        );
                        $name_a = $a->name;
                        $name_b = $b->name;
                        $priority_a = isset($order[$name_a]) ? $order[$name_a] : 999;
                        $priority_b = isset($order[$name_b]) ? $order[$name_b] : 999;
                        if ($priority_a == $priority_b) {
                            return strcasecmp($name_a, $name_b); // Сортировка по алфавиту для одинаковых приоритетов
                        }
                        return $priority_a - $priority_b;
                    }

                    // Сортируем и ограничиваем количество
                    if (!empty($service_types)) {
                        uasort($service_types, 'sort_hero_service_types'); // Используем uasort для сохранения ключей
                        $service_types = array_slice($service_types, 0, 6, true); // Сохраняем ключи

                        // Вывод услуг
                        foreach ($service_types as $service_type) {
                            // Формируем URL с префиксом города
                            $url = site_url('/') . $selected_city . '/uslugi-i-ceny/price/' . $service_type->slug . '/';
                            echo '<li class="home-hero__options-item">';
                            echo '<a href="' . esc_url($url) . '">';
                            echo '<p>' . esc_html($service_type->name) . '</p>';
                            echo '<img src="' . esc_url(get_template_directory_uri() . '/assets/img/arrow_diagonal_white.svg') . '" alt="">';
                            echo '</a>';
                            echo '</li>';
                        }
                    }
                    // Если данных нет, ничего не выводится
                    ?>
                </ul>
                <div class="home-hero__buttons">
                    <button class="home-hero__buttons-item booking-btn">
                        Записаться
                        <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/arrow_diagonal_white.svg'); ?>" alt=""> <!-- Используем esc_url и get_template_directory_uri -->
                    </button>
                    <a href="<?php echo esc_url($whatsapp_href); ?>" class="home-hero__buttons-item whatsapp"<?php echo $whatsapp_attrs; ?>>
                        Запись whatsapp
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <rect x="0.5" y="0.5" width="23" height="23" rx="11.5" stroke="white" />
                            <path d="M9.08108 8H16V14.9189M14.8108 9.18919L8 16" stroke="white"
                                stroke-width="1.5" stroke-linecap="round" />
                        </svg>
                    </a>
                </div>
            </div>
            <div class="home-hero__xray">
                <div class="home-hero__xray-background home-hero__xray-main">
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/hero_human.png'); ?>" alt="Рентген"> <!-- Используем esc_url и get_template_directory_uri -->
                </div>
                <div class="home-hero__xray-background home-hero__xray-background--end"></div>
                <div class="home-hero__xray-background home-hero__xray-background--middle"></div>
            </div>
        </div>
    </div>
</section>

<main class="main home">
    <div class="main-background">

        <section class="about">
            <div class="container">
                <div class="about__inner">
                    <div class="about__numbers">
                        <div class="about__numbers-item">
                            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/numbers_1.svg'); ?>" alt="">
                            <p class="about__numbers-numb">12</p>
                            <p class="about__numbers-text">ЛЕТ ОПЫТА</p>
                        </div>
                        <div class="about__numbers-item">
                            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/numbers_2.svg'); ?>" alt="">
                            <p class="about__numbers-numb">55</p>
                            <p class="about__numbers-text">ФИЛИАЛОВ МРТ ЛИДЕР</p>
                        </div>
                        <div class="about__numbers-item">
                            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/numbers_3.svg'); ?>" alt="">
                            <p class="about__numbers-numb">800 000</p>
                            <p class="about__numbers-text">ИССЛЕДОВАНИЙ <br> ЕЖЕГОДНО</p>
                        </div>
                        <div class="about__numbers-item">
                            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/numbers_4.svg'); ?>" alt="">
                            <p class="about__numbers-numb">10 000 000</p>
                            <p class="about__numbers-text">ОБСЛЕДОВАНИЙ <br> ПРОВЕДЕНО</p>
                        </div>
                    </div>
                    <div class="about__why">
                        <div class="about__middle">
                            <div class="about__middle-wrapper">
                                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/about_circle_1.png'); ?>" class="about__middle-img small" alt="">
                                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/about_circle_2.png'); ?>" class="about__middle-img middle" alt="">
                                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/about_circle_3.png'); ?>" class="about__middle-img big" alt="">
                            </div>
                            <h3 class="about__middle-title">ПОЧЕМУ <br> МЫ?</h3>
                        </div>
                        <div class="about__why-item">
                            <div class="about__why-head">
                                <p class="about__why-title">ВЫСОКИЕ ТЕХНОЛОГИИ</p>
                                <div class="about__why-circle"></div>
                            </div>
                            <div class="about__why-info">
                                <p class="about__why-text">Сканирование от 15 до 60 минут.</p>
                                <p class="about__why-text">Заключение врача в течение 3 часов.</p>
                            </div>
                        </div>
                        <div class="about__why-item">
                            <div class="about__why-head">
                                <p class="about__why-title">АКЦИИ И СКИДКИ</p>
                                <div class="about__why-circle"></div>
                            </div>
                            <div class="about__why-info">
                                <p class="about__why-text">Выгодные акции и доступные цены.</p>
                                <p class="about__why-text">Без очередей.</p>
                            </div>
                        </div>
                        <div class="about__why-item">
                            <div class="about__why-head">
                                <p class="about__why-title">СОВРЕМЕННОЕ ОБОРУДОВАНИЕ</p>
                                <div class="about__why-circle"></div>
                            </div>
                            <div class="about__why-info">
                                <p class="about__why-text">Максимальная точность диагностики.</p>
                                <p class="about__why-text">Аппарат экспертного класса <br> высокого разрешения 1,5 Тесла.</p>
                            </div>
                        </div>
                        <div class="about__why-item blue-item">
                            <div class="about__why-head">
                                <p class="about__why-title">Мрт Лидер — ЦЕНТР ДИАГНОСТИКИ <br> ЭКСПЕРТНОГО КЛАССА</p>
                            </div>
                            <div class="about__why-info">
                                <p class="about__why-text">Провели более <span>10 000 000</span> исследований</p>
                                <p class="about__why-text">Более <span>55</span> филиалов по всей стране</p>
                                <p class="about__why-text">Проводим диагностику <span>12</span> лет</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <div class="quality-banner">
            <div class="container">
                <div class="quality-banner__inner">
                    <h4>Федеральный стандарт качества: <br> Диагностика, которой доверяют по всей стране</h4>
                </div>
            </div>
        </div>

        <section class="raiting">
            <div class="container">
                <div class="raiting__inner">
                    <div class="raiting__top">
                        <h2 class="page-title">НАШ РЕЙТИНГ</h2>
                        <div class="raiting__slider raiting__slider--pc">
                            <div class="raiting__slider-arrows">
                                <button class="raiting__slider-arrow raitingSwiper-prev slider-buttons__item prev">
                                    <svg width="11" height="10" viewBox="0 0 11 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M10 1L2 5L10 9" stroke="#404040"/>
                                    </svg>
                                </button>
                                <p>ВИДЕО-ОТЗЫВЫ</p>
                                <button class="raiting__slider-arrow raitingSwiper-next slider-buttons__item next">
                                    <svg width="11" height="10" viewBox="0 0 11 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M10 1L2 5L10 9" stroke="#404040"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="swiper raitingSwiper">
                                <div class="swiper-wrapper">
                                    <div class="swiper-slide">
                                        <a class="raiting__slider-item" href="">
                                            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/slider_raiting.jpg'); ?>" alt="">
                                        </a>
                                    </div>
                                    <div class="swiper-slide">
                                        <a class="raiting__slider-item" href="">
                                            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/slider_raiting.jpg'); ?>" alt="">
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="raiting__reviews">
                        <div class="raiting__item yandex">
                            <div class="raiting__item-container">
                                <div class="raiting__item-title">
                                    <p>Яндекс</p>
                                    <div class="raiting__item-circle"></div>
                                </div>
                                <p class="raiting__item-text">Наш рейтинг: 5.0</p>
                                <div class="raiting__item-star">
                                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/star.png'); ?>" alt="Рейтинг">
                                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/star.png'); ?>" alt="Рейтинг">
                                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/star.png'); ?>" alt="Рейтинг">
                                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/star.png'); ?>" alt="Рейтинг">
                                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/star.png'); ?>" alt="Рейтинг">
                                </div>
                            </div>
                            <a href="" class="raiting__item-link">
                                <div class="raiting__item-read">
                                    <p>Читать отзывы</p>
                                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/arrow_diagonal_grey.svg'); ?>" alt="">
                                </div>
                                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/yandex.png'); ?>" class="raiting__item-logo" alt="">
                            </a>
                            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/rating_corner.svg'); ?>" class="raiting__corner left-top" alt="">
                            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/rating_corner.svg'); ?>" class="raiting__corner left-bottom" alt="">
                            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/rating_corner.svg'); ?>" class="raiting__corner right-top" alt="">
                            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/rating_corner.svg'); ?>" class="raiting__corner right-bottom" alt="">
                        </div>
                        <div class="raiting__item gis">
                            <div class="raiting__item-container">
                                <div class="raiting__item-title">
                                    <p>2GIS</p>
                                    <div class="raiting__item-circle"></div>
                                </div>
                                <p class="raiting__item-text">Отзывы:800+</p>
                                <p class="raiting__item-text">Рейтинг 5.0</p>
                            </div>
                            <a href="" class="raiting__item-link">
                                <div class="raiting__item-read">
                                    <p>Читать отзывы</p>
                                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/arrow_diagonal_grey.svg'); ?>" alt="">
                                </div>
                                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/2gis.png'); ?>" class="raiting__item-logo" alt="">
                            </a>
                            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/rating_corner.svg'); ?>" class="raiting__corner left-top" alt="">
                            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/rating_corner.svg'); ?>" class="raiting__corner left-bottom" alt="">
                            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/rating_corner.svg'); ?>" class="raiting__corner right-top" alt="">
                            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/rating_corner.svg'); ?>" class="raiting__corner right-bottom" alt="">
                        </div>
                        <div class="raiting__item prodoctor">
                            <div class="raiting__item-container">
                                <div class="raiting__item-title">
                                    <p>ПРОДОКТОРОВ</p>
                                    <div class="raiting__item-circle"></div>
                                </div>
                                <p class="raiting__item-text">Нас рекомендуют</p>
                            </div>
                            <a href="" class="raiting__item-link">
                                <div class="raiting__item-read">
                                    <p>Читать отзывы</p>
                                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/arrow_diagonal_grey.svg'); ?>" alt="">
                                </div>
                                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/pro_doctor.png'); ?>" class="raiting__item-logo" alt="">
                            </a>
                            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/rating_corner.svg'); ?>" class="raiting__corner left-top" alt="">
                            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/rating_corner.svg'); ?>" class="raiting__corner left-bottom" alt="">
                            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/rating_corner.svg'); ?>" class="raiting__corner right-top" alt="">
                            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/rating_corner.svg'); ?>" class="raiting__corner right-bottom" alt="">
                        </div>
                        <div class="raiting__item raiting__slider raiting__slider--mobile">
                            <div class="raiting__slider-arrows">
                                <button class="raiting__slider-arrow raitingSwiper-prev slider-buttons__item prev">
                                    <svg width="11" height="10" viewBox="0 0 11 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M10 1L2 5L10 9" stroke="#404040"/>
                                    </svg>
                                </button>
                                <p>ВИДЕО-ОТЗЫВЫ</p>
                                <button class="raiting__slider-arrow raitingSwiper-next slider-buttons__item next">
                                    <svg width="11" height="10" viewBox="0 0 11 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M10 1L2 5L10 9" stroke="#404040"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="swiper raitingSwiper">
                                <div class="swiper-wrapper">
                                    <div class="swiper-slide">
                                        <a class="raiting__slider-item" href="">
                                            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/slider_raiting.jpg'); ?>" alt="">
                                        </a>
                                    </div>
                                    <div class="swiper-slide">
                                        <a class="raiting__slider-item" href="">
                                            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/slider_raiting.jpg'); ?>" alt="">
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="examination">
            <div class="container">
                <div class="examination__inner">
                    <h2 class="examination__title page-title">ЗАПИШИТЕСЬ НА ОБСЛЕДОВАНИЕ</h2>
                    <div class="examination__content">
                        <div class="examination__info">
                            <div class="examination__info-item">
                                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/register_jackdaw.svg'); ?>" alt="">
                                <p class="examination__info-text">МРТ без лучевой нагрузки.</p>
                            </div>
                            <div class="examination__info-item">
                                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/register_jackdaw.svg'); ?>" alt="">
                                <p class="examination__info-text">Оперативное и качественное описание снимка любой сложности.</p>
                            </div>
                            <div class="examination__info-item">
                                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/register_jackdaw.svg'); ?>" alt="">
                                <p class="examination__info-text">Контроль врачом хода исследования в реальном времени.</p>
                            </div>
                            <div class="examination__info-item">
                                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/register_jackdaw.svg'); ?>" alt="">
                                <p class="examination__info-text">Перезвоним в течение 10 минут, запишем на обследование в удобное для Вас время.</p>
                            </div>
                        </div>
                        <div class="examination__form">
                            <form action="#"> <!-- TODO: Указать реальный action и метод -->
                                <input type="text" class="examination__form-inp" placeholder="Введите имя" required> <!-- Добавлено required -->
                                <input type="tel" class="examination__form-inp" placeholder="Введите телефон" required> <!-- Добавлено required и type="tel" -->
                                <button class="examination__form-btn" type="submit"> <!-- Добавлен type="submit" -->
                                    <p>Записаться на приём</p>
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <rect x="0.5" y="0.5" width="23" height="23" rx="11.5" stroke="#404040"/>
                                        <path d="M9.08108 8H16V14.9189M14.8108 9.18919L8 16" stroke="#404040" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                </button>
                                <p class="examination__form-privacy">
                                    Нажимая на кнопку, вы автоматически соглашаетесь с
                                    <a href="<?php echo esc_url(site_url('privacy/')); ?>">Политикой обработки персональных данных.</a>
                                </p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="specialists">
            <div class="container">
                <div class="specialists__inner">
                    <div class="specialists__top">
                        <h2 class="page-title">СПЕЦИАЛИСТЫ</h2>
                        <div class="specialists__buttons slider-buttons">
                            <button class="specialists__buttons-arrow specialistsSwiper-prev slider-buttons__item prev">
                                <svg width="16" height="16" viewBox="0 0 11 10" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M10 1L2 5L10 9" stroke="#404040" />
                                </svg>
                            </button>
                            <button class="specialists__buttons-arrow specialistsSwiper-next slider-buttons__item next">
                                <svg width="16" height="16" viewBox="0 0 11 10" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M10 1L2 5L10 9" stroke="#404040" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="specialists__slider">
                        <div class="swiper specialistsSwiper">
                            <div class="swiper-wrapper">
                                <?php
                                // Получаем специалистов для выбранного города
                                $specialists_args = array(
                                    'post_type' => 'post',
                                    'posts_per_page' => 10,
                                    'tax_query' => array(
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

                                $specialists_query = new WP_Query($specialists_args);

                                if ($specialists_query->have_posts()) :
                                    while ($specialists_query->have_posts()) : $specialists_query->the_post();
                                        ?>
                                        <div class="swiper-slide">
                                            <a class="specialists__slider-item" href="<?php the_permalink(); ?>">
                                                <div class="specialists__slider-wrapper">
                                                    <div class="specialists__slider-img">
                                                        <?php 
                                                        $image = get_field('specialists_image');
                                                        if ($image) : 
                                                        ?>
                                                            <img src="<?php echo esc_url($image); ?>" alt="<?php the_title(); ?>">
                                                        <?php else: ?>
                                                            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/specialists_1.png'); ?>" alt="<?php the_title(); ?>">
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="specialists__slider-arrow">
                                                        <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M2.08108 1H9V7.91892M7.81081 2.18919L1 9" stroke="#6180A1" stroke-width="1.5"
                                                                stroke-linecap="round" />
                                                        </svg>
                                                    </div>
                                                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/rating_corner.svg'); ?>" class="raiting__corner left-top" alt="">
                                                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/rating_corner.svg'); ?>" class="raiting__corner left-bottom" alt="">
                                                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/rating_corner.svg'); ?>" class="raiting__corner right-top" alt="">
                                                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/rating_corner.svg'); ?>" class="raiting__corner right-bottom" alt="">
                                                </div>
                                                <?php if (get_field('specialists_job')) : ?>
                                                    <p class="specialists__slider-specialty"><?php the_field('specialists_job'); ?></p>
                                                <?php endif; ?>
                                                <p class="specialists__slider-name"><?php the_title(); ?></p>
                                            </a>
                                        </div>
                                        <?php
                                    endwhile;
                                    wp_reset_postdata();
                                else :
                                    // Если специалистов нет, выводим заглушку (или можно ничего не выводить)
                                    ?>
                                    <div class="swiper-slide-spec">
                                        <p>
                                            Специалистов для выбранного города не найдено.
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="photos">
            <div class="container">
                <div class="photos__inner">
                    <h2 class="page-title">НАШ ЦЕНТР</h2>
                    <div class="photos__content">
                        <?php
                        // Запрашиваем пост контактов для выбранного города
                        $photos_args = array(
                            'post_type'      => 'post',
                            'posts_per_page' => 1,
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
                                    'terms'    => 'contacty',
                                )
                            )
                        );
                        $photos_query = new WP_Query( $photos_args );

                        // Временный массив для хранения обработанных изображений
                        $centre_photos = array();

                        if ( $photos_query->have_posts() ) :
                            while ( $photos_query->have_posts() ) : $photos_query->the_post();
                                // Получаем группу полей с фотографиями центра
                                $photos_group = get_field( 'photos_centre' ) ?: array();

                                // Проверяем до 8 полей и собираем реальные URL/миниатюры/alt
                                for ( $i = 1; $i <= 8; $i++ ) {
                                    $field_key = 'photos_centre_' . $i;
                                    if ( empty( $photos_group[ $field_key ] ) ) {
                                        continue;
                                    }

                                    $item = $photos_group[ $field_key ];

                                    $full_url  = '';
                                    $thumb_url = '';
                                    $alt_text  = '';

                                    // ID (число)
                                    if ( is_int( $item ) || ctype_digit( (string) $item ) ) {
                                        $attach_id = (int) $item;
                                        $full_url  = wp_get_attachment_image_url( $attach_id, 'full' );
                                        // Подберите размер миниатюры под вашу вёрстку — 'medium' / 'thumbnail' / 'large'
                                        $thumb_url = wp_get_attachment_image_url( $attach_id, 'medium' ) ?: $full_url;
                                        $alt_text  = get_post_meta( $attach_id, '_wp_attachment_image_alt', true );
                                    }
                                    // Если ACF вернул массив
                                    elseif ( is_array( $item ) ) {
                                        if ( ! empty( $item['url'] ) ) {
                                            $full_url = $item['url'];
                                        }
                                        if ( ! empty( $item['sizes']['medium'] ) ) {
                                            $thumb_url = $item['sizes']['medium'];
                                        } elseif ( ! empty( $item['sizes']['thumbnail'] ) ) {
                                            $thumb_url = $item['sizes']['thumbnail'];
                                        } else {
                                            $thumb_url = $full_url;
                                        }
                                        if ( ! empty( $item['alt'] ) ) {
                                            $alt_text = $item['alt'];
                                        } elseif ( ! empty( $item['ID'] ) ) {
                                            $alt_text = get_post_meta( $item['ID'], '_wp_attachment_image_alt', true );
                                        }
                                    }
                                    // Прямая строка — URL
                                    elseif ( is_string( $item ) ) {
                                        $full_url  = $item;
                                        $thumb_url = $item;
                                    }

                                    // Если получили URL — добавляем в массив
                                    if ( $full_url ) {
                                        $centre_photos[] = array(
                                            'full'  => esc_url_raw( $full_url ),
                                            'thumb' => esc_url_raw( $thumb_url ?: $full_url ),
                                            'alt'   => sanitize_text_field( $alt_text ?: get_the_title() ),
                                        );
                                    }
                                } // end for
                            endwhile;
                            wp_reset_postdata();
                        endif;

                        // Обрезаем до 5 изображений
                        if ( ! empty( $centre_photos ) ) :
                            $centre_photos = array_slice( $centre_photos, 0, 5 );

                            // Разбиваем на верх/низ (3 + 2)
                            $top_photos    = array_slice( $centre_photos, 0, 3 );
                            $bottom_photos = array_slice( $centre_photos, 3, 2 );
                            ?>
                            <div class="photos__top">
                                <?php foreach ( $top_photos as $index => $img ) : ?>
                                    <a href="<?php echo esc_url( $img['full'] ); ?>"
                                    class="photos__item"
                                    data-fancybox="photos-gallery">
                                        <img src="<?php echo esc_url( $img['thumb'] ); ?>"
                                            alt="<?php echo esc_attr( $img['alt'] ); ?>"
                                            class="photos__item-img" loading="lazy">
                                    </a>
                                <?php endforeach; ?>
                            </div>

                            <div class="photos__bottom">
                                <?php foreach ( $bottom_photos as $index => $img ) : ?>
                                    <a href="<?php echo esc_url( $img['full'] ); ?>"
                                    class="photos__item"
                                    data-fancybox="photos-gallery">
                                        <img src="<?php echo esc_url( $img['thumb'] ); ?>"
                                            alt="<?php echo esc_attr( $img['alt'] ); ?>"
                                            class="photos__item-img" loading="lazy">
                                    </a>
                                <?php endforeach; ?>
                            </div>

                            <div class="photos__more">
                                <a href="" class="photos__more-link">
                                    Ещё фото
                                </a>
                            </div>
                        <?php else : ?>
                            <div class="photos__empty">
                                <p>Фотографий центра для выбранного города не найдено.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>


        <section class="find">
            <div class="container">
                <div class="find__inner">
                    <div class="find__top">
                        <h2 class="page-title">НАЙТИ УСЛУГУ</h2>
                        <div class="find__search">
                            <input type="text" placeholder="поиск" id="find-search">
                            <svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M18.7617 15.7344C19.7383 14.1979 20.3112 12.375 20.3112 10.4154C20.3112 4.9401 15.8776 0.5 10.4089 0.5C4.93359 0.5 0.5 4.9401 0.5 10.4154C0.5 15.8906 4.93359 20.3307 10.4023 20.3307C12.388 20.3307 14.237 19.7448 15.7865 18.7422L16.2357 18.4297L23.306 25.5L25.5 23.2669L18.4362 16.1966L18.7617 15.7344ZM15.9557 4.875C17.4336 6.35286 18.2474 8.31901 18.2474 10.4089C18.2474 12.4987 17.4336 14.4648 15.9557 15.9427C14.4779 17.4206 12.5117 18.2344 10.4219 18.2344C8.33203 18.2344 6.36588 17.4206 4.88802 15.9427C3.41016 14.4648 2.59635 12.4987 2.59635 10.4089C2.59635 8.31901 3.41016 6.35286 4.88802 4.875C6.36588 3.39714 8.33203 2.58333 10.4219 2.58333C12.5117 2.58333 14.4779 3.39714 15.9557 4.875Z" fill="white"/>
                            </svg>
                        </div>
                    </div>
                    <p class="find__subtitle">Популярное:</p>
                    <div class="find__popular">
                        <?php

                        $service_posts = get_posts(array(
                            'post_type' => 'service',
                            'posts_per_page' => -1,
                            'fields' => 'ids', 
                            'tax_query' => array(
                                array(
                                    'taxonomy' => 'branch',
                                    'field' => 'slug',
                                    'terms' => $selected_city,
                                )
                            )
                        ));

                        // Группируем по типам услуг
                        $service_types_data = array();
                        if (!empty($service_posts)) {
                            foreach ($service_posts as $post_id) {
                                // Получаем тип услуги
                                $service_types = wp_get_post_terms($post_id, 'service_type');
                                if (!is_wp_error($service_types) && !empty($service_types)) {
                                    $service_type = $service_types[0]; // Берем первый тип услуги

                                    // Получаем категорию услуги (предполагается, что это произвольное поле)
                                    $category = get_post_meta($post_id, 'si_category', true);

                                    // Инициализируем массив для типа услуги, если нужно
                                    if (!isset($service_types_data[$service_type->term_id])) {
                                        $service_types_data[$service_type->term_id] = array(
                                            'term' => $service_type,
                                            'categories' => array()
                                        );
                                    }

                                    // Добавляем категорию в массив, если её там ещё нет и она не пуста
                                    if (!empty($category) && !in_array($category, $service_types_data[$service_type->term_id]['categories'])) {
                                        $service_types_data[$service_type->term_id]['categories'][] = $category;
                                    }
                                }
                            }
                        }

                        // Ограничиваем до 4 блоков
                        $service_types_data = array_slice($service_types_data, 0, 4, true); 

                        // Выводим блоки
                        foreach ($service_types_data as $type_data) {
                            $service_type = $type_data['term'];
                            $categories = array_slice($type_data['categories'], 0, 3); // максимум 3 категории
                            if (!empty($categories)) {
                                echo '<div class="find__popular-item">';
                                foreach ($categories as $category) {
                                    // Формируем URL с учетом выбранного города
                                    $url = home_url('/') . $selected_city . '/uslugi-i-ceny/price/' . $service_type->slug . '/';
                                    echo '<a href="' . esc_url($url) . '" class="find__popular-link">' . esc_html($category) . '</a>';
                                }
                                echo '</div>';
                            }
                        }

                        // Заглушка
                        if (empty($service_types_data)) {
                            echo '<div class="find__popular-item">';
                            echo '<p class="find__popular-link">Услуги не найдены</p>';
                            echo '</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </section>

        <!-- <section class="news">
            <div class="container">
                <div class="news__inner">
                    <div class="news__top">
                        <h2 class="page-title">НОВОСТИ</h2>
                        <div class="news__buttons">
                            <button class="news__buttons-arrow newsSwiper-prev slider-buttons__item">
                                <svg width="16" height="16" viewBox="0 0 11 10" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M10 1L2 5L10 9" stroke="#404040" />
                                </svg>
                            </button>
                            <button class="news__buttons-arrow newsSwiper-next slider-buttons__item">
                                <svg width="16" height="16" viewBox="0 0 11 10" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M10 1L2 5L10 9" stroke="#404040" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="news__content">
                    </div>
                </div>
            </div>
        </section> -->

        <section class="articles">
            <div class="container">
                <div class="articles__inner">
                    <div class="articles__top">
                        <h2 class="page-title">СТАТЬИ</h2>
                        <div class="articles__buttons slider-buttons">
                            <button class="articles__buttons-arrow articlesSwiper-prev slider-buttons__item prev">
                                <svg width="16" height="16" viewBox="0 0 11 10" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M10 1L2 5L10 9" stroke="#404040" />
                                </svg>
                            </button>
                            <button class="articles__buttons-arrow articlesSwiper-next slider-buttons__item next">
                                <svg width="16" height="16" viewBox="0 0 11 10" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M10 1L2 5L10 9" stroke="#404040" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="articles__slider">
                        <div class="swiper articlesSwiper">
                            <div class="swiper-wrapper">
                                <div class="swiper-slide">
                                    <a href="#" class="articles__slider-item">
                                        <h4 class="articles__slider-title">ИИ и МРТ</h4>
                                        <p class="articles__slider-text">
                                            Ученые обучали семантический декодер на мыслительной активности
                                            испытуемого, который прослушал несколько часов подкастов, пока его
                                            мозг исследовали с помощью аппарата МРТ...
                                        </p>
                                        <div class="articles__slider-arrow">
                                            <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M2.08108 1H9V7.91892M7.81081 2.18919L1 9" stroke="#6180A1" stroke-width="1.5"
                                                    stroke-linecap="round" />
                                            </svg>
                                        </div>
                                    </a>
                                </div>
                                <div class="swiper-slide">
                                    <a href="#" class="articles__slider-item">
                                        <h4 class="articles__slider-title">МРТ от Philips</h4>
                                        <p class="articles__slider-text">
                                            Инновационный способ снижения стресса и беспокойства во время МРТ
                                            внедрила компания Philips благодаря решению Ambient Experience...
                                        </p>
                                        <div class="articles__slider-arrow">
                                            <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M2.08108 1H9V7.91892M7.81081 2.18919L1 9" stroke="#6180A1" stroke-width="1.5"
                                                    stroke-linecap="round" />
                                            </svg>
                                        </div>
                                    </a>
                                </div>
                                <div class="swiper-slide">
                                    <a href="#" class="articles__slider-item">
                                        <h4 class="articles__slider-title">КАК ПОДГОТОВИТЬ К МРТ РЕБЁНКА</h4>
                                        <p class="articles__slider-text">
                                            Чтобы МРТ-исследование ребенка было результативным, к маленьким
                                            пациентам при необходимости и после консультации с врачом применяют
                                            седативные...
                                        </p>
                                        <div class="articles__slider-arrow">
                                            <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M2.08108 1H9V7.91892M7.81081 2.18919L1 9" stroke="#6180A1" stroke-width="1.5"
                                                    stroke-linecap="round" />
                                            </svg>
                                        </div>
                                    </a>
                                </div>
                                <div class="swiper-slide">
                                    <a href="#" class="articles__slider-item">
                                        <h4 class="articles__slider-title">МРТ от Philips</h4>
                                        <p class="articles__slider-text">
                                            Инновационный способ снижения стресса и беспокойства во время МРТ
                                            внедрила компания Philips благодаря решению Ambient Experience...
                                        </p>
                                        <div class="articles__slider-arrow">
                                            <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M2.08108 1H9V7.91892M7.81081 2.18919L1 9" stroke="#6180A1" stroke-width="1.5"
                                                    stroke-linecap="round" />
                                            </svg>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="stock">
            <div class="container">
                <div class="stock__inner">
                    <div class="stock__top">
                        <h2 class="page-title">АКЦИИ</h2>
                        <div class="stock__buttons slider-buttons">
                            <button class="stock__buttons-arrow stockSwiper-prev slider-buttons__item prev">
                                <svg width="16" height="16" viewBox="0 0 11 10" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M10 1L2 5L10 9" stroke="#404040" />
                                </svg>
                            </button>
                            <button class="stock__buttons-arrow stockSwiper-next slider-buttons__item next">
                                <svg width="16" height="16" viewBox="0 0 11 10" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M10 1L2 5L10 9" stroke="#404040" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="stock__slider">
                        <div class="swiper stockSwiper">
                            <div class="swiper-wrapper">
                                <div class="swiper-slide">
                                    <a href="#" class="stock__slider-item">
                                        <div class="stock__slider-img">
                                            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/stock_1.jpg'); ?>" alt="">
                                            <div class="stock__slider-date">
                                                <p>
                                                    <span>до</span>
                                                    <span>31.12.25</span>
                                                </p>
                                            </div>
                                        </div>
                                        <h4 class="stock__slider-title">
                                            Пройди диагностику ночью — Цена снижена
                                        </h4>
                                        <div class="stock__slider-arrow">
                                            <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M2.08108 1H9V7.91892M7.81081 2.18919L1 9" stroke="#6180A1" stroke-width="1.5"
                                                    stroke-linecap="round" />
                                            </svg>
                                        </div>
                                    </a>
                                </div>
                                <div class="swiper-slide">
                                    <a href="#" class="stock__slider-item">
                                        <div class="stock__slider-img">
                                            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/stock_2.jpg'); ?>" alt="">
                                            <div class="stock__slider-date">
                                                <p>
                                                    <span>до</span>
                                                    <span>31.12.25</span>
                                                </p>
                                            </div>
                                        </div>
                                        <h4 class="stock__slider-title">
                                            Скидка для льготных категорий граждан
                                        </h4>
                                        <div class="stock__slider-arrow">
                                            <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M2.08108 1H9V7.91892M7.81081 2.18919L1 9" stroke="#6180A1" stroke-width="1.5"
                                                    stroke-linecap="round" />
                                            </svg>
                                        </div>
                                    </a>
                                </div>
                                <div class="swiper-slide">
                                    <a href="#" class="stock__slider-item">
                                        <div class="stock__slider-img">
                                            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/stock_3.jpg'); ?>" alt="">
                                            <div class="stock__slider-date">
                                                <p>
                                                    <span>до</span>
                                                    <span>31.12.25</span>
                                                </p>
                                            </div>
                                        </div>
                                        <h4 class="stock__slider-title">
                                            Комплексные программы со скидкой
                                        </h4>
                                        <div class="stock__slider-arrow">
                                            <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M2.08108 1H9V7.91892M7.81081 2.18919L1 9" stroke="#6180A1" stroke-width="1.5"
                                                    stroke-linecap="round" />
                                            </svg>
                                        </div>
                                    </a>
                                </div>
                                <!--div class="swiper-slide">
                                    <a href="#" class="stock__slider-item">
                                        <div class="stock__slider-img">
                                            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/stock_2.jpg'); ?>" alt="">
                                            <div class="stock__slider-date">
                                                <p>
                                                    <span>до</span>
                                                    <span>29.02.25</span>
                                                </p>
                                            </div>
                                        </div>
                                        <h4 class="stock__slider-title">
                                            СКИДКА 15% НА МРТ ДЛЯ ДЕТЕЙ
                                        </h4>
                                        <div class="stock__slider-arrow">
                                            <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M2.08108 1H9V7.91892M7.81081 2.18919L1 9" stroke="#6180A1" stroke-width="1.5"
                                                    stroke-linecap="round" />
                                            </svg>
                                        </div>
                                    </a>
                                </div-->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="question">
            <div class="container">
                <div class="question__inner">
                    <div class="question__mobile">
                        <div class="question__mobile-item top">
                            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/semicircle_mobile.svg'); ?>" alt="">
                        </div>
                        <div class="question__mobile-item bottom">
                            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/semicircle_mobile.svg'); ?>" alt="">
                        </div>
                        <div class="question__mobile-tg">
                            <div class="question__tg">
                                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/tg.svg'); ?>" alt="">
                            </div>
                        </div>
                    </div>
                    <div class="question__images">
                        <div class="question__semicircle">
                            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/semicircle.svg'); ?>" alt="">
                        </div>
                        <div class="question__tg">
                            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/tg.svg'); ?>" alt="">
                        </div>
                    </div>
                    <div class="question__content">
                        <a href="<?php echo esc_url($contact_phone_href); ?>" class="question__info">
                            <p class="question__title">ОСТАЛИСЬ ВОПРОСЫ?</p>
                            <p class="question__text">Звоните! <?php echo esc_html($contact_phone_number); ?></p>
                        </a>
                        <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/any_questions_left.jpg'); ?>" alt="">
                    </div>
                </div>
            </div>
        </div>

        <div class="tour">
            <div class="container">
                <div class="tour__inner">
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/3d_tour.jpg'); ?>" alt="">
                    <a href="#" class="tour__content">
                        <h2 class="tour__title"><span>ПРОЙДИТЕ 3D ТУР</span> <span>ПО КЛИНИКЕ</span> <span>«МРТ ЛИДЕР»</span></h2>
                        <div class="tour__play">
                            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/play_video.svg'); ?>" alt="">
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>

<div class="booking" id="booking">
    <div class="booking__overlay">
        <form class="booking__form" action="#">
            <button type="button" class="booking__close-btn" aria-label="Закрыть">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M18 6L6 18M6 6L18 18" stroke="#404040" stroke-width="2"
                        stroke-linecap="round" />
                </svg>
            </button>

            <div class="booking__form-wrapper">
                <input type="text" class="booking__form-input" placeholder="Введите имя" required>
                <input type="text" class="booking__form-input" placeholder="Введите телефон" required>
                <button class="booking__form-btn btn-blue">
                    <p>Записаться на приём</p>
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <rect x="0.5" y="0.5" width="23" height="23" rx="11.5"
                            stroke="#404040" />
                        <path d="M9.08108 8H16V14.9189M14.8108 9.18919L8 16" stroke="#404040"
                            stroke-width="1.5" stroke-linecap="round" />
                    </svg>
                </button>
            </div>
            <p class="booking__form-privacy">
                Нажимая на кнопку, вы автоматически соглашаетесь с
                <a href="<?php echo site_url('') ?>/privacy/">Политикой обработки персональных данных.</a>
            </p>
        </form>
    </div>
</div>

<!-- Обработчики форм -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // --- Обработчик для формы .booking__form (модальное окно) ---
        function initBookingForm() {
            const form = document.querySelector('.booking__form');
            if (form) {
                // --- Инициализация маски для телефона в booking ---
                const phoneInput = form.querySelector('input[placeholder="Введите телефон"]');
                let phoneMaskInstance = null;
                if (phoneInput) {
                    phoneMaskInstance = IMask(phoneInput, {
                        mask: '+{7} (000) 000-00-00',
                        lazy: false,
                        placeholderChar: '_'
                    });
                }

                // Предотвращаем дублирование обработчиков
                const existingListener = form.dataset.submitListenerAdded;
                if (!existingListener) {
                    form.dataset.submitListenerAdded = 'true';

                    form.addEventListener('submit', function (e) {
                        e.preventDefault();

                        const nameInput = form.querySelector('input[placeholder="Введите имя"]');
                        const phoneInputElement = phoneInput; // Используем уже найденное поле

                        const name = nameInput ? nameInput.value.trim() : '';
                        
                        // Усиленная обработка номера телефона
                        let phone = '';
                        if (phoneInputElement) {
                            if (phoneMaskInstance && phoneMaskInstance.unmaskedValue) {
                                // Получаем "сырое" значение из маски
                                phone = phoneMaskInstance.unmaskedValue;
                                // Нормализуем: заменяем 8 на +7, добавляем +7 если нужно
                                if (phone.startsWith('7')) {
                                    phone = '+7' + phone.substring(1);
                                } else if (phone.startsWith('8')) {
                                    phone = '+7' + phone.substring(1);
                                } else {
                                    phone = '+7' + phone;
                                }
                            } else {
                                // Если маска не инициализирована, очищаем вручную
                                phone = phoneInputElement.value.replace(/[^\d\+]/g, '');
                            }
                        }

                        // --- Проверки ---
                        if (!name) {
                            alert('Пожалуйста, введите Ваше имя.');
                            if (nameInput) nameInput.focus();
                            return;
                        }

                        // Проверка телефона: убедимся, что это российский номер
                        const phoneDigits = phone.replace(/[^\d]/g, ''); // Оставляем только цифры
                        if (phoneDigits.length < 10 || phoneDigits.length > 11) { 
                            alert('Пожалуйста, введите корректный номер телефона (например, +7 (999) 999-99-99).');
                            if (phoneInputElement) phoneInputElement.focus();
                            return;
                        }
                        // --- Конец проверок ---

                        const data = new FormData();
                        data.append('action', 'send_booking_form');
                        data.append('name', name);
                        data.append('phone', phone); // Отправляем нормализованный номер
                        data.append('nonce', '<?php echo wp_create_nonce("booking_form_nonce"); ?>');

                        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                            method: 'POST',
                            body: data
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.text();
                        })
                        .then(result => {
                            alert(result);
                            form.reset();
                            // Если форма часть модального окна, можно добавить код для его закрытия
                        })
                        .catch(error => {
                            console.error('Ошибка отправки формы booking:', error);
                            alert('Ошибка отправки формы. Пожалуйста, попробуйте еще раз.');
                        });
                    });
                }
            }
        }

        // Инициализируем форму booking при загрузке страницы
        initBookingForm();

        // --- Обработчик для формы .examination__form ---
        const examinationForm = document.querySelector('.examination__form form');
        if (examinationForm) {
            // --- Инициализация маски для телефона в examination ---
            const phoneInputEx = examinationForm.querySelector('input[placeholder="Введите телефон"]');
            let phoneMaskInstanceEx = null;
            if (phoneInputEx) {
                phoneMaskInstanceEx = IMask(phoneInputEx, {
                    mask: '+{7} (000) 000-00-00',
                    lazy: false,
                    placeholderChar: '_'
                });
            }

            examinationForm.addEventListener('submit', function (e) {
                e.preventDefault();

                const nameInput = examinationForm.querySelector('input[placeholder="Введите имя"]');
                const phoneInputElement = phoneInputEx; // Используем уже найденное поле

                const name = nameInput ? nameInput.value.trim() : '';
                
                // Усиленная обработка номера телефона
                let phone = '';
                if (phoneInputElement) {
                    if (phoneMaskInstanceEx && phoneMaskInstanceEx.unmaskedValue) {
                        // Получаем "сырое" значение из маски
                        phone = phoneMaskInstanceEx.unmaskedValue;
                        // Нормализуем: заменяем 8 на +7, добавляем +7 если нужно
                        if (phone.startsWith('7')) {
                            phone = '+7' + phone.substring(1);
                        } else if (phone.startsWith('8')) {
                            phone = '+7' + phone.substring(1);
                        } else {
                            phone = '+7' + phone;
                        }
                    } else {
                        // Если маска не инициализирована, очищаем вручную
                        phone = phoneInputElement.value.replace(/[^\d\+]/g, '');
                    }
                }

                // --- Проверки ---
                if (!name) {
                    alert('Пожалуйста, введите Ваше имя.');
                    if (nameInput) nameInput.focus();
                    return;
                }

                // Проверка телефона: убедимся, что это российский номер
                const phoneDigits = phone.replace(/[^\d]/g, ''); // Оставляем только цифры
                if (phoneDigits.length < 10 || phoneDigits.length > 11) { 
                    alert('Пожалуйста, введите корректный номер телефона (например, +7 (999) 999-99-99).');
                    if (phoneInputElement) phoneInputElement.focus();
                    return;
                }
                // --- Конец проверок ---

                const data = new FormData();
                data.append('action', 'send_main_form');
                data.append('name', name);
                data.append('phone', phone); // Отправляем нормализованный номер
                data.append('nonce', '<?php echo wp_create_nonce("main_form_nonce"); ?>');

                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: data
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.text();
                })
                .then(result => {
                    alert(result);
                    examinationForm.reset();
                })
                .catch(error => {
                    console.error('Ошибка отправки формы examination:', error);
                    alert('Ошибка отправки формы. Пожалуйста, попробуйте еще раз.');
                });
            });
        }
    });
</script>
<!-- Обработчики форм -->

<?php get_footer(); ?>