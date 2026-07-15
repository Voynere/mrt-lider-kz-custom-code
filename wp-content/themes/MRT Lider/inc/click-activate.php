<?php
/**
 * Click-to-activate embeds (Yandex maps) and lazy 3D tours.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Catalogue of local 3D tours. City slugs may include aliases (e.g. blagoveshhensk).
 *
 * @return array<int, array{cities: string[], dir: string, file: string, title: string}>
 */
function mrt_get_3d_tour_catalog(): array {
    return [
        [
            'cities' => ['achinsk'],
            'dir'    => 'Achinsk',
            'file'   => 'mrt-lider-achinsk.html',
            'title'  => '3D тур МРТ Лидер Ачинск',
        ],
        [
            'cities' => ['angarsk'],
            'dir'    => 'Angarsk',
            'file'   => 'mrt-lider-angarsk.html',
            'title'  => '3D тур МРТ Лидер Ангарск',
        ],
        [
            'cities' => ['blagoveshhensk', 'blagoveshcensk', 'blagoveshensk'],
            'dir'    => 'Blagoveshcensk',
            'file'   => 'mrt-lider-blagoveshcensk.html',
            'title'  => '3D тур МРТ Лидер Благовещенск',
        ],
        [
            'cities' => ['bratsk'],
            'dir'    => 'Bratsk',
            'file'   => 'mrt-lider-bratsk.html',
            'title'  => '3D тур МРТ Лидер Братск',
        ],
        [
            'cities' => ['kirov'],
            'dir'    => 'Kirov',
            'file'   => 'mrt-lider-kirov.html',
            'title'  => '3D тур МРТ Лидер Киров',
        ],
        [
            'cities' => ['komsomolsk'],
            'dir'    => 'Komsomolsk',
            'file'   => 'mrt-lider-komsomolsk.html',
            'title'  => '3D тур МРТ Лидер Комсомольск',
        ],
        [
            'cities' => ['kurgan'],
            'dir'    => 'Kurgan',
            'file'   => 'mrt-lider-kurgan.html',
            'title'  => '3D тур МРТ Лидер Курган',
        ],
        [
            'cities' => ['vladivostok'],
            'dir'    => 'Vladivostok_Rysskaya_46',
            'file'   => 'mrt-lider-vladivostok.html',
            'title'  => '3D тур МРТ Лидер Владивосток',
        ],
        [
            'cities' => ['volgodonsk'],
            'dir'    => 'Volgodonsk',
            'file'   => 'mrt-lider-volgodonsk.html',
            'title'  => '3D тур МРТ Лидер Волгодонск',
        ],
    ];
}

/**
 * Wrap embed HTML (usually an iframe from ACF) with a click-to-interact overlay.
 *
 * @param string $html  Raw iframe / embed markup.
 * @param array  $args {
 *     @type string $label       Activator label.
 *     @type string $class       Extra classes for the frame.
 *     @type string $mode        interact|lazy (maps use interact).
 * }
 */
function mrt_wrap_click_activate_frame(string $html, array $args = []): string {
    $html = trim($html);
    if ($html === '') {
        return '';
    }

    // Avoid double-wrapping.
    if (strpos($html, 'data-click-frame') !== false) {
        return $html;
    }

    $label = isset($args['label']) && $args['label'] !== ''
        ? (string) $args['label']
        : 'Нажмите, чтобы взаимодействовать с картой';
    $class = 'mrt-click-frame';
    if (!empty($args['class'])) {
        $class .= ' ' . $args['class'];
    }
    $mode = isset($args['mode']) && $args['mode'] === 'lazy' ? 'lazy' : 'interact';

    // Soften iframe interaction until activated.
    if (stripos($html, '<iframe') !== false && stripos($html, 'pointer-events') === false) {
        if (preg_match('/<iframe\b[^>]*\bstyle=/i', $html)) {
            $html = preg_replace(
                '/(<iframe\b[^>]*\bstyle=(["\']))/i',
                '$1pointer-events:none;',
                $html,
                1
            );
        } else {
            $html = preg_replace(
                '/<iframe\b/i',
                '<iframe style="pointer-events:none;"',
                $html,
                1
            );
        }
        if (stripos($html, 'tabindex=') === false) {
            $html = preg_replace('/<iframe\b/i', '<iframe tabindex="-1"', $html, 1);
        }
    }

    ob_start();
    ?>
<div class="<?php echo esc_attr($class); ?>" data-click-frame data-click-mode="<?php echo esc_attr($mode); ?>">
    <?php echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted ACF / theme embeds ?>
    <button
        type="button"
        class="mrt-click-frame__activator"
        data-click-activator
        aria-label="<?php echo esc_attr($label); ?>"
    >
        <span class="mrt-click-frame__label"><?php echo esc_html($label); ?></span>
    </button>
</div>
    <?php
    return trim((string) ob_get_clean());
}
