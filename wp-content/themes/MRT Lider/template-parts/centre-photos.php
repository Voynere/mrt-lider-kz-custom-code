<?php
/**
 * «Наш центр» photo gallery (contacts + home).
 *
 * @var string $selected_city City slug for contacts post / fallbacks.
 * @var string $section_class Extra CSS classes for <section> (e.g. animals-photos).
 */

if (!isset($selected_city)) {
    $selected_city = mrt_resolve_selected_city('almaty', true);
}

$section_class = isset($section_class) ? (string) $section_class : '';
$centre_photos = mrt_get_centre_photos($selected_city, 5);
$show_more_link = count($centre_photos) >= 5;
?>

<section class="photos<?php echo $section_class ? ' ' . esc_attr($section_class) : ''; ?>">
    <div class="container">
        <div class="photos__inner">
            <h2 class="page-title">НАШ ЦЕНТР</h2>
            <div class="photos__content">
                <?php if (!empty($centre_photos)) : ?>
                    <?php
                    $top_photos    = array_slice($centre_photos, 0, 3);
                    $bottom_photos = array_slice($centre_photos, 3, 2);
                    ?>
                    <div class="photos__top">
                        <?php foreach ($top_photos as $img) : ?>
                            <a href="<?php echo esc_url($img['full']); ?>"
                               class="photos__item"
                               data-fancybox="photos-gallery">
                                <img src="<?php echo esc_url($img['thumb']); ?>"
                                     alt="<?php echo esc_attr($img['alt']); ?>"
                                     class="photos__item-img" loading="lazy">
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <?php if (!empty($bottom_photos)) : ?>
                        <div class="photos__bottom">
                            <?php foreach ($bottom_photos as $img) : ?>
                                <a href="<?php echo esc_url($img['full']); ?>"
                                   class="photos__item"
                                   data-fancybox="photos-gallery">
                                    <img src="<?php echo esc_url($img['thumb']); ?>"
                                         alt="<?php echo esc_attr($img['alt']); ?>"
                                         class="photos__item-img" loading="lazy">
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($show_more_link) : ?>
                        <div class="photos__more">
                            <a href="#" class="photos__more-link" data-fancybox-trigger="photos-gallery">
                                Ещё фото
                            </a>
                        </div>
                    <?php endif; ?>
                <?php else : ?>
                    <div class="photos__empty">
                        <p>Фотографий центра для выбранного города не найдено.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
