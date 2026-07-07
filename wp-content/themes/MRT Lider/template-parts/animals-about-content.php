<?php
$selected_city = mrt_resolve_selected_city('almaty_aubakirova', true);
$branch = mrt_get_branch($selected_city) ?: mrt_get_branch('almaty_aubakirova');
$address_full = $branch['address_full'] ?? 'ул. Аубакирова, 17/1, село Отеген батыра, Илийский район, Алматинская область';

$why_us = array(
    array(
        'icon' => '🔬',
        'title' => 'Аппарат экспертного класса',
        'text'  => 'МРТ Philips 1,5 Тесла — высокое разрешение снимков для точной диагностики неврологии, ортопедии и онкопоиска.',
    ),
    array(
        'icon' => '👨‍⚕️',
        'title' => 'Опытные специалисты',
        'text'  => 'Исследования проводят и описывают врачи-диагносты с опытом в ветеринарной МРТ. Заключение — в день обследования.',
    ),
    array(
        'icon' => '🐾',
        'title' => 'Комфорт для питомца',
        'text'  => 'Спокойная обстановка, седация по показаниям под контролем врача, индивидуальный подход к каждому пациенту.',
    ),
    array(
        'icon' => '📋',
        'title' => 'Запись без очередей',
        'text'  => 'Обследование по предварительной записи в удобное время. Хозяин может присутствовать рядом с питомцем.',
    ),
);

$highlights = array(
    array('value' => '1,5 Т', 'label' => 'МРТ Philips экспертного класса'),
    array('value' => '20–60', 'label' => 'минут — длительность исследования'),
    array('value' => 'в день', 'label' => 'заключение ветеринарного специалиста'),
    array('value' => '~30 км', 'label' => 'от Алматы · с. Отеген батыра'),
);
?>

<div class="animals-about__content">
    <div class="animals-about__intro">
        <p>
            Филиал МРТ животным «MRI Animal» в селе Отеген батыра — специализированный центр магнитно-резонансной
            томографии для животных. Мы проводим МРТ-диагностику собак, кошек и других питомцев на
            современном аппарате Philips 1,5 Тесла.
        </p>
        <p>
            МРТ — один из самых информативных и безопасных методов диагностики: исследование не
            использует ионизирующее излучение и позволяет детально оценить мягкие ткани, головной мозг,
            позвоночник, суставы и внутренние органы. Это особенно важно при неврологических симптомах,
            хромоте неясного происхождения, подозрении на опухоли и хронических болях.
        </p>
        <p>
            Наша цель — дать лечащему ветеринару и владельцу питомца максимально точную картину
            заболевания, чтобы как можно раньше начать правильное лечение. Мы работаем в тесной связке
            с ветклиниками Алматы и области: результаты исследования можно передать вашему врачу в
            удобном формате.
        </p>
    </div>

    <h2 class="animals-about__subtitle">Почему выбирают нас</h2>
    <div class="animals-about__cards">
        <?php foreach ($why_us as $card) : ?>
            <article class="animals-about__card">
                <span class="animals-about__card-icon" aria-hidden="true"><?php echo esc_html($card['icon']); ?></span>
                <h3><?php echo esc_html($card['title']); ?></h3>
                <p><?php echo esc_html($card['text']); ?></p>
            </article>
        <?php endforeach; ?>
    </div>

    <div class="animals-about__highlights">
        <?php foreach ($highlights as $item) : ?>
            <div class="animals-about__highlight">
                <p class="animals-about__highlight-value"><?php echo esc_html($item['value']); ?></p>
                <p class="animals-about__highlight-label"><?php echo esc_html($item['label']); ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="animals-about__mission">
        <h2 class="animals-about__subtitle">Наш подход</h2>
        <p>
            Мы понимаем, что визит на диагностику — стресс и для питомца, и для хозяина. Поэтому
            уделяем внимание не только качеству снимков, но и сервису: помогаем с подготовкой к
            исследованию, объясняем этапы процедуры и отвечаем на вопросы до и после обследования.
        </p>
        <p>
            Стоимость услуг прозрачна: «точная диагностика по честной цене» — принцип, которого мы
            придерживаемся во всех филиалах сети «МРТ Лидер». Полный прайс-лист и перечень
            исследований — на странице
            <a href="<?php echo esc_url(mrt_get_city_nav_url('uslugi-i-ceny', $selected_city)); ?>">«Услуги и цены»</a>.
        </p>
        <p class="animals-about__address">
            <strong>Адрес центра:</strong><br>
            <?php echo esc_html($address_full); ?>
        </p>
    </div>
</div>
