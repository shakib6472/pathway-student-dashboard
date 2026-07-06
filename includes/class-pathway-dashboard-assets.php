<?php
/**
 * Asset registration and enqueueing.
 *
 * Assets are registered globally but only enqueued on pages that
 * actually render the dashboard shortcode, so the rest of the site
 * is never affected.
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Pathway_Dashboard_Assets
 */
class Pathway_Dashboard_Assets {

	/**
	 * Chart.js version served from the CDN.
	 *
	 * @var string
	 */
	const CHARTJS_VERSION = '4.4.9';

	/**
	 * Tabs that ship their own stylesheet in assets/css/tabs/{slug}.css.
	 *
	 * @var string[]
	 */
	const TAB_STYLES = array( 'my-courses', 'progress', 'certificates', 'notes' );

	/**
	 * Tabs that ship their own script in assets/js/tabs/{slug}.js,
	 * mapped to their script dependencies.
	 *
	 * @var array<string, string[]>
	 */
	const TAB_SCRIPTS = array(
		'progress' => array( 'pathway-dash', 'pathway-dash-chartjs' ),
		'notes'    => array( 'pathway-dash' ),
	);

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'register' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue' ), 20 );
	}

	/**
	 * Registers all dashboard assets.
	 *
	 * @return void
	 */
	public function register() {
		wp_register_style(
			'pathway-dash-fonts',
			'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@600;700&display=swap',
			array(),
			null // Google Fonts manages its own versioning.
		);

		wp_register_style(
			'pathway-dash',
			PATHWAY_DASH_URL . 'assets/css/dashboard.css',
			array( 'pathway-dash-fonts' ),
			PATHWAY_DASH_VERSION
		);

		foreach ( self::TAB_STYLES as $tab_slug ) {
			wp_register_style(
				'pathway-dash-tab-' . $tab_slug,
				PATHWAY_DASH_URL . 'assets/css/tabs/' . $tab_slug . '.css',
				array( 'pathway-dash' ),
				PATHWAY_DASH_VERSION
			);
		}

		// Registered now, enqueued from the Progress Analytics tab (Phase 3).
		wp_register_script(
			'pathway-dash-chartjs',
			'https://cdn.jsdelivr.net/npm/chart.js@' . self::CHARTJS_VERSION . '/dist/chart.umd.min.js',
			array(),
			self::CHARTJS_VERSION,
			true
		);

		wp_register_script(
			'pathway-dash',
			PATHWAY_DASH_URL . 'assets/js/dashboard.js',
			array(),
			PATHWAY_DASH_VERSION,
			true
		);

		foreach ( self::TAB_SCRIPTS as $tab_slug => $tab_deps ) {
			wp_register_script(
				'pathway-dash-tab-' . $tab_slug,
				PATHWAY_DASH_URL . 'assets/js/tabs/' . $tab_slug . '.js',
				$tab_deps,
				PATHWAY_DASH_VERSION,
				true
			);
		}

		wp_localize_script(
			'pathway-dash',
			'pathwayDash',
			array(
				'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
				'nonce'      => wp_create_nonce( 'pathway_dash_nonce' ),
				'defaultTab' => pathway_dash_get_default_tab(),
				'tabs'       => array_keys( pathway_dash_get_tabs() ),
			)
		);
	}

	/**
	 * Enqueues assets early when the shortcode is detected in post content.
	 *
	 * This is a best-effort detection so styles land in <head>. Pages built
	 * with Elementor may store the shortcode in meta instead of post content;
	 * for those, enqueue_now() runs as a fallback during shortcode render.
	 *
	 * @return void
	 */
	public function maybe_enqueue() {
		if ( ! is_singular() ) {
			return;
		}

		$post = get_post();

		if ( $post && has_shortcode( (string) $post->post_content, 'pathway_dashboard' ) ) {
			$this->enqueue_now();
		}
	}

	/**
	 * Enqueues the dashboard style and script immediately.
	 *
	 * Safe to call multiple times; WordPress deduplicates handles.
	 *
	 * @return void
	 */
	public function enqueue_now() {
		wp_enqueue_style( 'pathway-dash' );

		// TinyMCE bundle for the note edit form (Lesson Notes tab).
		if ( function_exists( 'wp_enqueue_editor' ) ) {
			wp_enqueue_editor();
		}

		foreach ( self::TAB_STYLES as $tab_slug ) {
			wp_enqueue_style( 'pathway-dash-tab-' . $tab_slug );
		}

		wp_enqueue_script( 'pathway-dash' );

		foreach ( array_keys( self::TAB_SCRIPTS ) as $tab_slug ) {
			wp_enqueue_script( 'pathway-dash-tab-' . $tab_slug );
		}
	}
}
