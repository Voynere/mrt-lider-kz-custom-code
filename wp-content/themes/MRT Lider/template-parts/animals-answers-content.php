<?php
$faq_items = mrt_get_animals_faq_items();
?>

<div class="answers__tabs answers__content-item animals-answers__tabs">
    <?php foreach ($faq_items as $item) : ?>
        <div class="answers__tabs-item">
            <button type="button" class="answers__tabs-btn">
                <h3 class="answers__tabs-title"><?php echo esc_html($item['q']); ?></h3>
                <svg width="16" height="9" viewBox="0 0 16 9" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M15.03 0.5L16 1.3625L8 8.5L0 1.3625L0.965 0.5L8 6.77083L15.03 0.5Z" fill="#404040" />
                </svg>
            </button>
            <div class="answers__tabs-text">
                <p><?php echo wp_kses_post($item['a']); ?></p>
            </div>
        </div>
    <?php endforeach; ?>
</div>
