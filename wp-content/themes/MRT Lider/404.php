<?php 

// --- ЛОГИКА ОПРЕДЕЛЕНИЯ ГОРОДА (дублируем из header.php для независимости и корректности) ---

$known_city_slugs_404_php = array(
    'almaty', 'astana', 'karaganda', 'taldykorgan'
);

if (!function_exists('get_city_slug_from_request_path_404')) {
    function get_city_slug_from_request_path_404($known_slugs) {
        $request_uri = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '/';
        $path = explode('?', $request_uri)[0];
        $path_parts = array_filter(explode('/', trim($path, '/')));
        
        if (!empty($path_parts)) {
            $first_part = strtolower(reset($path_parts)); // Получаем первый элемент массива
            if (in_array($first_part, $known_slugs, true)) {
                return $first_part;
            }
        }
        return false;
    }
}

$city_slug_from_url_404 = get_city_slug_from_request_path_404($known_city_slugs_404_php);

if ($city_slug_from_url_404 !== false) {
    $selected_city_slug_404 = $city_slug_from_url_404;
    if (!headers_sent()) {
        if (!isset($_COOKIE['selected_city']) || $_COOKIE['selected_city'] !== $selected_city_slug_404) {
            setcookie('selected_city', $selected_city_slug_404, time() + 30 * DAY_IN_SECONDS, '/', $_SERVER['HTTP_HOST'], is_ssl(), true);
        }
    }
} else {
    $selected_city_cookie_value_404 = isset($_COOKIE['selected_city']) ? sanitize_text_field($_COOKIE['selected_city']) : 'almaty';
    if (in_array($selected_city_cookie_value_404, $known_city_slugs_404_php, true)) {
         $selected_city_slug_404 = $selected_city_cookie_value_404;
    } else {
         $selected_city_slug_404 = 'almaty';
         if (!headers_sent()) {
            setcookie('selected_city', $selected_city_slug_404, time() + 30 * DAY_IN_SECONDS, '/', $_SERVER['HTTP_HOST'], is_ssl(), true);
         }
    }
}
// --- КОНЕЦ ЛОГИКИ ОПРЕДЕЛЕНИЯ ГОРОДА ---

// Формируем URL домашней страницы выбранного города
$home_url_with_city = home_url('/') . $selected_city_slug_404 . '/';

get_header(); 
?>

<main class="main">
    <div class="main-background">

        <section class="error-page">
            <div class="container">
                <div class="error-page__inner">
                    <h1 class="error-page__title">Ошибка 404</h1>
                    <p class="error-page__text">Запрашиваемая страница не существует.</p>
                    <a href="<?php echo esc_url($home_url_with_city); ?>" class="error-page__link">
                        На главную
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="0.5" y="0.5" width="23" height="23" rx="11.5" stroke="white" />
                            <path d="M9.08108 8H16V14.9189M14.8108 9.18919L8 16" stroke="white" stroke-width="1.5"
                                stroke-linecap="round" />
                        </svg>
                    </a>
                </div>
            </div>
        </section>
        
    </div>
</main>

<?php get_footer(); ?>