<?php
/**
 * AJAX handlers for the notification bell (mark read / mark all).
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Pathway_Dashboard_Notifications_Ajax
 */
class Pathway_Dashboard_Notifications_Ajax {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_pathway_dash_notification_read', array( $this, 'mark_read' ) );
		add_action( 'wp_ajax_pathway_dash_notifications_read_all', array( $this, 'mark_all_read' ) );
	}

	/**
	 * Marks a single notification as read.
	 *
	 * @return void
	 */
	public function mark_read() {
		check_ajax_referer( 'pathway_dash_nonce', 'nonce' );

		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;

		if ( $id ) {
			Pathway_Dashboard_Notifications_DB::mark_read( $id, get_current_user_id() );
		}

		wp_send_json_success(
			array( 'unread' => Pathway_Dashboard_Notifications_DB::unread_count( get_current_user_id() ) )
		);
	}

	/**
	 * Marks all notifications as read.
	 *
	 * @return void
	 */
	public function mark_all_read() {
		check_ajax_referer( 'pathway_dash_nonce', 'nonce' );

		Pathway_Dashboard_Notifications_DB::mark_all_read( get_current_user_id() );

		wp_send_json_success( array( 'unread' => 0 ) );
	}
}
