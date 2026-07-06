<?php
/**
 * AJAX handlers for the Account tab: profile save, password
 * change, and profile photo upload/removal.
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Pathway_Dashboard_Account_Ajax
 */
class Pathway_Dashboard_Account_Ajax {

	/**
	 * Maximum profile photo size in bytes (2 MB).
	 */
	const MAX_PHOTO_BYTES = 2097152;

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_pathway_dash_save_profile', array( $this, 'save_profile' ) );
		add_action( 'wp_ajax_pathway_dash_change_password', array( $this, 'change_password' ) );
		add_action( 'wp_ajax_pathway_dash_upload_photo', array( $this, 'upload_photo' ) );
		add_action( 'wp_ajax_pathway_dash_remove_photo', array( $this, 'remove_photo' ) );
	}

	/**
	 * Verifies the nonce and returns the current user.
	 *
	 * @return WP_User
	 */
	private function validate_request() {
		check_ajax_referer( 'pathway_dash_nonce', 'nonce' );

		return wp_get_current_user();
	}

	/**
	 * Updates first name, last name, and email.
	 *
	 * @return void
	 */
	public function save_profile() {
		$user = $this->validate_request();

		$first_name = isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '';
		$last_name  = isset( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : '';
		$email      = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';

		if ( '' === $first_name ) {
			wp_send_json_error( array( 'message' => __( 'First name is required.', 'pathway-student-dashboard' ) ), 400 );
		}

		if ( ! is_email( $email ) ) {
			wp_send_json_error( array( 'message' => __( 'Please enter a valid email address.', 'pathway-student-dashboard' ) ), 400 );
		}

		$existing = email_exists( $email );

		if ( $existing && (int) $existing !== (int) $user->ID ) {
			wp_send_json_error( array( 'message' => __( 'This email address is already in use.', 'pathway-student-dashboard' ) ), 400 );
		}

		$result = wp_update_user(
			array(
				'ID'           => $user->ID,
				'first_name'   => $first_name,
				'last_name'    => $last_name,
				'user_email'   => $email,
				'display_name' => trim( $first_name . ' ' . $last_name ),
			)
		);

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ), 500 );
		}

		$updated = get_userdata( $user->ID );

		wp_send_json_success(
			array(
				'message'    => __( 'Profile updated.', 'pathway-student-dashboard' ),
				'first_name' => pathway_dash_get_user_first_name( $updated ),
				'initials'   => pathway_dash_get_user_initials( $updated ),
			)
		);
	}

	/**
	 * Changes the password after verifying the current one.
	 *
	 * @return void
	 */
	public function change_password() {
		$user = $this->validate_request();

		$current = isset( $_POST['current_password'] ) ? (string) wp_unslash( $_POST['current_password'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- passwords must not be altered.
		$new     = isset( $_POST['new_password'] ) ? (string) wp_unslash( $_POST['new_password'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- passwords must not be altered.
		$confirm = isset( $_POST['confirm_password'] ) ? (string) wp_unslash( $_POST['confirm_password'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- passwords must not be altered.

		if ( ! wp_check_password( $current, $user->user_pass, $user->ID ) ) {
			wp_send_json_error( array( 'message' => __( 'Your current password is incorrect.', 'pathway-student-dashboard' ) ), 400 );
		}

		if ( strlen( $new ) < 8 ) {
			wp_send_json_error( array( 'message' => __( 'The new password must be at least 8 characters.', 'pathway-student-dashboard' ) ), 400 );
		}

		if ( $new !== $confirm ) {
			wp_send_json_error( array( 'message' => __( 'The new passwords do not match.', 'pathway-student-dashboard' ) ), 400 );
		}

		wp_set_password( $new, $user->ID );

		// wp_set_password destroys all sessions; sign the user back in
		// so they are not silently logged out.
		wp_set_auth_cookie( $user->ID );

		wp_send_json_success( array( 'message' => __( 'Password changed successfully.', 'pathway-student-dashboard' ) ) );
	}

	/**
	 * Handles a profile photo upload.
	 *
	 * @return void
	 */
	public function upload_photo() {
		$user = $this->validate_request();

		if ( empty( $_FILES['photo'] ) || empty( $_FILES['photo']['name'] ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- handled by media_handle_upload.
			wp_send_json_error( array( 'message' => __( 'No file was uploaded.', 'pathway-student-dashboard' ) ), 400 );
		}

		$size = isset( $_FILES['photo']['size'] ) ? absint( $_FILES['photo']['size'] ) : 0;

		if ( $size > self::MAX_PHOTO_BYTES ) {
			wp_send_json_error( array( 'message' => __( 'The photo must be smaller than 2 MB.', 'pathway-student-dashboard' ) ), 400 );
		}

		$name  = sanitize_file_name( wp_unslash( $_FILES['photo']['name'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- unslashed inline.
		$check = wp_check_filetype( $name, array(
			'jpg|jpeg' => 'image/jpeg',
			'png'      => 'image/png',
			'gif'      => 'image/gif',
			'webp'     => 'image/webp',
		) );

		if ( empty( $check['type'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Please upload a JPG, PNG, GIF, or WebP image.', 'pathway-student-dashboard' ) ), 400 );
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		$attachment_id = media_handle_upload( 'photo', 0 );

		if ( is_wp_error( $attachment_id ) ) {
			wp_send_json_error( array( 'message' => $attachment_id->get_error_message() ), 500 );
		}

		$this->delete_current_photo( $user->ID );

		update_user_meta( $user->ID, Pathway_Dashboard_Account::PHOTO_META, $attachment_id );

		wp_send_json_success(
			array(
				'message' => __( 'Photo updated.', 'pathway-student-dashboard' ),
				'url'     => Pathway_Dashboard_Account::get_photo_url( $user->ID ),
			)
		);
	}

	/**
	 * Removes the profile photo.
	 *
	 * @return void
	 */
	public function remove_photo() {
		$user = $this->validate_request();

		$this->delete_current_photo( $user->ID );
		delete_user_meta( $user->ID, Pathway_Dashboard_Account::PHOTO_META );

		wp_send_json_success( array( 'message' => __( 'Photo removed.', 'pathway-student-dashboard' ) ) );
	}

	/**
	 * Deletes the user's current photo attachment, if any.
	 *
	 * @param int $user_id User ID.
	 * @return void
	 */
	private function delete_current_photo( $user_id ) {
		$old_id = absint( get_user_meta( $user_id, Pathway_Dashboard_Account::PHOTO_META, true ) );

		if ( $old_id ) {
			wp_delete_attachment( $old_id, true );
		}
	}
}
