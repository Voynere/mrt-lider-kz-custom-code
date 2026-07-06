<?php
/**
 * Header helpers (ported from mrt-lider.ru, adapted for KZ).
 */

if (!function_exists('mrt_get_city_slugs')) {
    function mrt_get_city_slugs(): array {
        return mrt_get_known_city_slugs();
    }
}

if (!function_exists('mrt_get_selected_city_slug')) {
    function mrt_get_selected_city_slug(array $options = []): string {
        $default = sanitize_key($options['default'] ?? 'almaty');
        if (!in_array($default, mrt_get_city_slugs(), true)) {
            $default = 'almaty';
        }
        return mrt_resolve_selected_city($default, !empty($options['sync_cookie']));
    }
}

if (!function_exists('mrt_get_city_base_url')) {
    function mrt_get_city_base_url(?string $city_slug = null): string {
        $city_slug = $city_slug ?? mrt_get_selected_city_slug();
        return trailingslashit(home_url('/') . sanitize_key($city_slug));
    }
}

if (!function_exists('mrt_get_city_specific_page_slugs')) {
    function mrt_get_city_specific_page_slugs(): array {
        return array(
            'uslugi-i-ceny',
            'specialisty',
            'vopros-otvet',
            'about',
            'kontakty',
            'pravovaja-i-juridicheskaja-informacija',
            'vacancies',
            'site-map',
            'informacija-o-medicinskoj-organizacii',
            'otzyvy-klientov',
            'tax',
            'zajavka-na-spravku-dlja-nalogovogo-vycheta',
        );
    }
}

if (!function_exists('mrt_get_city_nav_url')) {
    function mrt_get_city_nav_url(string $page_slug, ?string $city_slug = null): string {
        $city_slug = $city_slug ?? mrt_get_selected_city_slug();
        $base = mrt_get_city_base_url($city_slug);

        if (in_array($page_slug, mrt_get_city_specific_page_slugs(), true)) {
            return trailingslashit($base . $page_slug);
        }

        return trailingslashit(home_url('/') . $page_slug);
    }
}

if (!function_exists('mrt_seo_get_cities')) {
    /** Alias for RU header compatibility. */
    function mrt_seo_get_cities(): array {
        return mrt_get_city_map();
    }
}

if (!function_exists('mrt_kz_city_in_line')) {
    function mrt_kz_city_in_line(string $slug): string {
        static $map = array(
            'almaty'           => 'в Алматы',
            'astana'           => 'в Астане',
            'karaganda'        => 'в Караганде',
            'taldykorgan'      => 'в Талдыкоргане',
            'almaty_aubakirova'=> 'в Отеген батыра',
        );

        if (isset($map[$slug])) {
            return $map[$slug];
        }

        $branch = mrt_get_branch($slug);
        if ($branch && !empty($branch['subtitle'])) {
            return 'в ' . $branch['subtitle'];
        }

        return 'в ' . ($branch['label'] ?? $slug);
    }
}

if (!function_exists('mrt_city_timezone')) {
    function mrt_city_timezone(string $city): string {
        return 'Asia/Almaty';
    }
}

if (!function_exists('mrt_parse_opening_hours_line')) {
    function mrt_parse_opening_hours_line(string $line): ?array {
        $line = trim($line);
        if (!preg_match('/(\d{1,2}):(\d{2})\s*[-–—−]\s*(\d{1,2}):(\d{2})/u', $line, $matches)) {
            return null;
        }

        $open = ((int) $matches[1] * 60) + (int) $matches[2];
        $close_h = (int) $matches[3];
        $close_m = (int) $matches[4];
        $close = ($close_h * 60) + $close_m;
        $overnight = false;

        if ($close_h === 0 && $close_m === 0) {
            $close = 24 * 60;
        } elseif ($close <= $open) {
            $overnight = true;
        }

        return array(
            'open'      => $open,
            'close'     => $close,
            'overnight' => $overnight,
        );
    }
}

if (!function_exists('mrt_extract_closing_schedule')) {
    function mrt_extract_closing_schedule(array $opening_hours_group): ?array {
        for ($index = 1; $index <= 3; $index++) {
            $field_key = 'contacts_opening_hours_' . $index;
            if (empty($opening_hours_group[$field_key])) {
                continue;
            }
            $parsed = mrt_parse_opening_hours_line((string) $opening_hours_group[$field_key]);
            if ($parsed !== null) {
                return $parsed;
            }
        }

        return null;
    }
}

if (!function_exists('mrt_get_city_contacts_cached')) {
    function mrt_get_city_contacts_cached(string $city_slug): array {
        $city_slug = sanitize_key($city_slug) ?: 'almaty';
        $cache_key = 'mrt_hdr_contacts_v3_' . $city_slug;
        $cached = get_transient($cache_key);
        if (is_array($cached)) {
            return $cached;
        }

        $data = array(
            'found'               => false,
            'telegram_username'   => '',
            'max_link'            => '',
            'addresses_group'     => array(),
            'phones_group'        => array(),
            'opening_hours_group' => array(),
            'closing_schedule'    => null,
            'timezone'            => mrt_city_timezone($city_slug),
            'whatsapp_digits'     => '',
            'whatsapp_href'       => '#',
            'first_phone_href'    => 'tel:',
            'first_phone_raw'     => '',
            'booking_href'        => '#',
            'booking_label'       => 'Записаться whatsapp',
            'booking_is_max'      => false,
            'contact_post_id'     => 0,
        );

        $query = mrt_get_contacts_query($city_slug);
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $data['found'] = true;
                $data['contact_post_id'] = (int) get_the_ID();
                $data['addresses_group'] = get_field('contacts_addresses') ?: array();
                $data['phones_group'] = get_field('contacts_phones') ?: array();
                $data['opening_hours_group'] = get_field('contacts_opening_hours') ?: array();
                $data['closing_schedule'] = mrt_extract_closing_schedule($data['opening_hours_group']);

                $telegram_field = get_field('contacts_telegram');
                if (!empty($telegram_field)) {
                    $username = ltrim(trim($telegram_field), '@');
                    $data['telegram_username'] = preg_replace('/[^a-zA-Z0-9_]/', '', $username);
                }

                $max_field = get_field('contacts_max');
                if (!empty($max_field)) {
                    $data['max_link'] = trim((string) $max_field);
                }

                $whatsapp_field = get_field('contacts_whatsapp');
                if (!empty($whatsapp_field)) {
                    $data['whatsapp_digits'] = preg_replace('/\D+/', '', $whatsapp_field);
                    $data['whatsapp_href'] = 'https://wa.me/' . $data['whatsapp_digits'];
                }

                $phones_group = $data['phones_group'];
                if (!empty($phones_group['contacts_phone_1'])) {
                    $raw = $phones_group['contacts_phone_1'];
                    $data['first_phone_raw'] = $raw;
                    $data['first_phone_href'] = 'tel:' . preg_replace('/[^\d\+]/', '', $raw);
                }

                if (!empty($data['max_link'])) {
                    $data['booking_href'] = $data['max_link'];
                    $data['booking_label'] = 'Записаться в Max';
                    $data['booking_is_max'] = true;
                } elseif (!empty($data['whatsapp_digits'])) {
                    $data['booking_href'] = $data['whatsapp_href'];
                    $data['booking_label'] = 'Записаться whatsapp';
                }
            }
            wp_reset_postdata();
        }

        set_transient($cache_key, $data, 12 * HOUR_IN_SECONDS);
        return $data;
    }
}

if (!function_exists('mrt_flush_city_contacts_cache')) {
    function mrt_flush_city_contacts_cache(string $city_slug): void {
        delete_transient('mrt_hdr_contacts_v3_' . sanitize_key($city_slug));
    }
}

if (!function_exists('mrt_page_needs_closing_countdown')) {
    function mrt_page_needs_closing_countdown(): bool {
        $city = mrt_get_selected_city_slug();
        $contacts = mrt_get_city_contacts_cached($city);
        return !empty($contacts['closing_schedule']);
    }
}

if (!function_exists('mrt_render_sticky_contact_more')) {
    function mrt_render_sticky_contact_more(array $items, string $type = 'address'): void {
        if ($items === array()) {
            return;
        }

        $extra_count = count($items) - 1;
        echo '<div class="header__sticky-more header__sticky-more--' . esc_attr($type) . '">';

        if ($type === 'phone') {
            $tel_clean = preg_replace('/[^\d\+]/', '', $items[0]);
            echo '<a href="tel:' . esc_attr($tel_clean) . '" class="header__sticky-more-current" data-mrt-phone="header-sticky">' . esc_html($items[0]) . '</a>';
        } else {
            echo '<span class="header__sticky-more-current">' . esc_html($items[0]) . '</span>';
        }

        if ($extra_count > 0) {
            echo '<span class="header__sticky-more-count">+' . (int) $extra_count . '</span>';
            echo '<div class="header__sticky-more-drop">';
            for ($i = 1, $total = count($items); $i < $total; $i++) {
                if ($type === 'phone') {
                    $tel_clean = preg_replace('/[^\d\+]/', '', $items[$i]);
                    echo '<a href="tel:' . esc_attr($tel_clean) . '" data-mrt-phone="header-sticky">' . esc_html($items[$i]) . '</a>';
                } else {
                    echo '<span>' . esc_html($items[$i]) . '</span>';
                }
            }
            echo '</div>';
        }

        echo '</div>';
    }
}

if (!function_exists('mrt_header_booking_attrs')) {
    function mrt_header_booking_attrs(array $contacts): string {
        if (!empty($contacts['booking_href']) && $contacts['booking_href'] !== '#') {
            return ' target="_blank" rel="noopener noreferrer"';
        }
        return ' aria-disabled="true" tabindex="-1" onclick="return false;"';
    }
}
