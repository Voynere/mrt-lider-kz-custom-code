<?php
// Запрет прямого доступа
if (!defined('ABSPATH')) {
    exit;
}

// Проверка nonce
if (!wp_verify_nonce($_POST['nonce'], 'booking_form_with_service_nonce')) {
    wp_die('Ошибка безопасности');
}

// --- ЛОГИКА ОПРЕДЕЛЕНИЯ ГОРОДА (аналогично header.php) ---

$known_city_slugs_booking_service_php = array(
    'almaty', 'angarsk', 'astana', 'achinsk', 'blagoveshhensk', 'bratsk',
    'vladivostok', 'volgodonsk', 'irkutsk', 'karaganda', 'kemerovo', 'kirov',
    'komsomolsk', 'krasnoyarsk', 'kurgan', 'magadan', 'murmansk', 'naberezhnye_chelny',
    'nahodka', 'nizhnekamsk', 'nizhnij_novgorod', 'nizhnij_tagil', 'novosibirsk',
    'petropavlovsk_kamchatskij', 'rostov', 'samara', 'serov', 'taldykorgan', 'almaty_aubakirova', 'tomsk',
    'tumen', 'ussurijsk', 'khabarovsk'
);

if (!function_exists('get_city_slug_from_request_path_booking_service')) {
    function get_city_slug_from_request_path_booking_service($known_slugs) {
        $request_uri = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '/';
        $path = explode('?', $request_uri)[0];
        $path_parts = array_filter(explode('/', trim($path, '/')));
        
        if (!empty($path_parts)) {
            $first_part = strtolower(reset($path_parts)); // Получаем первый элемент массива
            if (in_array($first_part, $known_slugs, true)) {
                return $first_part;
            }
        }
        return false; // Город в URL не найден
    }
}

$city_slug_from_url_booking_service = get_city_slug_from_request_path_booking_service($known_city_slugs_booking_service_php);

if ($city_slug_from_url_booking_service !== false) {
    $selected_city_booking_service = $city_slug_from_url_booking_service;
    if (!headers_sent()) {
        if (!isset($_COOKIE['selected_city']) || $_COOKIE['selected_city'] !== $selected_city_booking_service) {
            setcookie('selected_city', $selected_city_booking_service, time() + 30 * DAY_IN_SECONDS, '/', $_SERVER['HTTP_HOST'], is_ssl(), true);
        }
    }
} else {
    $selected_city_cookie_value_booking_service = isset($_COOKIE['selected_city']) ? sanitize_text_field($_COOKIE['selected_city']) : 'tumen';
    if (in_array($selected_city_cookie_value_booking_service, $known_city_slugs_booking_service_php, true)) {
         $selected_city_booking_service = $selected_city_cookie_value_booking_service;
    } else {
         $selected_city_booking_service = 'tumen';
         if (!headers_sent()) {
            setcookie('selected_city', $selected_city_booking_service, time() + 30 * DAY_IN_SECONDS, '/', $_SERVER['HTTP_HOST'], is_ssl(), true);
         }
    }
}
// --- КОНЕЦ ЛОГИКИ ОПРЕДЕЛЕНИЯ ГОРОДА ---

// Получаем данные из формы
$name = sanitize_text_field($_POST['name']);
$phone = sanitize_text_field($_POST['phone']);
$category = sanitize_text_field($_POST['category']);
$oblast = sanitize_text_field($_POST['oblast']);

// --- Получаем email города ---
$form_notifications = mrt_get_form_notification_settings($selected_city_booking_service);
$to = $form_notifications['email'];
$telegram_chat_ids = $form_notifications['telegram_chat_ids'];
// --- Конец получения email ---

// Тема письма
$email_subject = 'Новая запись из модального окна (с услугой)';

// Тело письма
$body = "Письмо отправлено с модального окна (страница услуг) для города: " . esc_html($selected_city_booking_service) . "\n\n"; // Используем переопределенный $selected_city_booking_service
$body .= "Имя: $name\n";
$body .= "Телефон: $phone\n";
$body .= "Категория услуги: $category\n";
$body .= "Область исследования: $oblast\n";

// Заголовки
$headers = array('Content-Type: text/plain; charset=UTF-8');

// Отправка письма
if (wp_mail($to, $email_subject, $body, $headers)) {
	send_to_telegram($body, $telegram_chat_ids);
    echo 'Заявка успешно отправлена!';
} else {
    echo 'Ошибка при отправке заявки.';
}

wp_die();


function send_to_telegram($text, $chat_ids) {
    $token = '7050065436:AAE2BP-hWWcJSo_ATtJOnSfVJ_YGDR4stT4';
    
    foreach ($chat_ids as $chat_id) {
        $msg = urlencode(strip_tags($text));
        $ch = curl_init('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$chat_id.'&text='.$msg);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
    }
}

?>