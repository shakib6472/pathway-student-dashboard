<?php
/**
 * API key management for the Pathway REST endpoints.
 *
 * A single secret key (generated on first use, stored in options)
 * authenticates the main website. Clients send it in the
 * X-Pathway-Api-Key header.
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Pathway_Dashboard_Api_Keys
 */
class Pathway_Dashboard_Api_Keys {

	/**
	 * Option storing the API key.
	 */
	const OPTION = 'pathway_dash_api_key';

	/**
	 * Header the client must send.
	 */
	const HEADER = 'X-Pathway-Api-Key';

	/**
	 * Returns the API key, generating one on first use.
	 *
	 * @return string
	 */
	public static function get_key() {
		$key = (string) get_option( self::OPTION, '' );

		if ( '' === $key ) {
			$key = self::regenerate();
		}

		return $key;
	}

	/**
	 * Generates and stores a fresh API key, invalidating the old one.
	 *
	 * @return string The new key.
	 */
	public static function regenerate() {
		$key = 'pda_' . bin2hex( random_bytes( 24 ) );

		update_option( self::OPTION, $key, false );

		return $key;
	}

	/**
	 * REST permission callback: validates the API key header.
	 *
	 * @param WP_REST_Request $request Incoming request.
	 * @return true|WP_Error
	 */
	public static function verify_request( $request ) {
		$sent = (string) $request->get_header( self::HEADER );

		if ( '' !== $sent && hash_equals( self::get_key(), $sent ) ) {
			return true;
		}

		return new WP_Error(
			'pathway_dash_invalid_key',
			__( 'Invalid or missing API key.', 'pathway-student-dashboard' ),
			array( 'status' => 401 )
		);
	}
}
