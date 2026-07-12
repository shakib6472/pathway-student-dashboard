<?php
/**
 * Analytics data service for the Progress Analytics tab.
 *
 * Weekly activity, per-module completion, and quiz history.
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Pathway_Dashboard_Analytics
 */
class Pathway_Dashboard_Analytics {

	/**
	 * How many quiz attempts to show in the scores list.
	 */
	const QUIZ_HISTORY_LIMIT = 10;

	/**
	 * Returns learning minutes per day for the last 7 days.
	 *
	 * Prefers ProPanel's per-second time entries; falls back to
	 * estimating from the LearnDash activity table.
	 *
	 * @param int $user_id User ID.
	 * @return array[] [ { label: 'Mon', date: 'Y-m-d', minutes: int }, ... ] oldest first.
	 */
	public static function get_weekly_activity( $user_id ) {
		$days  = array();
		$today = wp_date( 'Y-m-d' );

		for ( $i = 6; $i >= 0; $i-- ) {
			$date = wp_date( 'Y-m-d', strtotime( $today . ' -' . $i . ' days' ) );

			$days[ $date ] = array(
				'label'   => wp_date( 'D', strtotime( $date ) ),
				'date'    => $date,
				'minutes' => 0,
			);
		}

		$since = strtotime( $today . ' -6 days midnight' );

		foreach ( self::get_time_sessions( $user_id, $since ) as $session ) {
			$date = wp_date( 'Y-m-d', $session['time'] );

			if ( isset( $days[ $date ] ) ) {
				$days[ $date ]['minutes'] += $session['seconds'] / MINUTE_IN_SECONDS;
			}
		}

		foreach ( $days as &$day ) {
			$day['minutes'] = (int) round( $day['minutes'] );
		}
		unset( $day );

		return array_values( $days );
	}

	/**
	 * Returns the user's current learning streak in days (max 7,
	 * since it is computed from the weekly activity window).
	 *
	 * A day counts when it has any logged learning time. Today is
	 * allowed to be empty without breaking yesterday's streak.
	 *
	 * @param int $user_id User ID.
	 * @return int
	 */
	public static function get_streak_days( $user_id ) {
		$days   = array_reverse( self::get_weekly_activity( $user_id ) ); // Newest first.
		$streak = 0;

		foreach ( $days as $index => $day ) {
			if ( $day['minutes'] > 0 ) {
				$streak++;
			} elseif ( 0 === $index ) {
				continue; // The day is young — today may still be empty.
			} else {
				break;
			}
		}

		return $streak;
	}

	/**
	 * Fetches raw time sessions (timestamp + seconds) since a moment.
	 *
	 * @param int $user_id User ID.
	 * @param int $since   Unix timestamp lower bound.
	 * @return array[] [ { time: int, seconds: int }, ... ]
	 */
	private static function get_time_sessions( $user_id, $since ) {
		global $wpdb;

		$sessions = array();
		$table    = Pathway_Dashboard_Stats::time_entries_table();

		if ( null !== $table ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- ProPanel exposes no API for this.
			$rows = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT activity_updated AS t, time_spent AS s FROM ' . $table . ' WHERE user_id = %d AND activity_updated >= %d', // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- prefixed table name.
					$user_id,
					$since
				),
				ARRAY_A
			);
		} elseif ( class_exists( 'LDLMS_DB' ) ) {
			$activity_table = LDLMS_DB::get_table_name( 'user_activity' );

			if ( empty( $activity_table ) ) {
				return $sessions;
			}

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- LearnDash exposes no API for this.
			$rows = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT activity_updated AS t,
						LEAST(
							CASE
								WHEN activity_completed > activity_started THEN activity_completed - activity_started
								WHEN activity_updated > activity_started THEN activity_updated - activity_started
								ELSE 0
							END,
							%d
						) AS s
					 FROM ' . $activity_table . ' ' . // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name from LDLMS_DB.
					'WHERE user_id = %d AND activity_started > 0 AND activity_updated >= %d',
					Pathway_Dashboard_Stats::SESSION_CAP_SECONDS,
					$user_id,
					$since
				),
				ARRAY_A
			);
		} else {
			return $sessions;
		}

		foreach ( (array) $rows as $row ) {
			$sessions[] = array(
				'time'    => absint( $row['t'] ),
				'seconds' => absint( $row['s'] ),
			);
		}

		return $sessions;
	}

	/**
	 * Returns per-lesson (module) completion for a course.
	 *
	 * A lesson marked complete counts as 100%; otherwise its percentage
	 * comes from completed topics vs. total topics.
	 *
	 * @param int $course_id Course ID.
	 * @param int $user_id   User ID.
	 * @return array[] [ { id, title, percentage, completed: bool }, ... ]
	 */
	public static function get_course_modules( $course_id, $user_id ) {
		if ( ! function_exists( 'learndash_get_course_lessons_list' ) ) {
			return array();
		}

		$lessons = learndash_get_course_lessons_list( $course_id, $user_id, array( 'num' => -1 ) );

		if ( empty( $lessons ) ) {
			return array();
		}

		$progress         = function_exists( 'learndash_user_get_course_progress' )
			? learndash_user_get_course_progress( $user_id, $course_id, 'legacy' )
			: array();
		$completed_topics = isset( $progress['topics'] ) && is_array( $progress['topics'] ) ? $progress['topics'] : array();

		$modules = array();

		foreach ( $lessons as $lesson ) {
			if ( empty( $lesson['post'] ) ) {
				continue;
			}

			$lesson_id    = $lesson['post']->ID;
			$is_completed = isset( $lesson['status'] ) && 'completed' === $lesson['status'];
			$percentage   = $is_completed ? 100 : 0;

			if ( ! $is_completed && function_exists( 'learndash_course_get_children_of_step' ) ) {
				$topic_ids = learndash_course_get_children_of_step( $course_id, $lesson_id, 'sfwd-topic', 'ids', true );
				$total     = count( $topic_ids );

				if ( $total > 0 ) {
					$done = 0;

					foreach ( $topic_ids as $topic_id ) {
						if ( ! empty( $completed_topics[ $lesson_id ][ $topic_id ] ) ) {
							$done++;
						}
					}

					$percentage = (int) ( $done * 100 / $total );
				}
			}

			$modules[] = array(
				'id'         => $lesson_id,
				'title'      => $lesson['post']->post_title,
				'percentage' => $percentage,
				'completed'  => $is_completed,
			);
		}

		return $modules;
	}

	/**
	 * Returns the user's most recent quiz attempts.
	 *
	 * Reads the '_sfwd-quizzes' user meta (newest first).
	 *
	 * @param int $user_id User ID.
	 * @return array[] [ { title, course_title, percentage, passed, time }, ... ]
	 */
	public static function get_quiz_history( $user_id ) {
		$attempts = get_user_meta( $user_id, '_sfwd-quizzes', true );

		if ( empty( $attempts ) || ! is_array( $attempts ) ) {
			return array();
		}

		$history = array();

		foreach ( $attempts as $attempt ) {
			if ( ! isset( $attempt['quiz'] ) ) {
				continue;
			}

			$history[] = array(
				'title'        => get_the_title( absint( $attempt['quiz'] ) ),
				'course_title' => ! empty( $attempt['course'] ) ? get_the_title( absint( $attempt['course'] ) ) : '',
				'percentage'   => isset( $attempt['percentage'] ) ? (int) round( (float) $attempt['percentage'] ) : 0,
				'passed'       => ! empty( $attempt['pass'] ),
				'time'         => isset( $attempt['time'] ) ? absint( $attempt['time'] ) : 0,
			);
		}

		usort(
			$history,
			static function ( $a, $b ) {
				return $b['time'] <=> $a['time'];
			}
		);

		return array_slice( $history, 0, self::QUIZ_HISTORY_LIMIT );
	}
}
