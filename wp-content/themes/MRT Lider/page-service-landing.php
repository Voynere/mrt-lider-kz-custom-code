<?php
/*
Template Name: service-landing (virtual)
Called via template_redirect for /{city}/services/{service-slug}/
*/

// Load service-specific content data
require_once __DIR__ . '/service-content.php';

$selected_city = mrt_get_selected_city_slug();

$path_parts = mrt_get_request_path_parts();

// --- Телефон города ---
$mrt_city_phone = function_exists('mrt_get_city_phone') ? mrt_get_city_phone($selected_city) : ['raw' => '+7 (3452) 500-735', 'href' => 'tel:+73452500735'];
$mrt_phone_raw = $mrt_city_phone['raw'] ?: '+7 (3452) 500-735';
$mrt_phone_href = $mrt_city_phone['href'] ?: 'tel:+73452500735';

// --- Определяем slug поста из URL (3-й сегмент: /city/services/{slug}/) ---
// URL pattern: /{city}/services/{service-post-slug}/
$service_slug = get_query_var('mrt_service_landing');
if ($service_slug === '' || $service_slug === false) {
    $service_slug = $path_parts[2] ?? '';
}
$service_slug = sanitize_title((string) $service_slug);
if (!$service_slug) {
    get_header();
    echo '<main class="main"><div class="container"><p>Услуга не указана</p></div></main>';
    get_footer();
    exit;
}

// --- Получаем пост услуги ---
$svc_post = null;
// Try as service CPT first
$posts = get_posts([
    'post_type' => 'service',
    'posts_per_page' => 1,
    'name' => $service_slug,
    'post_status' => 'publish',
]);
if (!empty($posts)) {
    $svc_post = $posts[0];
} else {
    // Try case-insensitive slug matching
    global $wpdb;
    $svc_post_id = $wpdb->get_var($wpdb->prepare(
        "SELECT ID FROM {$wpdb->posts} WHERE post_type='service' AND post_status='publish' AND post_name=%s LIMIT 1",
        $service_slug
    ));
    if ($svc_post_id) $svc_post = get_post($svc_post_id);
}

if (!$svc_post) {
    get_header();
    echo '<main class="main"><div class="container"><p>Услуга не найдена</p></div></main>';
    get_footer();
    exit;
}

// --- Мета-данные услуги ---
$svc_price = get_post_meta($svc_post->ID, 'si_price', true);
$svc_discount = get_post_meta($svc_post->ID, 'si_discount', true);
$svc_category = get_post_meta($svc_post->ID, 'si_category', true) ?? $svc_post->post_title;
$svc_oblast_raw = get_post_meta($svc_post->ID, 'si_oblast', true) ?? $svc_post->post_title;
$svc_names = mrt_service_display_names($svc_oblast_raw, $svc_category);
$svc_oblast = $svc_names['short'];
$svc_oblast_full = $svc_names['full'];

// --- Service type ---
$service_type_terms = wp_get_post_terms($svc_post->ID, 'service_type');
$service_type = !empty($service_type_terms) ? $service_type_terms[0]->name : 'Прочее';
$service_type_slug = !empty($service_type_terms) ? $service_type_terms[0]->slug : '';
$show_concessional_notice = mrt_should_show_concessional_price_notice(
    $selected_city,
    mrt_service_item_has_discounted_price($svc_price, $svc_discount, $selected_city, $service_type_slug, $service_type)
);

// --- Service-specific content (FAQ, preparation, etc.) ---
$svc_content = mrt_finalize_service_content(
    mrt_get_service_content($svc_category ?: $svc_oblast, $service_type, $svc_oblast),
    $svc_oblast,
    $svc_category
);

// --- Город в предложном падеже ---
$city_gen = mrt_seo_city_genitive($selected_city);

// --- Валюта ---
$kz_cities = mrt_get_kz_cities();
$use_tenge = in_array($selected_city, $kz_cities, true);
$currency = $use_tenge ? '₸' : '₽';

// --- Филиальные особенности (ОМС/ДМС и т.п.) ---
$branch_features = mrt_city_branch_features($selected_city);
$equipment_phrase = mrt_city_mri_equipment_phrase($selected_city);
$equipment_label = mrt_city_mri_equipment_label($selected_city);
$equipment_benefit_desc = mrt_city_mri_equipment_benefit_desc($selected_city);
$insurance_label = mrt_city_insurance_label($branch_features);
$insurance_hint = mrt_city_insurance_hint($branch_features);

// --- Похожие услуги (из той же подкатегории si_category, для того же города) ---
$branch_term = mrt_get_branch_term_for_city($selected_city);

$related_services = [];
if ($branch_term && $svc_category) {
    $all_city = get_posts([
        'post_type' => 'service', 'posts_per_page' => -1, 'post_status' => 'publish',
        'exclude' => [$svc_post->ID],
        'tax_query' => [['taxonomy' => 'branch', 'field' => 'slug', 'terms' => $branch_term->slug]],
    ]);
    $svc_cat_lower = mb_strtolower($svc_category, 'UTF-8');
    foreach ($all_city as $rsvc) {
        $rcat = get_post_meta($rsvc->ID, 'si_category', true);
        if (!$rcat) continue;
        $rcat_lower = mb_strtolower($rcat, 'UTF-8');
        // Match same subcategory (same si_category)
        if ($rcat_lower === $svc_cat_lower || strpos($rcat_lower, $svc_cat_lower) !== false) {
            $related_services[] = $rsvc;
        }
    }
}

get_header();
?>

<main class="main">
    <div class="main-background">
        <?php if (!is_front_page()) custom_breadcrumbs_service_landing($svc_oblast, $svc_category, $selected_city); ?>

        <section class="service-landing sl-hero">
            <div class="container">
                <h1 class="sl-hero__h1">
                    <?php echo esc_html($svc_oblast . ' в ' . $city_gen); ?>
                </h1>
                <p class="sl-hero__type">
                    <?php echo esc_html($service_type); ?>
                </p>

                <div class="sl-hero__grid">
                    <!-- Описание + цена -->
                    <div>
                        <div class="sl-price">
                            <?php echo mrt_render_price_hero_block($svc_price, $svc_discount, $currency, $selected_city, 'sl-price', $service_type_slug, $service_type); ?>
                        </div>
                        <?php if ($show_concessional_notice): ?>
                            <?php echo mrt_render_concessional_price_notice($selected_city); ?>
                        <?php endif; ?>

                        <!-- Рейтинг из Яндекс Справочника -->
                        <?php
                        $branch_rating = mrt_seo_get_branch_rating($selected_city);
                        if ($branch_rating):
                        ?>
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px;">
                            <div style="display:flex;gap:2px;">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="<?php echo $i <= $branch_rating['rating'] ? '#fbbf24' : '#d1d5db'; ?>">
                                        <path d="M8 0l2.5 5.1L16 5.9l-4 3.9.9 5.5L8 12.7l-4.9 2.6.9-5.5-4-3.9 5.5-.8z"/>
                                    </svg>
                                <?php endfor; ?>
                            </div>
                            <span style="font-size:14px;font-weight:600;color:#1f2937;"><?php echo esc_html($branch_rating['rating']); ?></span>
                            <span style="font-size:13px;color:#6b7280;"><?php echo esc_html(number_format($branch_rating['count'], 0, '', ' ')); ?> отзывов</span>
                        </div>
                        <?php endif; ?>

                        <p class="sl-desc"><?php echo esc_html($svc_oblast); ?> в медицинском центре МРТ Лидер в <?php echo esc_html($city_gen); ?>. <?php echo esc_html(ucfirst($equipment_phrase)); ?>, опытные врачи, быстрые результаты. Запись онлайн, без очередей.</p>
                    </div>

                    <!-- CTA карточка -->
                    <div class="sl-cta-card">
                        <div class="sl-cta-card__title">Записаться на исследование</div>
                        <a href="<?php echo esc_url($mrt_phone_href); ?>" class="sl-cta-card__phone" data-mrt-phone="landing"><?php echo esc_html($mrt_phone_raw); ?></a>
                        <div class="sl-cta-card__hint">Звонок по России</div>
                        <?php
                        // --- Urgency-блок: ближайшее окно записи ---
                        $urgency_text = mrt_city_booking_urgency_text($selected_city);
                        if ($urgency_text === null) {
                        $tz_offset = 5 * 3600; // UTC+5
                        $now_ts = time() + $tz_offset;
                        $day_of_week = (int) gmdate('w', $now_ts); // 0=Sun..6=Sat
                        $current_hour = (int) gmdate('G', $now_ts);
                        $is_weekday = ($day_of_week >= 1 && $day_of_week <= 5);
                        $is_saturday = ($day_of_week === 6);
                        $is_business_hours = ($is_weekday && $current_hour >= 8 && $current_hour < 18)
                            || ($is_saturday && $current_hour >= 9 && $current_hour < 15);
                        $ru_days = [0 => 'Воскресенье', 1 => 'Понедельник', 2 => 'Вторник', 3 => 'Среда', 4 => 'Четверг', 5 => 'Пятница', 6 => 'Суббота'];
                        if ($is_business_hours) {
                            $urgency_text = 'Ближайшее окно: сегодня';
                        } else {
                            // Найти следующий рабочий день
                            $days_ahead = 0;
                            $search_ts = $now_ts;
                            for ($d = 1; $d <= 8; $d++) {
                                $check_ts = $now_ts + $d * 86400;
                                $check_dow = (int) gmdate('w', $check_ts);
                                if ($check_dow >= 1 && $check_dow <= 6) { // Пн-Сб рабочие
                                    $days_ahead = $d;
                                    break;
                                }
                            }
                            if ($days_ahead === 1) {
                                $next_ts = $now_ts + 86400;
                                $urgency_text = 'Запись на ' . mb_strtolower($ru_days[(int) gmdate('w', $next_ts)], 'UTF-8');
                            } else {
                                $urgency_text = 'Завтра с 8:00';
                                // Если завтра воскресенье — «В ближайший рабочий день»
                                $tomorrow_dow = (int) gmdate('w', $now_ts + 86400);
                                if ($tomorrow_dow === 0) {
                                    $urgency_text = 'В ближайший рабочий день';
                                }
                            }
                        }
                        }
                        ?>
                        <div class="sl-urgency"><?php echo esc_html($urgency_text); ?></div>
                        <a href="#" class="sl-cta-card__btn booking-btn" data-ab-variant="a">Записаться онлайн</a>
                    </div>
                </div>

                <!-- Схема: как проходит процедура -->
                <div class="sl-steps">
                    <h2 class="sl-steps__title">Как проходит <?php echo esc_html($svc_oblast_full); ?></h2>
                    <div class="sl-steps__grid">
                        <div class="sl-step">
                            <div class="sl-step__num">1</div>
                            <div class="sl-step__title">Запись</div>
                            <div class="sl-step__desc">По телефону или онлайн</div>
                        </div>
                        <div class="sl-step">
                            <div class="sl-step__num">2</div>
                            <div class="sl-step__title">Подготовка</div>
                            <div class="sl-step__desc">Снять металл, удобная одежда</div>
                        </div>
                        <div class="sl-step">
                            <div class="sl-step__num">3</div>
                            <div class="sl-step__title">Исследование</div>
                            <div class="sl-step__desc"><?php echo esc_html($svc_content['duration']); ?></div>
                        </div>
                        <div class="sl-step">
                            <div class="sl-step__num">4</div>
                            <div class="sl-step__title">Результат</div>
                            <div class="sl-step__desc"><?php echo esc_html($svc_content['result']); ?></div>
                        </div>
                    </div>
                </div>

                <!-- Trust-бар -->
                <div class="sl-trust-bar">
                    <div class="sl-trust-bar__item">
                        <div class="sl-trust-bar__icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        </div>
                        <div class="sl-trust-bar__text">
                            <strong>Результат за 1 час</strong>
                            <span>Заключение в день обращения</span>
                        </div>
                    </div>
                    <?php if ($insurance_label): ?>
                    <div class="sl-trust-bar__item">
                        <div class="sl-trust-bar__icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        </div>
                        <div class="sl-trust-bar__text">
                            <strong><?php echo esc_html($insurance_label); ?></strong>
                            <span><?php echo esc_html($insurance_hint); ?></span>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="sl-trust-bar__item">
                        <div class="sl-trust-bar__icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                        </div>
                        <div class="sl-trust-bar__text">
                            <strong><?php echo esc_html($equipment_label); ?></strong>
                            <span>Томографы экспертного класса</span>
                        </div>
                    </div>
                </div>

                <!-- Что это за исследование -->
                <?php if (!empty($svc_content['what_is'])): ?>
                <div class="sl-section">
                    <h2 class="sl-section__title">Что такое <?php echo esc_html($svc_oblast); ?>?</h2>
                    <p class="sl-section__text"><?php echo esc_html($svc_content['what_is']); ?></p>
                </div>
                <?php endif; ?>

                <!-- Показания -->
                <?php if (!empty($svc_content['indications'])): ?>
                <div class="sl-section">
                    <h2 class="sl-section__title">Показания к <?php echo esc_html($svc_oblast); ?></h2>
                    <ul class="sl-section__list">
                        <?php foreach ($svc_content['indications'] as $ind): ?>
                            <li><?php echo esc_html($ind); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <!-- Подготовка -->
                <?php if (!empty($svc_content['preparation'])): ?>
                <div class="sl-box sl-box--warn">
                    <h2 class="sl-section__title">Подготовка к <?php echo esc_html($svc_oblast); ?></h2>
                    <p class="sl-section__text"><?php echo esc_html($svc_content['preparation']); ?></p>
                </div>
                <?php endif; ?>

                <!-- Противопоказания -->
                <?php if (!empty($svc_content['contraindications'])): ?>
                <div class="sl-box sl-box--danger">
                    <h2 class="sl-section__title">Противопоказания к <?php echo esc_html($svc_oblast); ?></h2>
                    <ul class="sl-section__list">
                        <?php foreach ($svc_content['contraindications'] as $contra): ?>
                            <li><?php echo esc_html($contra); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <!-- Long-tail SEO блоки (стоимость, подготовка, процедура, противопоказания) -->
                <?php
                if (function_exists('mrt_get_service_longtail_blocks')) {
                    $longtail_blocks = mrt_get_service_longtail_blocks($svc_category ?: $svc_oblast, $service_type, $selected_city, $svc_content, $svc_oblast, (int) $svc_price, (int) $svc_discount);
                    foreach ($longtail_blocks as $block):
                ?>
                <div class="sl-section sl-longtail">
                    <<?php echo esc_attr($block['heading_level']); ?> class="sl-section__title"><?php echo esc_html($block['title']); ?></<?php echo esc_attr($block['heading_level']); ?>>
                    <div class="sl-section__text"><?php echo wp_kses_post($block['content']); ?></div>
                </div>
                <?php
                    endforeach;
                }
                ?>

                <!-- FAQ -->
                <?php if (!empty($svc_content['faq'])): ?>
                <div class="sl-faq">
                    <h2 class="sl-faq__title">Часто задаваемые вопросы о <?php echo esc_html($svc_oblast); ?></h2>
                    <div class="sl-faq__list">
                        <?php foreach ($svc_content['faq'] as $i => $item): ?>
                        <details class="sl-faq__item" <?php echo $i === 0 ? 'open' : ''; ?>>
                            <summary class="sl-faq__q">
                                <span><?php echo esc_html($item['q']); ?></span>
                                <span class="sl-faq__arrow">▾</span>
                            </summary>
                            <p class="sl-faq__a"><?php echo esc_html($item['a']); ?></p>
                        </details>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Почему выбирают МРТ Лидер -->
                <div class="sl-benefits">
                    <h2 class="sl-benefits__title">Почему выбирают МРТ Лидер</h2>
                    <div class="sl-benefits__grid">
                        <div class="sl-benefit">
                            <div class="sl-benefit__icon">🏥</div>
                            <div class="sl-benefit__title">Современное оборудование</div>
                            <div class="sl-benefit__desc"><?php echo esc_html($equipment_benefit_desc); ?></div>
                        </div>
                        <div class="sl-benefit">
                            <div class="sl-benefit__icon">👨‍⚕️</div>
                            <div class="sl-benefit__title">Опытные врачи</div>
                            <div class="sl-benefit__desc">Рентгенологи с опытом от 10 лет</div>
                        </div>
                        <div class="sl-benefit">
                            <div class="sl-benefit__icon">⏱️</div>
                            <div class="sl-benefit__title">Быстрый результат</div>
                            <div class="sl-benefit__desc">Заключение в день обращения, за 1–2 часа</div>
                        </div>
                        <div class="sl-benefit">
                            <div class="sl-benefit__icon">📞</div>
                            <div class="sl-benefit__title">Бесплатная консультация</div>
                            <div class="sl-benefit__desc"><?php echo esc_html($mrt_phone_raw); ?> — звонок по всей России</div>
                        </div>
                        <div class="sl-benefit">
                            <div class="sl-benefit__icon">📍</div>
                            <div class="sl-benefit__title">34 города</div>
                            <div class="sl-benefit__desc">Сеть диагностических центров по всей России и Казахстану</div>
                        </div>
                        <div class="sl-benefit">
                            <div class="sl-benefit__icon">💰</div>
                            <div class="sl-benefit__title">Доступные цены</div>
                            <div class="sl-benefit__desc"><?php echo esc_html(mrt_city_discount_benefit_desc($selected_city)); ?></div>
                        </div>
                    </div>
                </div>

                <!-- Похожие услуги -->
                <?php if (!empty($related_services)): ?>
                <div class="sl-related">
                    <h2 class="sl-related__title">Другие услуги в категории «<?php echo esc_html($svc_category); ?>»</h2>
                    <div class="sl-related__grid">
                        <?php foreach (array_slice($related_services, 0, 8) as $rel): 
                            $rel_price = get_post_meta($rel->ID, 'si_price', true);
                            $rel_url = home_url('/' . $selected_city . '/services/' . $rel->post_name . '/');
                        ?>
                            <a href="<?php echo esc_url($rel_url); ?>" class="sl-related__item">
                                <span class="sl-related__name"><?php echo esc_html(mrt_get_service_oblast_display($rel->ID)); ?></span>
                                <span class="sl-related__price">
                                    <?php echo esc_html(mrt_format_service_price_display($rel_price, get_post_meta($rel->ID, 'si_discount', true), $currency)); ?>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- CTA блок -->
                <div class="sl-banner">
                    <h2 class="sl-banner__title">Запишитесь на <?php echo esc_html($svc_oblast); ?></h2>
                    <p class="sl-banner__text"><?php echo esc_html(ucfirst($equipment_phrase)); ?>, опытные врачи, результат в день обращения</p>
                    <a href="<?php echo esc_url($mrt_phone_href); ?>" class="sl-banner__btn" data-mrt-phone="landing"><?php echo esc_html($mrt_phone_raw); ?></a>
                    <div class="sl-banner__sub">звонок по России</div>
                </div>

                <!-- Schema.org: Service + FAQPage -->
                <?php
                // --- Service + Offer ---
                $schema_service = [
                    '@context' => 'https://schema.org',
                    '@type' => 'Service',
                    'name' => $svc_oblast . ' в ' . $city_gen,
                    'description' => $svc_oblast . ' в медицинском центре МРТ Лидер в ' . $city_gen . '. ' . ucfirst($equipment_phrase) . ', опытные врачи.',
                    'url' => mrt_seo_canonical_url(),
                    'serviceType' => $service_type,
                    'provider' => [
                        '@type' => 'MedicalClinic',
                        'name' => 'МРТ Лидер — ' . ($city_gen ?: $selected_city),
                        'url' => home_url('/' . $selected_city . '/'),
                        'telephone' => $mrt_phone_raw,
                    ],
                ];
                if ($svc_price && is_numeric($svc_price)) {
                    $schema_service['offers'] = [
                        '@type' => 'Offer',
                        'price' => (int) $svc_price,
                        'priceCurrency' => $use_tenge ? 'KZT' : 'RUB',
                        'availability' => 'https://schema.org/InStock',
                        'url' => mrt_seo_canonical_url(),
                    ];
                }
                echo '<script type="application/ld+json">' . json_encode($schema_service, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>' . "\n";

                // FAQPage генерируется в mrt_seo_schema_jsonld() (@graph) — дубль не нужен.
                ?>

                <!-- Sticky CTA (mobile) -->
                <div class="sl-sticky-cta">
                    <div class="sl-sticky-cta__inner">
                        <div class="sl-sticky-cta__price">
                            <?php echo esc_html(mrt_format_service_price_display($svc_price, $svc_discount, $currency)); ?>
                        </div>
                        <a href="<?php echo esc_url($mrt_phone_href); ?>" class="sl-sticky-cta__btn" data-mrt-phone="landing"><?php echo esc_html($mrt_phone_raw); ?></a>
                    </div>
                </div>

                <!-- Форма записи онлайн -->
                <div class="sl-section" style="margin-top:40px;background:#fff;border:1px solid #e5e7eb;border-radius:16px;padding:32px;box-shadow:0 4px 12px rgba(0,0,0,0.04);">
                    <h2 class="sl-section__title" style="text-align:center;">Записаться онлайн</h2>
                    <p class="sl-section__text" style="text-align:center;margin-bottom:24px;">Оставьте заявку — мы перезвоним в течение 15 минут</p>
                    <form id="mrt-landing-booking"
                          class="mrt-inline-booking-form"
                          data-mrt-form-type="service"
                          data-booking-ajax-action="send_booking_form_with_service"
                          data-booking-ajax-url="<?php echo esc_url(admin_url('admin-ajax.php')); ?>"
                          data-booking-nonce="<?php echo esc_attr(wp_create_nonce('booking_form_with_service_nonce')); ?>"
                          data-service-category="<?php echo esc_attr($svc_category); ?>"
                          data-service-oblast="<?php echo esc_attr($svc_oblast); ?>"
                          style="max-width:480px;margin:0 auto;">
                        <input type="hidden" name="city" value="<?php echo esc_attr($selected_city); ?>">
                        <div style="margin-bottom:16px;">
                            <label style="display:block;font-size:14px;font-weight:600;color:#374151;margin-bottom:6px;">Ваше имя</label>
                            <input type="text" name="name" required placeholder="Иван Иванович" class="booking__form-input" style="width:100%;padding:12px 16px;border:1px solid #d1d5db;border-radius:10px;font-size:15px;outline:none;box-sizing:border-box;">
                        </div>
                        <div style="margin-bottom:16px;">
                            <label style="display:block;font-size:14px;font-weight:600;color:#374151;margin-bottom:6px;">Телефон</label>
                            <input type="text" name="phone" required placeholder="Введите телефон" class="booking__form-input" style="width:100%;padding:12px 16px;border:1px solid #d1d5db;border-radius:10px;font-size:15px;outline:none;box-sizing:border-box;">
                        </div>
                        <div style="margin-bottom:16px;">
                            <label style="display:block;font-size:14px;font-weight:600;color:#374151;margin-bottom:6px;">Удобная дата и время</label>
                            <input type="text" name="comment" placeholder="Например: завтра утром, 15 июня в 10:00" style="width:100%;padding:12px 16px;border:1px solid #d1d5db;border-radius:10px;font-size:15px;outline:none;box-sizing:border-box;">
                        </div>
                        <button type="submit" class="sl-cta-card__btn" style="font-size:16px;font-weight:700;">Записаться</button>
                        <p style="font-size:12px;color:#9ca3af;text-align:center;margin-top:12px;">Нажимая кнопку, вы соглашаетесь с политикой конфиденциальности</p>
                    </form>
                </div>

            </div>
        </section>

    </div>
</main>

<?php include __DIR__ . '/template-parts/booking-modal.php'; ?>
<?php get_footer(); ?>
