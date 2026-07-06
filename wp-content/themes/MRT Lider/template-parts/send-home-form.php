<?php
// Запрет прямого доступа
if (!defined('ABSPATH')) {
    exit;
}

// Проверка nonce
if (!wp_verify_nonce($_POST['nonce'], 'main_form_nonce')) {
    wp_die('Ошибка безопасности');
}

// --- ЛОГИКА ОПРЕДЕЛЕНИЯ ГОРОДА (аналогично header.php) ---

$known_city_slugs_main_form_php = array(
    'almaty', 'angarsk', 'astana', 'achinsk', 'blagoveshhensk', 'bratsk',
    'vladivostok', 'volgodonsk', 'irkutsk', 'karaganda', 'kemerovo', 'kirov',
    'komsomolsk', 'krasnoyarsk', 'kurgan', 'magadan', 'murmansk', 'naberezhnye_chelny',
    'nahodka', 'nizhnekamsk', 'nizhnij_novgorod', 'nizhnij_tagil', 'novosibirsk',
    'petropavlovsk_kamchatskij', 'rostov', 'samara', 'serov', 'taldykorgan', 'tomsk',
    'tumen', 'ussurijsk', 'khabarovsk'
);

if (!function_exists('get_city_slug_from_request_path_main_form')) {
    function get_city_slug_from_request_path_main_form($known_slugs) {
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

$city_slug_from_url_main_form = get_city_slug_from_request_path_main_form($known_city_slugs_main_form_php);

if ($city_slug_from_url_main_form !== false) {
    $selected_city_main_form = $city_slug_from_url_main_form;
    if (!headers_sent()) {
        if (!isset($_COOKIE['selected_city']) || $_COOKIE['selected_city'] !== $selected_city_main_form) {
            setcookie('selected_city', $selected_city_main_form, time() + 30 * DAY_IN_SECONDS, '/', $_SERVER['HTTP_HOST'], is_ssl(), true);
        }
    }
} else {
    $selected_city_cookie_value_main_form = isset($_COOKIE['selected_city']) ? sanitize_text_field($_COOKIE['selected_city']) : 'tumen';
    if (in_array($selected_city_cookie_value_main_form, $known_city_slugs_main_form_php, true)) {
         $selected_city_main_form = $selected_city_cookie_value_main_form;
    } else {
         $selected_city_main_form = 'tumen';
         if (!headers_sent()) {
            setcookie('selected_city', $selected_city_main_form, time() + 30 * DAY_IN_SECONDS, '/', $_SERVER['HTTP_HOST'], is_ssl(), true);
         }
    }
}
// --- КОНЕЦ ЛОГИКИ ОПРЕДЕЛЕНИЯ ГОРОДА ---

// Получаем данные из формы
$name = sanitize_text_field($_POST['name']);
$phone = sanitize_text_field($_POST['phone']);

// --- Получаем email города ---
// Используем переопределенный $selected_city_main_form вместо оригинального $selected_city
$args = array(
    'post_type'      => 'post',
    'posts_per_page' => 1,
    'tax_query'      => array(
        'relation' => 'AND',
        array(
            'taxonomy' => 'category',
            'field' => 'slug',
            'terms' => $selected_city_main_form, // Используем переопределенный $selected_city_main_form
        ),
        array(
            'taxonomy' => 'category',
            'field' => 'slug',
            'terms' => 'contacty',
        )
    )
);
$contacts_query = new WP_Query($args);

$to = 'prooo100mix@yandex.ru'; // Email по умолчанию

$telegram_chat_ids = array(); // По умолчанию

if ($contacts_query->have_posts()) {
    $contacts_query->the_post();
    $emails_group = get_field('contacts_emails');
    if (!empty($emails_group) && !empty($emails_group['contacts_email_1'])) {
        $to = sanitize_email($emails_group['contacts_email_1']);
    }
	$telegram_group = get_field('telegram_chats');
		if (!empty($telegram_group)) {
			$chat_ids = array();
			if (!empty($telegram_group['telegram_chat_1'])) {
				$chat_ids[] = $telegram_group['telegram_chat_1'];
			}
			if (!empty($telegram_group['telegram_chat_2'])) {
				$chat_ids[] = $telegram_group['telegram_chat_2'];
			}
			if (!empty($chat_ids)) {
				$telegram_chat_ids = $chat_ids;
			}
		}
    wp_reset_postdata(); // Важно сбросить данные после запроса
}
// --- Конец получения email ---

// Тема письма
$email_subject = 'Заявка на запись';

// Тело письма
$body = "Письмо отправлено с главной страницы для города: " . esc_html($selected_city_main_form) . "\n\n"; // Используем переопределенный $selected_city_main_form
$body .= "Имя: $name\n";
$body .= "Телефон: $phone\n";

// Заголовки
$headers = array('Content-Type: text/plain; charset=UTF-8');

// Отправка письма
if (wp_mail($to, $email_subject, $body, $headers)) {
	send_to_telegram($body, $telegram_chat_ids);
    echo 'Сообщение успешно отправлено!';
} else {
    echo 'Ошибка при отправке сообщения.';
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