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
                'yandex_metrika_id' => 110468879,
            ),
            'taldykorgan' => array(
                'label'             => 'Талдыкорган',
                'type'              => 'standard',
                'currency'          => 'tenge',
                'country'           => 'kz',
                'yandex_metrika_id' => 110469944,
            ),
            'almaty_aubakirova' => array(
                'label'           => 'МРТ животным «MRI Animal»',
                'subtitle'        => 'с. Отеген батыра',
                'type'            => 'animals',
                'currency'        => 'tenge',
                'country'         => 'kz',
                'form_email'      => 'mri-animal@mail.ru',
                'address_short'   => 'ул. Аубакирова, 17/1',
                'address_full'    => 'ул. Аубакирова, 17/1, село Отеген батыра, Илийский район, Алматинская область',
                'branch_taxonomy' => 'almaty_aubakirova',
                'map_embed'       => 'https://yandex.ru/map-widget/v1/?um=constructor%3Aplaceholder&amp;source=constructor',
                'home_template'   => 'home-animals.php',
                'centre_photos'   => array(
                    array(
                        'file' => 'animals/centre/01-mri-room.png',
                        'alt'  => 'Кабинет МРТ MRI Animal — Philips 1,5 Т',
                    ),
                    array(
                        'file' => 'animals/centre/02-mri-scan.png',
                        'alt'  => 'МРТ-исследование животного под контролем врача',
                    ),
                    array(
                        'file' => 'animals/centre/03-flyer-front.png',
                        'alt'  => 'Филиал МРТ для животных MRI Animal в с. Отеген батыра',
                    ),
                    array(
                        'file' => 'animals/centre/04-flyer-back.png',
                        'alt'  => 'Услуги и цены MRI Animal — диагностика для питомцев',
                    ),
                    array(
                        'file' => 'animals/centre/05-equipment.png',
                        'alt'  => 'Современный аппарат МРТ экспертного класса',
                    ),
                ),
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

        $id = (string) $counter_id;

        return "<!-- Yandex.Metrika counter -->\n"
            . "<script type=\"text/javascript\">\n"
            . "    (function(m,e,t,r,i,k,a){\n"
            . "        m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};\n"
            . "        m[i].l=1*new Date();\n"
            . "        for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}\n"
            . "        k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)\n"
            . "    })(window, document,'script','https://mc.yandex.ru/metrika/tag.js?id={$id}', 'ym');\n\n"
            . "    ym({$id}, 'init', {ssr:true, webvisor:true, clickmap:true, ecommerce:\"dataLayer\", referrer: document.referrer, url: location.href, accurateTrackBounce:true, trackLinks:true});\n"
            . "</script>\n"
            . "<noscript><div><img src=\"https://mc.yandex.ru/watch/{$id}\" style=\"position:absolute; left:-9999px;\" alt=\"\" /></div></noscript>\n"
            . "<!-- /Yandex.Metrika counter -->";
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

if (!function_exists('mrt_get_form_notification_settings')) {
    /**
     * Email и Telegram для заявок с форм: конфиг филиала → ACF → дефолт.
     *
     * @return array{email: string, telegram_chat_ids: string[]}
     */
    function mrt_get_form_notification_settings($city_slug) {
        $default_email = 'prooo100mix@yandex.ru';
        $settings = array(
            'email'               => $default_email,
            'telegram_chat_ids'   => array(),
        );

        $branch = mrt_get_branch($city_slug);
        if ($branch && !empty($branch['form_email'])) {
            $settings['email'] = sanitize_email($branch['form_email']);
        }

        $query = mrt_get_contacts_query($city_slug);
        if (!$query->have_posts()) {
            return $settings;
        }

        while ($query->have_posts()) {
            $query->the_post();

            if (empty($branch['form_email'])) {
                $emails_group = get_field('contacts_emails');
                if (!empty($emails_group['contacts_email_1'])) {
                    $settings['email'] = sanitize_email($emails_group['contacts_email_1']);
                }
            }

            $telegram_group = get_field('telegram_chats');
            if (!empty($telegram_group)) {
                $chat_ids = array();
                if (!empty($telegram_group['telegram_chat_1'])) {
                    $chat_ids[] = $telegram_group['telegram_chat_1'];
                }
                if (!empty($telegram_group['telegram_chat_2'])) {
                    $chat_ids[] = $telegram_group['telegram_chat_2'];
                }
                if (!empty($chat_ids)) {
                    $settings['telegram_chat_ids'] = $chat_ids;
                }
            }
        }
        wp_reset_postdata();

        return $settings;
    }
}

if (!function_exists('mrt_get_contact_display_emails')) {
    /**
     * Emails for display on contacts page: ACF contacts_emails → branch form_email.
     *
     * @return string[]
     */
    function mrt_get_contact_display_emails($city_slug, array $emails_group = array()) {
        $emails = array();
        for ($i = 1; $i <= 10; $i++) {
            $key = 'contacts_email_' . $i;
            if (!empty($emails_group[$key])) {
                $emails[] = sanitize_email($emails_group[$key]);
            }
        }

        if (!empty($emails)) {
            return $emails;
        }

        $branch = mrt_get_branch($city_slug);
        if ($branch && !empty($branch['form_email'])) {
            return array(sanitize_email($branch['form_email']));
        }

        return $emails;
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
                return function_exists('mrt_wrap_click_activate_frame')
                    ? mrt_wrap_click_activate_frame($map_html, [
                        'label' => 'Нажмите, чтобы взаимодействовать с картой',
                        'class' => 'animals-map-click',
                    ])
                    : $map_html;
            }
        }

        $branch = mrt_get_branch($city_slug) ?: mrt_get_branch('almaty_aubakirova');
        $address_full = $branch['address_full'] ?? 'ул. Аубакирова, 17/1, село Отеген батыра, Илийский район, Алматинская область';
        $map_url = 'https://yandex.ru/map-widget/v1/?text=' . rawurlencode($address_full) . '&z=15';

        $fallback = sprintf(
            '<iframe src="%s" width="100%%" height="360" frameborder="0" allowfullscreen="true" title="%s"></iframe>',
            esc_url($map_url),
            esc_attr('Карта филиала MRI Animal')
        );

        return function_exists('mrt_wrap_click_activate_frame')
            ? mrt_wrap_click_activate_frame($fallback, [
                'label' => 'Нажмите, чтобы взаимодействовать с картой',
                'class' => 'animals-map-click',
            ])
            : $fallback;
    }
}

if (!function_exists('mrt_parse_acf_centre_photo_item')) {
    /**
     * Normalize one ACF photos_centre_* value to gallery item or null.
     *
     * @return array{full: string, thumb: string, alt: string}|null
     */
    function mrt_parse_acf_centre_photo_item($item, $default_alt = '') {
        if (empty($item)) {
            return null;
        }

        $full_url  = '';
        $thumb_url = '';
        $alt_text  = $default_alt;

        if (is_int($item) || ctype_digit((string) $item)) {
            $attach_id = (int) $item;
            $full_url  = wp_get_attachment_image_url($attach_id, 'full');
            $thumb_url = wp_get_attachment_image_url($attach_id, 'medium') ?: $full_url;
            $alt_text  = get_post_meta($attach_id, '_wp_attachment_image_alt', true) ?: $default_alt;
        } elseif (is_array($item)) {
            if (!empty($item['url'])) {
                $full_url = $item['url'];
            }
            if (!empty($item['sizes']['medium'])) {
                $thumb_url = $item['sizes']['medium'];
            } elseif (!empty($item['sizes']['thumbnail'])) {
                $thumb_url = $item['sizes']['thumbnail'];
            } else {
                $thumb_url = $full_url;
            }
            if (!empty($item['alt'])) {
                $alt_text = $item['alt'];
            } elseif (!empty($item['ID'])) {
                $alt_text = get_post_meta((int) $item['ID'], '_wp_attachment_image_alt', true) ?: $default_alt;
            }
        } elseif (is_string($item)) {
            $full_url  = $item;
            $thumb_url = $item;
        }

        if (!$full_url) {
            return null;
        }

        return array(
            'full'  => esc_url_raw($full_url),
            'thumb' => esc_url_raw($thumb_url ?: $full_url),
            'alt'   => sanitize_text_field($alt_text),
        );
    }
}

if (!function_exists('mrt_get_centre_photos_from_post')) {
    /**
     * @return array<int, array{full: string, thumb: string, alt: string}>
     */
    function mrt_get_centre_photos_from_post($post_id) {
        $photos = array();
        $photos_group = get_field('photos_centre', $post_id) ?: array();
        $default_alt = get_the_title($post_id);

        for ($i = 1; $i <= 8; $i++) {
            $field_key = 'photos_centre_' . $i;
            if (empty($photos_group[$field_key])) {
                continue;
            }

            $parsed = mrt_parse_acf_centre_photo_item($photos_group[$field_key], $default_alt);
            if ($parsed) {
                $photos[] = $parsed;
            }
        }

        return $photos;
    }
}

if (!function_exists('mrt_get_branch_centre_photo_fallbacks')) {
    /**
     * Theme asset gallery from branch config when ACF photos_centre is empty.
     *
     * @return array<int, array{full: string, thumb: string, alt: string}>
     */
    function mrt_get_branch_centre_photo_fallbacks($city_slug) {
        $branch = mrt_get_branch($city_slug);
        if (!$branch || empty($branch['centre_photos']) || !is_array($branch['centre_photos'])) {
            return array();
        }

        $base_uri = get_template_directory_uri() . '/assets/img/';
        $photos = array();

        foreach ($branch['centre_photos'] as $photo) {
            if (empty($photo['file'])) {
                continue;
            }

            $url = $base_uri . ltrim($photo['file'], '/');
            $photos[] = array(
                'full'  => esc_url_raw($url),
                'thumb' => esc_url_raw($url),
                'alt'   => sanitize_text_field($photo['alt'] ?? 'MRI Animal'),
            );
        }

        return $photos;
    }
}

if (!function_exists('mrt_get_centre_photos')) {
    /**
     * Centre gallery: ACF photos_centre on contacts post, then branch theme fallbacks.
     *
     * @return array<int, array{full: string, thumb: string, alt: string}>
     */
    function mrt_get_centre_photos($city_slug, $limit = 5) {
        $photos = array();
        $query = mrt_get_contacts_query($city_slug);

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $photos = mrt_get_centre_photos_from_post(get_the_ID());
            }
            wp_reset_postdata();
        }

        if (empty($photos)) {
            $photos = mrt_get_branch_centre_photo_fallbacks($city_slug);
        }

        if ($limit > 0) {
            $photos = array_slice($photos, 0, $limit);
        }

        return $photos;
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
