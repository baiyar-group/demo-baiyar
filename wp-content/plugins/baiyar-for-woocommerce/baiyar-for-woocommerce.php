<?php
/**
 * Plugin Name: Baiyar for WooCommerce
 * Description: Accept QRIS, virtual account, e-wallet and retail outlet payments through Baiyar. Exploration stub only, not for production use.
 * Version: 0.0.1
 * Requires Plugins: woocommerce
 * Author: Baiyar
 * Text Domain: baiyar-for-woocommerce
 */

if (!defined('ABSPATH')) {
    exit;
}

define('BAIYAR_WC_FILE', __FILE__);
define('BAIYAR_WC_DIR', __DIR__);

add_action('before_woocommerce_init', function () {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        // WooCommerce defaults new stores to the block-based Checkout/Cart, which a classic
        // WC_Payment_Gateway alone does not appear in - see includes/class-wc-gateway-baiyar-blocks.php.
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('cart_checkout_blocks', BAIYAR_WC_FILE, true);
    }
});

add_action('plugins_loaded', 'baiyar_wc_init', 11);

function baiyar_wc_init()
{
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

    require_once __DIR__ . '/includes/class-wc-gateway-baiyar.php';

    add_filter('woocommerce_payment_gateways', function ($gateways) {
        $gateways[] = 'WC_Gateway_Baiyar';
        return $gateways;
    });
}

add_action('woocommerce_blocks_payment_method_type_registration', function ($registry) {
    if (!class_exists(\Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType::class)) {
        return;
    }

    require_once __DIR__ . '/includes/class-wc-gateway-baiyar-blocks.php';
    $registry->register(new WC_Gateway_Baiyar_Blocks());
});
