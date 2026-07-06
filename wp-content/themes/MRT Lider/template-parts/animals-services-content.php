<?php
$price_items = array(
    array('name' => 'МРТ головного мозга', 'price' => '50 000 ₸'),
    array('name' => 'МРТ позвоночника', 'price' => '50 000 ₸'),
    array('name' => 'МРТ грудного отдела', 'price' => '50 000 ₸'),
    array('name' => 'МРТ поясничного отдела', 'price' => '50 000 ₸'),
    array('name' => 'МРТ шейного отдела', 'price' => '50 000 ₸'),
    array('name' => 'МРТ суставов', 'price' => '50 000 ₸'),
    array('name' => 'МРТ брюшной полости', 'price' => '50 000 ₸'),
    array('name' => 'МРТ мягких тканей', 'price' => '50 000 ₸'),
    array('name' => 'МРТ одного анатомического региона', 'price' => '50 000 ₸'),
    array('name' => 'Контрастное усиление', 'price' => '25 000 ₸'),
    array('name' => 'Наркоз / седация', 'price' => '25 000 ₸'),
);

$investigate = array(
    array(
        'title' => 'Неврология',
        'icon'  => '🧠',
        'items' => array(
            'Головной мозг',
            'Гипофиз',
            'Эпилепсия',
            'Судорожные состояния',
            'Новообразования ЦНС',
            'Воспалительные заболевания',
            'Черепно-мозговые травмы',
            'Гидроцефалия',
        ),
    ),
    array(
        'title' => 'Позвоночник и спинной мозг',
        'icon'  => '🦴',
        'items' => array(
            'Межпозвоночные грыжи',
            'Компрессия спинного мозга',
            'Дегенеративные изменения',
            'Шейный, грудной и поясничный отдел',
            'Хвостовой отдел',
            'Миелопатии',
            'Травмы позвоночника',
        ),
    ),
    array(
        'title' => 'Суставы и ортопедия',
        'icon'  => '🦵',
        'items' => array(
            'Тазобедренные суставы',
            'Коленные суставы',
            'Плечевые суставы',
            'Локтевые суставы',
            'Связки и мениски',
            'Хромота неясного происхождения',
            'Повреждения сухожилий',
            'Артриты и воспаления',
        ),
    ),
    array(
        'title' => 'Онкология',
        'icon'  => '🔬',
        'items' => array(
            'Поиск новообразований',
            'Оценка распространения процесса',
            'Контроль после операций',
            'Диагностика опухолей мягких тканей и ЦНС',
        ),
    ),
    array(
        'title' => 'Мягкие ткани',
        'icon'  => '💪',
        'items' => array(
            'Мышцы',
            'Сухожилия',
            'Связочный аппарат',
            'Подкожные образования',
            'Абсцессы и воспаления',
        ),
    ),
    array(
        'title' => 'Брюшная полость и внутренние органы',
        'icon'  => '🫀',
        'items' => array(
            'Печень',
            'Почки',
            'Надпочечники',
            'Органы малого таза',
            'Мочевыделительная система',
        ),
    ),
);

$extra_services = array(
    'Контрастные исследования',
    'Исследования под наркозом',
    'Повторные контрольные исследования',
    'Консультации по результатам диагностики',
);
?>

<div class="animals-services__content">
    <div class="animals-pricelist" id="pricelist">
        <div class="animals-pricelist__head">
            <span>Исследование</span>
            <span>Стоимость</span>
        </div>
        <ul class="animals-pricelist__list">
            <?php foreach ($price_items as $item) : ?>
                <li class="animals-pricelist__row">
                    <span class="animals-pricelist__name"><?php echo esc_html($item['name']); ?></span>
                    <span class="animals-pricelist__price"><?php echo esc_html($item['price']); ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <h2 class="animals-services__subtitle">Что мы исследуем</h2>
    <div class="animals-investigate">
        <?php foreach ($investigate as $block) : ?>
            <article class="animals-investigate__card">
                <div class="animals-investigate__card-head">
                    <span class="animals-investigate__icon" aria-hidden="true"><?php echo esc_html($block['icon']); ?></span>
                    <h3><?php echo esc_html($block['title']); ?></h3>
                </div>
                <ul class="animals-investigate__list">
                    <?php foreach ($block['items'] as $item) : ?>
                        <li><?php echo esc_html($item); ?></li>
                    <?php endforeach; ?>
                </ul>
            </article>
        <?php endforeach; ?>
    </div>

    <h2 class="animals-services__subtitle">Дополнительно</h2>
    <ul class="animals-extra__list">
        <?php foreach ($extra_services as $service) : ?>
            <li><?php echo esc_html($service); ?></li>
        <?php endforeach; ?>
    </ul>
</div>
