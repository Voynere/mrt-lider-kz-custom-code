<?php
/*
Template Name: site-map
*/
?>
<?php
$known_city_slugs_sitemap = array(
    'almaty', 'astana', 'karaganda', 'taldykorgan', 'almaty_aubakirova'
);

// Определяем город: URL > куки > fallback
$selected_city_slug_sitemap = 'almaty'; // fallback

$request_uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($request_uri, PHP_URL_PATH);
$path = trim($path, '/');
$path_parts = $path ? explode('/', $path) : array();
$url_city = !empty($path_parts[0]) ? sanitize_text_field($path_parts[0]) : '';

if ($url_city && in_array($url_city, $known_city_slugs_sitemap, true)) {
    $selected_city_slug_sitemap = $url_city;
} elseif (isset($_COOKIE['selected_city'])) {
    $cookie_city = sanitize_text_field($_COOKIE['selected_city']);
    if (in_array($cookie_city, $known_city_slugs_sitemap, true)) {
        $selected_city_slug_sitemap = $cookie_city;
    }
}

// Подготовка URL и навигации
$city_specific_pages_sitemap = array('uslugi-i-ceny', 'specialisty', 'vopros-otvet', 'about', 'kontakty', 'pravovaja-i-juridicheskaja-informacija', 'zajavka-na-spravku-dlja-nalogovogo-vycheta', 'otzyvy-klientov', 'vacancies');
$city_base_url_sitemap = home_url('/') . $selected_city_slug_sitemap . '/';

// Функция навигации (без setcookie!)
if (!function_exists('get_sitemap_nav_url')) {
    function get_sitemap_nav_url($page_slug, $city_base_url, $city_specific_pages) {
        if (in_array($page_slug, $city_specific_pages)) {
            return $city_base_url . $page_slug . '/';
        } else {
            return home_url('/') . $page_slug . '/';
        }
    }
}
?>

<?php get_header(); ?>

<main class="main">
    <div class="main-background">
        <?php 
            if (!is_front_page()) {
                custom_breadcrumbs();
            }
        ?>
        
        <section class="site-map">
            <div class="container">
                <div class="site-map__inner">
                    <a href="<?php echo esc_url($city_base_url_sitemap); ?>" class="site-map__item brown home">Главная страница</a>
                    <div class="site-map__main">
                        <div class="site-map__container">
                            <a href="<?php echo esc_url(get_sitemap_nav_url('uslugi-i-ceny', $city_base_url_sitemap, $city_specific_pages_sitemap)); ?>" class="site-map__item brown">Услуги и цены</a>
                            <div class="site-map__container-items">
                                <a href="<?php echo esc_url(get_sitemap_nav_url('uslugi-i-ceny', $city_base_url_sitemap, $city_specific_pages_sitemap)); ?>" class="site-map__item grey">Категории услуг</a>
                                <a href="<?php echo esc_url(get_sitemap_nav_url('uslugi-i-ceny', $city_base_url_sitemap, $city_specific_pages_sitemap)); ?>" class="site-map__item grey">Цены на услуги</a>
                            </div>
                        </div>
                        <div class="site-map__container">
                            <a href="<?php echo esc_url(get_sitemap_nav_url('specialisty', $city_base_url_sitemap, $city_specific_pages_sitemap)); ?>" class="site-map__item brown">Наши врачи</a>
                        </div>
                        <div class="site-map__container">
                            <a href="<?php echo esc_url(get_sitemap_nav_url('vopros-otvet', $city_base_url_sitemap, $city_specific_pages_sitemap)); ?>" class="site-map__item brown">Что нужно знать?</a>
                            <div class="site-map__container-items">
                                <a href="<?php echo esc_url(get_sitemap_nav_url('vopros-otvet', $city_base_url_sitemap, $city_specific_pages_sitemap)); ?>?category=mrt" class="site-map__item grey">МРТ</a>
                                <a href="<?php echo esc_url(get_sitemap_nav_url('vopros-otvet', $city_base_url_sitemap, $city_specific_pages_sitemap)); ?>?category=kt" class="site-map__item grey">КТ</a>
                                <a href="<?php echo esc_url(get_sitemap_nav_url('vopros-otvet', $city_base_url_sitemap, $city_specific_pages_sitemap)); ?>?category=uzi" class="site-map__item grey">УЗИ</a>
                                <a href="<?php echo esc_url(get_sitemap_nav_url('vopros-otvet', $city_base_url_sitemap, $city_specific_pages_sitemap)); ?>?category=densitometrija" class="site-map__item grey">Денситометрия</a>
                                <a href="<?php echo esc_url(get_sitemap_nav_url('vopros-otvet', $city_base_url_sitemap, $city_specific_pages_sitemap)); ?>?category=riski" class="site-map__item grey">Риски</a>
                                <a href="<?php echo esc_url(get_sitemap_nav_url('vopros-otvet', $city_base_url_sitemap, $city_specific_pages_sitemap)); ?>?category=obshhie-voprosy" class="site-map__item grey">Общие вопросы</a>
                            </div>
                        </div>
                        <div class="site-map__container">
                            <a href="<?php echo esc_url(get_sitemap_nav_url('kontakty', $city_base_url_sitemap, $city_specific_pages_sitemap)); ?>" class="site-map__item brown">Контакты</a>
                        </div>
                        <div class="site-map__container">
                            <a href="<?php echo esc_url(get_sitemap_nav_url('about', $city_base_url_sitemap, $city_specific_pages_sitemap)); ?>" class="site-map__item brown">О компании</a>
                            <div class="site-map__container-items">
                                <a href="<?php echo esc_url(get_sitemap_nav_url('about', $city_base_url_sitemap, $city_specific_pages_sitemap)); ?>" class="site-map__item grey">О медицинском центре</a>
                                <a href="<?php echo esc_url(get_sitemap_nav_url('pravovaja-i-juridicheskaja-informacija', $city_base_url_sitemap, $city_specific_pages_sitemap)); ?>" class="site-map__item grey">Правовая информация</a>
                                <a href="<?php echo esc_url(get_sitemap_nav_url('zajavka-na-spravku-dlja-nalogovogo-vycheta', $city_base_url_sitemap, $city_specific_pages_sitemap)); ?>" class="site-map__item grey">Заявка на налоговый вычет</a>
                                <a href="<?php echo esc_url(get_sitemap_nav_url('otzyvy-klientov', $city_base_url_sitemap, $city_specific_pages_sitemap)); ?>" class="site-map__item grey">Отзывы</a>
                                <a href="<?php echo esc_url(get_sitemap_nav_url('vacancies', $city_base_url_sitemap, $city_specific_pages_sitemap)); ?>" class="site-map__item grey">Вакансии</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="tour">
            <div class="container">
                <div class="tour__inner">
                    <img src="<?php bloginfo('template_url')?>/assets/img/3d_tour.jpg" alt="">
                    <a href="#" class="tour__content">
                        <h2 class="tour__title"><span>ПРОЙДИТЕ 3D ТУР</span> <span>ПО КЛИНИКЕ</span> <span>«МРТ
                                ЛИДЕР»</span></h2>
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