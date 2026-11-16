<?php

if (!defined('ABSPATH')) {
    exit;
}

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

/**
 * Makes the gateway show up in the block-based Checkout/Cart. A WC_Payment_Gateway alone is only
 * picked up by the legacy shortcode checkout - this is a separate registration WooCommerce's blocks
 * require, independent of class-wc-gateway-baiyar.php.
 */
final class WC_Gateway_Baiyar_Blocks extends AbstractPaymentMethodType
{
    protected $name = 'baiyar';
    private $gateway;

    public function initialize()
    {
        $this->settings = get_option('woocommerce_baiyar_settings', []);
        $gateways = WC()->payment_gateways->payment_gateways();
        $this->gateway = $gateways[$this->name] ?? null;
    }

    public function is_active()
    {
        return !empty($this->settings['enabled']) && 'yes' === $this->settings['enabled'];
    }

    public function get_payment_method_script_handles()
    {
        wp_register_script(
            'wc-baiyar-blocks',
            plugins_url('assets/blocks.js', BAIYAR_WC_FILE),
            ['wc-blocks-registry', 'wc-settings', 'wp-element', 'wp-html-entities'],
            '0.0.1',
            true,
        );
        return ['wc-baiyar-blocks'];
    }

    public function get_payment_method_data()
    {
        return [
            'title' => $this->gateway->title ?? 'Baiyar',
            'description' => $this->gateway->description ?? '',
        ];
    }
}
