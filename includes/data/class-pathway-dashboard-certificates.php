<?php
/**
 * Certificate data service.
 *
 * Builds locked/earned certificate cards from LearnDash course
 * certificates (works with Certificate Builder templates too,
 * since those render through the same certificate link).
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Pathway_Dashboard_Certificates
 */
class Pathway_Dashboard_Certificates {

	/**
	 * Returns certificate cards for all enrolled courses that have
	 * a certificate assigned.
	 *
	 * @param array[] $course_cards Cards from Pathway_Dashboard_Courses::get_course_card().
	 * @param int     $user_id      User ID.
	 * @return array[] [ { course_id, title, thumbnail, percentage, earned, url, completed_ts }, ... ]
	 */
	public static function get_certificates( $course_cards, $user_id ) {
		$certificates = array();

		foreach ( $course_cards as $card ) {
			if ( ! self::course_has_certificate( $card['id'] ) ) {
				continue;
			}

			$earned = 'completed' === $card['status'];
			$url    = '';

			if ( $earned && function_exists( 'learndash_get_course_certificate_link' ) ) {
				$url = (string) learndash_get_course_certificate_link( $card['id'], $user_id );

				// The link is empty when LearnDash does not consider the
				// certificate earned yet (e.g. threshold not met).
				$earned = '' !== $url;
			}

			$completed_ts = 0;

			if ( $earned && function_exists( 'learndash_user_get_course_completed_date' ) ) {
				$completed_ts = absint( learndash_user_get_course_completed_date( $user_id, $card['id'] ) );
			}

			$certificates[] = array(
				'course_id'    => $card['id'],
				'title'        => $card['title'],
				'thumbnail'    => $card['thumbnail'],
				'percentage'   => $card['progress']['percentage'],
				'earned'       => $earned,
				'url'          => $url,
				'completed_ts' => $completed_ts,
			);
		}

		return $certificates;
	}

	/**
	 * Whether a course has a certificate assigned.
	 *
	 * @param int $course_id Course ID.
	 * @return bool
	 */
	public static function course_has_certificate( $course_id ) {
		if ( ! function_exists( 'learndash_get_setting' ) ) {
			return false;
		}

		return absint( learndash_get_setting( $course_id, 'certificate' ) ) > 0;
	}
}
