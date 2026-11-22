<?php
/**
 * Makes the gateway show up in the block-based Cart and Checkout. A WC_Payment_Gateway alone is only picked up by the
 * shortcode checkout; the blocks need this separate registration.
 *
 * @package baiyar-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

final class WC_Gateway_Baiyar_Blocks extends AbstractPaymentMethodType {

	protected $name = 'baiyar';

	/** @var WC_Gateway_Baiyar|null */
	private $gateway;

	public function initialize() {
		$this->settings = get_option( 'woocommerce_baiyar_settings', array() );
		$gateways       = WC()->payment_gateways->payment_gateways();
		$this->gateway  = isset( $gateways[ $this->name ] ) ? $gateways[ $this->name ] : null;
	}

	public function is_active() {
		return null !== $this->gateway && $this->gateway->is_available();
	}

	public function get_payment_method_script_handles() {
		wp_register_script(
			'wc-baiyar-blocks',
			plugins_url( 'assets/blocks.js', BAIYAR_WC_FILE ),
			array( 'wc-blocks-registry', 'wc-settings', 'wp-element', 'wp-html-entities' ),
			BAIYAR_WC_VERSION,
			true
		);
		return array( 'wc-baiyar-blocks' );
	}

	public function get_payment_method_data() {
		return array(
			'title'       => $this->gateway ? $this->gateway->title : 'Baiyar',
			'description' => $this->gateway ? $this->gateway->description : '',
			'supports'    => $this->gateway ? array_values( $this->gateway->supports ) : array( 'products' ),
		);
	}
}
