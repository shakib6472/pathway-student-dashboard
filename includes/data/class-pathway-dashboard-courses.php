<?php
/**
 * Course data service.
 *
 * Reads enrollment, progress, and resume data from LearnDash.
 * All methods degrade gracefully (empty values) when LearnDash
 * is not active.
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Pathway_Dashboard_Courses
 */
class Pathway_Dashboard_Courses {

	/**
	 * Returns the IDs of courses the user is enrolled in.
	 *
	 * @param int $user_id User ID.
	 * @return int[]
	 */
	public static function get_enrolled_course_ids( $user_id ) {
		if ( ! function_exists( 'learndash_user_get_enrolled_courses' ) ) {
			return array();
		}

		$course_ids = learndash_user_get_enrolled_courses( $user_id, array(), true );

		return array_values( array_filter( array_map( 'absint', (array) $course_ids ) ) );
	}

	/**
	 * Returns the user's progress for a course.
	 *
	 * @param int $course_id Course ID.
	 * @param int $user_id   User ID.
	 * @return array { percentage: int, completed: int, total: int }
	 */
	public static function get_progress( $course_id, $user_id ) {
		$progress = array(
			'percentage' => 0,
			'completed'  => 0,
			'total'      => 0,
		);

		if ( ! function_exists( 'learndash_user_get_course_progress' ) ) {
			return $progress;
		}

		$raw = learndash_user_get_course_progress( $user_id, $course_id );

		$progress['completed'] = isset( $raw['completed'] ) ? absint( $raw['completed'] ) : 0;
		$progress['total']     = isset( $raw['total'] ) ? absint( $raw['total'] ) : 0;

		if ( isset( $raw['status'] ) && 'completed' === $raw['status'] ) {
			$progress['completed'] = $progress['total'];
		}

		if ( $progress['total'] > 0 ) {
			$progress['percentage'] = min( 100, (int) ( $progress['completed'] * 100 / $progress['total'] ) );
		}

		return $progress;
	}

	/**
	 * Builds all display data for a single course card.
	 *
	 * @param int $course_id Course ID.
	 * @param int $user_id   User ID.
	 * @return array
	 */
	public static function get_course_card( $course_id, $user_id ) {
		$progress = self::get_progress( $course_id, $user_id );

		if ( 100 === $progress['percentage'] ) {
			$status = 'completed';
		} elseif ( $progress['completed'] > 0 ) {
			$status = 'in-progress';
		} else {
			$status = 'not-started';
		}

		$enrolled_ts = function_exists( 'ld_course_access_from' )
			? absint( ld_course_access_from( $course_id, $user_id ) )
			: 0;

		$resume = self::get_resume_step( $course_id, $user_id );

		return array(
			'id'          => $course_id,
			'title'       => get_the_title( $course_id ),
			'url'         => (string) get_permalink( $course_id ),
			'thumbnail'   => (string) get_the_post_thumbnail_url( $course_id, 'medium_large' ),
			'progress'    => $progress,
			'status'      => $status,
			'enrolled_ts' => $enrolled_ts,
			'resume'      => $resume,
		);
	}

	/**
	 * Returns the next step the user should take in a course.
	 *
	 * @param int $course_id Course ID.
	 * @param int $user_id   User ID.
	 * @return array { url: string, label: string } Falls back to the course itself.
	 */
	public static function get_resume_step( $course_id, $user_id ) {
		$resume = array(
			'url'   => (string) get_permalink( $course_id ),
			'label' => '',
		);

		if ( ! function_exists( 'learndash_user_progress_get_first_incomplete_step' ) ) {
			return $resume;
		}

		$step_id = absint( learndash_user_progress_get_first_incomplete_step( $user_id, $course_id ) );

		if ( ! $step_id ) {
			return $resume;
		}

		$label = get_the_title( $step_id );

		// For topics/quizzes, prefix the parent lesson so the student
		// sees "Lesson title — Step title".
		if ( function_exists( 'learndash_course_get_single_parent_step' ) ) {
			$lesson_id = absint( learndash_course_get_single_parent_step( $course_id, $step_id, 'sfwd-lessons' ) );

			if ( $lesson_id && $lesson_id !== $step_id ) {
				$label = get_the_title( $lesson_id ) . ' — ' . $label;
			}
		}

		if ( function_exists( 'learndash_get_step_permalink' ) ) {
			$step_url = learndash_get_step_permalink( $step_id, $course_id );

			if ( $step_url ) {
				$resume['url'] = $step_url;
			}
		}

		$resume['label'] = $label;

		return $resume;
	}

	/**
	 * Picks the course for the "Continue Learning" card.
	 *
	 * Preference order: the most recently active in-progress course,
	 * then any not-started course, then null (all completed / none).
	 *
	 * @param array[] $course_cards Cards from get_course_card(), keyed by course ID.
	 * @param int     $user_id      User ID.
	 * @return array|null
	 */
	public static function pick_continue_learning( $course_cards, $user_id ) {
		$in_progress = array();
		$not_started = array();

		foreach ( $course_cards as $card ) {
			if ( 'in-progress' === $card['status'] ) {
				$in_progress[ $card['id'] ] = $card;
			} elseif ( 'not-started' === $card['status'] ) {
				$not_started[ $card['id'] ] = $card;
			}
		}

		if ( empty( $in_progress ) ) {
			return ! empty( $not_started ) ? reset( $not_started ) : null;
		}

		if ( count( $in_progress ) === 1 ) {
			return reset( $in_progress );
		}

		// Multiple in-progress courses: use LearnDash activity to find
		// the one the student touched most recently.
		$last_active = self::get_last_activity_per_course( $user_id );

		uksort(
			$in_progress,
			static function ( $a, $b ) use ( $last_active ) {
				$time_a = isset( $last_active[ $a ] ) ? $last_active[ $a ] : 0;
				$time_b = isset( $last_active[ $b ] ) ? $last_active[ $b ] : 0;

				return $time_b <=> $time_a;
			}
		);

		return reset( $in_progress );
	}

	/**
	 * Returns the latest activity timestamp per course for a user.
	 *
	 * @param int $user_id User ID.
	 * @return array<int, int> course_id => unix timestamp.
	 */
	private static function get_last_activity_per_course( $user_id ) {
		global $wpdb;

		if ( ! class_exists( 'LDLMS_DB' ) ) {
			return array();
		}

		$table = LDLMS_DB::get_table_name( 'user_activity' );

		if ( empty( $table ) ) {
			return array();
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- LearnDash exposes no API for this aggregate.
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT course_id, MAX(activity_updated) AS last_active
				 FROM {$table} " . // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name from LDLMS_DB.
				'WHERE user_id = %d AND course_id > 0
				 GROUP BY course_id',
				$user_id
			),
			ARRAY_A
		);

		$map = array();

		foreach ( (array) $rows as $row ) {
			$map[ absint( $row['course_id'] ) ] = absint( $row['last_active'] );
		}

		return $map;
	}
}
