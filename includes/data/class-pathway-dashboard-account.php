<?php
/**
 * Account data service.
 *
 * Student ID generation, state display, enrollment date, and
 * profile photo helpers.
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Pathway_Dashboard_Account
 */
class Pathway_Dashboard_Account {

	/**
	 * User meta key holding the 2-letter US state code (e.g. "TX").
	 * Written by the main site's checkout flow; defaults to CA.
	 */
	const STATE_META = 'pathway_student_state';

	/**
	 * User meta key holding the generated student ID.
	 */
	const STUDENT_ID_META = 'pathway_student_id';

	/**
	 * User meta key holding the profile photo attachment ID.
	 */
	const PHOTO_META = 'pathway_profile_photo';

	/**
	 * Default state code when the meta is missing.
	 */
	const DEFAULT_STATE = 'CA';

	/**
	 * US state codes mapped to full names.
	 *
	 * @var array<string, string>
	 */
	const STATES = array(
		'AL' => 'Alabama',
		'AK' => 'Alaska',
		'AZ' => 'Arizona',
		'AR' => 'Arkansas',
		'CA' => 'California',
		'CO' => 'Colorado',
		'CT' => 'Connecticut',
		'DE' => 'Delaware',
		'DC' => 'District of Columbia',
		'FL' => 'Florida',
		'GA' => 'Georgia',
		'HI' => 'Hawaii',
		'ID' => 'Idaho',
		'IL' => 'Illinois',
		'IN' => 'Indiana',
		'IA' => 'Iowa',
		'KS' => 'Kansas',
		'KY' => 'Kentucky',
		'LA' => 'Louisiana',
		'ME' => 'Maine',
		'MD' => 'Maryland',
		'MA' => 'Massachusetts',
		'MI' => 'Michigan',
		'MN' => 'Minnesota',
		'MS' => 'Mississippi',
		'MO' => 'Missouri',
		'MT' => 'Montana',
		'NE' => 'Nebraska',
		'NV' => 'Nevada',
		'NH' => 'New Hampshire',
		'NJ' => 'New Jersey',
		'NM' => 'New Mexico',
		'NY' => 'New York',
		'NC' => 'North Carolina',
		'ND' => 'North Dakota',
		'OH' => 'Ohio',
		'OK' => 'Oklahoma',
		'OR' => 'Oregon',
		'PA' => 'Pennsylvania',
		'RI' => 'Rhode Island',
		'SC' => 'South Carolina',
		'SD' => 'South Dakota',
		'TN' => 'Tennessee',
		'TX' => 'Texas',
		'UT' => 'Utah',
		'VT' => 'Vermont',
		'VA' => 'Virginia',
		'WA' => 'Washington',
		'WV' => 'West Virginia',
		'WI' => 'Wisconsin',
		'WY' => 'Wyoming',
	);

	/**
	 * Returns the student's state as code + full name.
	 *
	 * @param int $user_id User ID.
	 * @return array { code: string, name: string }
	 */
	public static function get_state( $user_id ) {
		$code = strtoupper( trim( (string) get_user_meta( $user_id, self::STATE_META, true ) ) );

		if ( '' === $code || ! isset( self::STATES[ $code ] ) ) {
			$code = self::DEFAULT_STATE;
		}

		return array(
			'code' => $code,
			'name' => self::STATES[ $code ],
		);
	}

	/**
	 * Returns the student ID, generating and persisting it on first use.
	 *
	 * Format: PDA-<registration year>-<user ID zero-padded to 4 digits>,
	 * e.g. PDA-2026-0042.
	 *
	 * @param WP_User $user User object.
	 * @return string
	 */
	public static function get_student_id( $user ) {
		$student_id = (string) get_user_meta( $user->ID, self::STUDENT_ID_META, true );

		if ( '' !== $student_id ) {
			return $student_id;
		}

		$year       = wp_date( 'Y', strtotime( $user->user_registered ) );
		$student_id = sprintf( 'PDA-%s-%04d', $year, $user->ID );

		update_user_meta( $user->ID, self::STUDENT_ID_META, $student_id );

		return $student_id;
	}

	/**
	 * Returns the earliest course enrollment timestamp for a user.
	 *
	 * @param int $user_id User ID.
	 * @return int Unix timestamp, or 0 when unknown.
	 */
	public static function get_enrolled_date( $user_id ) {
		if ( ! function_exists( 'ld_course_access_from' ) ) {
			return 0;
		}

		$timestamps = array();

		foreach ( Pathway_Dashboard_Courses::get_enrolled_course_ids( $user_id ) as $course_id ) {
			$from = absint( ld_course_access_from( $course_id, $user_id ) );

			if ( $from > 0 ) {
				$timestamps[] = $from;
			}
		}

		return empty( $timestamps ) ? 0 : min( $timestamps );
	}

	/**
	 * Returns the profile photo URL, or an empty string.
	 *
	 * @param int    $user_id User ID.
	 * @param string $size    Image size.
	 * @return string
	 */
	public static function get_photo_url( $user_id, $size = 'thumbnail' ) {
		$photo_id = absint( get_user_meta( $user_id, self::PHOTO_META, true ) );

		if ( ! $photo_id ) {
			return '';
		}

		return (string) wp_get_attachment_image_url( $photo_id, $size );
	}
}
