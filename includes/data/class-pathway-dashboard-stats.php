<?php
/**
 * Aggregate stats service for the dashboard stats row.
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Pathway_Dashboard_Stats
 */
class Pathway_Dashboard_Stats {

	/**
	 * Cap a single activity session at 2 hours when summing time,
	 * so a lesson left open overnight does not inflate Hours Logged.
	 */
	const SESSION_CAP_SECONDS = 7200;

	/**
	 * Optional course meta key holding the course's official hour count
	 * (e.g. 80 for an "80-Hour" program). Used for the "34/80" display.
	 */
	const COURSE_HOURS_META = 'pathway_course_hours';

	/**
	 * Builds the stats row values from a set of course cards.
	 *
	 * @param array[] $course_cards Cards from Pathway_Dashboard_Courses::get_course_card().
	 * @param int     $user_id      User ID.
	 * @return array
	 */
	public static function get_stats( $course_cards, $user_id ) {
		$completed_steps = 0;
		$total_steps     = 0;
		$hours_total     = 0;

		foreach ( $course_cards as $card ) {
			$completed_steps += $card['progress']['completed'];
			$total_steps     += $card['progress']['total'];
			$hours_total     += absint( get_post_meta( $card['id'], self::COURSE_HOURS_META, true ) );
		}

		// Truncate (not round) so the number always matches LearnDash's
		// own percentage math and the per-course cards.
		$overall = $total_steps > 0 ? min( 100, (int) ( $completed_steps * 100 / $total_steps ) ) : 0;

		return array(
			'overall_progress' => $overall,
			'completed_steps'  => $completed_steps,
			'total_steps'      => $total_steps,
			'quiz_average'     => self::get_quiz_average( $user_id ),
			'minutes_logged'   => self::get_minutes_logged( $user_id ),
			'hours_total'      => $hours_total,
		);
	}

	/**
	 * Returns the user's average quiz score in percent.
	 *
	 * Reads the '_sfwd-quizzes' user meta LearnDash maintains for
	 * every quiz attempt.
	 *
	 * @param int $user_id User ID.
	 * @return int|null Null when the user has no quiz attempts.
	 */
	public static function get_quiz_average( $user_id ) {
		$attempts = get_user_meta( $user_id, '_sfwd-quizzes', true );

		if ( empty( $attempts ) || ! is_array( $attempts ) ) {
			return null;
		}

		$scores = array();

		foreach ( $attempts as $attempt ) {
			if ( isset( $attempt['percentage'] ) && is_numeric( $attempt['percentage'] ) ) {
				$scores[] = (float) $attempt['percentage'];
			}
		}

		if ( empty( $scores ) ) {
			return null;
		}

		return (int) round( array_sum( $scores ) / count( $scores ) );
	}

	/**
	 * Returns total learning time for a user.
	 *
	 * Primary source: ProPanel's time entries table, which records
	 * real seconds spent via a front-end heartbeat (matches the
	 * numbers ProPanel reports show). Falls back to estimating from
	 * the LearnDash activity table when ProPanel is not installed.
	 *
	 * @param int $user_id User ID.
	 * @return int Whole minutes (rounded).
	 */
	public static function get_minutes_logged( $user_id ) {
		$seconds = self::get_propanel_seconds( $user_id );

		if ( null === $seconds ) {
			$seconds = self::get_activity_seconds( $user_id );
		}

		return (int) round( $seconds / MINUTE_IN_SECONDS );
	}

	/**
	 * Sums time from ProPanel's ld_time_entries table.
	 *
	 * @param int $user_id User ID.
	 * @return int|null Seconds, or null when the table does not exist.
	 */
	private static function get_propanel_seconds( $user_id ) {
		global $wpdb;

		static $table_exists = null;

		$table = $wpdb->prefix . 'ld_time_entries';

		if ( null === $table_exists ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- schema lookup.
			$table_exists = ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table );
		}

		if ( ! $table_exists ) {
			return null;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- ProPanel exposes no API for this aggregate.
		$seconds = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT SUM( time_spent ) FROM ' . $table . ' WHERE user_id = %d', // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- prefixed table name.
				$user_id
			)
		);

		return absint( $seconds );
	}

	/**
	 * Estimates time from the LearnDash activity table.
	 *
	 * Finished activities count (completed - started); activities still
	 * in progress count (updated - started), the same way ProPanel's
	 * fallback works. Each session is capped at SESSION_CAP_SECONDS.
	 *
	 * @param int $user_id User ID.
	 * @return int Seconds.
	 */
	private static function get_activity_seconds( $user_id ) {
		global $wpdb;

		if ( ! class_exists( 'LDLMS_DB' ) ) {
			return 0;
		}

		$table = LDLMS_DB::get_table_name( 'user_activity' );

		if ( empty( $table ) ) {
			return 0;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- LearnDash exposes no API for this aggregate.
		$seconds = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT SUM( LEAST(
					CASE
						WHEN activity_completed > activity_started THEN activity_completed - activity_started
						WHEN activity_updated > activity_started THEN activity_updated - activity_started
						ELSE 0
					END,
					%d
				) )
				 FROM ' . $table . ' ' . // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name from LDLMS_DB.
				'WHERE user_id = %d AND activity_started > 0',
				self::SESSION_CAP_SECONDS,
				$user_id
			)
		);

		return absint( $seconds );
	}

	/**
	 * Formats logged minutes for display in the stats card.
	 *
	 * Under an hour the value is shown in minutes ("34" + "min"),
	 * otherwise in hours ("3.4", or "34" once it passes 10 hours).
	 *
	 * @param int $minutes Total minutes logged.
	 * @return array { value: string, unit: string, is_hours: bool }
	 */
	public static function format_logged_time( $minutes ) {
		if ( $minutes < 60 ) {
			return array(
				'value'    => (string) $minutes,
				'unit'     => __( 'min', 'pathway-student-dashboard' ),
				'is_hours' => false,
			);
		}

		$hours = $minutes / 60;
		$value = $hours < 10 ? round( $hours, 1 ) : round( $hours );

		return array(
			'value'    => (string) $value,
			'unit'     => '',
			'is_hours' => true,
		);
	}
}
