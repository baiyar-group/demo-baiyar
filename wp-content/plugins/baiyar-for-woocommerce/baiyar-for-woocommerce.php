<?php
/**
 * Plugin Name:       Baiyar for WooCommerce
 * Plugin URI:        https://github.com/baiyar-group/baiyar-for-woocommerce
 * Description:       Accept QRIS, virtual account, e-wallet and retail outlet payments in WooCommerce through Baiyar.
 * Version:           0.1.0
 * Requires at least: 6.2
 * Requires PHP:      7.4
 * Requires Plugins:  woocommerce
 * Author:            Baiyar
 * Author URI:        https://baiyar.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       baiyar-for-woocommerce
 * WC requires at least: 8.0
 * WC tested up to:      11.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BAIYAR_WC_VERSION', '0.1.0' );
define( 'BAIYAR_WC_FILE', __FILE__ );
define( 'BAIYAR_WC_DIR', __DIR__ );

// The store keeps working with high-performance order storage and the block-based Cart and Checkout.
add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', BAIYAR_WC_FILE, true );
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', BAIYAR_WC_FILE, true );
		}
	}
);

add_action( 'plugins_loaded', 'baiyar_wc_init', 11 );

function baiyar_wc_init() {
	load_plugin_textdomain( 'baiyar-for-woocommerce', false, dirname( plugin_basename( BAIYAR_WC_FILE ) ) . '/languages' );

	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		return;
	}

	require_once BAIYAR_WC_DIR . '/includes/class-baiyar-api.php';
	require_once BAIYAR_WC_DIR . '/includes/class-baiyar-payload.php';
	require_once BAIYAR_WC_DIR . '/includes/class-baiyar-webhook.php';
	require_once BAIYAR_WC_DIR . '/includes/class-wc-gateway-baiyar.php';

	add_filter(
		'woocommerce_payment_gateways',
		function ( $gateways ) {
			$gateways[] = 'WC_Gateway_Baiyar';
			return $gateways;
		}
	);
}

add_filter(
	'plugin_action_links_' . plugin_basename( BAIYAR_WC_FILE ),
	function ( $links ) {
		$url = admin_url( 'admin.php?page=wc-settings&tab=checkout&section=baiyar' );
		array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'baiyar-for-woocommerce' ) . '</a>' );
		return $links;
	}
);

add_action(
	'woocommerce_blocks_payment_method_type_registration',
	function ( $registry ) {
		if ( ! class_exists( \Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType::class ) ) {
			return;
		}
		require_once BAIYAR_WC_DIR . '/includes/class-wc-gateway-baiyar-blocks.php';
		$registry->register( new WC_Gateway_Baiyar_Blocks() );
	}
);
