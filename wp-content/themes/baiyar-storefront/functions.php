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

    // Tailwind's Play CDN: this theme has no Node/build step (docker compose + wp-cli only), so this is
    // the pragmatic choice over a compiled pipeline. Markup we author ourselves (front-page.php,
    // footer-content.php, the announcement bar/cart-drawer chrome below) uses Tailwind utility classes
    // directly; style.css still carries the handful of overrides WooCommerce's own generated markup
    // (product loop, mini-cart widget, block checkout) needs, since we don't write that HTML ourselves.
    wp_enqueue_script('tailwind-play', 'https://cdn.tailwindcss.com/3.4.17', [], null, false);
    // Tailwind config (token-mapped from packages/ui/src/tokens.ts), attached as an inline script
    // guaranteed to print right after the CDN script - `tailwind.config` must exist before Tailwind
    // scans the page.
    wp_add_inline_script('tailwind-play', "tailwind.config = {
        // Preflight (Tailwind's base CSS reset) rewrites default element styles site-wide; disabled so
        // it doesn't reset the base styles WooCommerce/Storefront's own CSS assumes are still there.
        corePlugins: { preflight: false },
        // Tailwind's JIT scans the whole page's rendered HTML for class names and generates a utility
        // for anything that matches its naming convention - including class names it didn't author.
        // WooCommerce puts \"columns-N\" on the product grid <ul> (N = the catalog columns setting,
        // nothing to do with Tailwind), which collides with Tailwind's own `columns-N` utility
        // (CSS `columns: N`, multi-column text layout) and was splitting every product card's text into
        // narrow newspaper-style columns. Block the ones WooCommerce can produce (1-6).
        blocklist: ['columns-1', 'columns-2', 'columns-3', 'columns-4', 'columns-5', 'columns-6'],
        theme: {
            extend: {
                colors: {
                    brand: { 50: '#eff4ff', 100: '#dbe6fe', 400: '#5b93f5', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8' },
                    ink: '#0f172c',
                },
                fontFamily: { sans: ['Satoshi', 'ui-sans-serif', 'system-ui', 'sans-serif'] },
                borderRadius: { xs: '6px', sm: '8px', md: '10px', lg: '14px', xl: '18px', '2xl': '24px' },
                boxShadow: {
                    sm: '0 1px 2px rgba(15,23,42,.04)',
                    md: '0 4px 12px rgba(15,23,42,.06)',
                    lg: '0 12px 32px rgba(15,23,42,.08)',
                    xl: '0 20px 48px rgba(15,23,42,.10)',
                },
                backgroundImage: {
                    'brand-gradient': 'linear-gradient(180deg, #1d4ed8 0%, #3b82f6 50%, #2563eb 100%)',
                },
            },
        },
    };", 'after');

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

// The store has its own brand (set to "Lapik" by setup.sh via `wp option update blogname`) - Baiyar is
// the payment processor behind it, not the retailer, so its mark belongs at checkout and in the
// footer's "Pembayaran oleh" line (baiyar_inline_logo() below), never as the site's own header logo.
// The header therefore just shows the site title as a styled text wordmark (see .site-branding in
// style.css) - no custom_logo is set.

// This demo's product photos are SVG illustrations (bin/product-images, generated to match Baiyar's
// tokens) sideloaded by setup.sh via `wp media import --featured_image`; WP blocks SVG uploads by
// default, so both layers of its filetype check need to allow it.
add_filter('upload_mimes', function ($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
});
add_filter('wp_check_filetype_and_ext', function ($data, $file, $filename) {
    if (str_ends_with(strtolower((string) $filename), '.svg')) {
        $data['ext'] = 'svg';
        $data['type'] = 'image/svg+xml';
    }
    return $data;
}, 10, 3);

// A single-purpose store demo has no use for Storefront's blog sidebar (Recent Posts/Comments/
// Archives) next to the shop and product pages - drop it everywhere for a full-width, uncluttered
// layout closer to a real storefront than a blog with a shop bolted on.
add_action('init', function () {
    remove_action('storefront_sidebar', 'storefront_get_sidebar');
});

/** The real Baiyar wordmark, inline (so `currentColor`/height can be set from CSS), for spots other
 * than the header logo (e.g. the footer payment line) that custom-logo can't reach. */
function baiyar_inline_logo($height = 20)
{
    static $markup = null;
    if ($markup === null) {
        $path = get_stylesheet_directory() . '/assets/img/baiyar-logo.svg';
        $markup = file_exists($path) ? file_get_contents($path) : '';
    }
    return $markup ? preg_replace('/<svg /', '<svg style="height:' . (int) $height . 'px;width:auto" ', $markup, 1) : '';
}

// Announcement bar: wp_body_open fires right after <body>, before any theme markup, so this always
// lands above the header regardless of Storefront's own hook names.
add_action('wp_body_open', function () {
    echo '<div class="bg-ink px-4 py-2.5 text-center"><p class="m-0 text-[0.8125rem] font-medium tracking-wide text-white">'
        . esc_html__('Checkout ini pakai Baiyar sungguhan - coba bayar dengan QRIS, VA atau e-wallet.', 'baiyar-storefront')
        . '</p></div>';
});

// Replace Storefront's (empty, unconfigured) footer widget row with a newsletter + link-column block,
// kept ahead of storefront_credit so "(c) ... Built with WooCommerce" still closes the page.
add_action('init', function () {
    remove_action('storefront_footer', 'storefront_footer_widgets', 10);
});
add_action('storefront_footer', function () {
    get_template_part('template-parts/footer', 'content');
}, 5);
