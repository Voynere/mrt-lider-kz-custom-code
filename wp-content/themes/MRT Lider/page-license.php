<?php
/*
Template Name: license
*/

// --- Список валидных слагов городов (должен совпадать с header.php и JS) ---
$known_city_slugs = array(
    'almaty', 'angarsk', 'astana', 'achinsk', 'blagoveshhensk', 'bratsk',
    'vladivostok', 'volgodonsk', 'irkutsk', 'karaganda', 'kemerovo', 'kirov',
    'komsomolsk', 'krasnoyarsk', 'kurgan', 'magadan', 'murmansk', 'naberezhnye_chelny',
    'nahodka', 'nizhnekamsk', 'nizhnij_novgorod', 'nizhnij_tagil', 'novosibirsk',
    'petropavlovsk_kamchatskij', 'rostov', 'samara', 'serov', 'taldykorgan', 'tomsk',
    'tumen', 'ussurijsk', 'khabarovsk'
);

// --- Определяем город: URL > куки > fallback ---
$selected_city = 'tumen'; // fallback

$request_uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($request_uri, PHP_URL_PATH);
$path = trim($path, '/');
$path_parts = $path ? explode('/', $path) : array();
$url_city = !empty($path_parts[0]) ? sanitize_text_field($path_parts[0]) : '';

if ($url_city && in_array($url_city, $known_city_slugs, true)) {
    $selected_city = $url_city;
} elseif (isset($_COOKIE['selected_city'])) {
    $cookie_city = sanitize_text_field($_COOKIE['selected_city']);
    if (in_array($cookie_city, $known_city_slugs, true)) {
        $selected_city = $cookie_city;
    }
}

// --- Запрос контактов для выбранного города ---
$args = array(
    'post_type' => 'post',
    'posts_per_page' => 1,
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
            'terms'    => 'contacty',
        )
    )
);
$contacts_query = new WP_Query($args);

get_header();
?>

<main class="main">
    <div class="main-background">
        <?php 
            if (!is_front_page()) {
                custom_breadcrumbs();
            }
        ?>

        <section class="license">
            <div class="container">
                <div class="license__inner">
                    <h1 class="contacts__title page-title">ПРАВОВАЯ И ЮРИДИЧЕСКАЯ ИНФОРМАЦИЯ</h1>

                    <?php
                    $max_filials = 4;

                    $filials = array();
                    $post_id = 0;

                    if ($contacts_query->have_posts()) {
                        $contacts_query->the_post();
                        $post_id = get_the_ID();

                        // Собираем филиалы: пытаемся прочитать мета-поля contacts_juridicheskij_adress_N, contacts_ogrn_N, contacts_inn_N
                        for ($i = 1; $i <= $max_filials; $i++) {
                            $addr_key = 'contacts_juridicheskij_adress_' . $i;
                            $ogrn_key = 'contacts_ogrn_' . $i;
                            $inn_key  = 'contacts_inn_' . $i;
                            
                            $address = trim( (string) get_post_meta($post_id, $addr_key, true) );
                            $ogrn    = trim( (string) get_post_meta($post_id, $ogrn_key, true) );
                            $inn     = trim( (string) get_post_meta($post_id, $inn_key, true) );
                            
                            if ($address === '') {
                                $addresses_group = get_field('contacts_juridicheskij_adress', $post_id);
                                if (is_array($addresses_group) && isset($addresses_group[$addr_key])) {
                                    $address = trim( (string) $addresses_group[$addr_key] );
                                }
                            }
                            if ($ogrn === '') {
                                $ogrn_group = get_field('contacts_ogrn', $post_id);
                                if (is_array($ogrn_group) && isset($ogrn_group[$ogrn_key])) {
                                    $ogrn = trim( (string) $ogrn_group[$ogrn_key] );
                                }
                            }
                            if ($inn === '') {
                                $inn_group = get_field('contacts_inn', $post_id);
                                if (is_array($inn_group) && isset($inn_group[$inn_key])) {
                                    $inn = trim( (string) $inn_group[$inn_key] );
                                }
                            }

                            if ($address !== '' || $ogrn !== '' || $inn !== '') {
                                $filials[] = array(
                                    'index' => $i,
                                    'address' => $address,
                                    'ogrn' => $ogrn,
                                    'inn' => $inn,
                                );
                            }
                        } // end filials loop

                        $contacts_query->rewind_posts();
                    } // end have_posts
                    ?>

                    <?php
                    // формируем список видимых филиалов на основе contacts_form_license_* ---
                    $visible_filials = array();
                    if ($post_id) {
                        // сначала попробуем получить группу contacts_form_license (ACF group)
                        $form_group = get_field('contacts_form_license', $post_id);
                        // пройдём по уже собранным $filials и возьмём только те, где есть соответствующий contacts_form_license_N
                        if (!empty($filials)) {
                            foreach ($filials as $f) {
                                $idx = intval($f['index']);
                                if ($idx <= 0) continue;
                                $form_key = 'contacts_form_license_' . $idx;
                                $label = '';

                                // ищем в группе ACF (если группа — массив с ключами contacts_form_license_N)
                                if (is_array($form_group) && isset($form_group[$form_key]) && $form_group[$form_key] !== '') {
                                    $label = trim((string)$form_group[$form_key]);
                                }

                                // fallback — get_post_meta на всякий случай
                                if ($label === '') {
                                    $meta_val = get_post_meta($post_id, $form_key, true);
                                    if ($meta_val !== '') {
                                        $label = trim((string)$meta_val);
                                    }
                                }

                                // если нашли метку — добавляем в видимые филиалы
                                if ($label !== '') {
                                    $visible_filials[] = array(
                                        'index'   => $idx,  
                                        'label'   => $label,
                                        'address' => $f['address'] ?? '',
                                        'ogrn'    => $f['ogrn'] ?? '',
                                        'inn'     => $f['inn'] ?? '',
                                    );
                                }
                            }
                        }
                    }
                    ?>

                    <!-- табы: список филиалов -->
                    <div class="doc-tabs" role="tablist" aria-label="Выбор филиала">
                        <?php if (!empty($visible_filials)) : ?>
                            <?php foreach ($visible_filials as $i => $vf) :
                                // используем реальный индекс в id, чтобы можно было сопоставлять с мета-полями при необходимости
                                $tab_id = 'filial-' . intval($vf['index']);
                                $label_short = wp_trim_words( $vf['label'], 9, '...' );
                                $is_active = ($i === 0);
                            ?>
                                <button id="tab-<?php echo esc_attr($tab_id); ?>"
                                    class="doc-tab <?php echo $is_active ? 'active' : ''; ?>"
                                    role="tab"
                                    aria-controls="panel-<?php echo esc_attr($tab_id); ?>"
                                    aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
                                    data-tab="<?php echo esc_attr($tab_id); ?>"
                                    data-filial-index="<?php echo esc_attr($vf['index']); ?>"
                                    type="button"><?php echo esc_html($label_short); ?></button>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <p class="license__content-text bold">Данные филиалов не найдены</p>
                        <?php endif; ?>
                    </div>

                    
                    <div class="license__content">
                        <div class="doc-card">
                            <div class="doc-card-inner">
                                <?php if (!empty($visible_filials)) : ?>
                                    <?php foreach ($visible_filials as $i => $vf) :
                                        $tab_id = 'filial-' . intval($vf['index']);
                                        $hidden = $i === 0 ? '' : 'hidden';
                                    ?>
                                        <div id="panel-<?php echo esc_attr($tab_id); ?>" class="doc-panel" role="tabpanel"
                                            aria-labelledby="tab-<?php echo esc_attr($tab_id); ?>"
                                            data-tab="<?php echo esc_attr($tab_id); ?>" <?php echo $hidden; ?>>
                                            <div class="license__content-head">
                                                <div class="license__content-box">
                                                    <p class="license__content-text bold">
                                                        <?php echo esc_html( $vf['address'] ?: 'Адрес не указан' ); ?></p>
                                                    <p class="license__content-text bold">ОГРН:
                                                        <?php echo esc_html( $vf['ogrn'] ?: 'не указан' ); ?></p>
                                                    <p class="license__content-text bold">ИНН/КПП:
                                                        <?php echo esc_html( $vf['inn'] ?: 'не указан' ); ?></p>
                                                </div>
                                                <p class="license__content-text bold">Федеральное законодательство</p>
                                            </div>

                                            <?php
                                            // Определяем список городов Казахстана
                                            $kazakhstan_cities = array('almaty', 'astana', 'karaganda', 'taldykorgan');

                                            $current_city_for_license = isset($selected_city_slug_nav) ? $selected_city_slug_nav : 
                                                                    (isset($selected_city_slug_footer_nav) ? $selected_city_slug_footer_nav : 
                                                                    (isset($selected_city_home) ? $selected_city_home : 'tumen'));

                                            // Проверяем, является ли текущий город городом Казахстана
                                            $is_kazakhstan_city = in_array($current_city_for_license, $kazakhstan_cities, true);
                                            ?>

                                            <div class="license__content-body">
                                                <?php if ($is_kazakhstan_city): ?>
                                                    <ul class="doc-list">
                                                        <li>
                                                            <img src="<?php bloginfo('template_url')?>/assets/img/pdf_icon.svg" alt="PDF">
                                                            <a href="#" target="_blank" class="license__leg-link">
                                                                Федеральный закон от 21.11.2011 №323-ФЗ «Об основах охраны здоровья граждан в Российской Федерации». 
                                                            </a>
                                                        </li>
                                                    </ul>
                                                <?php else: ?>
                                                    <ul class="doc-list">
                                                        <li>
                                                            <img src="<?php bloginfo('template_url')?>/assets/img/pdf_icon.svg" alt="PDF">
                                                            <a href="<?php bloginfo('template_url')?>/assets/pdf/1_Федеральный_закон_от_21_11_2011_N_323_ФЗ_Об_основах_охраны_здоровья.pdf" target="_blank" class="license__leg-link">
                                                                Федеральный закон от 21.11.2011 №323-ФЗ «Об основах охраны здоровья граждан в Российской Федерации». 
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <img src="<?php bloginfo('template_url')?>/assets/img/pdf_icon.svg" alt="PDF">
                                                            <a href="<?php bloginfo('template_url')?>/assets/pdf/2_Федеральный_закон_от_29_11_2010_N_326_ФЗ_Об_обязательном_медицинском.pdf" target="_blank" class="license__leg-link">
                                                                Федеральный закон от 29.11.2010 №326-ФЗ «Об обязательном медицинском страховании в Российской Федерации».
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <img src="<?php bloginfo('template_url')?>/assets/img/pdf_icon.svg" alt="PDF">
                                                            <a href="<?php bloginfo('template_url')?>/assets/pdf/3_Закон_РФ_от_07_02_1992_N_2300_1_О_защите_прав_потребителей.pdf" target="_blank" class="license__leg-link">
                                                                Федеральный Закон РФ от 07.02.1992 №2300-I «О защите прав потребителей». 
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <img src="<?php bloginfo('template_url')?>/assets/img/pdf_icon.svg" alt="PDF">
                                                            <a href="<?php bloginfo('template_url')?>/assets/pdf/4_Постановление_Правительства_РФ_от_11_05_2023_N_736_Правил_предоставления.pdf" target="_blank" class="license__leg-link">
                                                                Постановление Правительства РФ от 11.05.2023 N 736 «Об утверждении Правил предоставления
                                                                медицинскимиорганизациями платных медицинских услуг, внесении изменений в некоторые акты Правительства
                                                                Российской Федерации». 
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <img src="<?php bloginfo('template_url')?>/assets/img/pdf_icon.svg" alt="PDF">
                                                            <a href="<?php bloginfo('template_url')?>/assets/pdf/5_Приказ_от_12_11_2021_N_1050н_Об_утверждении_Порядка_ознакомления.pdf" target="_blank" class="license__leg-link">
                                                                Порядок ознакомления пациента либо его законного представителя с медицинской документацией, отражающей
                                                                состояниездоровья пациента, утвержденный Приказом Министерства здравоохранения России от 12 ноября 2021 г. N
                                                                1050н. 
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <img src="<?php bloginfo('template_url')?>/assets/img/pdf_icon.svg" alt="PDF">
                                                            <a href="<?php bloginfo('template_url')?>/assets/pdf/6_Постановление_Правительства_РФ_30_07_94_N_890_О_государственной.pdf" target="_blank" class="license__leg-link">
                                                                Постановление Правительства РФ от 30.07.1994 № 890 (с ред.) «О государственной поддержке развития
                                                                медицинскойпромышленности и улучшении обеспечения населения и учреждений здравоохранения лекарственными
                                                                средствамии изделиями медицинского назначения» с Приложениями. 
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <img src="<?php bloginfo('template_url')?>/assets/img/pdf_icon.svg" alt="PDF">
                                                            <a href="<?php bloginfo('template_url')?>/assets/pdf/7_Постановление_Правительства_РФ_от_27_12_2024_N_1940_О_Программе.pdf" target="_blank" class="license__leg-link">
                                                                ПОСТАНОВЛЕНИЕ от 27 декабря 2024 г. N 1940 «О ПРОГРАММЕ ГОСУДАРСТВЕННЫХ ГАРАНТИЙ БЕСПЛАТНОГООКАЗАНИЯ
                                                                ГРАЖДАНАМ МЕДИЦИНСКОЙ ПОМОЩИ НА 2025 ГОД И НА ПЛАНОВЫЙ ПЕРИОД 2026 И 2027 ГОДОВ» 
                                                            </a>
                                                        </li>
                                                        <?php
                                                            // ACF поле PDF файла
                                                            $leg_pdf = '';

                                                            $leg_group = get_field('license_legislation', $post_id);
                                                            if (is_array($leg_group) && !empty($leg_group['license_legislation_pdf_1'])) {
                                                                $leg_pdf = trim((string) $leg_group['license_legislation_pdf_1']);
                                                            }

                                                            // fallback — прямое мета-поле (на случай, если группа хранится иначе)
                                                            if ($leg_pdf === '') {
                                                                $meta_pdf = get_post_meta($post_id, 'license_legislation_pdf_1', true);
                                                                if (!empty($meta_pdf)) {
                                                                    $leg_pdf = trim((string) $meta_pdf);
                                                                }
                                                            }

                                                            // безопасный вывод: если пусто — оставляем '#'
                                                            $pdf_href = $leg_pdf !== '' ? esc_url($leg_pdf) : '';
                                                        ?>
                                                        <li>
                                                            <img src="<?php bloginfo('template_url')?>/assets/img/pdf_icon.svg" alt="PDF">
                                                            <a href="<?php echo $pdf_href; ?>" target="_blank" rel="noopener noreferrer" class="license__leg-link">
                                                                ПОСТАНОВЛЕНИЕ от 28 декабря 2024 г. N 1163-пп «О ТЕРРИТОРИАЛЬНОЙ ПРОГРАММЕ ГОСУДАРСТВЕННЫХ ГАРАНТИЙ
                                                                БЕСПЛАТНОГО ОКАЗАНИЯ ГРАЖДАНАМ МЕДИЦИНСКОЙ ПОМОЩИ В ИРКУТСКОЙ ОБЛАСТИ НА 2025 ГОД И НА ПЛАНОВЫЙ ПЕРИОД 2026 И
                                                                2027 ГОДОВ».
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <img src="<?php bloginfo('template_url')?>/assets/img/pdf_icon.svg" alt="PDF">
                                                            <a href="<?php bloginfo('template_url')?>/assets/pdf/9_Распоряжение_Правительства_РФ_от_12_10_2019_N_2406_р_Об_утверждении.pdf" target="_blank" class="license__leg-link">
                                                                Распоряжение Правительства РФ от 12.10.2019 N 2406-р «Об утверждении перечня жизненно необходимых и
                                                                важнейших лекарственных препаратов, а также перечней лекарственных препаратов для медицинского
                                                                применения»
                                                            </a>
                                                        </li>
                                                    </ul>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <div class="doc-panel" role="tabpanel">
                                        <p class="license__content-text bold">Данные не найдены</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Блок лицензий -->
                    <div class="license__doc">
                        <div class="license__doc-head">
                            <h2 class="contacts__title page-title">
                                ЛИЦЕНЗИЯ НА ОСУЩЕСТВЛЕНИЕ МЕДИЦИНСКОЙ ДЕЯТЕЛЬНОСТИ
                            </h2>
                            <div class="specialists__buttons slider-buttons">
                                <button class="specialists__buttons-arrow specialistsSwiper-prev slider-buttons__item prev" type="button" aria-label="prev">
                                    <svg width="16" height="16" viewBox="0 0 11 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M10 1L2 5L10 9" stroke="#404040" />
                                    </svg>
                                </button>
                                <button class="specialists__buttons-arrow specialistsSwiper-next slider-buttons__item next" type="button" aria-label="next">
                                    <svg width="16" height="16" viewBox="0 0 11 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M10 1L2 5L10 9" stroke="#404040" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="license__slider">
                            <div class="swiper licenseSwiper">
                                <div class="swiper-wrapper">
                                    <?php
                                    // Собираем URL всех изображений лицензий
                                    $images_urls = array();

                                    if ( $contacts_query->have_posts() ) :
                                        while ( $contacts_query->have_posts() ) : $contacts_query->the_post();
                                            $post_id = get_the_ID();

                                            // Возможные варианты хранения:
                                            $license_field = get_field( 'contacts_license_img', $post_id );

                                            // Вспомогательная функция для получения URL из разных форматов
                                            $get_url = function( $item ) {
                                                if ( empty( $item ) ) return '';
                                                // ID
                                                if ( is_int( $item ) || ctype_digit( (string) $item ) ) {
                                                    return wp_get_attachment_image_url( (int) $item, 'full' );
                                                }
                                                // ACF array (['url'], ['sizes'], ['ID'])
                                                if ( is_array( $item ) ) {
                                                    if ( ! empty( $item['url'] ) ) return $item['url'];
                                                    if ( ! empty( $item['sizes'] ) && ! empty( $item['sizes']['full'] ) ) return $item['sizes']['full'];
                                                    if ( ! empty( $item['ID'] ) ) return wp_get_attachment_image_url( (int) $item['ID'], 'full' );
                                                    return '';
                                                }
                                                // string (прямая ссылка)
                                                if ( is_string( $item ) ) return $item;
                                                return '';
                                            };

                                            // 1) Если ACF вернул структуру с ключами contacts_license_img_1..N (associative array)
                                            if ( is_array( $license_field ) && ! empty( $license_field ) ) {
                                                // если массив — проверим на ключи contacts_license_img_1..14
                                                $found = false;
                                                for ( $m = 1; $m <= 14; $m++ ) {
                                                    $key = 'contacts_license_img_' . $m;
                                                    $val = '';
                                                    if ( isset( $license_field[ $key ] ) && ! empty( $license_field[ $key ] ) ) {
                                                        $val = $license_field[ $key ];
                                                    }
                                                    // fallback — если license_field — список (numeric keys)
                                                    if ( empty( $val ) && isset( $license_field[ $m - 1 ] ) ) {
                                                        $val = $license_field[ $m - 1 ];
                                                    }
                                                    // ещё fallback на прямое meta
                                                    if ( empty( $val ) ) {
                                                        $val = get_post_meta( $post_id, $key, true );
                                                    }
                                                    $url = $get_url( $val );
                                                    if ( $url ) {
                                                        $images_urls[] = $url;
                                                        $found = true;
                                                    }
                                                }
                                                // Если license_field — не в формате contacts_license_img_1..N, но является списком url/array
                                                if ( ! $found ) {
                                                    foreach ( $license_field as $item ) {
                                                        $url = $get_url( $item );
                                                        if ( $url ) $images_urls[] = $url;
                                                    }
                                                }
                                            } else {
                                                // 2) Если поле — простая строка (одна картинка) или пусто
                                                $single = get_post_meta( $post_id, 'contacts_license_img', true );
                                                if ( empty( $single ) && is_string( $license_field ) && $license_field !== '' ) {
                                                    $single = $license_field;
                                                }
                                                $url = $get_url( $single );
                                                if ( $url ) $images_urls[] = $url;
                                            }

                                        endwhile;
                                        // вернём указатель (в оригинальном коде wp_reset_postdata() вызывался ниже — но безопасно сделать здесь)
                                        wp_reset_postdata();
                                    endif;

                                    // Очистим дубликаты и пустые значения
                                    $images_urls = array_values( array_filter( array_unique( array_map( 'esc_url_raw', $images_urls ) ) ) );

                                    // Если вообще ничего не найдено — добавим заглушку (файл-изображение).
                                    if ( empty( $images_urls ) ) {
                                        $images_urls[] = get_template_directory_uri() . '/assets/img/license_default.jpg';
                                    }

                                    // Выводим слайды: видимая картинка — icon-pdf.svg, а href ведёт на реальное изображение
                                    foreach ( $images_urls as $idx => $img_url ) : ?>
                                        <div class="swiper-slide">
                                            <div class="license__slider-item">
                                                <a href="<?php echo esc_url( $img_url ); ?>"
                                                    data-fancybox="license-gallery"
                                                    data-thumb="<?php echo esc_url( $img_url ); ?>"
                                                    data-type="image"
                                                    data-caption="<?php echo esc_attr( 'Лицензия ' . ( $idx + 1 ) ); ?>"
                                                    aria-label="<?php echo esc_attr( 'Открыть лицензию ' . ( $idx + 1 ) ); ?>">
                                                        <img src="<?php bloginfo('template_url'); ?>/assets/img/icon-pdf.svg"
                                                            alt="<?php echo esc_attr( 'Лицензия ' . ( $idx + 1 ) ); ?>">
                                                </a>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div> <!-- .swiper-wrapper -->
                            </div> <!-- .swiper -->
                        </div> <!-- .license__slider -->

                    </div><!-- end license__doc -->

                </div> <!-- license__inner -->
            </div> <!-- .container -->
        </section>

        <?php get_template_part('template-parts/tour-or-animals-map'); ?>

    </div>
</main>

<?php get_footer(); ?>