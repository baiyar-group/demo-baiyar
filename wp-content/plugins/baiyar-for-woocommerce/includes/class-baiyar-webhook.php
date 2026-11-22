<?php
/**
 * Checks that a webhook really came from Baiyar.
 *
 * The `Baiyar-Signature` header is `t=<unix seconds>,v1=<hex>`: v1 is the HMAC-SHA256 of `t.body` with the endpoint's
 * `whsec_` secret. While a secret is being replaced the header carries one v1 for each secret that signs, and any of them
 * is enough.
 *
 * @package baiyar-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) && ! defined( 'BAIYAR_TESTS' ) ) {
	exit;
}

class Baiyar_Webhook {

	const TOLERANCE_SECONDS = 300;

	/**
	 * @param string $body   The raw request body, exactly as received.
	 * @param string $header The Baiyar-Signature header.
	 * @param string $secret The endpoint's secret.
	 * @param int    $now    Current unix time (a parameter for the tests).
	 */
	public static function verify( $body, $header, $secret, $now ) {
		if ( '' === (string) $secret || '' === (string) $header ) {
			return false;
		}
		$timestamp  = null;
		$signatures = array();
		foreach ( explode( ',', (string) $header ) as $part ) {
			$pair = explode( '=', trim( $part ), 2 );
			if ( 2 !== count( $pair ) ) {
				continue;
			}
			if ( 't' === $pair[0] && ctype_digit( $pair[1] ) ) {
				$timestamp = (int) $pair[1];
			} elseif ( 'v1' === $pair[0] && ctype_xdigit( $pair[1] ) ) {
				$signatures[] = strtolower( $pair[1] );
			}
		}
		if ( null === $timestamp || ! $signatures || abs( $now - $timestamp ) > self::TOLERANCE_SECONDS ) {
			return false;
		}
		$expected = hash_hmac( 'sha256', $timestamp . '.' . $body, (string) $secret );
		foreach ( $signatures as $signature ) {
			if ( hash_equals( $expected, $signature ) ) {
				return true;
			}
		}
		return false;
	}
}
