<?php
/**
 * Renders 3D tour for standard branches or embedded map for animals branch.
 */
$city_slug = isset($args['city_slug']) ? $args['city_slug'] : mrt_resolve_selected_city();

if (mrt_is_animals_branch($city_slug)) {
    get_template_part('template-parts/animals-map-block', null, array('city_slug' => $city_slug));
    return;
}

get_template_part('template-parts/tour-block', null, $args);
