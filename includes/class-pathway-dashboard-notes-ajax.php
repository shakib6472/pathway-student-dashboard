<?php
/**
 * AJAX handlers for the Lesson Notes tab (save + delete).
 *
 * Every request requires a valid nonce, a logged-in user, and
 * ownership of the note being modified.
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Pathway_Dashboard_Notes_Ajax
 */
class Pathway_Dashboard_Notes_Ajax {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_pathway_dash_save_note', array( $this, 'save_note' ) );
		add_action( 'wp_ajax_pathway_dash_delete_note', array( $this, 'delete_note' ) );
	}

	/**
	 * Verifies the nonce and note ownership; sends a JSON error on failure.
	 *
	 * @return int Validated note ID.
	 */
	private function validate_request() {
		check_ajax_referer( 'pathway_dash_nonce', 'nonce' );

		$note_id = isset( $_POST['note_id'] ) ? absint( $_POST['note_id'] ) : 0;

		if ( ! $note_id || ! Pathway_Dashboard_Notes::user_owns_note( $note_id, get_current_user_id() ) ) {
			wp_send_json_error(
				array( 'message' => __( 'You are not allowed to modify this note.', 'pathway-student-dashboard' ) ),
				403
			);
		}

		return $note_id;
	}

	/**
	 * Updates a note's title and content.
	 *
	 * @return void
	 */
	public function save_note() {
		$note_id = $this->validate_request();

		$title   = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
		$content = isset( $_POST['content'] ) ? wp_kses_post( wp_unslash( $_POST['content'] ) ) : '';

		if ( '' === trim( $content ) ) {
			wp_send_json_error(
				array( 'message' => __( 'The note content cannot be empty.', 'pathway-student-dashboard' ) ),
				400
			);
		}

		$result = wp_update_post(
			array(
				'ID'           => $note_id,
				'post_title'   => $title,
				'post_content' => $content,
			),
			true
		);

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ), 500 );
		}

		wp_send_json_success(
			array( 'note' => Pathway_Dashboard_Notes::format_note( get_post( $note_id ) ) )
		);
	}

	/**
	 * Moves a note to the trash.
	 *
	 * @return void
	 */
	public function delete_note() {
		$note_id = $this->validate_request();

		if ( ! wp_trash_post( $note_id ) ) {
			wp_send_json_error(
				array( 'message' => __( 'The note could not be deleted.', 'pathway-student-dashboard' ) ),
				500
			);
		}

		wp_send_json_success();
	}
}
