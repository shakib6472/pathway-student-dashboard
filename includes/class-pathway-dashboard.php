<?php
/**
 * Main plugin class.
 *
 * Boots all plugin components and exposes shared services.
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Pathway_Dashboard
 */
final class Pathway_Dashboard {

	/**
	 * Singleton instance.
	 *
	 * @var Pathway_Dashboard|null
	 */
	private static $instance = null;

	/**
	 * Assets manager.
	 *
	 * @var Pathway_Dashboard_Assets
	 */
	public $assets;

	/**
	 * Shortcode handler.
	 *
	 * @var Pathway_Dashboard_Shortcode
	 */
	public $shortcode;

	/**
	 * Login screen handler.
	 *
	 * @var Pathway_Dashboard_Login
	 */
	public $login;

	/**
	 * Notes AJAX handler.
	 *
	 * @var Pathway_Dashboard_Notes_Ajax
	 */
	public $notes_ajax;

	/**
	 * Account AJAX handler.
	 *
	 * @var Pathway_Dashboard_Account_Ajax
	 */
	public $account_ajax;

	/**
	 * Notification event listeners.
	 *
	 * @var Pathway_Dashboard_Notifications_Hooks
	 */
	public $notifications_hooks;

	/**
	 * Notification AJAX handler.
	 *
	 * @var Pathway_Dashboard_Notifications_Ajax
	 */
	public $notifications_ajax;

	/**
	 * Courses REST endpoint.
	 *
	 * @var Pathway_Dashboard_Api_Courses
	 */
	public $api_courses;

	/**
	 * Enrollment webhook endpoint.
	 *
	 * @var Pathway_Dashboard_Api_Enroll
	 */
	public $api_enroll;

	/**
	 * Admin settings page.
	 *
	 * @var Pathway_Dashboard_Settings_Page
	 */
	public $settings_page;

	/**
	 * Returns the singleton instance.
	 *
	 * @return Pathway_Dashboard
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor. Wires up components and hooks.
	 */
	private function __construct() {
		$this->assets              = new Pathway_Dashboard_Assets();
		$this->login               = new Pathway_Dashboard_Login();
		$this->shortcode           = new Pathway_Dashboard_Shortcode();
		$this->notes_ajax          = new Pathway_Dashboard_Notes_Ajax();
		$this->account_ajax        = new Pathway_Dashboard_Account_Ajax();
		$this->notifications_hooks = new Pathway_Dashboard_Notifications_Hooks();
		$this->notifications_ajax  = new Pathway_Dashboard_Notifications_Ajax();
		$this->api_courses         = new Pathway_Dashboard_Api_Courses();
		$this->api_enroll          = new Pathway_Dashboard_Api_Enroll();
		$this->settings_page       = new Pathway_Dashboard_Settings_Page();

		add_action( 'admin_notices', array( $this, 'maybe_show_dependency_notice' ) );

		// Creates/updates the notifications table after plugin updates
		// that ship a new schema version (activation covers installs).
		add_action( 'init', array( 'Pathway_Dashboard_Notifications_DB', 'install' ) );
	}

	/**
	 * Whether LearnDash LMS is active.
	 *
	 * @return bool
	 */
	public function is_learndash_active() {
		return defined( 'LEARNDASH_VERSION' );
	}

	/**
	 * Shows an admin notice when LearnDash is not active.
	 *
	 * The dashboard still renders without LearnDash, but all course
	 * data will be empty, so the site admin should know about it.
	 *
	 * @return void
	 */
	public function maybe_show_dependency_notice() {
		if ( $this->is_learndash_active() || ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		printf(
			'<div class="notice notice-warning"><p>%s</p></div>',
			esc_html__( 'Pathway Student Dashboard: LearnDash LMS is not active. The dashboard will render, but course data will be unavailable.', 'pathway-student-dashboard' )
		);
	}
}
