=== Baiyar for WooCommerce ===
Tags: woocommerce, payments, qris, virtual account, indonesia
Requires at least: 6.2
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Accept QRIS, virtual account, e-wallet and retail outlet payments in WooCommerce through Baiyar.

== Description ==

Customers place the order in your store, pay on a Baiyar page and come back to your store. Baiyar tells the store the order was paid, and the order moves on by itself.

* Rupiah (IDR) stores only.
* Hosted checkout: the customer pays on Baiyar's page.
* Works with the block-based Cart and Checkout and with high-performance order storage.
* Orders are marked paid from Baiyar's signed webhook. A payment is only accepted for the order's own checkout and exact total.

You need a Baiyar account (https://baiyar.com), a project, an API key with the payments:read and payments:write permissions, and a webhook endpoint. Start in the sandbox with a bk_test_ key.

== Installation ==

1. Upload the plugin and activate it.
2. Go to WooCommerce, Settings, Payments, Baiyar.
3. Enter the secret API key, the project ID and the webhook secret, and add the webhook address shown on that page as a webhook endpoint (events checkout.*) in the Baiyar dashboard.
4. Enable Baiyar and place a test order.

== Changelog ==

= 0.1.0 =
First release.
