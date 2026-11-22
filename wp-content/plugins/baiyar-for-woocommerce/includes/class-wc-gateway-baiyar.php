<?php
/**
 * The Baiyar payment gateway: a hosted checkout. The order is sent to Baiyar when the customer places it, the customer pays
 * on Baiyar's page and comes back, and Baiyar tells the store by webhook (and the store asks Baiyar again on return) that
 * the order was paid.
 *
 * @package baiyar-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Gateway_Baiyar extends WC_Payment_Gateway {

	const META_CHECKOUT = '_baiyar_checkout_id';
	/** Every checkout this order ever had (one entry each): an old one can still be paid after a newer one was made. */
	const META_HISTORY  = '_baiyar_checkout_history';
	const META_ATTEMPTS = '_baiyar_attempts';
	const META_PAYMENT  = '_baiyar_payment_id';

	/** @var WC_Logger|null */
	private static $logger;

	public function __construct() {
		$this->id                 = 'baiyar';
		$this->icon               = '';
		$this->has_fields         = false;
		$this->method_title       = 'Baiyar';
		$this->method_description = __( 'QRIS, virtual account, e-wallet and retail outlet payments through Baiyar. The customer pays on a Baiyar page and comes back to your store.', 'baiyar-for-woocommerce' );
		$this->supports           = array( 'products' );

		$this->init_form_fields();
		$this->init_settings();

		$this->title       = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );
		$this->enabled     = $this->get_option( 'enabled' );

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_api_wc_gateway_baiyar', array( $this, 'handle_webhook' ) );
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'on_return' ) );
	}

	public function init_form_fields() {
		$this->form_fields = array(
			'enabled'        => array(
				'title'   => __( 'Enable/Disable', 'baiyar-for-woocommerce' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Baiyar', 'baiyar-for-woocommerce' ),
				'default' => 'no',
			),
			'title'          => array(
				'title'   => __( 'Title', 'baiyar-for-woocommerce' ),
				'type'    => 'text',
				'default' => __( 'Baiyar', 'baiyar-for-woocommerce' ),
			),
			'description'    => array(
				'title'   => __( 'Description', 'baiyar-for-woocommerce' ),
				'type'    => 'textarea',
				'default' => __( 'Pay with QRIS, virtual account, e-wallet or a retail outlet.', 'baiyar-for-woocommerce' ),
			),
			'api_key'        => array(
				'title'       => __( 'Secret API key', 'baiyar-for-woocommerce' ),
				'type'        => 'password',
				'description' => __( 'From the Baiyar dashboard, Settings, API keys. A key starting with bk_test_ works in the sandbox (no real money), bk_live_ takes real payments. It needs the payments:read and payments:write permissions.', 'baiyar-for-woocommerce' ),
				'desc_tip'    => false,
			),
			'project_id'     => array(
				'title'       => __( 'Project ID', 'baiyar-for-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'The project that receives these payments, shown on its page in the dashboard (prj_...).', 'baiyar-for-woocommerce' ),
			),
			'webhook_secret' => array(
				'title'       => __( 'Webhook secret', 'baiyar-for-woocommerce' ),
				'type'        => 'password',
				'description' => sprintf(
					/* translators: %s: the webhook address of this store */
					__( 'Add this address as a webhook endpoint in the Baiyar dashboard, listening to checkout.* events: %s. Then paste its secret (whsec_...) here. The address must be public and use https.', 'baiyar-for-woocommerce' ),
					'<code>' . esc_html( WC()->api_request_url( 'wc_gateway_baiyar' ) ) . '</code>'
				),
			),
			'expiry_hours'   => array(
				'title'             => __( 'Payment time limit (hours)', 'baiyar-for-woocommerce' ),
				'type'              => 'number',
				'default'           => '24',
				'description'       => __( 'How long the customer has to pay. An order waiting for payment holds its stock, and is cancelled when this runs out. From 1 hour to 720 hours (30 days).', 'baiyar-for-woocommerce' ),
				'custom_attributes' => array(
					'min'  => '1',
					'max'  => '720',
					'step' => '1',
				),
			),
			'api_url'        => array(
				'title'       => __( 'API address', 'baiyar-for-woocommerce' ),
				'type'        => 'text',
				'default'     => Baiyar_API::DEFAULT_URL,
				'description' => __( 'Leave as it is unless Baiyar told you to use another address.', 'baiyar-for-woocommerce' ),
			),
			'debug'          => array(
				'title'       => __( 'Debug log', 'baiyar-for-woocommerce' ),
				'type'        => 'checkbox',
				'label'       => __( 'Log requests and webhooks (WooCommerce, Status, Logs, source baiyar). Keys and secrets are never logged.', 'baiyar-for-woocommerce' ),
				'default'     => 'no',
			),
		);
	}

	// ----- settings checks ---------------------------------------------------------------------------------------------

	public function validate_api_key_field( $key, $value ) {
		$value = trim( (string) $value );
		if ( '' !== $value && null === Baiyar_API::environment_of( $value ) ) {
			WC_Admin_Settings::add_error( __( 'The API key must start with bk_test_ or bk_live_.', 'baiyar-for-woocommerce' ) );
			return $this->get_option( $key );
		}
		return $value;
	}

	public function validate_project_id_field( $key, $value ) {
		$value = trim( (string) $value );
		if ( '' !== $value && ! preg_match( '/^prj_[0-9a-z]{26}$/', $value ) ) {
			WC_Admin_Settings::add_error( __( 'The project ID looks like prj_ followed by 26 characters.', 'baiyar-for-woocommerce' ) );
			return $this->get_option( $key );
		}
		return $value;
	}

	public function validate_webhook_secret_field( $key, $value ) {
		$value = trim( (string) $value );
		if ( '' !== $value && 0 !== strpos( $value, 'whsec_' ) ) {
			WC_Admin_Settings::add_error( __( 'The webhook secret starts with whsec_.', 'baiyar-for-woocommerce' ) );
			return $this->get_option( $key );
		}
		return $value;
	}

	public function admin_options() {
		if ( 'IDR' !== get_woocommerce_currency() ) {
			echo '<div class="notice notice-warning inline"><p>' . esc_html__( 'Baiyar takes payments in Indonesian rupiah only. Set the store currency to IDR (WooCommerce, Settings, General) or the payment method will not be offered.', 'baiyar-for-woocommerce' ) . '</p></div>';
		}
		$env = Baiyar_API::environment_of( $this->get_option( 'api_key' ) );
		if ( 'sandbox' === $env ) {
			echo '<div class="notice notice-info inline"><p>' . esc_html__( 'Sandbox mode: this key makes test payments only, no real money moves.', 'baiyar-for-woocommerce' ) . '</p></div>';
		}
		parent::admin_options();
	}

	public function is_available() {
		return parent::is_available()
			&& 'IDR' === get_woocommerce_currency()
			&& '' !== $this->get_option( 'api_key' )
			&& '' !== $this->get_option( 'project_id' );
	}

	// ----- paying ------------------------------------------------------------------------------------------------------

	private function api() {
		return new Baiyar_API( $this->get_option( 'api_key' ), $this->get_option( 'api_url' ) );
	}

	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return array( 'result' => 'failure' );
		}
		$api    = $this->api();
		$amount = (int) round( (float) $order->get_total() );

		// A customer who comes back to pay the same order again is sent to the checkout it already has, while that is still
		// open for the same amount. One that was paid meanwhile settles the order instead.
		$existing = (string) $order->get_meta( self::META_CHECKOUT );
		if ( '' !== $existing ) {
			$found = $api->get_checkout( $existing );
			if ( $found['ok'] && 'complete' === $found['data']['status'] ) {
				$this->mark_paid( $order, $found['data'] );
				return array(
					'result'   => 'success',
					'redirect' => $this->get_return_url( $order ),
				);
			}
			if ( $found['ok'] && 'open' === $found['data']['status'] && (int) $found['data']['amount'] === $amount && ! empty( $found['data']['url'] ) ) {
				return array(
					'result'   => 'success',
					'redirect' => $found['data']['url'],
				);
			}
		}

		$attempt = (int) $order->get_meta( self::META_ATTEMPTS ) + 1;
		$body    = Baiyar_Payload::from_order( $order, $this->get_option( 'project_id' ), $this->get_return_url( $order ), $order->get_checkout_payment_url(), $attempt, $this->get_option( 'expiry_hours', 24 ) );
		$key     = 'wc-' . $order->get_id() . '-' . substr( sha1( wp_json_encode( $body ) ), 0, 16 );
		$made    = $api->create_checkout( $body, $key );

		if ( ! $made['ok'] || empty( $made['data']['url'] ) ) {
			$this->log( sprintf( 'Creating a checkout for order %d failed: %s (%s) request %s', $order->get_id(), $made['error']['code'], $made['error']['detail'], (string) $made['request_id'] ), 'error' );
			wc_add_notice( __( 'The payment could not be started. Please try again, or choose another way to pay.', 'baiyar-for-woocommerce' ), 'error' );
			return array( 'result' => 'failure' );
		}

		$order->update_meta_data( self::META_CHECKOUT, $made['data']['id'] );
		$order->add_meta_data( self::META_HISTORY, $made['data']['id'] );
		$order->update_meta_data( self::META_ATTEMPTS, $attempt );
		$order->update_status(
			'on-hold',
			/* translators: %s: Baiyar checkout id */
			sprintf( __( 'Waiting for the customer to pay on Baiyar (checkout %s).', 'baiyar-for-woocommerce' ), $made['data']['id'] )
		);
		$order->save();
		wc_reduce_stock_levels( $order->get_id() );
		if ( WC()->cart ) {
			WC()->cart->empty_cart();
		}
		$this->log( sprintf( 'Order %d sent to Baiyar as %s', $order->get_id(), $made['data']['id'] ) );

		return array(
			'result'   => 'success',
			'redirect' => $made['data']['url'],
		);
	}

	/** The customer is back on the store: ask Baiyar what happened instead of waiting for the webhook. */
	public function on_return( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order || $order->is_paid() || $this->id !== $order->get_payment_method() ) {
			return;
		}
		$id = (string) $order->get_meta( self::META_CHECKOUT );
		if ( '' === $id ) {
			return;
		}
		$found = $this->api()->get_checkout( $id );
		if ( $found['ok'] && 'complete' === $found['data']['status'] ) {
			$this->mark_paid( $order, $found['data'] );
		}
	}

	/**
	 * Marks the order paid, but only for a checkout that is the order's own, was paid, and was for what the order costs. Anything
	 * else is left for the shop owner to look at.
	 */
	private function mark_paid( WC_Order $order, array $checkout ) {
		$payment_id = isset( $checkout['payment_id'] ) ? (string) $checkout['payment_id'] : '';
		if ( $order->is_paid() ) {
			// Paid once already: the same payment arriving again is nothing, another payment is one the shop owner must know of.
			$known = (string) $order->get_meta( self::META_PAYMENT );
			if ( 'complete' === $checkout['status'] && '' !== $payment_id && $payment_id !== $known && ! $this->noted( $order, $payment_id ) ) {
				$order->add_order_note(
					sprintf(
						/* translators: 1: Baiyar payment id, 2: checkout id */
						__( 'Baiyar received a second payment for this order (payment %1$s, checkout %2$s) after it was already paid. Refund it in the Baiyar dashboard.', 'baiyar-for-woocommerce' ),
						$payment_id,
						$checkout['id']
					)
				);
				$this->log( sprintf( 'Order %d was paid twice (%s)', $order->get_id(), $payment_id ), 'error' );
			}
			return;
		}
		$own    = $this->owns( $order, (string) $checkout['id'] );
		$amount = (int) round( (float) $order->get_total() );
		if ( ! $own || 'complete' !== $checkout['status'] || (int) $checkout['amount'] !== $amount || 'IDR' !== $checkout['currency'] ) {
			$order->add_order_note(
				sprintf(
					/* translators: 1: checkout id, 2: amount Baiyar reports, 3: amount of the order */
					__( 'Baiyar reported checkout %1$s as paid for %2$s, which does not match this order (%3$s). Check it in the Baiyar dashboard before shipping.', 'baiyar-for-woocommerce' ),
					$checkout['id'],
					(string) $checkout['amount'],
					(string) $amount
				)
			);
			$this->log( sprintf( 'Order %d not marked paid: checkout %s does not match', $order->get_id(), $checkout['id'] ), 'error' );
			return;
		}
		$order->update_meta_data( self::META_PAYMENT, $payment_id );
		$order->payment_complete( $payment_id );
		$order->add_order_note(
			/* translators: 1: Baiyar payment id, 2: checkout id */
			sprintf( __( 'Paid through Baiyar (payment %1$s, checkout %2$s).', 'baiyar-for-woocommerce' ), $payment_id, $checkout['id'] )
		);
		$this->log( sprintf( 'Order %d paid (%s)', $order->get_id(), $payment_id ) );
	}

	// ----- webhook -----------------------------------------------------------------------------------------------------

	public function handle_webhook() {
		$body   = (string) file_get_contents( 'php://input' );
		$secret = (string) $this->get_option( 'webhook_secret' );
		if ( '' === $secret ) {
			$this->log( 'A webhook arrived but no webhook secret is set', 'error' );
			status_header( 503 );
			exit;
		}
		$header = isset( $_SERVER['HTTP_BAIYAR_SIGNATURE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_BAIYAR_SIGNATURE'] ) ) : '';
		if ( ! Baiyar_Webhook::verify( $body, $header, $secret, time() ) ) {
			$this->log( 'A webhook with a wrong or missing signature was refused', 'error' );
			status_header( 401 );
			exit;
		}

		$event  = json_decode( $body, true );
		$type   = is_array( $event ) && isset( $event['type'] ) ? (string) $event['type'] : '';
		$object = is_array( $event ) && isset( $event['data']['object'] ) && is_array( $event['data']['object'] ) ? $event['data']['object'] : array();
		$id     = isset( $object['id'] ) ? (string) $object['id'] : '';
		$this->log( sprintf( 'Webhook %s for %s', $type, $id ) );

		// A sandbox key only believes sandbox events and a live key live ones.
		$env = Baiyar_API::environment_of( $this->get_option( 'api_key' ) );
		if ( isset( $event['livemode'] ) && null !== $env && ( 'live' === $env ) !== (bool) $event['livemode'] ) {
			$this->log( 'A webhook of the other environment was ignored', 'error' );
			status_header( 200 );
			exit;
		}

		if ( in_array( $type, array( 'checkout.completed', 'checkout.expired' ), true ) && 0 === strpos( $id, 'chk_' ) ) {
			$order = $this->find_order( $id );
			if ( $order ) {
				// What the webhook says is a reason to ask, not the answer: the answer is Baiyar's record of the checkout.
				$found = $this->api()->get_checkout( $id );
				if ( ! $found['ok'] ) {
					// Baiyar tries the webhook again later.
					$this->log( sprintf( 'Could not read checkout %s: %s', $id, $found['error']['code'] ), 'error' );
					status_header( 503 );
					exit;
				}
				if ( 'checkout.completed' === $type ) {
					$this->mark_paid( $order, $found['data'] );
				} elseif ( 'expired' === $found['data']['status'] && ! $order->is_paid() && (string) $order->get_meta( self::META_CHECKOUT ) === $id && $order->has_status( array( 'pending', 'on-hold' ) ) ) {
					$order->update_status( 'cancelled', __( 'The Baiyar checkout expired without a payment.', 'baiyar-for-woocommerce' ) );
				}
			} else {
				$this->log( sprintf( 'No order for checkout %s', $id ) );
			}
		}

		status_header( 200 );
		echo 'ok';
		exit;
	}

	/** @return WC_Order|null */
	private function find_order( $checkout_id ) {
		$found = wc_get_orders(
			array(
				'limit'      => 1,
				'meta_query' => array(
					array(
						'key'   => self::META_HISTORY,
						'value' => $checkout_id,
					),
				),
			)
		);
		if ( ! $found ) {
			// An order made before the history was kept has only its current checkout.
			$found = wc_get_orders(
				array(
					'limit'      => 1,
					'meta_query' => array(
						array(
							'key'   => self::META_CHECKOUT,
							'value' => $checkout_id,
						),
					),
				)
			);
		}
		return $found ? $found[0] : null;
	}

	/** Whether a checkout is one of this order's own, the current one or an earlier one. */
	private function owns( WC_Order $order, $checkout_id ) {
		return in_array( $checkout_id, array_map( 'strval', wp_list_pluck( $order->get_meta( self::META_HISTORY, false ), 'value' ) ), true )
			|| (string) $order->get_meta( self::META_CHECKOUT ) === $checkout_id;
	}

	/** Whether a note about this payment is already on the order. */
	private function noted( WC_Order $order, $payment_id ) {
		foreach ( wc_get_order_notes( array( 'order_id' => $order->get_id() ) ) as $note ) {
			if ( false !== strpos( $note->content, $payment_id ) && false !== strpos( $note->content, 'second payment' ) ) {
				return true;
			}
		}
		return false;
	}

	// ----- log ---------------------------------------------------------------------------------------------------------

	private function log( $message, $level = 'info' ) {
		if ( 'error' !== $level && 'yes' !== $this->get_option( 'debug' ) ) {
			return;
		}
		if ( ! self::$logger ) {
			self::$logger = wc_get_logger();
		}
		self::$logger->log( $level, $message, array( 'source' => 'baiyar' ) );
	}
}
