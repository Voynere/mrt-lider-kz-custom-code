<?php
/**
 * Map block for animals branch (replaces 3D tour).
 */
$city_slug = isset($args['city_slug']) ? $args['city_slug'] : mrt_resolve_selected_city('almaty_aubakirova', true);
$branch = mrt_get_branch($city_slug) ?: mrt_get_branch('almaty_aubakirova');
$address_full = $branch['address_full'] ?? '';
$map_html = mrt_get_animals_map_html($city_slug);
?>
<section class="animals-map-block" aria-label="Карта филиала MRI Animal">
    <div class="container">
        <div class="animals-map-block__inner">
            <div class="animals-map-block__header">
                <h2 class="animals-map-block__title">Как нас найти</h2>
                <?php if ($address_full !== '') : ?>
                    <p class="animals-map-block__address"><?php echo esc_html($address_full); ?></p>
                <?php endif; ?>
            </div>
            <div class="animals-map-block__embed">
                <?php echo $map_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- ACF iframe or escaped fallback ?>
            </div>
        </div>
    </div>
</section>
