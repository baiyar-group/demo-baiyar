<?php
/**
 * A small client for the two Baiyar Merchant API calls the plugin makes.
 *
 * @package baiyar-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Baiyar_API {

	const DEFAULT_URL = 'https://api.baiyar.com';

	/** @var string */
	private $key;

	/** @var string */
	private $base_url;

	public function __construct( $key, $base_url = self::DEFAULT_URL ) {
		$this->key      = trim( (string) $key );
		$this->base_url = untrailingslashit( '' !== trim( (string) $base_url ) ? trim( (string) $base_url ) : self::DEFAULT_URL );
	}

	/** Whether a key is a sandbox key (`bk_test_...`) or a live one (`bk_live_...`); null when it is neither. */
	public static function environment_of( $key ) {
		if ( 0 === strpos( (string) $key, 'bk_test_' ) ) {
			return 'sandbox';
		}
		if ( 0 === strpos( (string) $key, 'bk_live_' ) ) {
			return 'live';
		}
		return null;
	}

	/**
	 * Makes a checkout. The idempotency key makes a repeat of the same request (a double click, a retry after a timeout)
	 * answer with the checkout that was already made instead of making a second one.
	 *
	 * @param array  $body            The request body.
	 * @param string $idempotency_key Same value for the same attempt.
	 * @return array See request().
	 */
	public function create_checkout( array $body, $idempotency_key ) {
		return $this->request( 'POST', '/v1/checkouts', $body, array( 'Idempotency-Key' => $idempotency_key ) );
	}

	/** Reads a checkout: the place to ask what really happened, whatever a browser or a webhook said. */
	public function get_checkout( $id ) {
		return $this->request( 'GET', '/v1/checkouts/' . rawurlencode( $id ) );
	}

	/**
	 * @return array{ok: bool, status: int, data: ?array, error: ?array{code: string, detail: string}, request_id: ?string}
	 */
	private function request( $method, $path, $body = null, array $headers = array() ) {
		$args = array(
			'method'  => $method,
			'timeout' => 20,
			'headers' => array_merge(
				array(
					'Authorization' => 'Bearer ' . $this->key,
					'Accept'        => 'application/json',
					'User-Agent'    => sprintf(
						'Baiyar-for-WooCommerce/%s (WordPress/%s; WooCommerce/%s)',
						BAIYAR_WC_VERSION,
						get_bloginfo( 'version' ),
						defined( 'WC_VERSION' ) ? WC_VERSION : 'unknown'
					),
				),
				$headers
			),
		);
		if ( null !== $body ) {
			$args['headers']['Content-Type'] = 'application/json';
			$args['body']                    = wp_json_encode( $body );
		}

		$response = wp_remote_request( $this->base_url . $path, $args );
		if ( is_wp_error( $response ) ) {
			return array(
				'ok'         => false,
				'status'     => 0,
				'data'       => null,
				'error'      => array(
					'code'   => 'network',
					'detail' => $response->get_error_message(),
				),
				'request_id' => null,
			);
		}

		$status  = (int) wp_remote_retrieve_response_code( $response );
		$decoded = json_decode( wp_remote_retrieve_body( $response ), true );
		$decoded = is_array( $decoded ) ? $decoded : array();
		$ok      = $status >= 200 && $status < 300 && isset( $decoded['data'] ) && is_array( $decoded['data'] );

		return array(
			'ok'         => $ok,
			'status'     => $status,
			'data'       => $ok ? $decoded['data'] : null,
			'error'      => $ok
				? null
				: array(
					'code'   => isset( $decoded['error']['code'] ) ? (string) $decoded['error']['code'] : 'http_' . $status,
					'detail' => isset( $decoded['error']['detail'] ) ? (string) $decoded['error']['detail'] : 'Unexpected response',
				),
			'request_id' => isset( $decoded['meta']['request_id'] ) ? (string) $decoded['meta']['request_id'] : null,
		);
	}
}
