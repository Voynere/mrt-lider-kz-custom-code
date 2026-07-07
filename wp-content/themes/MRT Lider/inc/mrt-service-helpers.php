<?php
/**
 * Service landing helpers (ported from mrt-lider.ru, adapted for KZ).
 */

if (!function_exists('mrt_get_kz_cities')) {
    function mrt_get_kz_cities(): array {
        return array('almaty', 'astana', 'karaganda', 'taldykorgan', 'almaty_aubakirova');
    }
}

if (!function_exists('mrt_get_city_phone')) {
    function mrt_get_city_phone(string $city = ''): array {
        if ($city === '') {
            $city = mrt_get_selected_city_slug();
        }

        $contacts = mrt_get_city_contacts_cached($city);
        return array(
            'raw'  => $contacts['first_phone_raw'] ?: '',
            'href' => $contacts['first_phone_href'] ?: 'tel:',
        );
    }
}

if (!function_exists('mrt_get_branch_term_for_city')) {
    function mrt_get_branch_term_for_city(string $city_slug): ?WP_Term {
        $branch = mrt_get_branch($city_slug);
        $taxonomy_slug = !empty($branch['branch_taxonomy'])
            ? $branch['branch_taxonomy']
            : $city_slug;

        $term = get_term_by('slug', $taxonomy_slug, 'branch');
        if ($term && !is_wp_error($term)) {
            return $term;
        }

        if ($branch && !empty($branch['label'])) {
            $term = get_term_by('name', $branch['label'], 'branch');
            if ($term && !is_wp_error($term)) {
                return $term;
            }
        }

        return null;
    }
}

if (!function_exists('mrt_category_to_subcat_slug')) {
    function mrt_category_to_subcat_slug($cat) {
        $cat_lower = mb_strtolower((string) $cat, 'UTF-8');

        $prefix = 'mrt-';
        if (strpos($cat_lower, 'кт') !== false && strpos($cat_lower, 'спект') === false) {
            $prefix = 'kt-';
        } elseif (strpos($cat_lower, 'узи') !== false || strpos($cat_lower, 'ультразвук') !== false) {
            $prefix = 'uzi-';
        } elseif (strpos($cat_lower, 'денситометр') !== false) {
            return 'densitometriya';
        }

        $anatomical = array(
            'голов' => 'golovy', 'головн' => 'golovy', 'гипофиз' => 'golovy',
            'орбит' => 'golovy', 'пазух' => 'golovy', 'ангиограф' => 'golovy',
            'онкопоиск' => 'golovy', 'программ' => 'golovy', 'комплексн' => 'golovy',
            'позвоноч' => 'pozvonochnika', 'спин' => 'pozvonochnika',
            'кресцов' => 'pozvonochnika', 'копчик' => 'pozvonochnika', 'крестц' => 'pozvonochnika',
            'сустав' => 'sustavov', 'колен' => 'sustavov', 'плеч' => 'sustavov',
            'локт' => 'sustavov', 'голеностоп' => 'sustavov', 'кист' => 'sustavov', 'стоп' => 'sustavov',
            'брюшн' => 'brushnoy-polosti', 'живот' => 'brushnoy-polosti',
            'печен' => 'brushnoy-polosti', 'желч' => 'brushnoy-polosti',
            'поджелуд' => 'brushnoy-polosti', 'селезен' => 'brushnoy-polosti',
            'поче' => 'brushnoy-polosti', 'надпочеч' => 'brushnoy-polosti',
            'таз' => 'malogo-taza', 'мочевого' => 'malogo-taza',
            'предстательной' => 'malogo-taza', 'яични' => 'malogo-taza',
            'матк' => 'malogo-taza', 'мошонк' => 'malogo-taza',
            'сердц' => 'serdca-i-sosudov', 'сосуд' => 'serdca-i-sosudov',
            'аорт' => 'serdca-i-sosudov', 'коронар' => 'serdca-i-sosudov',
            'миокард' => 'serdca-i-sosudov', 'вен' => 'serdca-i-sosudov',
            'узлов' => 'myagkih-tkaney', 'лимф' => 'myagkih-tkaney',
            'желез' => 'molochnykh-zhelez', 'молоч' => 'molochnykh-zhelez',
            'грудн' => 'grudnoy-kletki', 'легк' => 'grudnoy-kletki',
            'средостен' => 'grudnoy-kletki', 'ребер' => 'grudnoy-kletki', 'плевр' => 'grudnoy-kletki',
            'мягк' => 'myagkih-tkaney', 'мышц' => 'myagkih-tkaney',
            'связ' => 'myagkih-tkaney', 'сухожил' => 'myagkih-tkaney', 'жиров' => 'myagkih-tkaney',
            'нерв' => 'nervnoj-sistemy', 'сплетен' => 'nervnoj-sistemy',
            'ше' => 'golovy', 'челюст' => 'golovy',
            'гортан' => 'gortani',
            'кост' => 'sustavov',
            'всего тела' => 'golovy',
            'энтерограф' => 'brushnoy-polosti',
            'колоноскоп' => 'brushnoy-polosti',
            'пантомограф' => 'golovy',
        );

        foreach ($anatomical as $kw => $suffix) {
            if (strpos($cat_lower, $kw) !== false) {
                return $prefix . $suffix;
            }
        }

        return rtrim($prefix, '-');
    }
}

if (!function_exists('mrt_get_subcat_slugs')) {
    function mrt_get_subcat_slugs(): array {
        return array(
            'mrt', 'mrt-golovy', 'mrt-pozvonochnika', 'mrt-sustavov', 'mrt-brushnoy-polosti',
            'mrt-malogo-taza', 'mrt-serdca-i-sosudov', 'mrt-grudnoy-kletki', 'mrt-myagkih-tkaney',
            'kt', 'kt-golovy', 'kt-gortani', 'kt-pozvonochnika', 'kt-sustavov', 'kt-brushnoy-polosti',
            'kt-malogo-taza', 'kt-serdca-i-sosudov', 'kt-grudnoy-kletki', 'kt-myagkih-tkaney', 'kt-kompleksnye',
            'uzi', 'uzi-golovy', 'uzi-serdca-i-sosudov', 'uzi-brushnoy-polosti', 'uzi-malogo-taza',
            'uzi-molochnykh-zhelez', 'uzi-sustavov', 'uzi-myagkih-tkaney', 'uzi-grudnoy-kletki',
            'uzi-nervnoj-sistemy', 'densitometriya',
        );
    }
}

if (!function_exists('mrt_normalize_subcat_slug')) {
    function mrt_normalize_subcat_slug(string $subcat_slug): string {
        $subcat_slug = trim($subcat_slug, '/');
        if ($subcat_slug === '') {
            return '';
        }
        if (in_array($subcat_slug, mrt_get_subcat_slugs(), true)) {
            return $subcat_slug;
        }
        if (strpos($subcat_slug, 'mrt-') === 0) {
            return 'mrt';
        }
        if (strpos($subcat_slug, 'kt-') === 0) {
            return 'kt';
        }
        if (strpos($subcat_slug, 'uzi-') === 0) {
            return 'uzi';
        }
        if (in_array($subcat_slug, array('mrt', 'kt', 'uzi', 'densitometriya'), true)) {
            return $subcat_slug;
        }
        return '';
    }
}

if (!function_exists('mrt_service_parent_category_url')) {
    function mrt_service_parent_category_url(WP_Post $service_post, string $city_slug): string {
        $si_category = get_post_meta($service_post->ID, 'si_category', true);
        if (!$si_category) {
            $si_category = get_post_meta($service_post->ID, 'si_oblast', true) ?: $service_post->post_title;
        }
        $subcat_slug = mrt_normalize_subcat_slug(mrt_category_to_subcat_slug($si_category));
        if ($subcat_slug !== '') {
            return home_url('/' . $city_slug . '/uslugi-i-ceny/' . $subcat_slug . '/');
        }
        return home_url('/' . $city_slug . '/uslugi-i-ceny/');
    }
}

if (!function_exists('mrt_find_equivalent_service_in_city')) {
    function mrt_find_equivalent_service_in_city(WP_Post $service_post, string $target_city): ?WP_Post {
        $cities = mrt_seo_get_cities();
        if (!array_key_exists($target_city, $cities)) {
            return null;
        }

        $branch_slug = $target_city;
        $branch = mrt_get_branch($target_city);
        if (!empty($branch['branch_taxonomy'])) {
            $branch_slug = $branch['branch_taxonomy'];
        }

        $branches = wp_get_post_terms($service_post->ID, 'branch', array('fields' => 'slugs'));
        if (!is_wp_error($branches) && in_array($branch_slug, $branches, true)) {
            return $service_post;
        }

        global $wpdb;
        $sibling_ids = $wpdb->get_col($wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts} WHERE post_type='service' AND post_status='publish' AND post_title=%s",
            $service_post->post_title
        ));
        foreach ($sibling_ids as $sid) {
            $sid = (int) $sid;
            $svc_branches = wp_get_post_terms($sid, 'branch', array('fields' => 'slugs'));
            if (!is_wp_error($svc_branches) && in_array($branch_slug, $svc_branches, true)) {
                return get_post($sid);
            }
        }

        $si_oblast = get_post_meta($service_post->ID, 'si_oblast', true);
        if ($si_oblast) {
            $all_in_city = get_posts(array(
                'post_type'      => 'service',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'tax_query'      => array(array(
                    'taxonomy' => 'branch',
                    'field'    => 'slug',
                    'terms'    => $branch_slug,
                )),
            ));
            $oblast_lower = mb_strtolower($si_oblast, 'UTF-8');
            foreach ($all_in_city as $svc) {
                $oblast = get_post_meta($svc->ID, 'si_oblast', true);
                if ($oblast && mb_strtolower($oblast, 'UTF-8') === $oblast_lower) {
                    return $svc;
                }
            }
        }

        return null;
    }
}

if (!function_exists('mrt_resolve_service_city_switch_url')) {
    function mrt_resolve_service_city_switch_url(string $service_slug, string $target_city): string {
        $cities = mrt_seo_get_cities();
        if (!array_key_exists($target_city, $cities)) {
            return home_url('/' . $target_city . '/');
        }

        $svc = get_page_by_path($service_slug, OBJECT, 'service');
        if (!$svc) {
            global $wpdb;
            $svc_id = $wpdb->get_var($wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts} WHERE post_type='service' AND post_status='publish' AND post_name=%s LIMIT 1",
                $service_slug
            ));
            $svc = $svc_id ? get_post((int) $svc_id) : null;
        }
        if (!$svc) {
            return home_url('/' . $target_city . '/uslugi-i-ceny/');
        }

        $equivalent = mrt_find_equivalent_service_in_city($svc, $target_city);
        if ($equivalent) {
            return home_url('/' . $target_city . '/services/' . $equivalent->post_name . '/');
        }

        return mrt_service_parent_category_url($svc, $target_city);
    }
}

if (!function_exists('custom_breadcrumbs_service_landing')) {
    function custom_breadcrumbs_service_landing($svc_name, $si_category = '', $city_slug = 'almaty') {
        $city_base_url = trailingslashit(home_url('/') . $city_slug);
        $uslugi_url = trailingslashit($city_base_url . 'uslugi-i-ceny');

        $subcat_slug = '';
        $subcat_name = '';
        if ($si_category) {
            $subcat_slug = mrt_category_to_subcat_slug($si_category);
            $subcat_names = array(
                'mrt-golovy' => 'МРТ Головы', 'mrt-pozvonochnika' => 'МРТ Позвоночника',
                'mrt-sustavov' => 'МРТ Суставов', 'mrt-brushnoy-polosti' => 'МРТ Брюшной полости',
                'mrt-malogo-taza' => 'МРТ Малого таза', 'mrt-serdca-i-sosudov' => 'МРТ Сердца и сосудов',
                'mrt-grudnoy-kletki' => 'МРТ Грудной клетки', 'mrt-myagkih-tkaney' => 'МРТ Мягких тканей',
                'kt' => 'КТ', 'uzi' => 'УЗИ', 'densitometriya' => 'Денситометрия',
            );
            $subcat_name = $subcat_slug ? ($subcat_names[$subcat_slug] ?? '') : '';
        }

        echo '<div class="breadcrumbs"><div class="container"><ul class="breadcrumbs__list">';
        echo '<li><a href="' . esc_url($city_base_url) . '">Главная</a></li>';
        echo '<li><svg width="8" height="17" viewBox="0 0 8 17" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M0 1.47L0.8625 0.5L8 8.5L0.8625 16.5L0 15.535L6.27083 8.5L0 1.47Z" fill="#404040"/></svg></li>';
        echo '<li><a href="' . esc_url($uslugi_url) . '">Услуги и цены</a></li>';

        if ($subcat_name && $subcat_slug) {
            $subcat_url = trailingslashit($city_base_url . 'uslugi-i-ceny/' . $subcat_slug);
            echo '<li><svg width="8" height="17" viewBox="0 0 8 17" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M0 1.47L0.8625 0.5L8 8.5L0.8625 16.5L0 15.535L6.27083 8.5L0 1.47Z" fill="#404040"/></svg></li>';
            echo '<li><a href="' . esc_url($subcat_url) . '">' . esc_html($subcat_name) . '</a></li>';
        }

        echo '<li><svg width="8" height="17" viewBox="0 0 8 17" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M0 1.47L0.8625 0.5L8 8.5L0.8625 16.5L0 15.535L6.27083 8.5L0 1.47Z" fill="#404040"/></svg></li>';
        echo '<li>' . esc_html($svc_name) . '</li>';
        echo '</ul></div></div>';
    }
}

if (!function_exists('mrt_seo_is_service_landing')) {
    function mrt_seo_is_service_landing(): bool {
        if (!empty(get_query_var('mrt_service_landing')) && !empty(get_query_var('mrt_city'))) {
            return true;
        }

        $parts = mrt_get_request_path_parts();
        $cities = mrt_seo_get_cities();
        return count($parts) >= 3
            && isset($cities[$parts[0]])
            && ($parts[1] ?? '') === 'services'
            && !empty($parts[2]);
    }
}

if (!function_exists('mrt_get_service_landing_url')) {
    function mrt_get_service_landing_url(string $city_slug, string $service_post_name): string {
        return trailingslashit(home_url('/' . sanitize_key($city_slug) . '/services/' . sanitize_title($service_post_name)));
    }
}

if (!function_exists('mrt_normalize_service_text')) {
    function mrt_normalize_service_text(string $text): string {
        $text = str_replace(array("\r\n", "\r"), "\n", $text);
        $text = preg_replace('/\n+/u', ' ', $text);
        $text = preg_replace('/[ \t]+/u', ' ', $text);
        return trim((string) $text);
    }
}

if (!function_exists('mrt_service_display_names')) {
    function mrt_service_display_names(string $raw, string $category_hint = ''): array {
        $clean = mrt_normalize_service_text($raw);
        return array(
            'short'      => $clean,
            'full'       => $clean,
            'is_complex' => false,
        );
    }
}

if (!function_exists('mrt_get_service_oblast_display')) {
    function mrt_get_service_oblast_display(int $post_id, string $style = 'short'): string {
        $raw = get_post_meta($post_id, 'si_oblast', true);
        if ($raw === '' || $raw === null) {
            $raw = get_post_field('post_title', $post_id);
        }
        $names = mrt_service_display_names((string) $raw, (string) get_post_meta($post_id, 'si_category', true));
        return $style === 'full' ? $names['full'] : $names['short'];
    }
}

if (!function_exists('mrt_city_branch_features')) {
    function mrt_city_branch_features(string $city): array {
        return array('oms' => false, 'dms' => true);
    }
}

if (!function_exists('mrt_city_mri_equipment_brand')) {
    function mrt_city_mri_equipment_brand(string $city): string {
        return 'Siemens';
    }
}

if (!function_exists('mrt_city_mri_equipment_label')) {
    function mrt_city_mri_equipment_label(string $city): string {
        return mrt_city_mri_equipment_brand($city) . ' 1.5Т';
    }
}

if (!function_exists('mrt_city_mri_equipment_phrase')) {
    function mrt_city_mri_equipment_phrase(string $city): string {
        return 'современное оборудование ' . mrt_city_mri_equipment_brand($city);
    }
}

if (!function_exists('mrt_city_mri_equipment_benefit_desc')) {
    function mrt_city_mri_equipment_benefit_desc(string $city): string {
        return 'Томографы Siemens Magnetom — точность до 0.5 мм';
    }
}

if (!function_exists('mrt_city_insurance_label')) {
    function mrt_city_insurance_label(array $features): string {
        $parts = array();
        if (!empty($features['dms'])) {
            $parts[] = 'ДМС';
        }
        return implode(', ', $parts);
    }
}

if (!function_exists('mrt_city_insurance_hint')) {
    function mrt_city_insurance_hint(array $features): string {
        return !empty($features['dms']) ? 'Работаем с полисами ДМС' : '';
    }
}

if (!function_exists('mrt_city_uses_concessional_pricing')) {
    function mrt_city_uses_concessional_pricing(string $city, string $service_type_slug = '', string $service_type_name = ''): bool {
        return false;
    }
}

if (!function_exists('mrt_city_has_concessional_price_notice')) {
    function mrt_city_has_concessional_price_notice(string $city): bool {
        return false;
    }
}

if (!function_exists('mrt_should_show_concessional_price_notice')) {
    function mrt_should_show_concessional_price_notice(string $city, bool $section_has_discounted_prices): bool {
        return false;
    }
}

if (!function_exists('mrt_service_price_is_numeric')) {
    function mrt_service_price_is_numeric(mixed $value): bool {
        if ($value === null || $value === '') {
            return false;
        }
        if (is_int($value) || is_float($value)) {
            return true;
        }
        $string = trim((string) $value);
        return $string !== '' && is_numeric($string);
    }
}

if (!function_exists('mrt_service_price_text_label')) {
    function mrt_service_price_text_label(mixed $price, mixed $discount): string {
        foreach (array($price, $discount) as $value) {
            if ($value === null || $value === '') {
                continue;
            }
            if (!mrt_service_price_is_numeric($value)) {
                return trim((string) $value);
            }
        }
        return '';
    }
}

if (!function_exists('mrt_service_price_parts')) {
    function mrt_service_price_parts(int $price, int $discount, bool $concessional_mode): array {
        $has_discount = ($price > 0 && $discount > 0 && $price !== $discount);
        $current = $has_discount ? min($price, $discount) : $price;
        $old = $has_discount ? max($price, $discount) : 0;

        return array(
            'mode'            => 'standard',
            'current'         => $current,
            'old'             => $old,
            'has_discount'    => $has_discount,
            'discount_amount' => $has_discount ? ($old - $current) : 0,
        );
    }
}

if (!function_exists('mrt_service_price_parts_from_meta')) {
    function mrt_service_price_parts_from_meta(mixed $price_raw, mixed $discount_raw, bool $concessional_mode): array {
        $text = mrt_service_price_text_label($price_raw, $discount_raw);
        if ($text !== '') {
            return array('mode' => 'text', 'text' => $text);
        }
        return mrt_service_price_parts((int) $price_raw, (int) $discount_raw, $concessional_mode);
    }
}

if (!function_exists('mrt_format_price_amount')) {
    function mrt_format_price_amount(int $amount, string $currency_symbol): string {
        return $amount > 0
            ? number_format($amount, 0, '', ' ') . ' ' . $currency_symbol
            : '';
    }
}

if (!function_exists('mrt_format_service_price_display')) {
    function mrt_format_service_price_display(mixed $price, mixed $discount, string $currency_symbol): string {
        $text = mrt_service_price_text_label($price, $discount);
        if ($text !== '') {
            return $text;
        }

        $numeric = 0;
        if (mrt_service_price_is_numeric($price)) {
            $numeric = (int) $price;
        } elseif (mrt_service_price_is_numeric($discount)) {
            $numeric = (int) $discount;
        }

        return $numeric > 0 ? mrt_format_price_amount($numeric, $currency_symbol) : '—';
    }
}

if (!function_exists('mrt_service_item_has_discounted_price')) {
    function mrt_service_item_has_discounted_price(mixed $price, mixed $discount, string $city, string $service_type_slug = '', string $service_type_name = ''): bool {
        if (mrt_service_price_text_label($price, $discount) !== '') {
            return false;
        }
        $parts = mrt_service_price_parts((int) $price, (int) $discount, false);
        return !empty($parts['has_discount']);
    }
}

if (!function_exists('mrt_render_price_table_cell')) {
    function mrt_render_price_table_cell(array $parts, string $currency_symbol): string {
        if (($parts['mode'] ?? '') === 'text') {
            $text = trim((string) ($parts['text'] ?? ''));
            return '<p class="price__item-cena price__item-cena--text">'
                . esc_html($text !== '' ? $text : 'Цена не указана')
                . '</p>';
        }

        $current = (int) ($parts['current'] ?? 0);
        $old = (int) ($parts['old'] ?? 0);
        $has_discount = !empty($parts['has_discount']);

        $html = '<p class="price__item-cena">';
        if ($has_discount && $old > 0) {
            $html .= '<span style="text-decoration:line-through;color:#999;font-size:13px;">'
                . esc_html(mrt_format_price_amount($old, $currency_symbol)) . '</span><br>';
        }
        $html .= $current > 0
            ? esc_html(mrt_format_price_amount($current, $currency_symbol))
            : 'Цена не указана';
        $html .= '</p>';
        return $html;
    }
}

if (!function_exists('mrt_render_price_hero_block')) {
    function mrt_render_price_hero_block(mixed $price, mixed $discount, string $currency_symbol, string $city, string $wrapper_class, string $service_type_slug = '', string $service_type_name = ''): string {
        $text = mrt_service_price_text_label($price, $discount);
        if ($text !== '') {
            return '<div class="' . esc_attr($wrapper_class) . ' ' . esc_attr($wrapper_class) . '--text">'
                . '<div class="' . esc_attr($wrapper_class) . '__value">' . esc_html($text) . '</div>'
                . '</div>';
        }

        $parts = mrt_service_price_parts((int) $price, (int) $discount, false);
        $current = (int) ($parts['current'] ?? 0);
        $old = (int) ($parts['old'] ?? 0);
        $html = '<div class="' . esc_attr($wrapper_class) . '">';
        if ($current > 0) {
            $html .= '<div class="' . esc_attr($wrapper_class) . '__value">'
                . esc_html(mrt_format_price_amount($current, $currency_symbol)) . '</div>';
            if ($old > 0 && $old !== $current) {
                $html .= '<div class="' . esc_attr($wrapper_class) . '__old">'
                    . esc_html(mrt_format_price_amount($old, $currency_symbol)) . '</div>';
            }
        } else {
            $html .= '<div class="' . esc_attr($wrapper_class) . '__value">Цена уточняется</div>';
        }
        $html .= '</div>';
        return $html;
    }
}

if (!function_exists('mrt_render_concessional_price_notice')) {
    function mrt_render_concessional_price_notice(string $city): string {
        return '';
    }
}

if (!function_exists('mrt_is_subcategory_hidden_for_city')) {
    function mrt_is_subcategory_hidden_for_city(string $subcategory_slug, string $city): bool {
        return false;
    }
}

if (!function_exists('mrt_is_service_hidden_for_city')) {
    function mrt_is_service_hidden_for_city(int $service_id, string $city): bool {
        return false;
    }
}

if (!function_exists('mrt_city_discount_benefit_desc')) {
    function mrt_city_discount_benefit_desc(string $city): string {
        return 'Скидки до 30%, акции для постоянных клиентов';
    }
}

if (!function_exists('mrt_city_booking_urgency_text')) {
    function mrt_city_booking_urgency_text(string $city): ?string {
        return null;
    }
}

if (!function_exists('mrt_apply_city_equipment_branding')) {
    function mrt_apply_city_equipment_branding(string $text, string $city): string {
        if ($text === '' || mrt_city_mri_equipment_brand($city) === 'Siemens') {
            return $text;
        }

        $brand = mrt_city_mri_equipment_brand($city);
        $replacements = array(
            'Siemens Magnetom' => $brand,
            'томографах Siemens' => 'томографах ' . $brand,
            'аппаратах Siemens' => 'аппаратах ' . $brand,
            'оборудование Siemens' => 'оборудование ' . $brand,
            'Siemens' => $brand,
        );

        foreach ($replacements as $from => $to) {
            $text = str_replace($from, $to, $text);
        }

        return $text;
    }
}

if (!function_exists('mrt_posts_have_discounted_price')) {
    /**
     * @param iterable<int|string|WP_Post> $posts
     */
    function mrt_posts_have_discounted_price(iterable $posts, string $city, string $service_type_slug = '', string $service_type_name = ''): bool {
        foreach ($posts as $post) {
            $post_id = $post instanceof WP_Post ? $post->ID : (int) $post;
            if ($post_id <= 0) {
                continue;
            }

            $slug = $service_type_slug;
            $name = $service_type_name;
            if ($slug === '' && $name === '') {
                $types = wp_get_post_terms($post_id, 'service_type');
                if (!empty($types) && !is_wp_error($types)) {
                    $slug = (string) ($types[0]->slug ?? '');
                    $name = (string) ($types[0]->name ?? '');
                }
            }

            $price = get_post_meta($post_id, 'si_price', true);
            $discount = get_post_meta($post_id, 'si_discount', true);
            if (mrt_service_price_text_label($price, $discount) !== '') {
                continue;
            }
            if (mrt_service_item_has_discounted_price((int) $price, (int) $discount, $city, $slug, $name)) {
                return true;
            }
        }

        return false;
    }
}
