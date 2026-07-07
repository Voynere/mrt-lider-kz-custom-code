<?php
/**
 * SEO и мета для филиала МРТ для животных (almaty_aubakirova).
 */

if (!function_exists('mrt_is_animals_context')) {
    function mrt_is_animals_context(): bool {
        $slug = mrt_resolve_selected_city('almaty', false);
        return mrt_is_animals_branch($slug) || is_page_template('home-animals.php');
    }
}

if (!function_exists('mrt_animals_seo_title')) {
    function mrt_animals_seo_title(): string {
        return 'МРТ для животных в Алматы — от 50 000 ₸ | MRI Animal';
    }
}

if (!function_exists('mrt_animals_seo_description')) {
    function mrt_animals_seo_description(): string {
        return 'MRI Animal — мрт животным. Ветеринарное МРТ для собак и кошек. Philips 1,5 Т, заключение в день. с. Отеген батыра, ул. Аубакирова 17/1. Запись онлайн и WhatsApp.';
    }
}

add_filter('document_title_parts', function ($parts) {
    if (!mrt_is_animals_context()) {
        return $parts;
    }
    $parts['title'] = mrt_animals_seo_title();
    return $parts;
}, 20);

add_filter('pre_get_document_title', function ($title) {
    if (mrt_is_animals_context()) {
        return mrt_animals_seo_title();
    }
    return $title;
}, 20);

add_action('wp_head', function () {
    if (!mrt_is_animals_context()) {
        return;
    }
    $description = mrt_animals_seo_description();
    echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr(mrt_animals_seo_title()) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($description) . '">' . "\n";
    echo '<meta property="og:type" content="website">' . "\n";
    echo '<meta property="og:url" content="' . esc_url(trailingslashit(home_url('/almaty_aubakirova/'))) . '">' . "\n";
}, 5);
