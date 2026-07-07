<?php
/**
 * Центральная конфигурация филиалов MRT Lider KZ.
 * Используется шаблонами, формами и JS (через wp_localize_script).
 */

if (!function_exists('mrt_get_branches')) {
    function mrt_get_branches() {
        return array(
            'almaty' => array(
                'label'             => 'Алматы',
                'type'              => 'standard',
                'currency'          => 'tenge',
                'country'           => 'kz',
                'yandex_metrika_id' => 110465113,
            ),
            'astana' => array(
                'label'             => 'Астана',
                'type'              => 'standard',
                'currency'          => 'tenge',
                'country'           => 'kz',
                'yandex_metrika_id' => 110466202,
            ),
            'karaganda' => array(
                'label'             => 'Караганда',
                'type'              => 'standard',
                'currency'          => 'tenge',
                'country'           => 'kz',
                'yandex_metrika_id' => 110469944,
            ),
            'taldykorgan' => array(
                'label'             => 'Талдыкорган',
                'type'              => 'standard',
                'currency'          => 'tenge',
                'country'           => 'kz',
                'yandex_metrika_id' => 110468879,
            ),
            'almaty_aubakirova' => array(
                'label'           => 'МРТ животным «MRI Animal»',
                'subtitle'        => 'с. Отеген батыра',
                'type'            => 'animals',
                'currency'        => 'tenge',
                'country'         => 'kz',
                'address_short'   => 'ул. Аубакирова, 17/1',
                'address_full'    => 'ул. Аубакирова, 17/1, село Отеген батыра, Илийский район, Алматинская область',
                'branch_taxonomy' => 'almaty_aubakirova',
                'map_embed'       => 'https://yandex.ru/map-widget/v1/?um=constructor%3Aplaceholder&amp;source=constructor',
                'home_template'   => 'home-animals.php',
            ),
        );
    }
}

if (!function_exists('mrt_get_known_city_slugs')) {
    function mrt_get_known_city_slugs() {
        return array_keys(mrt_get_branches());
    }
}

if (!function_exists('mrt_get_city_map')) {
    /** slug => label (для шапки и JS) */
    function mrt_get_city_map() {
        $map = array();
        foreach (mrt_get_branches() as $slug => $branch) {
            $map[$slug] = $branch['label'];
        }
        return $map;
    }
}

if (!function_exists('mrt_get_branch_yandex_metrika_id')) {
    /**
     * ID счётчика Яндекс.Метрики из конфига филиала (источник истины в теме).
     */
    function mrt_get_branch_yandex_metrika_id($slug) {
        $branch = mrt_get_branch($slug);
        if (!$branch || empty($branch['yandex_metrika_id'])) {
            return null;
        }

        return (int) $branch['yandex_metrika_id'];
    }
}

if (!function_exists('mrt_build_yandex_metrika_snippet')) {
    /** Стандартный snippet счётчика Яндекс.Метрики для wp_head. */
    function mrt_build_yandex_metrika_snippet($counter_id) {
        $counter_id = (int) $counter_id;
        if ($counter_id <= 0) {
            return '';
        }

        return sprintf(
            "<!-- Yandex.Metrika counter -->\n"
            . "<script type=\"text/javascript\">\n"
            . "    (function(m,e,t,r,i,k,a){\n"
            . "        m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};\n"
            . "        m[i].l=1*new Date();\n"
            . "        for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}\n"
            . "        k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)\n"
            . "    })(window, document,'script','https://mc.yandex.ru/metrika/tag.js?id=%1$d', 'ym');\n\n"
            . "    ym(%1\$d, 'init', {ssr:true, webvisor:true, clickmap:true, ecommerce:\"dataLayer\", referrer: document.referrer, url: location.href, accurateTrackBounce:true, trackLinks:true});\n"
            . "</script>\n"
            . "<noscript><div><img src=\"https://mc.yandex.ru/watch/%1\$d\" style=\"position:absolute; left:-9999px;\" alt=\"\" /></div></noscript>\n"
            . "<!-- /Yandex.Metrika counter -->",
            $counter_id
        );
    }
}

if (!function_exists('mrt_get_metrics_cities_list')) {
    /** slug => label — только KZ-филиалы для WP Admin «Метрики городов». */
    function mrt_get_metrics_cities_list(): array {
        $list = array();
        foreach (mrt_get_branches() as $slug => $branch) {
            if (($branch['country'] ?? '') !== 'kz') {
                continue;
            }
            $list[$slug] = $branch['label'];
        }
        return $list;
    }
}

if (!function_exists('mrt_get_branch')) {
    function mrt_get_branch($slug) {
        $branches = mrt_get_branches();
        return isset($branches[$slug]) ? $branches[$slug] : null;
    }
}

if (!function_exists('mrt_is_animals_branch')) {
    function mrt_is_animals_branch($slug) {
        $branch = mrt_get_branch($slug);
        return $branch && ($branch['type'] ?? '') === 'animals';
    }
}

if (!function_exists('mrt_get_city_slug_from_request')) {
    function mrt_get_city_slug_from_request($known_slugs = null) {
        if ($known_slugs === null) {
            $known_slugs = mrt_get_known_city_slugs();
        }

        $request_uri = isset($_SERVER['REQUEST_URI'])
            ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI']))
            : '/';
        $path = explode('?', $request_uri)[0];
        $path_parts = array_filter(explode('/', trim($path, '/')));

        if (!empty($path_parts)) {
            $first_part = strtolower(sanitize_text_field(reset($path_parts)));
            if (in_array($first_part, $known_slugs, true)) {
                return $first_part;
            }
        }

        return false;
    }
}

if (!function_exists('mrt_resolve_selected_city')) {
    /**
     * URL > cookie > fallback.
     *
     * @param string $fallback
     * @param bool   $set_cookie
     * @return string
     */
    function mrt_resolve_selected_city($fallback = 'almaty', $set_cookie = false) {
        $known_slugs = mrt_get_known_city_slugs();
        $from_url = mrt_get_city_slug_from_request($known_slugs);

        if ($from_url !== false) {
            if ($set_cookie && !headers_sent()) {
                if (!isset($_COOKIE['selected_city']) || $_COOKIE['selected_city'] !== $from_url) {
                    setcookie(
                        'selected_city',
                        $from_url,
                        time() + 30 * DAY_IN_SECONDS,
                        '/',
                        $_SERVER['HTTP_HOST'],
                        is_ssl(),
                        true
                    );
                }
            }
            return $from_url;
        }

        if (isset($_COOKIE['selected_city'])) {
            $cookie_city = sanitize_text_field($_COOKIE['selected_city']);
            if (in_array($cookie_city, $known_slugs, true)) {
                return $cookie_city;
            }
        }

        if ($set_cookie && !headers_sent()) {
            setcookie(
                'selected_city',
                $fallback,
                time() + 30 * DAY_IN_SECONDS,
                '/',
                $_SERVER['HTTP_HOST'],
                is_ssl(),
                true
            );
        }

        return $fallback;
    }
}

if (!function_exists('mrt_get_contacts_query')) {
    function mrt_get_contacts_query($city_slug) {
        return new WP_Query(array(
            'post_type'      => 'post',
            'posts_per_page' => 1,
            'tax_query'      => array(
                'relation' => 'AND',
                array(
                    'taxonomy' => 'category',
                    'field'    => 'slug',
                    'terms'    => $city_slug,
                ),
                array(
                    'taxonomy' => 'category',
                    'field'    => 'slug',
                    'terms'    => 'contacty',
                ),
            ),
        ));
    }
}

if (!function_exists('mrt_get_contact_phone')) {
    function mrt_get_contact_phone($city_slug) {
        $query = mrt_get_contacts_query($city_slug);
        $phone_number = '';
        $phone_href = '#';

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $phones_field = get_field('contacts_phones');
                if (!empty($phones_field)) {
                    for ($i = 1; $i <= 3; $i++) {
                        $field_key = 'contacts_phone_' . $i;
                        if (!empty($phones_field[$field_key])) {
                            $phone_number = $phones_field[$field_key];
                            $phone_clean = preg_replace('/[^\d\+]/', '', $phone_number);
                            $phone_href = 'tel:' . $phone_clean;
                            break 2;
                        }
                    }
                }
            }
            wp_reset_postdata();
        }

        return array('number' => $phone_number, 'href' => $phone_href);
    }
}

if (!function_exists('mrt_get_animals_map_html')) {
    /**
     * Embedded map for animals branch: ACF contacts_map or Yandex widget by address.
     */
    function mrt_get_animals_map_html($city_slug = null) {
        if ($city_slug === null) {
            $city_slug = mrt_resolve_selected_city('almaty_aubakirova', true);
        }

        $contacts_query = mrt_get_contacts_query($city_slug);
        if ($contacts_query->have_posts()) {
            $contacts_query->the_post();
            $map_html = get_field('contacts_map');
            wp_reset_postdata();
            if (!empty($map_html)) {
                return $map_html;
            }
        }

        $branch = mrt_get_branch($city_slug) ?: mrt_get_branch('almaty_aubakirova');
        $address_full = $branch['address_full'] ?? 'ул. Аубакирова, 17/1, село Отеген батыра, Илийский район, Алматинская область';
        $map_url = 'https://yandex.ru/map-widget/v1/?text=' . rawurlencode($address_full) . '&z=15';

        return sprintf(
            '<iframe src="%s" width="100%%" height="360" frameborder="0" allowfullscreen="true" title="%s"></iframe>',
            esc_url($map_url),
            esc_attr('Карта филиала MRI Animal')
        );
    }
}

if (!function_exists('mrt_get_whatsapp_href')) {
    function mrt_get_whatsapp_href($city_slug) {
        $query = mrt_get_contacts_query($city_slug);
        $digits = '';

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $whatsapp_field = get_field('contacts_whatsapp');
                if (!empty($whatsapp_field)) {
                    $digits = preg_replace('/\D+/', '', $whatsapp_field);
                    break;
                }
            }
            wp_reset_postdata();
        }

        return $digits ? 'https://wa.me/' . $digits : '#';
    }
}
