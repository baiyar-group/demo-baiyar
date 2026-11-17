<?php
/**
 * Baiyar Storefront child theme: Storefront's markup/layout, Baiyar's own color, type, radius, shadow
 * and motion tokens on top (see style.css, hand-synced from packages/ui/src/tokens.ts).
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_enqueue_scripts', function () {
    $theme = wp_get_theme();

    wp_enqueue_style('storefront-style', get_template_directory_uri() . '/style.css', [], $theme->parent() ? $theme->parent()->get('Version') : null);
    wp_enqueue_style(
        'baiyar-storefront-style',
        get_stylesheet_uri(),
        ['storefront-style'],
        $theme->get('Version'),
    );

    wp_enqueue_script(
        'baiyar-storefront-script',
        get_stylesheet_directory_uri() . '/assets/js/baiyar-storefront.js',
        ['jquery'],
        $theme->get('Version'),
        true,
    );
}, 20);

// `no-js` is removed by the script itself on load; CSS treats unremoved `no-js` as "skip animation",
// so a visitor without JS always sees the final, visible state instead of a permanently-hidden one.
add_filter('language_attributes', function ($output) {
    return $output;
});
add_action('wp_head', function () {
    echo "<script>document.documentElement.classList.add('no-js');</script>\n";
}, 0);

// Real Baiyar wordmark in the header instead of Storefront's default site-title text.
add_action('after_setup_theme', function () {
    add_theme_support('custom-logo', [
        'height' => 40,
        'width' => 140,
        'flex-height' => true,
        'flex-width' => true,
    ]);
});

add_action('after_switch_theme', function () {
    $logo_path = get_stylesheet_directory() . '/assets/img/baiyar-logo.svg';
    $existing = get_theme_mod('custom_logo');
    if ($existing || !file_exists($logo_path)) {
        return;
    }

    $upload_dir = wp_upload_dir();
    $destination = trailingslashit($upload_dir['path']) . 'baiyar-logo.svg';
    if (!file_exists($destination)) {
        copy($logo_path, $destination);
    }

    $filetype = wp_check_filetype($destination, null);
    $attachment_id = wp_insert_attachment([
        'post_mime_type' => $filetype['type'] ?: 'image/svg+xml',
        'post_title' => 'Baiyar logo',
        'post_status' => 'inherit',
    ], $destination);

    if (!is_wp_error($attachment_id) && $attachment_id) {
        set_theme_mod('custom_logo', $attachment_id);
    }
});

// WordPress core doesn't generate attachment metadata for SVGs; without this the logo renders at the
// SVG's native 380x116 instead of the constrained size requested above.
add_filter('wp_get_attachment_image_src', function ($image, $attachment_id, $size) {
    if ($image && str_ends_with((string) $image[0], '.svg')) {
        $image[1] = 140;
        $image[2] = 40;
    }
    return $image;
}, 10, 3);
