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

$map_html = mrt_get_animals_map_html($selected_city);

get_header();
?>

<section class="animals-hero">
    <div class="container">
        <div class="animals-hero__inner">
            <div class="animals-hero__content">
                <span class="animals-hero__badge">MRI Animal · мрт животным · Philips 1,5 Т</span>
                <h1 class="animals-hero__title">МРТ для животных</h1>
                <p class="animals-hero__price">от <strong>50 000 ₸</strong> за исследование</p>
                <p class="animals-hero__lead">
                    Точная и безопасная магнитно-резонансная томография для собак, кошек и других питомцев.
                    Заключение ветеринарного специалиста — в день обследования.
                </p>
                <ul class="animals-hero__features">
                    <li>Аппарат 1,5 Тесла экспертного класса</li>
                    <li>Опытные врачи-диагносты</li>
                    <li>Комфортные условия для питомца и хозяина</li>
                    <li>~30 км от Алматы · с. Отеген батыра</li>
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

<?php
$animals_home_stats = array(
    array(
        'value'      => '1,5 Т',
        'label'      => 'МРТ Philips экспертного класса',
        'animate'    => false,
    ),
    array(
        'value'      => '50 000',
        'suffix'     => ' ₸',
        'label'      => 'базовая стоимость исследования',
        'animate'    => true,
        'data_count' => 50000,
    ),
    array(
        'value'      => '20–60',
        'label'      => 'минут — длительность сканирования',
        'animate'    => false,
    ),
    array(
        'value'      => 'в день',
        'label'      => 'заключение ветеринарного специалиста',
        'animate'    => false,
    ),
);

$animals_home_why = array(
    array(
        'icon'  => '🔬',
        'title' => 'Точная диагностика',
        'text'  => 'Высокое разрешение снимков для неврологии, ортопедии и онкопоиска у питомцев.',
    ),
    array(
        'icon'  => '👨‍⚕️',
        'title' => 'Опытные врачи',
        'text'  => 'Исследования проводят и описывают специалисты с опытом в ветеринарной МРТ.',
    ),
    array(
        'icon'  => '🐾',
        'title' => 'Комфорт питомца',
        'text'  => 'Спокойная обстановка, седация по показаниям, хозяин может быть рядом.',
    ),
    array(
        'icon'  => '📋',
        'title' => 'Запись без очередей',
        'text'  => 'Обследование по предварительной записи в удобное время — ~30 км от Алматы.',
    ),
);
?>

<main class="main animals-main">
    <section class="animals-section animals-section--stats">
        <div class="container">
            <h2 class="animals-section__title">MRI Animal в цифрах</h2>
            <div class="animals-home__stats">
                <?php foreach ($animals_home_stats as $stat) : ?>
                    <div class="animals-home__stat">
                        <?php if (!empty($stat['animate']) && !empty($stat['data_count'])) : ?>
                            <p class="animals-home__stat-value"
                               data-count="<?php echo esc_attr((string) $stat['data_count']); ?>"
                               data-suffix="<?php echo esc_attr($stat['suffix'] ?? ''); ?>">0<?php echo esc_html($stat['suffix'] ?? ''); ?></p>
                        <?php else : ?>
                            <p class="animals-home__stat-value"><?php echo esc_html($stat['value']); ?></p>
                        <?php endif; ?>
                        <p class="animals-home__stat-label"><?php echo esc_html($stat['label']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="animals-section animals-section--why">
        <div class="container">
            <h2 class="animals-section__title">Почему выбирают нас</h2>
            <div class="animals-home__why">
                <?php foreach ($animals_home_why as $card) : ?>
                    <article class="animals-home__why-card">
                        <span class="animals-home__why-icon" aria-hidden="true"><?php echo esc_html($card['icon']); ?></span>
                        <h3><?php echo esc_html($card['title']); ?></h3>
                        <p><?php echo esc_html($card['text']); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

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

            <?php
            $services_url = mrt_get_city_nav_url('uslugi-i-ceny', $selected_city);
            ?>
            <div class="animals-prices">
                <h3 class="animals-prices__title">Услуги и цены</h3>
                <p class="animals-prices__lead">Полный прайс-лист, направления диагностики и дополнительные услуги — на отдельной странице.</p>
                <a href="<?php echo esc_url($services_url); ?>" class="animals-btn animals-btn--primary">
                    Смотреть прайс-лист
                </a>
            </div>
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
                    <h2 class="animals-section__title">Контакты филиала MRI Animal</h2>
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
                    <?php echo $map_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- ACF iframe or escaped fallback ?>
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
