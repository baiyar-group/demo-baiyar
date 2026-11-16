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
