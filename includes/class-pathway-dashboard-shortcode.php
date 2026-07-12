<?php
/**
 * The [pathway_dashboard] shortcode.
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Pathway_Dashboard_Shortcode
 */
class Pathway_Dashboard_Shortcode {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_shortcode( 'pathway_dashboard', array( $this, 'render' ) );
	}

	/**
	 * Renders the dashboard, or a login prompt for logged-out visitors.
	 *
	 * @return string
	 */
	public function render() {
		// Fallback enqueue for pages (e.g. Elementor) where head-time
		// shortcode detection could not see the shortcode.
		pathway_dash()->assets->enqueue_now();

		if ( ! is_user_logged_in() ) {
			return $this->render_login_prompt();
		}

		$user = wp_get_current_user();

		ob_start();

		pathway_dash_template(
			'dashboard',
			array(
				'user'        => $user,
				'first_name'  => pathway_dash_get_user_first_name( $user ),
				'initials'    => pathway_dash_get_user_initials( $user ),
				'tabs'        => pathway_dash_get_tabs(),
				'default_tab' => pathway_dash_get_default_tab(),
			)
		);

		return ob_get_clean();
	}

	/**
	 * Renders the branded login screen for logged-out visitors,
	 * redirecting back to this page after sign-in.
	 *
	 * @return string
	 */
	private function render_login_prompt() {
		return pathway_dash()->login->render( (string) get_permalink() );
	}
}
