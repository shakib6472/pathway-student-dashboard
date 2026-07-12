<?php
/**
 * Branded login screen.
 *
 * Registers the [pathway_login] shortcode and renders the same
 * screen for logged-out visitors of [pathway_dashboard]. Failed
 * logins submitted from this screen bounce back to it with an
 * error banner instead of landing on wp-login.php.
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Pathway_Dashboard_Login
 */
class Pathway_Dashboard_Login {

	/**
	 * Default background photo for the form panel.
	 */
	const DEFAULT_BG_URL = 'https://learn.pathway2da.com/wp-content/uploads/2026/07/Home-page-Photo.png';

	/**
	 * Default promo (student) photo for the bottom banner.
	 */
	const DEFAULT_PROMO_URL = 'http://localhost/learn.pathway/wp-content/uploads/2026/07/lady.png';

	/**
	 * Default "Learn More" target.
	 */
	const DEFAULT_LEARN_MORE_URL = 'https://pathway2da.com/';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_shortcode( 'pathway_login', array( $this, 'shortcode' ) );
		add_action( 'wp_login_failed', array( $this, 'redirect_back_on_failure' ) );
		add_action( 'template_redirect', array( $this, 'guard_pages' ) );
	}

	/**
	 * Returns the login page URL.
	 *
	 * @return string
	 */
	public function get_login_url() {
		/**
		 * Filters the login page URL used for redirects.
		 *
		 * @param string $url Login page URL.
		 */
		return apply_filters( 'pathway_dash_login_page_url', home_url( '/login/' ) );
	}

	/**
	 * Returns the dashboard URL (the site front page).
	 *
	 * @return string
	 */
	public function get_dashboard_url() {
		/**
		 * Filters the dashboard URL used for redirects.
		 *
		 * @param string $url Dashboard URL.
		 */
		return apply_filters( 'pathway_dash_dashboard_url', home_url( '/' ) );
	}

	/**
	 * Front-end access guard:
	 * - Logged-out visitors of a dashboard page are sent to the login page.
	 * - Logged-in visitors of the login page are sent to the dashboard.
	 *
	 * Detects the shortcodes in post content, with a fallback scan of
	 * Elementor's data meta for pages built with an Elementor widget.
	 *
	 * @return void
	 */
	public function guard_pages() {
		if ( is_admin() || ! is_singular() ) {
			return;
		}

		$post = get_post();

		if ( ! $post ) {
			return;
		}

		$has_dashboard = $this->page_uses_shortcode( $post, 'pathway_dashboard' );
		$has_login     = $this->page_uses_shortcode( $post, 'pathway_login' );

		if ( $has_dashboard && ! $has_login && ! is_user_logged_in() ) {
			wp_safe_redirect( $this->get_login_url() );
			exit;
		}

		if ( $has_login && is_user_logged_in() ) {
			wp_safe_redirect( $this->get_dashboard_url() );
			exit;
		}
	}

	/**
	 * Whether a page contains a shortcode, checking post content and
	 * Elementor's serialized widget data.
	 *
	 * @param WP_Post $post Page to inspect.
	 * @param string  $shortcode Shortcode tag.
	 * @return bool
	 */
	private function page_uses_shortcode( $post, $shortcode ) {
		if ( has_shortcode( (string) $post->post_content, $shortcode ) ) {
			return true;
		}

		$elementor_data = get_post_meta( $post->ID, '_elementor_data', true );

		return is_string( $elementor_data ) && false !== strpos( $elementor_data, '[' . $shortcode );
	}

	/**
	 * [pathway_login] shortcode handler.
	 *
	 * @param array|string $atts Shortcode attributes (redirect).
	 * @return string
	 */
	public function shortcode( $atts ) {
		$atts = shortcode_atts(
			array( 'redirect' => '' ),
			$atts,
			'pathway_login'
		);

		return $this->render( $atts['redirect'] );
	}

	/**
	 * Renders the login screen.
	 *
	 * @param string $redirect URL to send the user to after login.
	 *                         Defaults to the dashboard (front page).
	 * @return string
	 */
	public function render( $redirect = '' ) {
		pathway_dash()->assets->enqueue_login();

		if ( '' === $redirect ) {
			$redirect = $this->get_dashboard_url();
		}

		/**
		 * Filters the post-login redirect URL for the login screen.
		 *
		 * @param string $redirect Redirect URL.
		 */
		$redirect = apply_filters( 'pathway_dash_login_redirect', $redirect );

		$status = '';

		if ( isset( $_GET['login'] ) && 'failed' === $_GET['login'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display-only flag.
			$status = 'failed';
		} elseif ( isset( $_GET['loggedout'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display-only flag.
			$status = 'loggedout';
		}

		$contact = Pathway_Dashboard_Support::get_contact();

		ob_start();

		pathway_dash_template(
			'login',
			array(
				'redirect'       => $redirect,
				'status'         => $status,
				'is_logged_in'   => is_user_logged_in(),
				'support_email'  => $contact['email'],
				/** Filters the login hero background image URL. */
				'bg_url'         => apply_filters( 'pathway_dash_login_bg_url', self::DEFAULT_BG_URL ),
				/** Filters the promo banner image URL. */
				'promo_url'      => apply_filters( 'pathway_dash_login_promo_url', self::DEFAULT_PROMO_URL ),
				/** Filters the Learn More button URL. */
				'learn_more_url' => apply_filters( 'pathway_dash_learn_more_url', self::DEFAULT_LEARN_MORE_URL ),
			)
		);

		return ob_get_clean();
	}

	/**
	 * Sends failed front-end logins back to the page they came from
	 * with a ?login=failed flag (wp-login.php referers are left alone).
	 *
	 * @return void
	 */
	public function redirect_back_on_failure() {
		$referer = wp_get_referer();

		if (
			! $referer
			|| false !== strpos( $referer, 'wp-login.php' )
			|| false !== strpos( $referer, 'wp-admin' )
		) {
			return;
		}

		wp_safe_redirect( add_query_arg( 'login', 'failed', remove_query_arg( array( 'login', 'loggedout' ), $referer ) ) );
		exit;
	}
}
