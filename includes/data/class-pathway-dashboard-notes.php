<?php
/**
 * Notes data service.
 *
 * Reads student notes created by the "Notes by LearnDash" plugin
 * (CPT llms_student_notes, linked to lessons/topics via the
 * related_post_id meta).
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Pathway_Dashboard_Notes
 */
class Pathway_Dashboard_Notes {

	/**
	 * The post type registered by Notes by LearnDash.
	 */
	const POST_TYPE = 'llms_student_notes';

	/**
	 * Returns all notes of a user, grouped by their related lesson.
	 *
	 * @param int $user_id User ID.
	 * @return array[] [ { lesson_id, lesson_title, notes: array[] }, ... ]
	 */
	public static function get_notes_grouped( $user_id ) {
		$query = new WP_Query(
			array(
				'post_type'      => self::POST_TYPE,
				'author'         => $user_id,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'modified',
				'order'          => 'DESC',
			)
		);

		$groups = array();

		foreach ( $query->posts as $note_post ) {
			$note      = self::format_note( $note_post );
			$lesson_id = $note['lesson_id'];

			if ( ! isset( $groups[ $lesson_id ] ) ) {
				$groups[ $lesson_id ] = array(
					'lesson_id'    => $lesson_id,
					'lesson_title' => $note['lesson_title'],
					'notes'        => array(),
				);
			}

			$groups[ $lesson_id ]['notes'][] = $note;
		}

		return array_values( $groups );
	}

	/**
	 * Formats a single note post for the dashboard.
	 *
	 * @param WP_Post $note_post Note post.
	 * @return array
	 */
	public static function format_note( $note_post ) {
		$lesson_id    = absint( get_post_meta( $note_post->ID, 'related_post_id', true ) );
		$lesson_title = $lesson_id ? get_the_title( $lesson_id ) : '';

		if ( '' === $lesson_title ) {
			$lesson_title = __( 'General', 'pathway-student-dashboard' );
		}

		$title = $note_post->post_title;

		if ( '' === trim( $title ) ) {
			$title = __( 'Untitled note', 'pathway-student-dashboard' );
		}

		$admin_response = trim( (string) get_post_meta( $note_post->ID, 'admin_response', true ) );

		return array(
			'id'             => $note_post->ID,
			'title'          => $title,
			'content_raw'    => $note_post->post_content,
			'content_html'   => wpautop( wp_kses_post( $note_post->post_content ) ),
			'lesson_id'      => $lesson_id,
			'lesson_title'   => $lesson_title,
			'date'           => wp_date( get_option( 'date_format' ), get_post_timestamp( $note_post, 'modified' ) ),
			'admin_response' => $admin_response,
		);
	}

	/**
	 * Whether the current user owns a given note.
	 *
	 * @param int $note_id Note post ID.
	 * @param int $user_id User ID.
	 * @return bool
	 */
	public static function user_owns_note( $note_id, $user_id ) {
		$note = get_post( $note_id );

		return $note
			&& self::POST_TYPE === $note->post_type
			&& (int) $note->post_author === (int) $user_id;
	}
}
