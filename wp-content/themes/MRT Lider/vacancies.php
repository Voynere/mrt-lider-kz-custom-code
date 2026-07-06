<?php

// --- Массив городов ---
$vacancy_cities_map = array(
    'almaty' => 'Алматы',
    'astana' => 'Астана',
    'karaganda' => 'Караганда',
    'taldykorgan' => 'Талдыкорган'
);

// --- ФУНКЦИЯ ДЛЯ АВТОМАТИЧЕСКОГО СОЗДАНИЯ ТЕРМИНОВ ТАКСОНОМИИ ---
function create_vacancy_city_terms($cities_map) {
    foreach ($cities_map as $slug => $name) {
        // Проверяем, существует ли термин с таким слагом
        $term_exists = term_exists($slug, 'vacancy_city');
        
        if (!$term_exists) {
            // Если термин не существует, создаем его
            wp_insert_term(
                $name, // Название термина
                'vacancy_city', // Таксономия
                array(
                    'slug' => $slug, // Slug термина
                    'description' => 'Город ' . $name
                )
            );
            // error_log("Vacancy City Term Created: {$name} ({$slug})");
        } else {
            // Если термин существует, проверяем, совпадает ли его название
            $term = get_term_by('slug', $slug, 'vacancy_city');
            if ($term && $term->name !== $name) {
                // Обновляем название, если оно отличается
                wp_update_term(
                    $term->term_id,
                    'vacancy_city',
                    array(
                        'name' => $name
                    )
                );
                // error_log("Vacancy City Term Updated: {$name} ({$slug})");
            }
        }
    }
}

// --- Регистрация cpt "вакансии" с таксономией "город" ---
add_action('init', 'register_vacancy_post_type');
function register_vacancy_post_type() {
    global $vacancy_cities_map; // Делаем массив доступным внутри функции
    
    $labels = array(
        'name'                  => 'Вакансии',
        'singular_name'         => 'Вакансия',
        'menu_name'             => 'Вакансии',
        'name_admin_bar'        => 'Вакансия',
        'archives'              => 'Архив вакансий',
        'attributes'            => 'Атрибуты вакансии',
        'parent_item_colon'     => 'Родительская вакансия:',
        'all_items'             => 'Все вакансии',
        'add_new_item'          => 'Добавить новую вакансию',
        'add_new'               => 'Добавить новую',
        'new_item'              => 'Новая вакансия',
        'edit_item'             => 'Редактировать вакансию',
        'update_item'           => 'Обновить вакансию',
        'view_item'             => 'Просмотреть вакансию',
        'view_items'            => 'Просмотреть вакансии',
        'search_items'          => 'Искать вакансии',
        'not_found'             => 'Вакансии не найдены',
        'not_found_in_trash'    => 'Вакансии не найдены в корзине',
        'featured_image'        => 'Изображение вакансии',
        'set_featured_image'    => 'Установить изображение вакансии',
        'remove_featured_image' => 'Удалить изображение вакансии',
        'use_featured_image'    => 'Использовать как изображение вакансии',
        'insert_into_item'      => 'Вставить в вакансию',
        'uploaded_to_this_item' => 'Загружено для этой вакансии',
        'items_list'            => 'Список вакансий',
        'items_list_navigation' => 'Навигация по списку вакансий',
        'filter_items_list'     => 'Фильтровать список вакансий',
    );
    $args = array(
        'label'                 => 'Вакансия',
        'description'           => 'Вакансии для медицинского центра',
        'labels'                => $labels,
        'supports'              => array('title', 'editor', 'excerpt', 'thumbnail', 'custom-fields'),
        'taxonomies'            => array(),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 25,
        'menu_icon'             => 'dashicons-businessman',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => false,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true, // Для поддержки Gutenberg
    );
    register_post_type('vacancy', $args);

    // Регистрация таксономии "город" для cpt "вакансии"
    $city_labels = array(
        'name'                       => 'Города',
        'singular_name'              => 'Город',
        'menu_name'                  => 'Города',
        'all_items'                  => 'Все города',
        'parent_item'                => 'Родительский город',
        'parent_item_colon'          => 'Родительский город:',
        'new_item_name'              => 'Новый город',
        'add_new_item'               => 'Добавить новый город',
        'edit_item'                  => 'Редактировать город',
        'update_item'                => 'Обновить город',
        'view_item'                  => 'Просмотреть город',
        'separate_items_with_commas' => 'Разделяйте города запятыми',
        'add_or_remove_items'        => 'Добавить или удалить города',
        'choose_from_most_used'      => 'Выбрать из часто используемых',
        'popular_items'              => 'Популярные города',
        'search_items'               => 'Искать города',
        'not_found'                  => 'Города не найдены',
        'no_terms'                   => 'Нет городов',
        'items_list'                 => 'Список городов',
        'items_list_navigation'      => 'Навигация по списку городов',
    );
    $city_args = array(
        'labels'                     => $city_labels,
        'hierarchical'               => false,
        'public'                     => true,
        'show_ui'                    => true,
        'show_admin_column'          => true,
        'show_in_nav_menus'          => true,
        'show_tagcloud'              => false,
        'show_in_rest'               => true, // Для поддержки Gutenberg
    );
    register_taxonomy('vacancy_city', array('vacancy'), $city_args);
    
    // Создаем термины таксономии vacancy_city при регистрации CPT
    create_vacancy_city_terms($vacancy_cities_map);
}

// --- ДОБАВЛЕНИЕ МЕТАБОКСОВ ДЛЯ ПОЛЕЙ ВАКАНСИИ ---
add_action('add_meta_boxes', 'add_vacancy_meta_boxes');
function add_vacancy_meta_boxes() {
    add_meta_box(
        'vacancy_details',
        'Детали вакансии',
        'vacancy_details_callback',
        'vacancy',
        'normal',
        'high'
    );
}

function vacancy_details_callback($post) {
    //_nonce для безопасности
    wp_nonce_field('save_vacancy_details', 'vacancy_details_nonce');

    // Получаем текущие значения полей
    $salary = get_post_meta($post->ID, '_vacancy_salary', true);
    $payments_per_month = get_post_meta($post->ID, '_vacancy_payments_per_month', true);
    $experience = get_post_meta($post->ID, '_vacancy_experience', true);
    $employment_type = get_post_meta($post->ID, '_vacancy_employment_type', true);
    $working_hours = get_post_meta($post->ID, '_vacancy_working_hours', true);
    $work_format = get_post_meta($post->ID, '_vacancy_work_format', true);
    ?>

    <table class="form-table">
        <tr>
            <th><label for="vacancy_salary">Зарплата (от)</label></th>
            <td><input type="text" id="vacancy_salary" name="vacancy_salary" value="<?php echo esc_attr($salary); ?>" size="25" /></td>
        </tr>
        <tr>
            <th><label for="vacancy_payments_per_month">Количество выплат в месяц</label></th>
            <td><input type="text" id="vacancy_payments_per_month" name="vacancy_payments_per_month" value="<?php echo esc_attr($payments_per_month); ?>" size="25" /></td>
        </tr>
        <tr>
            <th><label for="vacancy_experience">Опыт работы</label></th>
            <td><input type="text" id="vacancy_experience" name="vacancy_experience" value="<?php echo esc_attr($experience); ?>" size="25" /></td>
        </tr>
        <tr>
            <th><label for="vacancy_employment_type">Вид занятости</label></th>
            <td>
                <select id="vacancy_employment_type" name="vacancy_employment_type">
                    <option value="Полная" <?php selected($employment_type, 'Полная'); ?>>Полная</option>
                    <option value="Частичная" <?php selected($employment_type, 'Частичная'); ?>>Частичная</option>
                    <option value="Временная" <?php selected($employment_type, 'Временная'); ?>>Временная</option>
                    <option value="Стажировка" <?php selected($employment_type, 'Стажировка'); ?>>Стажировка</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="vacancy_working_hours">Рабочие часы</label></th>
            <td><input type="text" id="vacancy_working_hours" name="vacancy_working_hours" value="<?php echo esc_attr($working_hours); ?>" size="25" /></td>
        </tr>
        <tr>
            <th><label for="vacancy_work_format">Формат работы</label></th>
            <td><input type="text" id="vacancy_work_format" name="vacancy_work_format" value="<?php echo esc_attr($work_format); ?>" size="25" /></td>
        </tr>
    </table>
    <?php
}

// --- СОХРАНЕНИЕ МЕТАДАННЫХ ВАКАНСИИ ---
add_action('save_post', 'save_vacancy_details');
function save_vacancy_details($post_id) {
    // Проверяем nonce
    if (!isset($_POST['vacancy_details_nonce']) || !wp_verify_nonce($_POST['vacancy_details_nonce'], 'save_vacancy_details')) {
        return;
    }

    // Проверяем, если это автосохранение
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Проверяем права пользователя
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Сохраняем данные полей
    if (isset($_POST['vacancy_salary'])) {
        update_post_meta($post_id, '_vacancy_salary', sanitize_text_field($_POST['vacancy_salary']));
    }
    if (isset($_POST['vacancy_payments_per_month'])) {
        update_post_meta($post_id, '_vacancy_payments_per_month', sanitize_text_field($_POST['vacancy_payments_per_month']));
    }
    if (isset($_POST['vacancy_experience'])) {
        update_post_meta($post_id, '_vacancy_experience', sanitize_text_field($_POST['vacancy_experience']));
    }
    if (isset($_POST['vacancy_employment_type'])) {
        update_post_meta($post_id, '_vacancy_employment_type', sanitize_text_field($_POST['vacancy_employment_type']));
    }
    if (isset($_POST['vacancy_working_hours'])) {
        update_post_meta($post_id, '_vacancy_working_hours', sanitize_text_field($_POST['vacancy_working_hours']));
    }
    if (isset($_POST['vacancy_work_format'])) {
        update_post_meta($post_id, '_vacancy_work_format', sanitize_text_field($_POST['vacancy_work_format']));
    }
}
?>