<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Stub gateway. process_payment() does not call Baiyar's API yet - this plugin exists to work
 * out what the WooCommerce side actually needs from the Merchant API (see the repo README),
 * not to accept real payments.
 */
class WC_Gateway_Baiyar extends WC_Payment_Gateway
{
    public function __construct()
    {
        $this->id = 'baiyar';
        $this->icon = '';
        $this->has_fields = false;
        $this->method_title = 'Baiyar';
        $this->method_description = 'QRIS, virtual account, e-wallet and retail outlet payments via Baiyar.';

        $this->init_form_fields();
        $this->init_settings();

        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->enabled = $this->get_option('enabled');

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
        add_action('woocommerce_api_wc_gateway_baiyar', [$this, 'handle_webhook']);
    }

    public function init_form_fields()
    {
        $this->form_fields = [
            'enabled' => [
                'title' => 'Enable/Disable',
                'type' => 'checkbox',
                'label' => 'Enable Baiyar',
                'default' => 'no',
            ],
            'title' => [
                'title' => 'Title',
                'type' => 'text',
                'default' => 'Baiyar',
            ],
            'description' => [
                'title' => 'Description',
                'type' => 'textarea',
                'default' => 'Pay with QRIS, virtual account, e-wallet or a retail outlet.',
            ],
            'secret_key' => [
                'title' => 'Secret key',
                'type' => 'password',
                'description' => 'From Baiyar dashboard > Settings > API keys (bk_live_...).',
            ],
        ];
    }

    public function process_payment($order_id)
    {
        $order = wc_get_order($order_id);

        // TODO: call Baiyar's payment link / payment API with this order's line items and amount,
        // then either redirect to the hosted checkout URL or render the instruction inline.
        // See the repo README for the two integration modes being evaluated.

        $order->update_status('on-hold', 'Awaiting Baiyar payment (demo stub, not wired up yet).');

        return [
            'result' => 'success',
            'redirect' => $this->get_return_url($order),
        ];
    }

    public function handle_webhook()
    {
        // TODO: verify Baiyar's webhook signature, then mark the matching order paid.
        status_header(200);
        exit;
    }
}
