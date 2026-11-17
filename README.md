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
  the newsletter + link columns + social row, hooked in ahead of Storefront's own credit line.
- `bin/product-images/` - generated SVG product photos (sneaker/sandal line art on Baiyar-token
  backdrops), sideloaded onto each product by `setup.sh` via `wp media import --featured_image`.
- `bin/setup.sh` - provisions the store via WP-CLI (run through the `wpcli` compose service).

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

## Status

Exploration only. See `baiyar-payment-platform`'s `demo/woocommerce-plugin` branch for the server-side
notes this is paired with.
