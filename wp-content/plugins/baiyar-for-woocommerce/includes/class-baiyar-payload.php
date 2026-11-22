<?php
/**
 * Turns a WooCommerce order into the body of POST /v1/checkouts. The pure parts take plain arrays so they can be tested
 * without WordPress.
 *
 * @package baiyar-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) && ! defined( 'BAIYAR_TESTS' ) ) {
	exit;
}

class Baiyar_Payload {

	const MAX_LINES = 100;

	/**
	 * The lines the payer sees. Baiyar checks that they add up to the amount, so rows that cannot be shown exactly (a price
	 * that does not divide by its quantity) are shown as one line of quantity one, and when the lines still do not add up
	 * to the amount none are sent: the payer then sees the total only, which is always right.
	 *
	 * @param array[] $rows   Each: type (product|shipping|fee|discount), name, quantity, total (whole rupiah, signed).
	 * @param int     $amount What the payer is charged.
	 * @return array[]|null
	 */
	public static function line_items( array $rows, $amount ) {
		$lines = array();
		$sum   = 0;
		foreach ( $rows as $row ) {
			$quantity = max( 1, (int) $row['quantity'] );
			$total    = (int) $row['total'];
			$type     = $row['type'];
			$name     = self::clean_name( $row['name'] );
			if ( $total < 0 ) {
				// A negative row (a negative fee) is a discount line, never a negative price.
				$type  = 'discount';
				$total = -$total;
			}
			if ( 0 === $total % $quantity ) {
				$unit = intdiv( $total, $quantity );
			} else {
				$unit = $total;
				$name = self::clean_name( $name . ' × ' . $quantity );
				$quantity = 1;
			}
			$lines[] = array(
				'type'        => $type,
				'name'        => $name,
				'quantity'    => $quantity,
				'unit_amount' => $unit,
			);
			$sum += ( 'discount' === $type ? -1 : 1 ) * $quantity * $unit;
		}
		if ( 0 === count( $lines ) || count( $lines ) > self::MAX_LINES || $sum !== (int) $amount ) {
			return null;
		}
		return $lines;
	}

	/** How long a checkout stays open, in seconds: Baiyar accepts 5 minutes to 30 days. */
	public static function expires_in( $hours ) {
		$seconds = (int) round( (float) $hours * 3600 );
		return max( 300, min( 2592000, $seconds ) );
	}

	/** The merchant's own order number as Baiyar's `reference`; a later attempt for the same order gets a suffix. */
	public static function reference( $order_number, $attempt = 1 ) {
		$base = 'WC-' . preg_replace( '/[^A-Za-z0-9._-]/', '', (string) $order_number );
		if ( $attempt > 1 ) {
			$base .= '-' . (int) $attempt;
		}
		return substr( $base, 0, 100 );
	}

	/** A phone number in the shape Baiyar accepts, or null. */
	public static function phone( $raw ) {
		$text = preg_replace( '/[\s().-]+/', '', (string) $raw );
		return preg_match( '/^\+?[0-9]{6,20}$/', (string) $text ) ? $text : null;
	}

	public static function clean_name( $name ) {
		$text = trim( preg_replace( '/\s+/', ' ', baiyar_wc_strip_tags( (string) $name ) ) );
		if ( '' === $text ) {
			$text = 'Item';
		}
		return function_exists( 'mb_substr' ) ? mb_substr( $text, 0, 200 ) : substr( $text, 0, 200 );
	}

	/** The checkout request for an order. `$attempt` is how many checkouts this order has had, counting this one. */
	public static function from_order( WC_Order $order, $project_id, $success_url, $cancel_url, $attempt = 1, $expiry_hours = 24 ) {
		$amount = (int) round( (float) $order->get_total() );

		$rows = array();
		foreach ( $order->get_items() as $item ) {
			$rows[] = array(
				'type'     => 'product',
				'name'     => $item->get_name(),
				'quantity' => (int) $item->get_quantity(),
				'total'    => (int) round( (float) $item->get_total() + (float) $item->get_total_tax() ),
			);
		}
		$shipping = (float) $order->get_shipping_total() + (float) $order->get_shipping_tax();
		if ( $shipping > 0 ) {
			$rows[] = array(
				'type'     => 'shipping',
				'name'     => __( 'Shipping', 'baiyar-for-woocommerce' ),
				'quantity' => 1,
				'total'    => (int) round( $shipping ),
			);
		}
		foreach ( $order->get_fees() as $fee ) {
			$rows[] = array(
				'type'     => 'fee',
				'name'     => $fee->get_name(),
				'quantity' => 1,
				'total'    => (int) round( (float) $fee->get_total() + (float) $fee->get_total_tax() ),
			);
		}

		$body = array(
			'project_id'  => $project_id,
			'reference'   => self::reference( $order->get_order_number(), $attempt ),
			'amount'      => $amount,
			'currency'    => 'IDR',
			'mode'        => 'hosted',
			'expires_in'  => self::expires_in( $expiry_hours ),
			'success_url' => $success_url,
			'cancel_url'  => $cancel_url,
			'metadata'    => array(
				'woocommerce_order_id'     => (string) $order->get_id(),
				'woocommerce_order_number' => substr( (string) $order->get_order_number(), 0, 500 ),
			),
		);

		$lines = self::line_items( $rows, $amount );
		if ( null !== $lines ) {
			$body['line_items'] = $lines;
		}

		$customer = array();
		$name     = trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );
		if ( '' !== $name ) {
			$customer['name'] = substr( $name, 0, 120 );
		}
		if ( is_email( $order->get_billing_email() ) ) {
			$customer['email'] = $order->get_billing_email();
		}
		$phone = self::phone( $order->get_billing_phone() );
		if ( null !== $phone ) {
			$customer['phone'] = $phone;
		}
		if ( $customer ) {
			$body['customer'] = $customer;
		}

		return $body;
	}
}

/** wp_strip_all_tags exists in WordPress; the tests stand in for it. */
function baiyar_wc_strip_tags( $text ) {
	return function_exists( 'wp_strip_all_tags' ) ? wp_strip_all_tags( $text ) : strip_tags( $text );
}
