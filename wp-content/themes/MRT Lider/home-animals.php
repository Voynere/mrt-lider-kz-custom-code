<?php
/*
Template Name: home-animals
*/

$selected_city = mrt_resolve_selected_city('almaty_aubakirova', true);
$branch = mrt_get_branch($selected_city) ?: mrt_get_branch('almaty_aubakirova');

$contact_phone = mrt_get_contact_phone($selected_city);
$whatsapp_href = mrt_get_whatsapp_href($selected_city);
$whatsapp_attrs = ($whatsapp_href !== '#') ? ' target="_blank" rel="noopener noreferrer"' : ' aria-disabled="true"';

$address_full = $branch['address_full'] ?? 'ул. Аубакирова, 17/1, село Отеген батыра, Илийский район, Алматинская область';
$address_short = $branch['address_short'] ?? 'ул. Аубакирова, 17/1';
$theme_uri = get_template_directory_uri();

$service_posts = get_posts(array(
    'post_type'      => 'service',
    'posts_per_page' => -1,
    'fields'         => 'ids',
    'tax_query'      => array(
        array(
            'taxonomy' => 'branch',
            'field'    => 'slug',
            'terms'    => $branch['branch_taxonomy'] ?? $selected_city,
        ),
    ),
));

$service_types = array();
if (!empty($service_posts)) {
    foreach ($service_posts as $post_id) {
        $types = wp_get_post_terms($post_id, 'service_type');
        if (!is_wp_error($types) && !empty($types)) {
            foreach ($types as $type) {
                if (!isset($service_types[$type->term_id])) {
                    $service_types[$type->term_id] = $type;
                }
            }
        }
    }
}

$contacts_query = mrt_get_contacts_query($selected_city);
$map_html = '';
if ($contacts_query->have_posts()) {
    $contacts_query->the_post();
    $map_html = get_field('contacts_map');
    wp_reset_postdata();
}

get_header();
?>

<section class="animals-hero">
    <div class="container">
        <div class="animals-hero__inner">
            <div class="animals-hero__content">
                <span class="animals-hero__badge">Филиал МРТ Лидер</span>
                <h1 class="animals-hero__title">МРТ для животных</h1>
                <p class="animals-hero__lead">
                    Точная и безопасная магнитно-резонансная томография для собак, кошек и других питомцев.
                    Заключение ветеринарного специалиста — в день обследования.
                </p>
                <ul class="animals-hero__features">
                    <li>Аппарат 1,5 Тесла экспертного класса</li>
                    <li>Опытные врачи-диагносты</li>
                    <li>Комфортные условия для питомца и хозяина</li>
                </ul>
                <div class="animals-hero__actions">
                    <button type="button" class="animals-btn animals-btn--primary booking-btn">
                        Записать питомца
                    </button>
                    <a href="<?php echo esc_url($whatsapp_href); ?>" class="animals-btn animals-btn--outline"<?php echo $whatsapp_attrs; ?>>
                        WhatsApp
                    </a>
                </div>
                <p class="animals-hero__address">
                    <strong><?php echo esc_html($address_short); ?></strong><br>
                    <?php echo esc_html($address_full); ?>
                </p>
            </div>
            <div class="animals-hero__visual" aria-hidden="true">
                <div class="animals-hero__paw animals-hero__paw--1"></div>
                <div class="animals-hero__paw animals-hero__paw--2"></div>
                <div class="animals-hero__circle">
                    <span class="animals-hero__emoji">🐾</span>
                    <p>Диагностика<br>без стресса</p>
                </div>
            </div>
        </div>
    </div>
</section>

<main class="main animals-main">
    <section class="animals-section animals-section--services">
        <div class="container">
            <h2 class="animals-section__title">Что диагностируем</h2>
            <div class="animals-cards">
                <article class="animals-card">
                    <span class="animals-card__icon">🐕</span>
                    <h3>Собаки</h3>
                    <p>Суставы, позвоночник, головной мозг, брюшная полость, онкопоиск.</p>
                </article>
                <article class="animals-card">
                    <span class="animals-card__icon">🐈</span>
                    <h3>Кошки</h3>
                    <p>Мягкие ткани, внутренние органы, неврология, травмы и хронические боли.</p>
                </article>
                <article class="animals-card">
                    <span class="animals-card__icon">🐾</span>
                    <h3>Другие питомцы</h3>
                    <p>Индивидуальный подход — уточняйте возможность исследования по телефону.</p>
                </article>
            </div>

            <?php if (!empty($service_types)) : ?>
                <div class="animals-prices">
                    <h3 class="animals-prices__title">Услуги и цены</h3>
                    <ul class="animals-prices__list">
                        <?php foreach ($service_types as $service_type) :
                            $url = site_url('/') . $selected_city . '/uslugi-i-ceny/price/' . $service_type->slug . '/';
                            ?>
                            <li>
                                <a href="<?php echo esc_url($url); ?>">
                                    <?php echo esc_html($service_type->name); ?>
                                    <span>→</span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="animals-section animals-section--prep">
        <div class="container">
            <h2 class="animals-section__title">Как подготовить питомца</h2>
            <ol class="animals-steps">
                <li>
                    <strong>Голодная диета 6–8 часов</strong>
                    <span>Для исследований брюшной полости — по рекомендации врача.</span>
                </li>
                <li>
                    <strong>Седация по показаниям</strong>
                    <span>Для спокойного и неподвижного сканирования — только под контролем специалиста.</span>
                </li>
                <li>
                    <strong>Документы и направление</strong>
                    <span>Паспорт питомца, выписка от лечащего ветврача — при наличии.</span>
                </li>
                <li>
                    <strong>Приезд за 15 минут</strong>
                    <span>Чтобы питомец успел адаптироваться к кабинету до исследования.</span>
                </li>
            </ol>
        </div>
    </section>

    <section class="animals-section animals-section--faq">
        <div class="container">
            <h2 class="animals-section__title">Частые вопросы</h2>
            <div class="animals-faq">
                <details class="animals-faq__item" open>
                    <summary>МРТ безопасно для животных?</summary>
                    <p>Да. МРТ не использует ионизирующее излучение. Исследование проводится на современном аппарате с контролем врача.</p>
                </details>
                <details class="animals-faq__item">
                    <summary>Сколько длится исследование?</summary>
                    <p>Обычно от 20 до 60 минут в зависимости от зоны и необходимости седации.</p>
                </details>
                <details class="animals-faq__item">
                    <summary>Нужно ли направление от ветеринара?</summary>
                    <p>Желательно, но не обязательно. Мы поможем с интерпретацией результатов и передадим заключение вашему ветврачу.</p>
                </details>
                <details class="animals-faq__item">
                    <summary>Где находится центр?</summary>
                    <p><?php echo esc_html($address_full); ?></p>
                </details>
            </div>
        </div>
    </section>

    <section class="animals-section animals-section--contacts">
        <div class="container">
            <div class="animals-contacts">
                <div class="animals-contacts__info">
                    <h2 class="animals-section__title">Контакты филиала</h2>
                    <p class="animals-contacts__address"><?php echo esc_html($address_full); ?></p>
                    <?php if (!empty($contact_phone['number'])) : ?>
                        <a class="animals-contacts__phone" href="<?php echo esc_attr($contact_phone['href']); ?>">
                            <?php echo esc_html($contact_phone['number']); ?>
                        </a>
                    <?php endif; ?>
                    <div class="animals-contacts__actions">
                        <button type="button" class="animals-btn animals-btn--primary booking-btn">Записаться</button>
                        <a href="<?php echo esc_url($whatsapp_href); ?>" class="animals-btn animals-btn--outline"<?php echo $whatsapp_attrs; ?>>WhatsApp</a>
                    </div>
                </div>
                <div class="animals-contacts__map">
                    <?php
                    if (!empty($map_html)) {
                        echo $map_html;
                    } else {
                        ?>
                        <iframe
                            src="https://yandex.ru/map-widget/v1/?text=<?php echo rawurlencode($address_full); ?>&amp;z=15"
                            width="100%"
                            height="360"
                            frameborder="0"
                            allowfullscreen="true"
                            title="Карта филиала МРТ для животных"
                        ></iframe>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
    </section>

    <section class="animals-cta">
        <div class="container">
            <div class="animals-cta__inner">
                <h2>Забота о здоровье питомца начинается с точной диагностики</h2>
                <p>Запишитесь на МРТ — мы ответим на вопросы и подберём удобное время.</p>
                <button type="button" class="animals-btn animals-btn--light booking-btn">Записать питомца</button>
            </div>
        </div>
    </section>
</main>

<?php
$booking_cta = 'Записать питомца на МРТ';
get_template_part('template-parts/booking-modal');
get_footer();
?>
