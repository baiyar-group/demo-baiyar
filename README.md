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

The second command installs WordPress, WooCommerce, the Storefront theme, activates "Baiyar for
WooCommerce" (mounted from `wp-content/plugins/baiyar-for-woocommerce`), and creates four sample
products. Takes under a minute.

- Shop, product pages, add to cart, cart, checkout: click "Shop" in the Storefront menu - all stock
  WooCommerce/Storefront, nothing custom. Prices are in IDR.
- Admin: `http://localhost:8080/wp-admin` (`admin` / `admin`)
- Payment methods at checkout: `wp-admin > WooCommerce > Settings > Payments` - enable "Baiyar" there to
  see it next to the stock gateways (it won't actually charge anything yet, see below).

Re-running `setup.sh` is safe (products are created with `|| true` so a re-run just skips duplicates).

## What's here

- `wp-content/plugins/baiyar-for-woocommerce/` - a plugin stub. `process_payment()` and the webhook
  handler (`class-wc-gateway-baiyar.php`) are `TODO`s, not implemented - the point right now is the
  store/checkout flow, not a working gateway.
- `bin/setup.sh` - provisions the store via WP-CLI (run through the `wpcli` compose service).

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

## Status

Exploration only. See `baiyar-payment-platform`'s `demo/woocommerce-plugin` branch for the server-side
notes this is paired with.
