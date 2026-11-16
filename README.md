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
```

Then open `http://localhost:8080`, finish the WordPress install, install WooCommerce from the plugin
directory, and activate "Baiyar for WooCommerce" (already mounted under
`wp-content/plugins/baiyar-for-woocommerce`).

## What's here

- `wp-content/plugins/baiyar-for-woocommerce/` - a plugin stub (`WC_Gateway_Baiyar`). `process_payment()`
  and the webhook handler are `TODO`s, not implemented - the point right now is the store/checkout flow,
  not a working gateway.

## Status

Exploration only. See `baiyar-payment-platform`'s `demo/woocommerce-plugin` branch for the server-side
notes this is paired with.
