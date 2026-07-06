<?php
    // --- Список валидных слагов городов ---
    $known_city_slugs = array(
        'almaty', 'astana', 'karaganda', 'taldykorgan'
    );

    // --- Определяем город: URL > кука > fallback ---
    $selected_city_slug_nav = 'almaty';

    $request_uri = $_SERVER['REQUEST_URI'] ?? '/';
    $path = parse_url($request_uri, PHP_URL_PATH);
    $path = trim($path, '/');
    $path_parts = $path ? explode('/', $path) : array();
    $url_city = !empty($path_parts[0]) ? sanitize_text_field($path_parts[0]) : '';

    if ($url_city && in_array($url_city, $known_city_slugs, true)) {
        $selected_city_slug_nav = $url_city;
    } elseif (isset($_COOKIE['selected_city'])) {
        $cookie_city = sanitize_text_field($_COOKIE['selected_city']);
        if (in_array($cookie_city, $known_city_slugs, true)) {
            $selected_city_slug_nav = $cookie_city;
        }
    }

    // --- Подготовка URL и навигации ---
    $city_specific_pages = array('uslugi-i-ceny', 'specialisty', 'vopros-otvet', 'about', 'kontakty');
    $city_base_url_nav = home_url('/') . $selected_city_slug_nav . '/';
    $city_home_url = $city_base_url_nav;

    // --- Функция навигации (без setcookie!) ---
    if (!function_exists('get_nav_url')) {
        function get_nav_url($page_slug, $city_base_url, $city_specific_pages) {
            if (in_array($page_slug, $city_specific_pages, true)) {
                return $city_base_url . $page_slug . '/';
            } else {
                return home_url('/') . $page_slug . '/';
            }
        }
    }

    $contacts_args = array(
        'post_type'      => 'post',
        'posts_per_page' => 1,
        'tax_query'      => array(
            'relation' => 'AND',
            array(
                'taxonomy' => 'category',
                'field'    => 'slug',
                'terms'    => $selected_city_slug_nav,
            ),
            array(
                'taxonomy' => 'category',
                'field'    => 'slug',
                'terms'    => 'contacty',
            ),
        ),
    );
    $contacts_query = new WP_Query($contacts_args);

    $whatsapp_digits = '';
    if ( $contacts_query->have_posts() ) {
        while ( $contacts_query->have_posts() ) {
            $contacts_query->the_post();
            $whatsapp_field = get_field('contacts_whatsapp'); // ACF
            if ( ! empty( $whatsapp_field ) ) {
                $whatsapp_digits = preg_replace('/\D+/', '', $whatsapp_field);
            }
        }
        wp_reset_postdata();
    }
    $whatsapp_href = $whatsapp_digits ? 'https://wa.me/' . $whatsapp_digits : '#';
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset') ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="facebook-domain-verification" content="baawbxh7yddo6g4edqe7wqeclmm57y" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,100..900;1,100..900&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Raleway:ital,wght@0,100..900;1,100..900&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <?php wp_head(); ?>
    <link rel="stylesheet" href="/assets/css/style.css">
    
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    
</head>

<body>
<script>
// Передаем PHP-массив в JavaScript
    const citySpecificPagesJs = <?php echo json_encode($city_specific_pages); ?>;
</script>
    <div class="wrapper">
        <div class="overlay"></div>
        <div class="overlay-menu"></div>
        <header class="header">
            <div class="container-city">
                <div class="header__inner">

                    <div class="header__top">
                        <div class="container">
                            <a href="<?php echo esc_url($city_home_url); ?>" class="header__logo">
                                <img src="<?php bloginfo('template_url')?>/assets/img/logo.svg" alt="Логотип">
                            </a>
                            <div class="header__top-content">
                                <div class="header__mobile">
                                    <div class="header__mobile-phone">
                                        <?php
                                        $selected_city = $selected_city_slug_nav;
                                        $args = array(
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
                                                ),
                                            ),
                                        );
                                        $contacts_query = new WP_Query($args);
    
                                        $first_phone_href = 'tel:';
                                        if ($contacts_query->have_posts()) {
                                            while ($contacts_query->have_posts()) {
                                                $contacts_query->the_post();
                                                $phones_group = get_field('contacts_phones') ?: [];
                                                
                                                // Получаем первый номер телефона
                                                $field_key = 'contacts_phone_1';
                                                if (!empty($phones_group[$field_key])) {
                                                    $raw = $phones_group[$field_key];
                                                    // Оставляем плюс и цифры для tel:
                                                    $tel_clean = preg_replace('/[^\d\+]/', '', $raw);
                                                    $first_phone_href = 'tel:' . esc_attr($tel_clean);
                                                }
                                            }
                                            wp_reset_postdata();
                                        }
                                        ?>
                                        <a href="<?php echo esc_url($first_phone_href); ?>" class="header__mobile-btn">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M3.56438 0.443876C4.46304 -0.182683 5.64783 -0.146252 6.29493 0.555269L6.41756 0.705042L6.98388 1.54377C7.83727 2.82325 9.337 5.09938 9.54968 5.43504C9.8029 5.83401 9.83381 6.2803 9.79306 6.63696C9.75125 7.00282 9.62411 7.36429 9.45233 7.69005L9.45327 7.69099C9.31621 7.95224 8.97611 8.55585 8.681 9.07638C8.58596 9.24401 8.49336 9.40495 8.41234 9.54723C8.49712 9.66212 8.60037 9.80039 8.72687 9.96004C9.20751 10.5667 10.0065 11.4954 11.2543 12.743C12.5023 13.9908 13.4314 14.7893 14.0382 15.2704C14.1972 15.3965 14.3345 15.5002 14.4491 15.5849C14.5915 15.5039 14.7532 15.4123 14.9209 15.3172C15.4401 15.0228 16.0414 14.6837 16.3035 14.5459C16.6325 14.3719 16.9964 14.2468 17.3622 14.2061C17.7186 14.1665 18.155 14.2007 18.5473 14.4401C18.7904 14.5888 19.8899 15.3138 20.9699 16.0305L23.2792 17.5732L23.2801 17.5741C24.1625 18.1928 24.2112 19.4933 23.5497 20.4366C23.2942 20.8022 22.6329 21.6342 21.9191 22.378C21.5617 22.7504 21.1666 23.1267 20.7836 23.4152C20.5923 23.5593 20.3826 23.6988 20.1648 23.8037C19.9598 23.9024 19.6695 24.0095 19.3401 23.9993H19.3383C18.8121 23.9822 17.542 23.9385 15.5069 22.9827C13.4976 22.0391 10.7741 20.2223 7.27501 16.7223C3.77661 13.2234 1.95987 10.4998 1.01637 8.4904C0.0608344 6.45529 0.0175548 5.18483 0.000723427 4.6581C-0.00981607 4.32844 0.0967187 4.03784 0.195428 3.83248C0.300203 3.61451 0.438905 3.40425 0.582966 3.21279C0.871405 2.82947 1.2478 2.43391 1.62014 2.0764C2.36493 1.36129 3.19883 0.699729 3.56345 0.444812L3.56438 0.443876ZM4.92357 1.91633C4.87694 1.9165 4.78457 1.92912 4.66053 2.01555L4.66147 2.01649C4.38346 2.21092 3.62673 2.80866 2.94844 3.45992C2.60909 3.78575 2.31527 4.10032 2.11533 4.36604C2.01534 4.49893 1.95471 4.59777 1.92343 4.66278C1.92253 4.66466 1.92145 4.66664 1.92062 4.66839C1.93395 5.07267 1.98824 6.0497 2.75186 7.67601C3.57736 9.43401 5.24409 11.98 8.63045 15.3668C12.0175 18.7548 14.5645 20.4217 16.3222 21.2473C17.943 22.0084 18.9181 22.0648 19.3252 22.0785C19.3275 22.0774 19.3301 22.0769 19.3327 22.0757C19.3977 22.0444 19.4973 21.9849 19.6303 21.8847C19.896 21.6846 20.2107 21.3901 20.5365 21.0507C21.1876 20.3721 21.7851 19.6152 21.979 19.3377L21.9799 19.3358C22.0636 19.2164 22.0814 19.1271 22.0838 19.0793C21.4267 18.6336 18.1985 16.487 17.6018 16.1101C17.5942 16.1104 17.5848 16.1107 17.5738 16.1119C17.4896 16.1213 17.3576 16.1579 17.2003 16.2411L17.1975 16.2421C16.9702 16.3615 16.3985 16.6826 15.8664 16.9844C15.6041 17.1331 15.3573 17.2748 15.1755 17.3785C15.0848 17.4302 15.01 17.4725 14.9584 17.502C14.9326 17.5167 14.9121 17.5279 14.8984 17.5357C14.8916 17.5396 14.886 17.5431 14.8825 17.5451C14.8809 17.546 14.8796 17.5465 14.8788 17.5469L14.8779 17.5479L14.3948 17.824L13.9156 17.5423L13.9137 17.5413C13.9129 17.5409 13.9118 17.54 13.9109 17.5395C13.909 17.5383 13.9068 17.5372 13.9043 17.5357C13.8993 17.5327 13.893 17.529 13.8856 17.5245C13.8706 17.5153 13.8504 17.5028 13.8257 17.487C13.7759 17.4552 13.706 17.409 13.617 17.3476C13.4388 17.2247 13.1823 17.0383 12.8475 16.7728C12.1776 16.2417 11.1936 15.393 9.89884 14.0984C8.60413 12.804 7.75535 11.8198 7.22446 11.1498C6.95913 10.8149 6.77252 10.5586 6.6497 10.3803C6.58829 10.2912 6.54296 10.2215 6.51116 10.1716C6.49534 10.1468 6.48295 10.1268 6.47372 10.1117C6.4691 10.1041 6.46554 10.0971 6.46249 10.092C6.461 10.0896 6.45893 10.0874 6.45781 10.0855C6.45731 10.0846 6.45729 10.0834 6.45687 10.0827L6.455 10.0808L6.17417 9.60152L6.45032 9.11944L6.45125 9.11851C6.45173 9.11767 6.45222 9.11634 6.45313 9.11476C6.45513 9.11127 6.45858 9.10569 6.46249 9.09885C6.47031 9.08518 6.4815 9.06462 6.49619 9.03894C6.52572 8.98728 6.56895 8.9134 6.62069 8.82271C6.72439 8.64092 6.86508 8.39333 7.01384 8.13094C7.31581 7.59831 7.63728 7.02646 7.75615 6.79984L7.75709 6.79797C7.84166 6.63791 7.87844 6.50391 7.88814 6.4198C7.88905 6.41184 7.88866 6.40433 7.88907 6.39827C7.48757 5.775 5.36314 2.55706 4.92357 1.91633ZM14.8872 15.8901L14.891 15.892L14.89 15.891L14.8872 15.8901Z"
                                                    fill="white" stroke="#6180A1" stroke-width="0.4" stroke-miterlimit="10" />
                                            </svg>
                                        </a>
                                    </div>
                                    <div class="header__mobile-search">
                                        <button class="header__mobile-btn">
                                            <svg width="26" height="26" viewBox="0 0 26 26" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M18.7617 15.7344C19.7383 14.1979 20.3112 12.375 20.3112 10.4154C20.3112 4.9401 15.8776 0.5 10.4089 0.5C4.93359 0.5 0.5 4.9401 0.5 10.4154C0.5 15.8906 4.93359 20.3307 10.4023 20.3307C12.388 20.3307 14.237 19.7448 15.7865 18.7422L16.2357 18.4297L23.306 25.5L25.5 23.2669L18.4362 16.1966L18.7617 15.7344ZM15.9557 4.875C17.4336 6.35286 18.2474 8.31901 18.2474 10.4089C18.2474 12.4987 17.4336 14.4648 15.9557 15.9427C14.4779 17.4206 12.5117 18.2344 10.4219 18.2344C8.33203 18.2344 6.36588 17.4206 4.88802 15.9427C3.41016 14.4648 2.59635 12.4987 2.59635 10.4089C2.59635 8.31901 3.41016 6.35286 4.88802 4.875C6.36588 3.39714 8.33203 2.58333 10.4219 2.58333C12.5117 2.58333 14.4779 3.39714 15.9557 4.875Z"
                                                    fill="white" />
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="burger">
                                        <div class="burger__line burger__line-top"></div>
                                        <div class="burger__line burger__line-middle"></div>
                                        <div class="burger__line burger__line-bottom"></div>
                                    </div>
                                </div>
                                <nav class="header__nav">
                                    <ul class="header__nav-list">
                                        <li class="header__nav-item">
                                            <a href="<?php echo esc_url(get_nav_url('uslugi-i-ceny', $city_base_url_nav, $city_specific_pages)); ?>">
                                                Услуги и цены
                                            </a>
                                        </li>
                                        <li class="header__nav-item">
                                            <a href="<?php echo esc_url(get_nav_url('specialisty', $city_base_url_nav, $city_specific_pages)); ?>">
                                                Наши врачи
                                            </a>
                                        </li>
                                        <li class="header__nav-item">
                                            <a href="<?php echo esc_url(get_nav_url('vopros-otvet', $city_base_url_nav, $city_specific_pages)); ?>">
                                                Что нужно знать?
                                            </a>
                                        </li>
                                        <li class="header__nav-item">
                                            <a href="<?php echo esc_url(get_nav_url('about', $city_base_url_nav, $city_specific_pages)); ?>">
                                                О компании
                                            </a>
                                        </li>
                                        <li class="header__nav-item">
                                            <a href="<?php echo esc_url(get_nav_url('kontakty', $city_base_url_nav, $city_specific_pages)); ?>">
                                                Контакты
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                                <div class="header__search">
                                    <button class="header__search-btn">
                                        <svg width="26" height="26" viewBox="0 0 26 26" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M18.7617 15.7344C19.7383 14.1979 20.3112 12.375 20.3112 10.4154C20.3112 4.9401 15.8776 0.5 10.4089 0.5C4.93359 0.5 0.5 4.9401 0.5 10.4154C0.5 15.8906 4.93359 20.3307 10.4023 20.3307C12.388 20.3307 14.237 19.7448 15.7865 18.7422L16.2357 18.4297L23.306 25.5L25.5 23.2669L18.4362 16.1966L18.7617 15.7344ZM15.9557 4.875C17.4336 6.35286 18.2474 8.31901 18.2474 10.4089C18.2474 12.4987 17.4336 14.4648 15.9557 15.9427C14.4779 17.4206 12.5117 18.2344 10.4219 18.2344C8.33203 18.2344 6.36588 17.4206 4.88802 15.9427C3.41016 14.4648 2.59635 12.4987 2.59635 10.4089C2.59635 8.31901 3.41016 6.35286 4.88802 4.875C6.36588 3.39714 8.33203 2.58333 10.4219 2.58333C12.5117 2.58333 14.4779 3.39714 15.9557 4.875Z"
                                                fill="white" />
                                        </svg>
                                    </button>
                                </div>
    
                            </div>
                        </div>
                    </div>

                    <div class="header__bottom">
                        <div class="container">
                            <div class="header__city">
                                <button class="header__city-choice">
                                    <p>Выбрать город</p>
                                    <img src="<?php bloginfo('template_url')?>/assets/img/choice_city.svg" alt="Выбрать город">
                                </button>
                                <p class="header__city-selected" style="font-weight:bold"></p>
                            </div>
    
                            <?php
                                // выбранный город из куки
                                $selected_city = $selected_city_slug_nav;
    
                                // Запрос поста для выбранного города с рубрикой "Контакты"
                                $args = array(
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
                                $contacts_query = new WP_Query($args);
                            ?>
    
                            <div class="header__info">
                                <?php
                                // подготовим переменные для whatsapp
                                $whatsapp_digits = '';
                                if ($contacts_query->have_posts()) :
                                    while ($contacts_query->have_posts()) : $contacts_query->the_post();
    
                                        $addresses_group = get_field('contacts_addresses') ?: [];
                                        $phones_group = get_field('contacts_phones') ?: [];
                                        $opening_hours_group = get_field('contacts_opening_hours') ?: [];
    
                                        $whatsapp_field = get_field('contacts_whatsapp');
                                        if (!empty($whatsapp_field)) {
                                            $whatsapp_digits = preg_replace('/\D+/', '', $whatsapp_field);
                                        }
                                ?>
    
                                        <!-- Адреса -->
                                        <ul class="header__info-item">
                                            <?php
                                            $address_index = 1;
                                            while ($address_index <= 4) {
                                                $field_key = 'contacts_address_' . $address_index;
                                                if (!empty($addresses_group[$field_key])) {
                                                    echo '<li>' . esc_html($addresses_group[$field_key]) . '</li>';
                                                }
                                                $address_index++;
                                            }
                                            ?>
                                        </ul>
    
                                        <!-- Телефоны -->
                                        <ul class="header__info-item">
                                            <?php
                                            $phone_index = 1;
                                            while ($phone_index <= 4) {
                                                $field_key = 'contacts_phone_' . $phone_index;
                                                if (!empty($phones_group[$field_key])) {
                                                    $raw = $phones_group[$field_key];
                                                    // Оставляем плюс и цифры для tel:
                                                    $tel_clean = preg_replace('/[^\d\+]/', '', $raw);
                                                    echo '<li><a href="tel:' . esc_attr($tel_clean) . '">' . esc_html($raw) . '</a></li>';
                                                }
                                                $phone_index++;
                                            }
                                            ?>
                                        </ul>
    
                                        <!-- Часы работы -->
                                        <div class="header__schedule header__info-item">
                                            <?php if (!empty($opening_hours_group['contacts_opening_days'])) : ?>
                                                <p class="header__schedule-time"><?php echo esc_html($opening_hours_group['contacts_opening_days']); ?></p>
                                            <?php endif; ?>
    
                                            <?php
                                            $hours_index = 1;
                                            $hours_found = false;
                                            while ($hours_index <= 3) {
                                                $field_key = 'contacts_opening_hours_' . $hours_index;
                                                if (!empty($opening_hours_group[$field_key])) {
                                                    echo '<p class="header__schedule-time">' . esc_html($opening_hours_group[$field_key]) . '</p>';
                                                    $hours_found = true;
                                                }
                                                $hours_index++;
                                            }
    
                                            if (!$hours_found) {
                                                echo '<p class="header__schedule-time">Время работы не указано</p>';
                                            }
                                            ?>
                                            <p class="header__schedule-closing">до закрытия 40 мин.</p>
                                        </div>
    
                                    <?php endwhile; ?>
                                    <?php wp_reset_postdata(); ?>
                                <?php else : ?>
                                    <!-- Значения по умолчанию, если контакты не найдены -->
                                    <ul class="header__info-item">
                                        <li>Адреса не найдены</li>
                                    </ul>
                                    <ul class="header__info-item">
                                        <li><a href="#">Контакты не найдены</a></li>
                                    </ul>
                                    <div class="header__schedule header__info-item">
                                        <p class="header__schedule-time">График не указан</p>
                                        <p class="header__schedule-time">Время работы не указано</p>
                                    </div>
                                <?php endif; ?>
                            </div> <!-- header__info -->
    
                            <?php
                                $whatsapp_link = '#';
                                $whatsapp_attrs = '';
                                if (!empty($whatsapp_digits)) {
                                    $whatsapp_link = 'https://wa.me/' . $whatsapp_digits;
                                    $whatsapp_attrs = ' target="_blank" rel="noopener noreferrer"';
                                } else {
                                    $whatsapp_attrs = ' aria-disabled="true"';
                                }
                            ?>
                            <div class="header__whatsapp">
                                <a href="<?php echo esc_url($whatsapp_link); ?>" class="header__whatsapp-btn"<?php echo $whatsapp_attrs; ?>>
                                    <p>Записаться whatsapp</p>
                                    <img src="<?php bloginfo('template_url')?>/assets/img/arrow_whatsApp.svg" alt="">
                                </a>
                            </div>
                        </div>
                    </div> <!-- .header__bottom -->

                    <div class="container bottom">
                        <div class="header__burger">
                            <div class="header__burger-logo">
                                <a href="<?php echo site_url('') ?>/" class="header__logo">
                                    <img src="<?php bloginfo('template_url')?>/assets/img/logo.svg" alt="Логотип">
                                </a>
                            </div>
                            <nav class="header__burger-nav">
                                <ul class="header__burger-list">
                                    <li class="header__burger-item">
                                        <a href="<?php echo esc_url(get_nav_url('uslugi-i-ceny', $city_base_url_nav, $city_specific_pages)); ?>">Услуги и цены</a>
                                    </li>
                                    <li class="header__burger-item">
                                        <a href="<?php echo esc_url(get_nav_url('specialisty', $city_base_url_nav, $city_specific_pages)); ?>">Наши врачи</a>
                                    </li>
                                    <li class="header__burger-item">
                                        <a href="<?php echo esc_url(get_nav_url('vopros-otvet', $city_base_url_nav, $city_specific_pages)); ?>">Что нужно знать?</a>
                                    </li>
                                    <li class="header__burger-item">
                                        <a href="<?php echo esc_url(get_nav_url('about', $city_base_url_nav, $city_specific_pages)); ?>">О компании</a>
                                    </li>
                                    <li class="header__burger-item">
                                        <a href="<?php echo esc_url(get_nav_url('kontakty', $city_base_url_nav, $city_specific_pages)); ?>">Контакты</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
    
                        <div class="modal-city">
                            <button class="modal-city__close">
                                <div class="modal-city__close-line first"></div>
                                <div class="modal-city__close-line second"></div>
                            </button>
                            <div class="modal-city__content">
                                <div class="modal-city__item">
                                    <p class="modal-city__item-country">Казахстан</p>
                                    <ul>
                                        <li>
                                            <p class="abc">А</p>
                                        </li>
                                        <li><a href="#" data-city="almaty">Алматы</a></li>
                                        <li><a href="#" data-city="astana">Астана</a></li>
                                        <li class="abc-container">
                                            <p class="abc">К</p>
                                        </li>
                                        <li><a href="#" data-city="karaganda">Караганда</a></li>
                                        <li class="abc-container">
                                            <p class="abc">Т</p>
                                        </li>
                                        <li><a href="#" data-city="taldykorgan">Талдыкорган</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="city-chosen" id="cityChosenBanner">
                            <h2 class="city-chosen__title">
                                Ваш город — <span id="cityChosenName">
                                    <?php
                                    // Получаем выбранный город из cookie или используем значение по умолчанию
                                    $selected_city_slug_chosen = $selected_city_slug_nav;
                                    
                                    // Массив соответствий slug => Название на русском (такой же, как в JS)
                                    $city_map_chosen = array(
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
                                        'naberezhnye_chelny' => 'Набережные Челны',
                                        'nahodka' => 'Находка',
                                        'nizhnekamsk' => 'Нижнекамск',
                                        'nizhnij_novgorod' => 'Нижний Новгород',
                                        'novosibirsk' => 'Новосибирск',
                                        'petropavlovsk_kamchatskij' => 'Петропавловск-Камчатский',
                                        'rostov' => 'Ростов-на-Дону',
                                        'samara' => 'Самара',
                                        'serov' => 'Серов',
                                        'taldykorgan' => 'Талдыкорган',
                                        'tomsk' => 'Томск',
                                        'tumen' => 'Тюмень',
                                        'ussurijsk' => 'Уссурийск',
                                        'khabarovsk' => 'Хабаровск'
                                    );
                                    
                                    // Отображаем название города
                                    echo isset($city_map_chosen[$selected_city_slug_chosen]) ? esc_html($city_map_chosen[$selected_city_slug_chosen]) : 'Хабаровск';
                                    ?>
                                </span>
                            </h2>
                            <p class="city-chosen__text">Покажем услуги и акции для вашего города.</p>
                            <div class="city-chosen__buttons">
                                <button class="city-chosen__buttons-btn city-chosen__true" id="cityChosenYes">Да, верно</button>
                                <button class="city-chosen__buttons-btn city-chosen__false" id="cityChosenNo">Нет, другой</button>
                            </div>
                        </div>
    
                        <div class="mobile-nav">
                            <ul class="mobile-nav__list">
                                <li class="mobile-nav__list-item">
                                    <a class="mobile-nav__list-link" href="<?php echo esc_url(get_nav_url('uslugi-i-ceny', $city_base_url_nav, $city_specific_pages)); ?>">
                                        <svg width="24" height="26" viewBox="0 0 24 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M12.0578 1.00014C14.6349 1.01281 17.1256 1.87114 19.095 3.42495C21.0643 4.97876 22.388 7.12951 22.8342 9.5021C23.2804 11.8747 22.8208 14.3189 21.5364 16.4074C20.3691 18.3053 18.5863 19.7973 16.4563 20.6798L16.7239 22.0119C17.0341 23.5591 15.8508 25.0031 14.2727 25.0031H9.72679C8.14892 25.0028 6.96546 23.559 7.27562 22.0119L7.54222 20.6798C5.37591 19.782 3.57104 18.254 2.40648 16.3136C1.14558 14.2126 0.713915 11.7638 1.18675 9.39565C1.65962 7.02761 3.00765 4.88996 4.99437 3.35561C6.98107 1.82138 9.48088 0.9875 12.0578 1.00014ZM11.2815 13.2521C10.567 13.2523 9.9513 13.7566 9.81077 14.4572L8.25608 22.2081C8.06995 23.1364 8.78014 24.0028 9.72679 24.0031H14.2727C15.2196 24.0031 15.9296 23.1366 15.7434 22.2081L14.1887 14.4572C14.0481 13.7565 13.4327 13.2521 12.718 13.2521H11.2815ZM12.053 2.00014C9.69236 1.98856 7.41007 2.75236 5.60472 4.14663C3.80074 5.53988 2.59081 7.47047 2.16722 9.59194C1.74397 11.7122 2.12897 13.9079 3.2639 15.799C4.28099 17.4936 5.84787 18.8508 7.74339 19.6789L8.14378 17.6808C6.78364 16.9645 5.6781 15.8825 4.97483 14.5695C4.17431 13.0745 3.94369 11.3688 4.32054 9.73452C4.69746 8.10016 5.65965 6.63516 7.04808 5.5812C8.43645 4.52739 10.1679 3.94711 11.9563 3.93764C13.7448 3.92834 15.484 4.49017 16.885 5.52944C18.2858 6.56878 19.2659 8.02424 19.6623 9.65444C20.0587 11.2846 19.8482 12.993 19.0657 14.4962C18.3651 15.8417 17.2419 16.9487 15.8547 17.6789L16.2561 19.6808C18.1189 18.8672 19.6656 17.54 20.6848 15.883C21.8411 14.0027 22.2502 11.8107 21.8508 9.68667C21.4511 7.56163 20.2634 5.62057 18.4758 4.2101C16.6866 2.79849 14.4136 2.01175 12.053 2.00014ZM11.9602 4.55874C10.3231 4.56737 8.73795 5.09896 7.46702 6.06362C6.19613 7.02839 5.31498 8.36935 4.96995 9.86538C4.62496 11.3615 4.8366 12.9235 5.56956 14.2921C6.17911 15.43 7.11968 16.3763 8.27464 17.0304L8.8303 14.2609C9.06454 13.0931 10.0905 12.2523 11.2815 12.2521H12.718C13.9091 12.2521 14.9349 13.093 15.1692 14.2609L15.7248 17.0314C16.9044 16.3637 17.8601 15.3915 18.468 14.2238C19.1842 12.8478 19.3767 11.2843 19.0139 9.79213C18.651 8.29985 17.7542 6.96716 16.4719 6.01577C15.1895 5.06444 13.5973 4.55023 11.9602 4.55874Z" fill="white" stroke="white" stroke-width="0.2"/>
                                        </svg>
                                        <p>Услуги</p>
                                    </a>
                                </li>
    
                                <li class="mobile-nav__list-item">
                                    <a class="mobile-nav__list-link" href="<?php echo esc_url(get_nav_url('specialisty', $city_base_url_nav, $city_specific_pages)); ?>">
                                        <svg width="20" height="22" viewBox="0 0 20 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M9.16699 12.6211H10.833C13.2518 12.6211 15.5717 13.5278 17.2812 15.1396C18.9903 16.7512 19.9501 18.9362 19.9502 21.2139C19.9502 21.408 19.8682 21.5952 19.7217 21.7334C19.575 21.8717 19.3755 21.9501 19.167 21.9502H0.833008C0.624454 21.9501 0.424952 21.8717 0.27832 21.7334C0.13176 21.5952 0.0498047 21.408 0.0498047 21.2139C0.0499225 18.9362 1.0097 16.7512 2.71875 15.1396C4.42826 13.5278 6.74818 12.6211 9.16699 12.6211ZM9.16699 14.0928C7.31137 14.0936 5.52027 14.739 4.13672 15.9062C2.75308 17.0737 1.87345 18.6817 1.66699 20.4229L1.66016 20.4785H18.3398L18.333 20.4229C18.1265 18.6817 17.2469 17.0737 15.8633 15.9062C14.4797 14.739 12.6886 14.0936 10.833 14.0928H9.16699ZM7.78613 0.464844C8.84317 0.0521044 10.0066 -0.0552077 11.1289 0.155273C12.2513 0.365798 13.2822 0.885064 14.0908 1.64746C14.8993 2.40983 15.449 3.38099 15.6719 4.4375C15.8948 5.49408 15.7811 6.58949 15.3438 7.58496C14.9064 8.58042 14.1649 9.43208 13.2139 10.0312C12.2628 10.6303 11.1443 10.9502 10 10.9502C8.46525 10.9502 6.99357 10.375 5.90918 9.35254C4.82505 8.33023 4.2168 6.94433 4.2168 5.5C4.2168 4.42289 4.55546 3.36976 5.19043 2.47363C5.82557 1.5774 6.72902 0.877693 7.78613 0.464844ZM10 1.52148C8.88265 1.52148 7.81049 1.93985 7.01953 2.68555C6.22846 3.43142 5.7832 4.44384 5.7832 5.5C5.7832 6.28757 6.03141 7.05759 6.49512 7.71191C6.95878 8.36599 7.61772 8.87507 8.3877 9.17578C9.15776 9.47647 10.005 9.55563 10.8223 9.40234C11.6395 9.24904 12.3906 8.87052 12.9805 8.31445C13.5704 7.75825 13.9728 7.04871 14.1357 6.27637C14.2986 5.50418 14.2149 4.70375 13.8955 3.97656C13.576 3.24935 13.035 2.62821 12.3418 2.19141C11.6485 1.75461 10.8333 1.52148 10 1.52148Z" fill="white" stroke="#6180A1" stroke-width="0.1"/>
                                        </svg>
                                        <p>Врачи</p>
                                    </a>
                                </li>
    
                                <li class="mobile-nav__list-item">
                                    <a class="mobile-nav__list-link green" href="<?php echo esc_url( $whatsapp_href ); ?>"
                                    <?php if ( $whatsapp_digits ): ?> target="_blank" rel="noopener noreferrer"<?php else: ?> aria-disabled="true"<?php endif; ?>>
                                        <svg width="25" height="24" viewBox="0 0 25 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path d="M3.71582 0.801758C5.05789 -0.487253 7.34183 -0.465528 8.66211 0.855469L11.0449 3.24121C12.4234 4.62144 12.4234 6.86684 11.0449 8.24707L9.67188 9.62207L12.4961 12.4512L15.3564 15.3164L16.7314 13.9414C17.3981 13.273 18.2855 12.9043 19.2305 12.9043C20.1754 12.9043 21.0639 13.273 21.7314 13.9404L24.1143 16.3281C24.7818 16.9957 25.1504 17.885 25.1504 18.8301C25.1504 19.745 24.8039 20.6074 24.1758 21.2676C23.844 21.7149 22.1288 23.8089 18.9229 24.1123C18.6631 24.1377 18.3994 24.1504 18.1357 24.1504C14.7207 24.1503 10.9693 22.1047 6.96875 18.1172C6.95915 18.1083 6.94878 18.1002 6.93945 18.0908C4.82614 15.9748 3.23939 13.904 2.22656 11.9355C1.97197 11.4407 2.16576 10.8332 2.66016 10.5781C3.15654 10.3231 3.76192 10.5188 4.0166 11.0127C4.92365 12.7759 6.37105 14.6645 8.3252 16.626L9.0498 17.3311C12.6548 20.7394 15.9828 22.3616 18.7344 22.1055C20.0247 21.9833 20.9818 21.4728 21.6182 20.9902C22.2429 20.5165 22.5561 20.0717 22.5674 20.0557L22.5693 20.0537C22.6045 20.0014 22.6448 19.9509 22.6914 19.9053C22.9778 19.6187 23.1357 19.2362 23.1357 18.8301C23.1357 18.4237 22.9775 18.0416 22.6904 17.7539V17.7529L20.3076 15.3672C20.0205 15.0797 19.6383 14.9219 19.2305 14.9219C18.8236 14.9219 18.4425 15.0788 18.1562 15.3662V15.3672L16.0693 17.4551C15.6757 17.849 15.0392 17.8489 14.6455 17.4551L11.0713 13.877C11.0679 13.8735 11.0649 13.8697 11.0615 13.8662L7.53516 10.3359C7.3459 10.1464 7.24024 9.89018 7.24023 9.62305C7.24023 9.35623 7.34558 9.09906 7.53516 8.91016L9.62109 6.82227C10.214 6.22858 10.214 5.26166 9.62109 4.66797L7.23828 2.28223C6.66505 1.70926 5.65989 1.7086 5.08691 2.28223C5.04211 2.32708 4.9923 2.36607 4.94141 2.40137C4.85712 2.46008 2.52397 4.12832 2.90723 7.58203C2.96856 8.13526 2.57065 8.63281 2.01758 8.69434L2.01855 8.69531C1.46914 8.75902 0.96766 8.35789 0.90625 7.80469C0.427349 3.49605 3.16509 1.2138 3.71582 0.801758Z"
                                                fill="#8CFF9D" stroke="#8CFF9D" stroke-width="0.3" />
                                        </svg>
                                        <p>Запись</p>
                                    </a>
                                </li>
                                <li class="mobile-nav__list-item">
                                    <a class="mobile-nav__list-link" href="<?php echo esc_url(get_nav_url('kontakty', $city_base_url_nav, $city_specific_pages)); ?>">
                                        <svg width="24" height="20" viewBox="0 0 24 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M23.0771 6.90039C23.3509 6.90046 23.6117 7.01788 23.8027 7.22461C23.9936 7.4314 24.0996 7.71073 24.0996 8V16C24.0989 16.8194 23.798 17.6062 23.2607 18.1875C22.7239 18.7697 21.9938 19.0989 21.2305 19.0996H2.76953C2.00616 19.0989 1.27609 18.7697 0.739258 18.1875C0.202001 17.6062 -0.0989435 16.8194 -0.0996094 16V8C-0.0996094 7.71073 0.00638158 7.4314 0.197266 7.22461C0.388309 7.01788 0.649089 6.90046 0.922852 6.90039C1.19681 6.90039 1.4583 7.01766 1.64941 7.22461C1.8403 7.4314 1.94629 7.71073 1.94629 8V16C1.94629 16.2412 2.03509 16.4714 2.19043 16.6396C2.34548 16.8074 2.55412 16.9004 2.76953 16.9004H21.2305C21.4459 16.9004 21.6545 16.8074 21.8096 16.6396C21.9649 16.4714 22.0537 16.2412 22.0537 16V8C22.0537 7.71073 22.1597 7.4314 22.3506 7.22461C22.5417 7.01766 22.8032 6.90039 23.0771 6.90039ZM21.2305 0.900391C21.9938 0.901119 22.7239 1.23026 23.2607 1.8125C23.4913 2.06235 23.6815 2.35174 23.8232 2.66895L23.8242 2.66992C23.9247 2.89778 23.9486 3.15569 23.8936 3.40039C23.8453 3.61463 23.739 3.8089 23.5869 3.95703L23.5186 4.01758L12.6172 12.8779C12.4402 13.0218 12.2234 13.0996 12 13.0996C11.7766 13.0996 11.5598 13.0218 11.3828 12.8779L0.481445 4.01758C0.293224 3.86382 0.161581 3.64529 0.106445 3.40039C0.0513533 3.15569 0.0752697 2.89778 0.175781 2.66992L0.176758 2.66895C0.318531 2.35174 0.508745 2.06235 0.739258 1.8125C1.27609 1.23026 2.00616 0.901119 2.76953 0.900391H21.2305ZM12 10.6201L21.209 3.09961H2.79102L12 10.6201Z" fill="#FFFFFF" stroke="#FFFFFF" stroke-width="0.2"/>
                                        </svg>
                                        <p>Контакты</p>
                                    </a>
                                </li>
                                <li class="mobile-nav__list-item">
                                    <a class="mobile-nav__list-link" href="#">
                                        <svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M8.71973 0.0996094C13.4769 0.0996094 17.334 3.96246 17.334 8.72559C17.334 10.4286 16.8364 12.0127 15.9883 13.3486L15.7021 13.7559L15.6533 13.8242L15.7129 13.8838L21.8594 20.0352L20.0674 21.8574L13.918 15.707L13.8594 15.6484L13.79 15.6963L13.3975 15.9688C12.0495 16.8409 10.4413 17.3506 8.71387 17.3506C3.95684 17.3505 0.0996449 13.4886 0.0996094 8.72559C0.0996094 3.96252 3.95684 0.0996444 8.71973 0.0996094ZM8.73145 1.7334C6.86594 1.7334 5.11029 2.46009 3.79102 3.7793C2.47174 5.09857 1.74513 6.85419 1.74512 8.71973C1.74512 10.5853 2.47173 12.3409 3.79102 13.6602C5.1103 14.9794 6.86591 15.7061 8.73145 15.7061C10.5969 15.706 12.3526 14.9794 13.6719 13.6602C14.9911 12.3409 15.7178 10.5852 15.7178 8.71973C15.7178 6.85422 14.9911 5.09856 13.6719 3.7793C12.3526 2.46006 10.5969 1.73345 8.73145 1.7334Z" fill="white" stroke="#6180A1" stroke-width="0.2"/>
                                        </svg>
                                        <p>Поиск</p>
                                    </a>
                                </li>
                            </ul>
                        </div>
    
                        <div class="cookie" id="cookieBanner">
                            <div class="cookie__head">
                                <h3 class="cookie__head-title">Мы используем Cookie<span class="cookie__head-text"> для работы сайта, аналитики, персонализации. </span></h3>
                                <a href="<?php echo esc_url(get_nav_url('privacy', $city_base_url_nav, $city_specific_pages)); ?>" class="cookie__head-link">Политика конфиденциальности</a>
                            </div>
                            <div class="cookie__buttons">
                                <button class="cookie__btn blue" id="acceptCookies">Принять</button>
                                <button class="cookie__btn white" id="declineCookies">Отклонить</button>
                                <button class="cookie__btn grey" id="customizeCookies">Настроить</button>
                            </div>
                        </div>
    
                        <button class="to-top">
                            <svg width="16" height="9" viewBox="0 0 16 9" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M15.03 0.5L16 1.3625L8 8.5L0 1.3625L0.965 0.5L8 6.77083L15.03 0.5Z" fill="white" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </header>