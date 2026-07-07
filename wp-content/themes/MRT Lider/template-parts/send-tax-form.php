<?php
// Запрет прямого доступа
if (!defined('ABSPATH')) {
    exit;
}

// Проверка nonce
if (!wp_verify_nonce($_POST['nonce'], 'tax_form_nonce')) {
    wp_die('Ошибка безопасности');
}

// --- ЛОГИКА ОПРЕДЕЛЕНИЯ ГОРОДА (аналогично header.php) ---

$known_city_slugs_tax_form_php = array(
    'almaty', 'angarsk', 'astana', 'achinsk', 'blagoveshhensk', 'bratsk',
    'vladivostok', 'volgodonsk', 'irkutsk', 'karaganda', 'kemerovo', 'kirov',
    'komsomolsk', 'krasnoyarsk', 'kurgan', 'magadan', 'murmansk', 'naberezhnye_chelny',
    'nahodka', 'nizhnekamsk', 'nizhnij_novgorod', 'nizhnij_tagil', 'novosibirsk',
    'petropavlovsk_kamchatskij', 'rostov', 'samara', 'serov', 'taldykorgan', 'almaty_aubakirova', 'tomsk',
    'tumen', 'ussurijsk', 'khabarovsk'
);

if (!function_exists('get_city_slug_from_request_path_tax_form')) {
    function get_city_slug_from_request_path_tax_form($known_slugs) {
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

$city_slug_from_url_tax_form = get_city_slug_from_request_path_tax_form($known_city_slugs_tax_form_php);

if ($city_slug_from_url_tax_form !== false) {
    $selected_city_tax_form = $city_slug_from_url_tax_form;
    if (!headers_sent()) {
        if (!isset($_COOKIE['selected_city']) || $_COOKIE['selected_city'] !== $selected_city_tax_form) {
            setcookie('selected_city', $selected_city_tax_form, time() + 30 * DAY_IN_SECONDS, '/', $_SERVER['HTTP_HOST'], is_ssl(), true);
        }
    }
} else {
    $selected_city_cookie_value_tax_form = isset($_COOKIE['selected_city']) ? sanitize_text_field($_COOKIE['selected_city']) : 'tumen';
    if (in_array($selected_city_cookie_value_tax_form, $known_city_slugs_tax_form_php, true)) {
         $selected_city_tax_form = $selected_city_cookie_value_tax_form;
    } else {
         $selected_city_tax_form = 'tumen';
         if (!headers_sent()) {
            setcookie('selected_city', $selected_city_tax_form, time() + 30 * DAY_IN_SECONDS, '/', $_SERVER['HTTP_HOST'], is_ssl(), true);
         }
    }
}
// --- КОНЕЦ ЛОГИКИ ОПРЕДЕЛЕНИЯ ГОРОДА ---

// --- Получаем email города ---
$form_notifications = mrt_get_form_notification_settings($selected_city_tax_form);
$to = $form_notifications['email'];
$telegram_chat_ids = $form_notifications['telegram_chat_ids'];
// --- Конец получения email ---

// --- Сбор данных формы ---
$relation = sanitize_text_field($_POST['relation'] ?? '');

// Данные пациента
$patient_city = sanitize_text_field($_POST['patient_city'] ?? '');
$patient_inn = sanitize_text_field($_POST['patient_inn'] ?? '');
$patient_name = sanitize_text_field($_POST['patient_name'] ?? '');
$patient_dob = sanitize_text_field($_POST['patient_dob'] ?? '');
$patient_doc_type = sanitize_text_field($_POST['patient_doc_type'] ?? '');
$patient_doc_number = sanitize_text_field($_POST['patient_doc_number'] ?? '');
$patient_doc_date = sanitize_text_field($_POST['patient_doc_date'] ?? '');

// Данные налогоплательщика
$taxpayer_name = sanitize_text_field($_POST['taxpayer_name'] ?? '');
$taxpayer_dob = sanitize_text_field($_POST['taxpayer_dob'] ?? '');
$taxpayer_doc_type = sanitize_text_field($_POST['taxpayer_doc_type'] ?? '');
$taxpayer_doc_number = sanitize_text_field($_POST['taxpayer_doc_number'] ?? '');
$taxpayer_doc_date = sanitize_text_field($_POST['taxpayer_doc_date'] ?? '');
$taxpayer_inn = sanitize_text_field($_POST['taxpayer_inn'] ?? '');
$taxpayer_relation_to_patient = sanitize_text_field($_POST['taxpayer_relation_to_patient'] ?? '');
$taxpayer_phone = sanitize_text_field($_POST['taxpayer_phone'] ?? '');
$taxpayer_comment = sanitize_textarea_field($_POST['taxpayer_comment'] ?? '');
$years = array_map('sanitize_text_field', $_POST['years'] ?? []);
$years_str = !empty($years) ? implode(', ', $years) : 'Не указаны';
// --- Конец сбора данных ---

// --- Формирование темы и тела письма ---
$email_subject = 'Новая заявка на налоговую справку';

$body = "Новая заявка на налоговую справку для города: " . esc_html($selected_city_tax_form) . "\n\n"; // Используем переопределенный $selected_city_tax_form
$body .= "Кем приходится налогоплательщик пациенту: " . ($relation === 'patient' ? 'Я и есть пациент' : 'Я — законный представитель пациента') . "\n\n";

$body .= "--- Данные пациента ---\n";
$body .= "Город: $patient_city\n";
$body .= "ИНН: $patient_inn\n";
$body .= "ФИО: $patient_name\n";
$body .= "Дата рождения: $patient_dob\n";
$body .= "Документ: $patient_doc_type\n";
$body .= "Серия и номер: $patient_doc_number\n";
$body .= "Дата выдачи документа: $patient_doc_date\n\n";

$body .= "--- Данные налогоплательщика ---\n";
$body .= "ФИО: $taxpayer_name\n";
$body .= "Дата рождения: $taxpayer_dob\n";
$body .= "Документ: $taxpayer_doc_type\n";
$body .= "Серия и номер: $taxpayer_doc_number\n";
$body .= "Дата выдачи документа: $taxpayer_doc_date\n";
$body .= "ИНН: $taxpayer_inn\n";
$body .= "Кем приходится пациенту: $taxpayer_relation_to_patient\n";
$body .= "Контактный телефон: $taxpayer_phone\n";
$body .= "Комментарий: $taxpayer_comment\n";
$body .= "Годы справки: $years_str\n";

$headers = array('Content-Type: text/plain; charset=UTF-8');
// --- Конец формирования письма ---

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