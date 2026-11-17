# demo-baiyar

A throwaway WooCommerce store to work out what a real `baiyar-for-woocommerce` plugin would need from
Baiyar's Merchant API, before building it for real. Nothing here is wired up to Baiyar yet and nothing
here accepts real payments.

## Why

Baiyar's landing page already lists WooCommerce as a planned integration. Before building a plugin for
real, this repo is a sandbox to answer open questions concretely:

- Hosted redirect (`checkout.baiyar.com`) vs. an inline/embedded checkout on the store's own page, or both.
- What a line-item array (product name, qty, price) needs to look like on `CreatePaymentRequest` /
  `CreatePaymentLinkRequest` so the checkout page can show an itemized breakdown instead of one free-text
  description.
- How order status updates come back (webhook vs. the merchant polling a payment's status).

Reference implementations studied so far: `ipaymu/ipaymu-for-woocommerce` and DOKU's WooCommerce plugin -
both are hosted-redirect only, no inline mode.

## Run the demo store

```bash
docker compose up -d
docker compose run --rm wpcli bash /setup/setup.sh
```

Port 8080 taken? Set `DEMO_PORT` first, e.g. `DEMO_PORT=18080 docker compose up -d && DEMO_PORT=18080 docker compose run --rm wpcli bash /setup/setup.sh`.

The second command installs WordPress, WooCommerce, the Baiyar Storefront child theme, activates
"Baiyar for WooCommerce" (mounted from `wp-content/plugins/baiyar-for-woocommerce`), enables the Baiyar
gateway, sets up a curated nav menu, and creates a 6-product catalog (sneakers + sandals, two
categories) in IDR with generated product photos. Takes under a minute.

- Homepage: hero, a trust bar, two category tiles, a "Paling laris" (best sellers) grid, a brand-story
  block, and a full footer - see "The theme" below. `front-page.php` takes over the homepage
  automatically (WordPress template hierarchy), no Settings > Reading change needed.
- Shop, category pages, product pages, add to cart, cart, checkout: use the nav links or the header cart
  icon (don't hand-type a raw `?page_id=` URL for the shop - WooCommerce's shop-page detection only
  kicks in on the canonical `/shop/` URL once pretty permalinks are on).
- Admin: `http://localhost:8080/wp-admin` (`admin` / `admin`)
- Payment options at checkout already show "Baiyar" selected - the gateway is enabled by `setup.sh`.

Re-running `setup.sh` is safe: it wipes and regenerates the product catalog, menu and theme/plugin/
currency/gateway settings every time, so it's always idempotent rather than silently drifting.

## What's here

- `wp-content/plugins/baiyar-for-woocommerce/` - a plugin stub. `process_payment()` and the webhook
  handler (`class-wc-gateway-baiyar.php`) are `TODO`s, not implemented - the point right now is the
  store/checkout flow, not a working gateway.
- `wp-content/themes/baiyar-storefront/` - a Storefront child theme restyled with Baiyar's own color,
  type, radius, shadow and motion tokens (hand-synced from `packages/ui/src/tokens.ts` in
  `baiyar-payment-platform` - see that repo's `baiyar-theming-ui` skill). The store's own brand is
  **"Lapik"** (a WordPress text wordmark, see "Store brand vs. payment processor" below); buttons use
  the brand gradient with a hover lift; product cards get a shadow lift on hover; add-to-cart gives a
  short pulse and the header cart icon bumps; sections fade up into view on scroll. Every animation is
  skipped under `prefers-reduced-motion`, per that skill's rule. `front-page.php` is the homepage
  template (hero/trust-bar/category-tiles/best-sellers/story); `template-parts/footer-content.php` is
  the newsletter + link columns + social row, hooked in ahead of Storefront's own credit line. The
  mini-cart dropdown Storefront already ships (hidden off-screen, shown on hover) is restyled into a
  real slide-out drawer, opened by click (not hover) and auto-opened on add-to-cart.
  - Markup we write ourselves (front-page.php, the footer, the announcement bar, the cart drawer's
    overlay/close button) uses **Tailwind utility classes**, loaded via the Play CDN (no Node/build step
    in this repo) with a token-mapped config in `functions.php`. WooCommerce's own generated markup
    (the product loop, the mini-cart widget, block checkout) still gets plain CSS in `style.css` -
    we don't write that HTML, so there's nothing to put a utility class on.
- `bin/product-images/` - generated SVG product photos (sneaker/sandal line art on Baiyar-token
  backdrops), sideloaded onto each product by `setup.sh` via `wp media import --featured_image`.
- `bin/setup.sh` - provisions the store via WP-CLI (run through the `wpcli` compose service).
- `assets/img/hero-photo.jpg` - the homepage hero banner, a free Unsplash-licensed stock photo (royalty-
  free, no attribution required, credit appreciated - photo id `1635252261174-aa7945dc2902`). The header
  is `position: sticky` (stays onscreen while scrolling) and the hero photo is pulled up behind it with a
  negative margin so the pill nav visibly floats on top of the image, closer to how Allbirds' own hero
  banner behaves.

## Store brand vs. payment processor

The storefront is its own brand, **Lapik** ("sole/base" in Indonesian) - Baiyar is the payment processor
behind it, the way a real merchant uses Stripe or Shopify Payments without being "a Stripe store." So:
the header shows Lapik's own text wordmark (no Baiyar logo there), and Baiyar only appears where a
payment processor actually would - the footer's "Pembayaran oleh" line and the checkout page's payment
method itself. Keep this split if either brand's content changes.

## Findings so far

- **A `WC_Payment_Gateway` alone is not enough.** New WooCommerce stores default to the block-based
  Checkout/Cart (Gutenberg blocks), which only lists payment methods registered separately through
  `AbstractPaymentMethodType` + a small JS file calling `wc.wcBlocksRegistry.registerPaymentMethod` (see
  `includes/class-wc-gateway-baiyar-blocks.php` and `assets/blocks.js`). Without it, the gateway is
  enabled in settings but checkout still says "There are no payment methods available." A real plugin
  needs both the classic gateway class and the blocks registration.
- Add to cart, cart and checkout (line items, quantities, subtotal, total) all come from WooCommerce/
  Storefront for free - nothing to build there. Confirms the earlier call: Baiyar doesn't need its own
  catalog/cart, just a line-items array on `CreatePaymentRequest` to mirror what WooCommerce already
  computed at checkout time.
- WooCommerce's shop-page detection (`is_shop()`) does not reliably trigger on a raw `?page_id=` request
  once pretty permalinks are active, even though the page ID is correct - only the canonical `/shop/`
  URL does. Cost some time chasing a phantom "empty product grid" bug that was really just this.
- WordPress can't read an SVG's intrinsic size (no GD/Imagick support for the format), so a `<img>` for
  an SVG product photo can end up with bogus `width="1" height="1"` attributes - force the real display
  size in CSS rather than trusting those attributes.
- Reusing WooCommerce's `<ul class="products">` loop markup (`woocommerce_product_loop_start()`) outside
  its normal archive/shortcode context (e.g. a custom "best sellers" query on the homepage) collides with
  Storefront's own `.products` grid CSS in ways that are hard to fully override even with `!important` -
  simplest fix was a plain wrapper `<div>` instead of `<ul class="products">`, keeping only the inner
  `wc_get_template_part('content', 'product')` card markup. An orphaned `<li>` outside a real `<ul>` then
  needs its own `list-style: none`.
- Storefront's `<header class="site-header">` has `position:relative; z-index:999` of its own. A
  `position:fixed` drawer nested inside it (the mini-cart) gets trapped in *that* stacking context - its
  own higher z-index only wins against siblings inside the header, not against something like our
  overlay `<div>` appended straight to `<body>`. The overlay has to stay below 999, not just below the
  drawer's own z-index, or it silently blocks clicks on the drawer's own buttons.
- Tailwind's Play CDN JIT-scans the whole rendered page for class names and generates a matching utility
  for anything that looks like one - including class names it didn't author. WooCommerce puts
  `columns-N` on the product grid `<ul>` (N = the catalog "columns" setting); Tailwind has its own
  `columns-N` utility (CSS `columns: N`, multi-column *text* layout) and generated it for that same
  class, splitting every product card's text into narrow newspaper-style columns. Fixed by blocklisting
  `columns-1` through `columns-6` in `tailwind.config`. Also disabled Tailwind's Preflight (base CSS
  reset) entirely - it rewrites element defaults (lists, forms, etc.) that Storefront/WooCommerce's own
  CSS assumes are still there; standard practice when adding Tailwind to a site with existing CSS. When
  a Tailwind-rendered page looks subtly wrong, check for a class-name collision with existing markup
  before assuming it's a specificity issue.
- The child theme's stylesheet was enqueued with the theme header's static `Version: 0.1.0` as its cache-
  busting `?ver=`. That number never changes just because `style.css`'s *contents* change, so every CSS
  fix in this repo kept getting served from the browser's cache under the same URL - correct on disk,
  invisible in the browser, several rounds of "this still isn't fixed" that were really just stale CSS.
  Enqueue with `filemtime()` instead so the version changes on every save.
- `position: sticky` silently does nothing (no console error, computed style still says `sticky`) if any
  ancestor between the sticky element and `<html>` has `overflow` other than `visible` on either axis -
  even `overflow-x: hidden` alone, since the other axis then resolves to `auto` as a side effect, and even
  when that ancestor never actually has anything to scroll. Storefront sets `overflow-x: hidden` on both
  `<body>` and `.site` (`#page`, the wrapper div right inside `<body>`) just to stop horizontal scroll -
  that alone broke our sticky header. Fixed by resetting `body`/`#page` back to `overflow-x: visible` and
  doing the actual horizontal clip on `<html>` instead, which doesn't create the same mismatch. Worth
  checking for on any "sticky isn't sticking" bug before suspecting z-index or specificity.
- The `filemtime()` cache-busting fix above only fixed *our own* enqueue call. Storefront's parent theme
  (`class-storefront.php`) independently re-enqueues this exact same `style.css` a second time, under its
  own handle (`storefront-child-style`), specifically so it loads *after* ours - and still versions it by
  the static `Version:` header, never filemtime. Two `<link>` tags for one file, one correctly cache-busted
  and one that never changes, loading in that order: the stale one wins the cascade for any rule a browser
  had already cached from an earlier version of the file. This is almost certainly the real explanation for
  nearly every "I fixed this already, why isn't it showing up" moment across this whole project, not just
  the single enqueue the filemtime fix addressed on its own. Dequeued Storefront's copy outright
  (`wp_dequeue_style('storefront-child-style')` at a later priority) since ours already depends on
  `storefront-style` and loads after it too.
- Centering the nav menu in the header pill (logo left, nav center, cart right) fought a real shrink-to-fit
  sizing bug: the nav's own box kept resolving to a much wider width than its visible text, no matter what
  `display`/`width` was set on it or its children - `.primary-navigation` (a plain `display:block` div
  Storefront wraps the menu in) needed `display: inline-block` to stop it filling available space, and even
  then the outer `<nav>` itself needed `width: fit-content !important; flex: none !important` to actually
  match. Never fully traced to one specific rule fighting it. Separately: absolutely positioning the *cart*
  icon (rather than the nav) to achieve the 3-part layout broke the cart drawer outright - the drawer is
  `position: fixed !important`, and a `transform` on any ancestor (even one that isn't the drawer's direct
  parent) becomes the containing block for that fixed descendant instead of the viewport, so it rendered as
  a small inline box next to the cart icon instead of sliding in from off-screen. Centering the nav instead
  (which has no `position: fixed` descendants) avoided that trap entirely - when something that's
  `position: fixed` stops behaving like it, check every ancestor for a new `transform`, not just the
  stacking-context/z-index angle documented above.
- WooCommerce's own cart styling (`woocommerce.css`, enqueued by Storefront's WooCommerce integration
  class) was landing *after* our child theme's stylesheet in the page - not because of any explicit
  priority, just because that's the order their two separate `wp_enqueue_style()` hooks happened to fire
  in, and nothing told WordPress's dependency resolver otherwise. For any selector where we and
  `woocommerce.css` target the exact same specificity (easy to do by accident on cart/product markup,
  since we're often matching their own class names), theirs silently won by loading later - this is what
  made the cart icon's padding/flex reset not apply at all, no error, just the "wrong" rule winning a tie.
  Fixed by adding `'storefront-woocommerce-style'` as an explicit dependency on our own `wp_enqueue_style`
  call, which reorders the final `<link>` order regardless of hook timing. Anywhere our CSS matches a
  WooCommerce-generated class and seems to be silently ignored, check `<link>` order in the rendered
  `<head>` before assuming it's a specificity or caching problem - by now the third distinct "our rule is
  correct but something else's cascade position wins anyway" bug in this file, after the two above.
- The cart icon's own vertical centering was *also* a cascade casualty of that same bug (one level
  removed): once the dependency fix let our `display: flex` actually apply, the icon snapped to properly
  centered immediately - no separate fix needed. The *logo*, on the other hand, turned out to have a
  real bug of its own once vertical centering was checked properly: `.site-branding` carries a sizeable
  bottom margin from Storefront's original two-row header design (meant to separate branding from the
  nav row underneath it), and margins count toward a flex item's size for `align-items: center` purposes
  - so the pill was centering that *padded* box, not the visible wordmark inside it, pushing the logo
  text above center. Reset with a plain `.site-branding { margin-bottom: 0 }`.

## Status

Exploration only. See `baiyar-payment-platform`'s `demo/woocommerce-plugin` branch for the server-side
notes this is paired with.
