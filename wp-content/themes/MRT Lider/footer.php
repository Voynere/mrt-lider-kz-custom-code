<?php
// Определение города аналогично header.php
$known_city_slugs_footer_php = array(
    'almaty', 'astana', 'karaganda', 'taldykorgan'
);

// Функция для определения города из URL запроса (аналогично header.php для независимости)
if (!function_exists('get_city_slug_from_request_path_footer')) {
    function get_city_slug_from_request_path_footer($known_slugs) {
        $request_uri = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '/';
        $path = explode('?', $request_uri)[0];
        // Разбиваем путь на части
        $path_parts = array_filter(explode('/', trim($path, '/')));

        if (!empty($path_parts)) {
            $first_part = strtolower(reset($path_parts));
            if (in_array($first_part, $known_slugs, true)) {
                return $first_part;
            }
        }
        return false; // Город в URL не найден
    }
}

$city_slug_from_url_footer = get_city_slug_from_request_path_footer($known_city_slugs_footer_php);

if ($city_slug_from_url_footer !== false) {
    $selected_city_slug_footer_nav = $city_slug_from_url_footer;
} else {
    $selected_city_slug_footer_nav = isset($_COOKIE['selected_city']) ? sanitize_text_field($_COOKIE['selected_city']) : 'almaty';
    if (!in_array($selected_city_slug_footer_nav, $known_city_slugs_footer_php, true)) {
         $selected_city_slug_footer_nav = 'almaty';
    }
}

$city_specific_pages_footer = array('pravovaja-i-juridicheskaja-informacija', 'vacancies', 'site-map', 'informacija-o-medicinskoj-organizacii', 'otzyvy-klientov', 'tax', 'zajavka-na-spravku-dlja-nalogovogo-vycheta');
$city_base_url_footer_nav = home_url('/') . $selected_city_slug_footer_nav . '/';

// Функция формирования URL навигации (дублируем из header.php для независимости)
if (!function_exists('get_footer_nav_url')) {
    function get_footer_nav_url($page_slug, $city_base_url, $city_specific_pages) {
        if (in_array($page_slug, $city_specific_pages)) {
            return $city_base_url . $page_slug . '/';
        } else {
            if (filter_var($page_slug, FILTER_VALIDATE_URL) || strpos($page_slug, '#') === 0) {
                 return $page_slug;
            }
            return home_url('/') . $page_slug . '/';
        }
    }
}
// --- КОНЕЦ ЛОГИКИ ОПРЕДЕЛЕНИЯ ГОРОДА ДЛЯ FOOTER ---

$args_footer_contacts = array(
    'post_type'      => 'post',
    'posts_per_page' => 1,
    'tax_query'      => array(
        'relation' => 'AND',
        array(
            'taxonomy' => 'category',
            'field'    => 'slug',
            'terms'    => $selected_city_slug_footer_nav,
        ),
        array(
            'taxonomy' => 'category',
            'field'    => 'slug',
            'terms'    => 'contacty',
        )
    )
);
$contacts_query_footer = new WP_Query($args_footer_contacts);
?>

    <footer class="footer">
        <div class="container">
            <div class="footer__inner">
                <button class="footer__visually">
                    <p>Версия для слабовидящих</p>
                    <div class="footer__visually-container">
                        <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M2.08108 1H9V7.91892M7.81081 2.18919L1 9" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    </div>
                </button>
                <div class="footer__links">
                    <div class="footer__links-block">
                        <li class="footer__links-item big-item">
                            <a href="<?php echo esc_url(get_footer_nav_url('pravovaja-i-juridicheskaja-informacija', $city_base_url_footer_nav, $city_specific_pages_footer)); ?>">Правовая и юридическая информация</a>
                        </li>
                        <li class="footer__links-item">
                            <a href="<?php echo esc_url(get_footer_nav_url('vacancies', $city_base_url_footer_nav, $city_specific_pages_footer)); ?>">Наши вакансии</a>
                        </li>
                        <li class="footer__links-item">
                            <a href="<?php echo esc_url(get_footer_nav_url('site-map', $city_base_url_footer_nav, $city_specific_pages_footer)); ?>">Карта сайта</a>
                        </li>
                    </div>
                    <div class="footer__links-block">
                        <li class="footer__links-item big-item">
                            <a href="<?php echo esc_url(get_footer_nav_url('informacija-o-medicinskoj-organizacii', $city_base_url_footer_nav, $city_specific_pages_footer)); ?>">Информация о медицинской организации</a>
                        </li>
                        <li class="footer__links-item">
                            <a href="<?php echo esc_url(get_footer_nav_url('otzyvy-klientov', $city_base_url_footer_nav, $city_specific_pages_footer)); ?>">Отзывы клиентов</a>
                        </li>
                        <li class="footer__links-item">
                            <a href="<?php echo esc_url(get_footer_nav_url('zajavka-na-spravku-dlja-nalogovogo-vycheta', $city_base_url_footer_nav, $city_specific_pages_footer)); ?>">Заявка на налоговый вычет</a>
                        </li>
                    </div>
                </div>
                <div class="footer__bottom">
                    <div class="footer__info footer__bottom-item">
                        <?php if ($contacts_query_footer->have_posts()) : ?>
                            <?php while ($contacts_query_footer->have_posts()) : $contacts_query_footer->the_post(); ?>
                                <?php 
                                // Получаем данные из групп полей
                                $licenses_group = get_field('contacts_license') ?: [];
                                $form_licenses_group = get_field('contacts_form_license') ?: [];
                                
                                // Определяем максимальное количество филиалов (проверяем до 5 пар)
                                $max_filials = 5;
                                
                                // Проходим по каждой паре полей
                                for ($i = 1; $i <= $max_filials; $i++) {
                                    $license_field_key = 'contacts_license_' . $i;
                                    $form_license_field_key = 'contacts_form_license_' . $i;
                                    
                                    $license_value = !empty($licenses_group[$license_field_key]) ? $licenses_group[$license_field_key] : '';
                                    $form_license_value = !empty($form_licenses_group[$form_license_field_key]) ? $form_licenses_group[$form_license_field_key] : '';
                                    
                                    // Если есть хотя бы одно значение для этой пары, создаем блок
                                    if (!empty($license_value) || !empty($form_license_value)) {
                                        echo '<div class="footer__info-item">'; // Добавляем обертку для каждой группы
                                        
                                        // Выводим лицензию из contacts_license
                                        if (!empty($license_value)) {
                                            echo '<p>' . esc_html($license_value) . '</p>';
                                        }
                                        
                                        // Выводим данные из contacts_form_license
                                        if (!empty($form_license_value)) {
                                            echo '<p>' . esc_html($form_license_value) . '</p>';
                                        }
                                        
                                        echo '</div>'; // Закрываем обертку
                                    }
                                }
                                ?>
                            <?php endwhile; ?>
                            <?php wp_reset_postdata(); ?>
                        <?php endif; ?>
                        
                        <p>© Все права защищены.</p>
                        <p>Центр магнитно-резонансной томографии «МРТ Лидер»</p>
                    </div>
                    <div class="footer__socials footer__bottom-item">
                        <p>Мы в социальных сетях</p>
                        <?php 
                        // Получаем данные о социальных сетях из поста контактов выбранного города
                        $vk_link = '';
                        
                        if ($contacts_query_footer->have_posts()) :
                            // Сбрасываем указатель поста, если мы уже прошлись по результатам выше
                            rewind_posts();
                            while ($contacts_query_footer->have_posts()) : $contacts_query_footer->the_post();
                                // Получаем группу полей социальных сетей
                                $social_media_group = get_field('soc_media') ?: [];
                                
                                // Проверяем наличие поля soc_media_vk
                                if (!empty($social_media_group['soc_media_vk'])) {
                                    $vk_link = esc_url($social_media_group['soc_media_vk']);
                                    break; // Выходим из цикла, как только нашли ссылку
                                }
                            endwhile;
                            wp_reset_postdata();
                        endif;
                        
                        // Выводим ссылку на ВКонтакте или сообщение об отсутствии данных
                        if (!empty($vk_link)) :
                        ?>
                            <a href="<?php echo $vk_link; ?>" class="footer__socials-item" target="_blank" rel="noopener noreferrer">
                                <img src="<?php bloginfo('template_url')?>/assets/img/vk.svg" alt="ВКонтакте">
                            </a>
                        <?php else : ?>
                            <p>Данные о социальных сетях для данного филиала отсутствуют</p>
                        <?php endif; ?>
                    </div>

                    <div class="footer__contacts footer__bottom-item">
                        <?php
                        $phones_found = false;

                        if ( ! empty( $contacts_query_footer->posts ) && is_array( $contacts_query_footer->posts ) ) {
                            $contact_post = $contacts_query_footer->posts[0];
                            $contact_post_id = intval( $contact_post->ID );

                            // получаем группу телефонов через ACF по ID поста
                            $phones_group = get_field('contacts_phones', $contact_post_id);

                            // Если ACF не вернул массив -> попробуем взять по прямым meta ключам
                            if ( ! is_array( $phones_group ) || empty( $phones_group ) ) {
                                $phones_group = array();
                                for ($pi = 1; $pi <= 3; $pi++) {
                                    $k = 'contacts_phone_' . $pi;
                                    $val = get_post_meta($contact_post_id, $k, true);
                                    if ($val !== '') {
                                        $phones_group[$k] = $val;
                                    }
                                }
                            }

                            // выводим до 3 телефонов
                            for ($phone_index = 1; $phone_index <= 3; $phone_index++) {
                                $field_key = 'contacts_phone_' . $phone_index;
                                if (!empty($phones_group[$field_key])) {
                                    $raw = trim( (string) $phones_group[$field_key] );
                                    // Оставляем плюс и цифры для tel:
                                    $tel_clean = preg_replace('/[^\d\+]/', '', $raw);
                                    if ($tel_clean !== '') {
                                        echo '<a href="tel:' . esc_attr($tel_clean) . '">' . esc_html($raw) . '</a>';
                                        $phones_found = true;
                                    }
                                }
                            }
                        }

                        if (!$phones_found) {
                            echo '<p>Контакты не найдены</p>';
                        }
                        ?>
                    </div>

                </div>
            </div>
        </div>
    </footer>

</div> <!-- wrapper -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<?php wp_footer(); ?>
</body>
</html>