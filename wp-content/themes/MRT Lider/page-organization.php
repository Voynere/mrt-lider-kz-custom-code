<?php
/*
Template Name: organization
*/

// --- Список валидных слагов городов (должен совпадать с header.php и JS) ---
$known_city_slugs = array(
    'almaty', 'astana', 'karaganda', 'taldykorgan', 'almaty_aubakirova'
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

        <section class="organization">
            <div class="container">
                <div class="organization__inner">
                    <h1 class="contacts__title page-title">ИНФОРМАЦИЯ О МЕДИЦИНСКОЙ ОРГАНИЗАЦИИ</h1>

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

                    <div class="organization__content">
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
                                            <div class="organization__content-head">
                                                <div class="organization__content-box">
                                                    <p class="organization__content-text bold">
                                                        <?php echo esc_html( $vf['address'] ?: 'Адрес не указан' ); ?></p>
                                                    <p class="organization__content-text bold">ОГРН:
                                                        <?php echo esc_html( $vf['ogrn'] ?: 'не указан' ); ?></p>
                                                    <p class="organization__content-text bold">ИНН/КПП:
                                                        <?php echo esc_html( $vf['inn'] ?: 'не указан' ); ?></p>
                                                </div>
                                            </div>

                                            <div class="organization__content-body">
                                                <?php
                                                    // --- Статика (6 элементов, одинаковы для всех городов/филиалов) ---
                                                    // Замените 'path/to/your.pdf' на реальные файлы в вашей теме/медиатеке.
                                                    // Можно оставить пустую строку или '#' — тогда элемент не будет выводиться.
                                                    $static_items = array(
                                                        array('title' => 'Порядок оплат медицинских услуг', 'url' => get_template_directory_uri() . '/assets/pdf/payment_rules.pdf'),
                                                        array('title' => 'Подготовка к МРТ диагностике', 'url' => get_template_directory_uri() . '/assets/pdf/prep_mrt.pdf'),
                                                        array('title' => 'Политика обработки персональных данных', 'url' => get_template_directory_uri() . '/assets/pdf/privacy_policy.pdf'),
                                                        array('title' => 'Правила внутреннего распорядка потребительских услуг', 'url' => get_template_directory_uri() . '/assets/pdf/internal_rules.pdf'),
                                                        array('title' => 'Договор об оказании услуг', 'url' => get_template_directory_uri() . '/assets/pdf/contract_services.pdf'),
                                                        array('title' => 'Подготовка в КТ', 'url' => get_template_directory_uri() . '/assets/pdf/prep_kt.pdf'),
                                                    );

                                                    // Количество динамических items в каждой группе (по ТЗ — 3)
                                                    $dyn_per_group = 3;

                                                    // Убедимся, что есть $post_id (если не определён, получим из $contacts_query)
                                                    if ( empty( $post_id ) && isset( $contacts_query ) && $contacts_query->have_posts() ) {
                                                        $contacts_query->the_post();
                                                        $post_id = get_the_ID();
                                                        wp_reset_postdata();
                                                    }

                                                    // Получим родительскую группу (если ACF настроен именно так)
                                                    $org_group = $post_id ? get_field( 'organization_info', $post_id ) : null;

                                                    // Внутри foreach($visible_filials as $i => $vf) — мы уже в нужном контексте
                                                    // строим 9 ссылок в порядке: 1 (динамический) , 2..5 (статичные первые 4) , 6 (динамический) , 7 (статичная) , 8 (динамический) , 9 (статичная)
                                                    // Однако вы говорили, что 6 статичных и 3 динамичных: приведу маппинг как в вашем примере:
                                                    // Позиции: 1 => dyn (О медицинской организации — + label), 2 => stat[0], 3 => stat[1], 4 => stat[2], 5 => stat[3], 6 => dyn (Сведения о специалистах), 7 => stat[4], 8 => dyn (Контакты контролирующих органов), 9 => stat[5]

                                                    // Функция-утилита: получить URL поля с учетом разных возможных хранений
                                                    $extract_url = function( $val, $post_id = 0 ) {
                                                        if ( empty( $val ) ) return '';
                                                        // ID
                                                        if ( is_int( $val ) || ctype_digit( (string) $val ) ) {
                                                            return wp_get_attachment_url( (int) $val );
                                                        }
                                                        // ACF array
                                                        if ( is_array( $val ) ) {
                                                            if ( ! empty( $val['url'] ) ) return $val['url'];
                                                            if ( ! empty( $val['sizes'] ) ) {
                                                                // выберем largest available
                                                                if ( ! empty( $val['sizes']['full'] ) ) return $val['sizes']['full'];
                                                                if ( ! empty( $val['sizes']['large'] ) ) return $val['sizes']['large'];
                                                                if ( ! empty( $val['sizes']['medium'] ) ) return $val['sizes']['medium'];
                                                                if ( ! empty( $val['sizes']['thumbnail'] ) ) return $val['sizes']['thumbnail'];
                                                            }
                                                            if ( ! empty( $val['ID'] ) ) return wp_get_attachment_url( (int) $val['ID'] );
                                                            return '';
                                                        }
                                                        // string
                                                        if ( is_string( $val ) ) return trim( $val );
                                                        return '';
                                                    };

                                                    // Получаем ключ группы для текущего филиала
                                                    $subgroup_key = 'organization_info_' . intval( $vf['index'] );

                                                    // Попытаемся достать подгруппу несколькими способами:
                                                    $subgroup = null;
                                                    if ( is_array( $org_group ) && isset( $org_group[ $subgroup_key ] ) ) {
                                                        $subgroup = $org_group[ $subgroup_key ];
                                                    }
                                                    // Если не нашли в parent-group — попробуем получить отдельным get_field
                                                    if ( empty( $subgroup ) && $post_id ) {
                                                        $subgroup = get_field( $subgroup_key, $post_id ); // ACF: organization_info_1
                                                    }
                                                    // Если всё ещё пусто — попытка через get_post_meta (fallback)
                                                    if ( empty( $subgroup ) && $post_id ) {
                                                        // возможно поля хранятся как: organization_info_1_organization_info_item_1
                                                        $tmp = array();
                                                        for ( $k = 1; $k <= $dyn_per_group; $k++ ) {
                                                            $meta_key = $subgroup_key . '_organization_info_item_' . $k;
                                                            $v = get_post_meta( $post_id, $meta_key, true );
                                                            if ( $v ) $tmp[ 'organization_info_item_' . $k ] = $v;
                                                        }
                                                        if ( ! empty( $tmp ) ) $subgroup = $tmp;
                                                    }

                                                    // Подготовим массив динамических URL (organization_info_item_1..3)
                                                    $dynamic_urls = array();
                                                    for ( $m = 1; $m <= $dyn_per_group; $m++ ) {
                                                        $dyn_key = 'organization_info_item_' . $m;
                                                        $url = '';

                                                        if ( is_array( $subgroup ) && isset( $subgroup[ $dyn_key ] ) ) {
                                                            $url = $extract_url( $subgroup[ $dyn_key ], $post_id );
                                                        }

                                                        // fallback: если subgroup — список numeric keys
                                                        if ( empty( $url ) && is_array( $subgroup ) && isset( $subgroup[ $m - 1 ] ) ) {
                                                            $url = $extract_url( $subgroup[ $m - 1 ], $post_id );
                                                        }

                                                        // fallback: прямое метаполе organization_info_{N}_organization_info_item_{m}
                                                        if ( empty( $url ) && $post_id ) {
                                                            $meta_key = $subgroup_key . '_organization_info_item_' . $m;
                                                            $meta_val = get_post_meta( $post_id, $meta_key, true );
                                                            $url = $extract_url( $meta_val, $post_id );
                                                        }

                                                        $dynamic_urls[ $m ] = $url; // пустая строка если не заполнено
                                                    }

                                                    // Теперь составим итоговую последовательность элементов (9 штук)
                                                    // Позиции: 1(dyn1), 2(stat0), 3(stat1), 4(stat2), 5(stat3), 6(dyn2), 7(stat4), 8(dyn3), 9(stat5)
                                                    $sequence = array(
                                                        array('type' => 'dyn', 'dyn_index' => 1, 'title' => 'О медицинской организации'),
                                                        array('type' => 'stat', 'stat_index' => 0),
                                                        array('type' => 'stat', 'stat_index' => 1),
                                                        array('type' => 'stat', 'stat_index' => 2),
                                                        array('type' => 'stat', 'stat_index' => 3),
                                                        array('type' => 'dyn', 'dyn_index' => 2, 'title' => 'Сведения о специалистах'),
                                                        array('type' => 'stat', 'stat_index' => 4),
                                                        array('type' => 'dyn', 'dyn_index' => 3, 'title' => 'Контакты контролирующих органов'),
                                                        array('type' => 'stat', 'stat_index' => 5),
                                                    );

                                                    // Выводим элементы — рендерим только те, у которых есть URL (статичные — если URL не пустой)
                                                    foreach ( $sequence as $pos ) :

                                                        if ( $pos['type'] === 'stat' ) :
                                                            $stat = $static_items[ $pos['stat_index'] ] ?? null;
                                                            if ( $stat && ! empty( $stat['url'] ) ) : ?>
                                                                <a href="<?php echo esc_url( $stat['url'] ); ?>" class="organization__item" target="_blank" rel="noopener noreferrer">
                                                                    <img src="<?php bloginfo('template_url')?>/assets/img/pdf-organization.svg" class="organization__item-img" alt="">
                                                                    <p class="organization__item-text"><?php echo esc_html( $stat['title'] ); ?></p>
                                                                </a>
                                                            <?php
                                                            endif;

                                                        else: // dyn
                                                            $idx = intval( $pos['dyn_index'] );
                                                            $dyn_url = isset( $dynamic_urls[ $idx ] ) ? $dynamic_urls[ $idx ] : '';
                                                            if ( $dyn_url ) :
                                                                // подпись: для первого динамического элемента добавляем label вкладки (короткий вариант)
                                                                if ( $idx === 1 ) {
                                                                    $label_short = ! empty( $vf['label'] ) ? $vf['label'] : '';
                                                                    $title = trim( $pos['title'] . ( $label_short ? ' — ' . $label_short : '' ) );
                                                                } else {
                                                                    $title = $pos['title'];
                                                                }
                                                                ?>
                                                                <a href="<?php echo esc_url( $dyn_url ); ?>" class="organization__item" target="_blank" rel="noopener noreferrer">
                                                                    <img src="<?php bloginfo('template_url')?>/assets/img/pdf-organization.svg" class="organization__item-img" alt="">
                                                                    <p class="organization__item-text"><?php echo esc_html( $title ); ?></p>
                                                                </a>
                                                            <?php
                                                            endif;
                                                        endif;

                                                    endforeach;
                                                ?>
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
                    </div><!-- end organization__content -->

                </div>
            </div>
        </section>

        <div class="tour">
            <div class="container">
                <div class="tour__inner">
                    <img src="<?php bloginfo('template_url')?>/assets/img/3d_tour.jpg" alt="">
                    <a href="#" class="tour__content">
                        <h2 class="tour__title"><span>ПРОЙДИТЕ 3D ТУР</span> <span>ПО КЛИНИКЕ</span> <span>«МРТ ЛИДЕР»</span></h2>
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