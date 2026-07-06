<?php
/**
 * URL с префиксом филиала: /{city}/uslugi-i-ceny/ и редирект с коротких путей.
 */

if (!function_exists('mrt_get_request_path_parts')) {
    function mrt_get_request_path_parts(): array {
        $request_uri = isset($_SERVER['REQUEST_URI']) ? (string) wp_unslash($_SERVER['REQUEST_URI']) : '/';
        $path = trim((string) parse_url($request_uri, PHP_URL_PATH), '/');
        return $path !== '' ? explode('/', $path) : array();
    }
}

if (!function_exists('mrt_build_city_path_url')) {
    function mrt_build_city_path_url(string $city_slug, array $path_parts): string {
        $segments = array_merge(array(sanitize_key($city_slug)), $path_parts);
        return trailingslashit(home_url('/' . implode('/', $segments)));
    }
}

if (!function_exists('mrt_register_city_rewrite_rules')) {
    function mrt_register_city_rewrite_rules(): void {
        $cities = implode('|', array_map(static function ($slug) {
            return preg_quote($slug, '');
        }, mrt_get_known_city_slugs()));

        $pages = implode('|', array_map(static function ($slug) {
            return preg_quote($slug, '');
        }, mrt_get_city_specific_page_slugs()));

        add_rewrite_rule(
            '^(' . $cities . ')/uslugi-i-ceny/price/([^/]+)/?$',
            'index.php?pagename=uslugi-i-ceny/price&service_type=$matches[2]&mrt_city=$matches[1]',
            'top'
        );

        add_rewrite_rule(
            '^(' . $cities . ')/(' . $pages . ')/?$',
            'index.php?pagename=$matches[2]&mrt_city=$matches[1]',
            'top'
        );

        add_rewrite_rule(
            '^(' . $cities . ')/?$',
            'index.php?mrt_city=$matches[1]',
            'top'
        );
    }
}
add_action('init', 'mrt_register_city_rewrite_rules', 5);

if (!function_exists('mrt_city_routing_query_vars')) {
    function mrt_city_routing_query_vars(array $vars): array {
        $vars[] = 'mrt_city';
        return $vars;
    }
}
add_filter('query_vars', 'mrt_city_routing_query_vars');

if (!function_exists('mrt_maybe_flush_city_rewrite_rules')) {
    function mrt_maybe_flush_city_rewrite_rules(): void {
        $version = '4';
        if (get_option('mrt_city_rewrite_version') === $version) {
            return;
        }
        flush_rewrite_rules(false);
        update_option('mrt_city_rewrite_version', $version, false);
    }
}
add_action('init', 'mrt_maybe_flush_city_rewrite_rules', 99);

if (!function_exists('mrt_get_service_type_from_request')) {
    /** Slug вида услуги из query var или URL …/price/{slug}/. */
    function mrt_get_service_type_from_request(): string {
        $service_type = get_query_var('service_type');
        if (is_string($service_type) && $service_type !== '') {
            return sanitize_title($service_type);
        }

        $parts = mrt_get_request_path_parts();
        $price_idx = array_search('price', $parts, true);
        if ($price_idx !== false && isset($parts[$price_idx + 1]) && $parts[$price_idx + 1] !== '') {
            return sanitize_title($parts[$price_idx + 1]);
        }

        return '';
    }
}

if (!function_exists('mrt_service_price_template')) {
    /**
     * /{city}/uslugi-i-ceny/price/{service_type}/ → page-service-item.php (прайс-таблица).
     * Без этого WordPress отдаёт родительскую страницу «Услуги и цены» (список карточек).
     */
    function mrt_service_price_template(string $template): string {
        if (is_admin() || wp_doing_ajax()) {
            return $template;
        }

        $service_type = mrt_get_service_type_from_request();
        if ($service_type === '') {
            return $template;
        }

        $detail_template = locate_template('page-service-item.php');
        return $detail_template ?: $template;
    }
}
add_filter('template_include', 'mrt_service_price_template', 25);

if (!function_exists('mrt_fix_city_canonical_urls')) {
    /**
     * WordPress по умолчанию срезает /{city}/ из URL страниц.
     * Здесь: не трогаем city-prefixed URL и редиректим короткие на полные.
     */
    function mrt_fix_city_canonical_urls($redirect_url, $requested_url) {
        $parts = mrt_get_request_path_parts();
        if ($parts === array()) {
            return $redirect_url;
        }

        $known_cities = mrt_get_known_city_slugs();
        $city_pages = mrt_get_city_specific_page_slugs();

        if (in_array($parts[0], $known_cities, true)) {
            return false;
        }

        $is_short_hub = in_array($parts[0], $city_pages, true);
        $is_short_price = (count($parts) >= 3 && $parts[0] === 'uslugi-i-ceny' && $parts[1] === 'price');

        if ($is_short_hub || $is_short_price) {
            $city = mrt_get_selected_city_slug();
            return mrt_build_city_path_url($city, $parts);
        }

        return $redirect_url;
    }
}
add_filter('redirect_canonical', 'mrt_fix_city_canonical_urls', 0, 2);

if (!function_exists('mrt_redirect_short_city_urls')) {
    /** Запасной редирект, если redirect_canonical не сработал. */
    function mrt_redirect_short_city_urls(): void {
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

        $city_pages = mrt_get_city_specific_page_slugs();
        $should_redirect = in_array($parts[0], $city_pages, true)
            || (count($parts) >= 3 && $parts[0] === 'uslugi-i-ceny' && $parts[1] === 'price');

        if (!$should_redirect) {
            return;
        }

        $city = mrt_get_selected_city_slug();
        wp_safe_redirect(mrt_build_city_path_url($city, $parts), 301);
        exit;
    }
}
add_action('template_redirect', 'mrt_redirect_short_city_urls', 0);

if (!function_exists('mrt_city_home_template')) {
    function mrt_city_home_template(string $template): string {
        $parts = mrt_get_request_path_parts();
        $city_slug = get_query_var('mrt_city');

        if ($city_slug !== '' && $city_slug !== false) {
            $city_slug = sanitize_key((string) $city_slug);
            if (count($parts) === 1 && ($parts[0] ?? '') === $city_slug) {
                $branch = mrt_get_branch($city_slug);
                if ($branch && !empty($branch['home_template'])) {
                    $home_template = locate_template($branch['home_template']);
                    if ($home_template) {
                        return $home_template;
                    }
                }
            }
            return $template;
        }

        if (is_front_page()) {
            $resolved = mrt_resolve_selected_city('almaty', false);
            if (mrt_is_animals_branch($resolved)) {
                $branch = mrt_get_branch($resolved);
                if ($branch && !empty($branch['home_template'])) {
                    $home_template = locate_template($branch['home_template']);
                    if ($home_template) {
                        return $home_template;
                    }
                }
            }
        }

        return $template;
    }
}
add_filter('template_include', 'mrt_city_home_template', 20);
