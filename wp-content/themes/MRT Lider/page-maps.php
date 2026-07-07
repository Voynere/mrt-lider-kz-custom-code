<?php
/*
Template Name: about
*/

$selected_city = mrt_get_selected_city_slug(['sync_cookie' => true]);

if (mrt_is_animals_branch($selected_city)) {
    get_header();
    ?>
    <main class="main animals-main">
        <div class="main-background">
            <?php custom_breadcrumbs(); ?>
            <section class="about-us animals-about">
                <div class="container">
                    <div class="about-us__inner">
                        <h1 class="maps__title page-title">О ЦЕНТРЕ</h1>
                        <?php get_template_part('template-parts/animals-about-content'); ?>
                    </div>
                </div>
            </section>
        </div>
    </main>
    <?php
    get_footer();
    return;
}
?>

<?php get_header(); ?>

<main class="main">
    <div class="main-background">
        <?php 
            if (!is_front_page()) {
                custom_breadcrumbs();
            }
        ?>
        <section class="about-us">
            <div class="container">
                <div class="about-us__inner">
                    <h1 class="maps__title page-title">О КОМПАНИИ</h1>
                    <div class="about-us__content">
                        <p>
                            Группа компаний «МРТ Лидер», представляет собой федеральную сеть диагностических центров
                            высокого уровня. Диагностические центры «МРТ Лидер» берут свое начало с Дальнего Востока, и
                            на сегодняшний день «МРТ Лидер» представляет собой одну из крупнейших сетей по всей России.
                            Так же сеть представлена в Казахстане.
                        </p>
                        <p>
                            Основное направление нашей компании – оказание высококачественных и высокотехнологичных
                            диагностических услуг. Мы специализируемся на МРТ диагностике – одном из самых современных,
                            информативных и безопасных методов диагностики в наше время. Наши центры оснащены самыми
                            современными и точными МРТ аппаратами (Philips, Siemens, Toshiba, GE 1.5 Т), признанные
                            мировыми лидерами в диагностике.
                        </p>
                        <p>
                            Как известно, без специалиста даже самое современное оборудование может не дать ожидаемого
                            результата. Центр «МРТ Лидер» уделяет пристальное внимание к качеству проводимых
                            исследований, привлекая самых квалифицированных специалистов. Наши врачи приглашены из
                            лучших мед учреждений и являются одними из лучших специалистов в своей области.
                        </p>
                        <p>
                            Наша главная цель – обеспечить доступность, квалифицированность, качество диагностических
                            услуг населению, тем самым обеспечивая своевременный поиск заболевания, а значит скорейшую
                            постановку правильного диагноза. Правильно и вовремя поставленный диагноз – залог успешного
                            лечения.
                        </p>
                        <p>
                            Наряду с высокотехнологичным оборудованием и профессиональным врачебным коллективом,
                            отличительной особенностью центров «МРТ Лидер», является уровень сервиса.
                        </p>
                        <p>
                            Диагностика проводится в удобное для посетителя время по предварительной записи. Поэтому в
                            центре нет очередей, а уютная обстановка позволит комфортно пройти обследование.
                        </p>
                        <p>
                            Стоимость услуг в наших филиалах достаточно демократична: «высокое качество диагностики по
                            честной цене» — одно из твердых правил центров «МРТ Лидер».
                        </p>
                    </div>

                </div>
            </div>
        </section>

        <section class="maps">
            <div class="container">
                <div class="maps__inner">
                    <div class="maps__content">
                        <h2 class="maps__subtitle">Расположение Федеральной сети МРТ Лидер в России</h2>
                        <div class="maps__item">
                            <img src="<?php bloginfo('template_url') ?>/assets/img/map_russ.png" alt="">
                        </div>
                    </div>
                    <div class="maps__content">
                        <h2 class="maps__subtitle">Расположение Федеральной сети МРТ Лидер в Казахстане</h2>
                        <div class="maps__item">
                            <img src="<?php bloginfo('template_url') ?>/assets/img/map_kz.png" alt="">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="tour">
            <div class="container">
                <div class="tour__inner">
                    <img src="<?php bloginfo('template_url')?>/assets/img/3d_tour.jpg" alt="">
                    <a href="#" class="tour__content">
                        <h2 class="tour__title"><span>ПРОЙДИТЕ 3D ТУР</span> <span>ПО КЛИНИКЕ</span> <span>«МРТ
                                ЛИДЕР»</span></h2>
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