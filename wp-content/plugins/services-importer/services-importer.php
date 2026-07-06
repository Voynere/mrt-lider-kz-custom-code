<?php
/*
Plugin Name: Services Importer (Excel -> Services CPT)
Description: Импорт прайса из Excel (каждый лист = филиал) в CPT services. Использует SimpleXLSX (no composer).
Version: 2.1
Author: brightdark
*/

if ( ! defined( 'ABSPATH' ) ) exit;

// Подключаем SimpleXLSX
if ( file_exists( __DIR__ . '/SimpleXLSX.php' ) ) {
    require_once __DIR__ . '/SimpleXLSX.php';
} else {
    // Ошибка
    add_action( 'admin_notices', function(){
        if ( current_user_can('manage_options') ) {
            echo '<div class="error"><p>SimpleXLSX.php не найден в папке плагина services-importer. Скачайте SimpleXLSX и положите файл в папку плагина.</p></div>';
        }
    });
}

// Регистрация CPT и таксономий
add_action( 'init', 'si_register_cpt_and_tax' );
function si_register_cpt_and_tax() {
    register_post_type('service', array(
        'labels' => array(
            'name' => 'Услуги',
            'singular_name' => 'Услуга'
        ),
        'public' => true,
        'has_archive' => false,
        'show_in_menu' => true,
        'supports' => array('title'),
    ));

    register_taxonomy('branch', 'service', array(
        'labels' => array('name'=>'Филиалы','singular_name'=>'Филиал'),
        'hierarchical' => false,
        'public' => true,
    ));
    
    register_taxonomy('service_type', 'service', array(
        'labels' => array('name'=>'Типы услуг','singular_name'=>'Тип услуги'),
        'hierarchical' => false,
        'public' => true,
    ));
}

// Админ-страница для импорта
add_action( 'admin_menu', function(){
    add_submenu_page('tools.php', 'Import Services', 'Import Services', 'manage_options', 'si-import', 'si_import_page');
});

function si_import_page() {
    if ( ! current_user_can('manage_options') ) return;

    echo '<div class="wrap"><h1>Импорт услуг из excel</h1>';

    if ( isset($_POST['si_import_nonce']) && wp_verify_nonce($_POST['si_import_nonce'],'si_import_action') ) {
        if (! empty($_FILES['si_file']['tmp_name'])) {
            $clean_choice = isset($_POST['si_clean_branch']) && $_POST['si_clean_branch'] === '1';
            $result = si_handle_upload_and_import($_FILES['si_file'], $clean_choice);
            echo '<div style="background:#fff;padding:12px;border:1px solid #ddd;margin-top:10px;">' . esc_html($result) . '</div>';
        } else {
            echo '<div style="color:red;">Файл не загружен.</div>';
        }
    }

    echo '<form method="post" enctype="multipart/form-data">';
    wp_nonce_field('si_import_action','si_import_nonce');
    echo '<p><input type="file" name="si_file" accept=".xlsx" required /></p>';
    echo '<p><label><input type="checkbox" name="si_clean_branch" value="1" /> Очистить старые записи для филиала перед импортом (удалит все записи с этим филиалом)</label></p>';
    submit_button('Загрузить и импортировать');
    echo '</form>';
    
    // Добавляем секцию для настройки соответствий
    echo '<div class="wrap" style="margin-top: 30px;">
        <h2>Настройка соответствий видов услуг</h2>
        <p>Варианты написания должны объединяться в один тип услуги.</p>
        <p>Формат:</p>
        <pre>Варианты написания в excel: вариант1, вариант2, вариант3</pre>
        <p>Пример:</p>
        <pre>МРТ 1.5 тесла: мрт 1.5 т, мрт 1,5 тесла, мрт 1.5т</pre>
        
        <form method="post">
            ' . wp_nonce_field('si_mapping_save', 'si_mapping_nonce', true, false) . '
            <textarea name="si_mapping" style="width: 100%; height: 200px; font-family: monospace;">' . 
                esc_textarea(si_get_mapping_config()) . 
            '</textarea>
            <p><input type="submit" name="save_mapping" class="button button-primary" value="Сохранить настройки"></p>
        </form>
    </div>';

    // Обработка сохранения настроек
    if (isset($_POST['save_mapping']) && wp_verify_nonce($_POST['si_mapping_nonce'], 'si_mapping_save')) {
        si_save_mapping_config($_POST['si_mapping']);
        echo '<div class="updated"><p>Настройки сохранены.</p></div>';
    }
}

// Получаем текущие настройки соответствий
function si_get_mapping_config() {
    $path = plugin_dir_path(__FILE__) . 'service_types_mapping.txt';
    if (file_exists($path)) {
        return file_get_contents($path);
    }
    return "МРТ 1.5 тесла: мрт 1.5 т, мрт 1,5 тесла, мрт 1.5т\nМРТ 3 тесла: мрт 3 т, мрт 3.0 тесла, мрт 3т\nКТ: кт, кт диагностика\nДенситометрия: денситометрия, денситометрија\nНевролог: невролог, прием невролога";
}

// Сохраняем настройки соответствий
function si_save_mapping_config($content) {
    $path = plugin_dir_path(__FILE__) . 'service_types_mapping.txt';
    file_put_contents($path, $content);
}

// Парсим файл соответствий
function si_parse_mapping_config() {
    $path = plugin_dir_path(__FILE__) . 'service_types_mapping.txt';
    $mapping = array();
    
    if (!file_exists($path)) {
        // Создаем файл с дефолтными настройками
        $default = "МРТ 1.5 тесла: мрт 1.5 т, мрт 1,5 тесла, мрт 1.5т\nМРТ 3 тесла: мрт 3 т, мрт 3.0 тесла, мрт 3т\nКТ: кт, кт диагностика\nДенситометрия: денситометрия, денситометрија\nНевролог: невролог, прием невролога";
        file_put_contents($path, $default);
    }
    
    $content = file_get_contents($path);
    $lines = explode("\n", $content);
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, ':') === false) continue;
        
        list($canonical, $variants) = explode(':', $line, 2);
        $canonical = trim($canonical);
        $variants = array_map('trim', explode(',', $variants));
        
        $mapping[$canonical] = $variants;
    }
    
    return $mapping;
}

// Находим канонический тип услуги по варианту написания
function si_find_canonical_type($type) {
    if (empty($type)) return '';
    
    $type_lower = mb_strtolower($type, 'UTF-8');
    $mapping = si_parse_mapping_config();
    
    foreach ($mapping as $canonical => $variants) {
        foreach ($variants as $variant) {
            // Удаляем лишние пробелы и приводим к нижнему регистру
            $variant_clean = preg_replace('/\s+/', ' ', trim($variant));
            $variant_clean = mb_strtolower($variant_clean, 'UTF-8');
            
            // Проверяем точное совпадение
            if ($type_lower === $variant_clean) {
                return $canonical;
            }
            
            // Проверяем на наличие варианта в строке
            if (strpos($type_lower, $variant_clean) !== false) {
                return $canonical;
            }
        }
    }
    
    // Если не найдено соответствие, возвращаем оригинальное название
    return $type;
}

// Основная логика импорта (SimpleXLSX)
function si_handle_upload_and_import($file, $clean_branch = false) {

    require_once( ABSPATH . 'wp-admin/includes/file.php' );
    $overrides = array('test_form' => false);
    $move = wp_handle_upload( $file, $overrides );
    if ( isset($move['error']) ) {
        return 'Upload error: ' . $move['error'];
    }
    $filepath = $move['file'];

    // Поддержка разных версий SimpleXLSX
    $xlsx_class = null;
    if ( class_exists('SimpleXLSX') ) {
        $xlsx_class = 'SimpleXLSX';
    } elseif ( class_exists('Shuchkin\\SimpleXLSX') ) {
        $xlsx_class = 'Shuchkin\\SimpleXLSX';
    }

    if ( ! $xlsx_class ) {
        @unlink($filepath);
        return 'SimpleXLSX class not found. Please install SimpleXLSX.php into plugin folder (class SimpleXLSX or Shuchkin\\SimpleXLSX).';
    }

    // Попытка распарсить файл
    $xlsx = false;
    if ( is_callable(array($xlsx_class, 'parse')) ) {
        $xlsx = call_user_func(array($xlsx_class, 'parse'), $filepath);
    } else {
        // старый стиль: new SimpleXLSX($filepath)
        try {
            $xlsx = new $xlsx_class($filepath);
            if ( method_exists($xlsx, 'success') && ! $xlsx->success() ) {
                $err = method_exists($xlsx, 'error') ? $xlsx->error() : 'Unknown parse error';
                @unlink($filepath);
                return 'Parse error: ' . $err;
            }
        } catch ( Exception $e ) {
            @unlink($filepath);
            return 'Parse exception: ' . $e->getMessage();
        }
    }

    // Проверим результат
    if ( ! $xlsx || (is_bool($xlsx) && $xlsx === false) ) {
        $err = '';
        if ( is_callable(array($xlsx_class, 'parseError')) ) {
            $err = call_user_func(array($xlsx_class, 'parseError'));
        }
        @unlink($filepath);
        return 'Parse error: ' . ($err ? $err : 'unknown error');
    }


    $sheetNames = $xlsx->sheetNames();
    $created = 0;
    $deleted_total = 0;

    foreach ($sheetNames as $sheetIndex => $sheetName) {
        // очищаем старые записи для филиала (если выбрано)
        if ( $clean_branch ) {
            // находим посты с таксономией branch = $sheetName
            $to_delete = get_posts(array(
                'post_type' => 'service',
                'posts_per_page' => -1,
                'fields' => 'ids',
                'tax_query' => array(array(
                    'taxonomy' => 'branch',
                    'field' => 'name',
                    'terms' => $sheetName,
                )),
            ));
            if ( ! empty($to_delete) ) {
                foreach ($to_delete as $pid) {
                    wp_delete_post($pid, true);
                    $deleted_total++;
                }
            }
        }

        // Получаем строки конкретного листа
        $rows = $xlsx->rows($sheetIndex);
        if ( empty($rows) ) continue;

        // Определяем структуру листа по первой строке данных
        // Нужно учитывать Акции и Скидки
        // Проверим, есть ли в строке 7 колонок (вариант 2) или 6 (вариант 1)
        $structure_type = 1; // по умолчанию вариант 1
        if (isset($rows[0]) && count($rows[0]) >= 7) {
            $structure_type = 2; // вариант 2
        }

        $current_category = '';

        // Пропускаем заголовки и начинаем с первой строки данных
        for ($r = 0; $r < count($rows); $r++) {
            $row = $rows[$r];
            
            // ИНДЕКСЫ КОЛОНК В ЗАВИСИМОСТИ ОТ СТРУКТУРЫ:
            // Вариант 1 (6 колонок):
            // 0 - Код (нумерация)
            // 1 - Область исследования
            // 2 - Цена
            // 3 - Скидка
            // 4 - Вид услуги
            // 5 - Категория
            
            // Вариант 2 (7 колонок):
            // 0 - Код (нумерация)
            // 1 - Область исследования
            // 2 - Цена
            // 3 - Скидка
            // 4 - Акция (не используется)
            // 5 - Вид услуги
            // 6 - Категория
            
            // Проверяем формат кода (должен быть x.x, x.x.x)
            // точка в конце номера (кода) будет создавать ошибку
            $code = isset($row[0]) ? si_normalize_val($row[0]) : '';
            if (!si_is_valid_code_format($code)) {
                if (preg_match('/^\d+\.\s/', $code)) {
                    // Для категории используем область исследования
                    if (isset($row[1])) {
                        $category_name = trim($row[1]);
                        if (!empty($category_name)) {
                            $current_category = $category_name;
                        }
                    }
                }
                continue;
            }
            
            // Получаем данные из колонок в зависимости от структуры
            $oblastCell = isset($row[1]) ? $row[1] : null;
            $priceCell = isset($row[2]) ? $row[2] : null;
            $discountCell = isset($row[3]) ? $row[3] : null;
            
            if ($structure_type == 1) {
                // Вариант 1: 6 колонок
                $typeCell = isset($row[4]) ? $row[4] : null;
                $categoryCell = isset($row[5]) ? $row[5] : null;
            } else {
                // Вариант 2: 7 колонок
                $typeCell = isset($row[5]) ? $row[5] : null;
                $categoryCell = isset($row[6]) ? $row[6] : null;
            }

            // Обрабатываем категорию
            if (si_not_empty($categoryCell)) {
                $current_category = si_normalize_val($categoryCell);
            }
            
            // Если категория не заполнена, пропускаем строку
            if (empty($current_category)) {
                continue;
            }

            // Нормализуем данные
            $oblast = si_normalize_val($oblastCell);
            $price = si_normalize_val($priceCell);
            $discount = si_normalize_val($discountCell);
            $type = si_normalize_val($typeCell);
            
            // Пропускаем строки без области исследования и цены
            if (empty($oblast) && empty($price)) {
                continue;
            }

            // Создаём запись CPT
            $post_id = wp_insert_post(array(
                'post_type' => 'service',
                'post_title' => si_truncate_for_title($current_category . ' — ' . $oblast),
                'post_status' => 'publish',
            ));

            if ( is_wp_error($post_id) ) continue;

            // Устанавливаем таксономию филиала
            wp_set_object_terms($post_id, array($sheetName), 'branch', true);

            // Устанавливаем таксономию типа услуги (если указано)
            if (si_not_empty($type)) {
                // Находим канонический тип услуги
                $canonical_type = si_find_canonical_type($type);
                wp_set_object_terms($post_id, $canonical_type, 'service_type', false);
                
                // Сохраняем оригинальное значение для отображения
                update_post_meta($post_id, 'si_type', $type);
            }

            // Сохраняем мета
            update_post_meta($post_id, 'si_category', $current_category);
            update_post_meta($post_id, 'si_oblast', $oblast);
            update_post_meta($post_id, 'si_price', $price);
            update_post_meta($post_id, 'si_discount', $discount);

            $created++;
        } // for rows
    } // foreach sheets

    // Удаляем загруженный временный файл
    @unlink($filepath);

    $msg = "Импорт завершён. Создано записей: $created.";
    if ($clean_branch) $msg .= " Удалено старых записей: $deleted_total.";
    return $msg;
}
// Проверка формата кода (x.x, x.x.x)
function si_is_valid_code_format($code) {
    if (empty($code)) return false;
    return preg_match('/^\d+(\.\d+)+$/', $code);
}

function si_normalize_val($v) {
    if (is_null($v)) return '';
    if (is_bool($v)) return $v ? '1' : '0';
    if (is_numeric($v)) {
        // Приводим к строке, убирая лишние .0
        if (intval($v) == $v) return (string) intval($v);
        return (string) $v;
    }
    return trim((string)$v);
}

function si_not_empty($v) {
    return ! is_null($v) && trim((string)$v) !== '';
}

function si_truncate_for_title($s, $len=120) {
    $s = strip_tags($s);
    if (mb_strlen($s) > $len) return mb_substr($s,0,$len-3).'...';
    return $s;
}