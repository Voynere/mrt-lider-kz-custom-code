<?php
/*
Template Name: price
*/

// Редиректим с /price/ (без slug услуги) на /uslugi-i-ceny/
if (is_page('price') && mrt_get_service_type_from_request() === '') {
    $city = function_exists('mrt_get_selected_city_slug') ? mrt_get_selected_city_slug() : 'almaty';
    wp_redirect(trailingslashit(home_url('/' . $city . '/uslugi-i-ceny/')), 301);
    exit;
}

get_header();
?>

<?php get_footer(); ?>