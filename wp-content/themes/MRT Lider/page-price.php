<?php
/*
Template Name: price
*/

// Редиректим с /price/ на /uslugi-i-ceny/
if (is_page('price') && !get_query_var('service')) {
    wp_redirect(home_url('/uslugi-i-ceny/'), 301);
    exit;
}

get_header();
?>

<?php get_footer(); ?>