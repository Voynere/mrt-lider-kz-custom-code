<?php
/**
 * Service landing & subcategory routing: /{city}/services/{slug}/, /{city}/uslugi-i-ceny/{subcat}/.
 */

if (!function_exists('mrt_service_routing_query_vars')) {
    function mrt_service_routing_query_vars(array $vars): array {
        $vars[] = 'mrt_service_landing';
        $vars[] = 'mrt_subcat';
        return $vars;
    }
}
add_filter('query_vars', 'mrt_service_routing_query_vars');

if (!function_exists('mrt_register_service_rewrite_rules')) {
    function mrt_register_service_rewrite_rules(): void {
        $cities = implode('|', array_map(static function ($slug) {
            return preg_quote($slug, '');
        }, mrt_get_known_city_slugs()));

        add_rewrite_rule(
            '^(' . $cities . ')/services/([^/]+)/?$',
            'index.php?mrt_service_landing=$matches[2]&mrt_city=$matches[1]',
            'top'
        );
    }
}
add_action('init', 'mrt_register_service_rewrite_rules', 6);

if (!function_exists('mrt_route_uslugi_subpages')) {
    /** /{city}/uslugi-i-ceny/price/{type}/ and /{city}/uslugi-i-ceny/{subcat}/ */
    function mrt_route_uslugi_subpages(): void {
        if (is_admin() || wp_doing_ajax()) {
            return;
        }

        $parts = mrt_get_request_path_parts();
        $cities = mrt_seo_get_cities();

        if (count($parts) < 3) {
            return;
        }
        if (!array_key_exists($parts[0], $cities)) {
            return;
        }
        if (($parts[1] ?? '') !== 'uslugi-i-ceny') {
            return;
        }

        $city = $parts[0];
        set_query_var('mrt_city', $city);

        if (count($parts) >= 4 && ($parts[2] ?? '') === 'price') {
            $template = get_template_directory() . '/page-service-item.php';
            if (file_exists($template)) {
                status_header(200);
                include $template;
                exit;
            }
        }

        $subcat = $parts[2] ?? '';
        if ($subcat === '' || $subcat === 'price') {
            return;
        }

        if (!in_array($subcat, mrt_get_subcat_slugs(), true)) {
            return;
        }

        if (mrt_is_subcategory_hidden_for_city($subcat, $city)) {
            global $wp_query;
            $wp_query->set_404();
            status_header(404);
            nocache_headers();
            get_header();
            echo '<main class="main"><div class="container"><p>Услуга не найдена.</p></div></main>';
            get_footer();
            exit;
        }

        set_query_var('mrt_subcat', $subcat);

        $allowed_svc_type = null;
        if (strpos($subcat, 'mrt-') === 0 || $subcat === 'mrt') {
            $allowed_svc_type = 'мрт';
        } elseif (strpos($subcat, 'kt-') === 0 || $subcat === 'kt') {
            $allowed_svc_type = 'кт';
        } elseif (strpos($subcat, 'uzi-') === 0 || $subcat === 'uzi') {
            $allowed_svc_type = 'узи';
        } elseif ($subcat === 'densitometriya') {
            $allowed_svc_type = 'денситометр';
        }
        $GLOBALS['mrt_subcat_allowed_svc_type'] = $allowed_svc_type;

        $template = get_template_directory() . '/page-subservice.php';
        if (file_exists($template)) {
            status_header(200);
            include $template;
            exit;
        }
    }
}
add_action('template_redirect', 'mrt_route_uslugi_subpages', 1);

if (!function_exists('mrt_route_service_landing')) {
    /** /{city}/services/{slug}/ → page-service-landing.php */
    function mrt_route_service_landing(): void {
        if (is_admin() || wp_doing_ajax()) {
            return;
        }

        $parts = mrt_get_request_path_parts();
        $cities = mrt_seo_get_cities();

        if (count($parts) < 3) {
            return;
        }
        if (!array_key_exists($parts[0], $cities)) {
            return;
        }
        if (($parts[1] ?? '') !== 'services') {
            return;
        }

        $landing_slug = sanitize_title($parts[2] ?? '');
        if ($landing_slug === '') {
            return;
        }

        $city = $parts[0];
        set_query_var('mrt_city', $city);
        set_query_var('mrt_service_landing', $landing_slug);

        if (mrt_is_animals_branch($city)) {
            global $wp_query;
            $wp_query->set_404();
            status_header(404);
            nocache_headers();
            get_header();
            echo '<main class="main"><div class="container"><p>Услуга не найдена.</p></div></main>';
            get_footer();
            exit;
        }

        $svc = get_page_by_path($landing_slug, OBJECT, 'service');
        if (!$svc) {
            global $wpdb;
            $svc_id = $wpdb->get_var($wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts} WHERE post_type='service' AND post_status='publish' AND post_name=%s LIMIT 1",
                $landing_slug
            ));
            $svc = $svc_id ? get_post((int) $svc_id) : null;
        }
        if (!$svc) {
            return;
        }

        $equivalent = mrt_find_equivalent_service_in_city($svc, $city);
        if (!$equivalent) {
            wp_safe_redirect(mrt_service_parent_category_url($svc, $city), 302);
            exit;
        }
        if ($equivalent->post_name !== $landing_slug) {
            wp_safe_redirect(mrt_get_service_landing_url($city, $equivalent->post_name), 302);
            exit;
        }
        $svc = $equivalent;

        if (mrt_is_service_hidden_for_city((int) $svc->ID, $city)) {
            global $wp_query;
            $wp_query->set_404();
            status_header(404);
            nocache_headers();
            get_header();
            echo '<main class="main"><div class="container"><p>Услуга не найдена.</p></div></main>';
            get_footer();
            exit;
        }

        global $post;
        $post = $svc;
        setup_postdata($post);

        $template = get_template_directory() . '/page-service-landing.php';
        if (file_exists($template)) {
            status_header(200);
            include $template;
            exit;
        }
    }
}
add_action('template_redirect', 'mrt_route_service_landing', 2);

if (!function_exists('mrt_redirect_short_service_urls')) {
    function mrt_redirect_short_service_urls(): void {
        if (is_admin() || wp_doing_ajax() || (defined('REST_REQUEST') && REST_REQUEST)) {
            return;
        }

        $parts = mrt_get_request_path_parts();
        if ($parts === array()) {
            return;
        }

        if (in_array($parts[0], mrt_get_known_city_slugs(), true)) {
            return;
        }

        if (($parts[0] ?? '') !== 'services' || empty($parts[1])) {
            return;
        }

        $city = mrt_get_selected_city_slug();
        wp_safe_redirect(mrt_build_city_path_url($city, $parts), 301);
        exit;
    }
}
add_action('template_redirect', 'mrt_redirect_short_service_urls', 0);
