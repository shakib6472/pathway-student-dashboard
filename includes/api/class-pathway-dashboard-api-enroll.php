<?php
/**
 * Enrollment webhook.
 *
 * POST /wp-json/pathway/v1/enroll
 *
 * Called by the main website after a purchase. Flow:
 * 1. Validate the payload (state is the course selector).
 * 2. Create the student account, or reuse it by email.
 * 3. Save the state meta and generate the Student ID.
 * 4. Enroll the student in every course whose `state` meta
 *    contains the incoming state (or in course_id when given).
 * 5. Email the student a welcome message (login link only,
 *    never the password) and add bell notifications.
 *
 * When no course matches the state, the account is still created
 * (the student has already paid) and the site admin gets an alert
 * email to enroll them manually.
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Pathway_Dashboard_Api_Enroll
 */
class Pathway_Dashboard_Api_Enroll {

	/**
	 * Course meta key holding its state codes (multi-select via
	 * the Post Meta Data Manager plugin; values arrive as nested
	 * arrays of lowercase codes, e.g. [ [ 'ca', 'tx', 'fl' ] ]).
	 */
	const COURSE_STATE_META = 'state';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Registers the REST route.
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			'pathway/v1',
			'/enroll',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'enroll' ),
				'permission_callback' => array( 'Pathway_Dashboard_Api_Keys', 'verify_request' ),
				'args'                => array(
					'email'      => array(
						'type'     => 'string',
						'required' => true,
					),
					'first_name' => array(
						'type'     => 'string',
						'required' => true,
					),
					'last_name'  => array(
						'type'     => 'string',
						'required' => false,
						'default'  => '',
					),
					'password'   => array(
						'type'     => 'string',
						'required' => false,
						'default'  => '',
					),
					'state'      => array(
						'type'     => 'string',
						'required' => true,
					),
					'course_id'  => array(
						// Optional legacy path: enrolls this exact course
						// instead of matching courses by state.
						'type'     => 'integer',
						'required' => false,
						'default'  => 0,
					),
				),
			)
		);
	}

	/**
	 * Handles the enrollment request.
	 *
	 * @param WP_REST_Request $request Incoming request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function enroll( $request ) {
		$email      = sanitize_email( $request['email'] );
		$first_name = sanitize_text_field( $request['first_name'] );
		$last_name  = sanitize_text_field( $request['last_name'] );
		$password   = (string) $request['password'];
		$course_id  = absint( $request['course_id'] );
		$state      = strtoupper( trim( sanitize_text_field( $request['state'] ) ) );

		// ---- 1. Validation. ----

		if ( ! is_email( $email ) ) {
			return new WP_Error(
				'pathway_dash_invalid_email',
				__( 'A valid email address is required.', 'pathway-student-dashboard' ),
				array( 'status' => 400 )
			);
		}

		if ( '' === $first_name ) {
			return new WP_Error(
				'pathway_dash_missing_name',
				__( 'first_name is required.', 'pathway-student-dashboard' ),
				array( 'status' => 400 )
			);
		}

		if ( ! isset( Pathway_Dashboard_Account::STATES[ $state ] ) ) {
			return new WP_Error(
				'pathway_dash_invalid_state',
				__( 'state must be a valid 2-letter US state code (e.g. TX, CA, FL).', 'pathway-student-dashboard' ),
				array( 'status' => 400 )
			);
		}

		if ( ! function_exists( 'ld_update_course_access' ) ) {
			return new WP_Error(
				'pathway_dash_learndash_missing',
				__( 'LearnDash is not active on this site.', 'pathway-student-dashboard' ),
				array( 'status' => 500 )
			);
		}

		// ---- 2. Find or create the user. ----

		$existing_id = email_exists( $email );
		$created     = false;

		if ( $existing_id ) {
			$user_id = (int) $existing_id;

			// Refresh names; never touch the existing password.
			wp_update_user(
				array(
					'ID'           => $user_id,
					'first_name'   => $first_name,
					'last_name'    => $last_name,
					'display_name' => trim( $first_name . ' ' . $last_name ),
				)
			);
		} else {
			if ( strlen( $password ) < 8 ) {
				return new WP_Error(
					'pathway_dash_weak_password',
					__( 'password is required for new accounts (minimum 8 characters).', 'pathway-student-dashboard' ),
					array( 'status' => 400 )
				);
			}

			$user_id = wp_insert_user(
				array(
					'user_login'   => $email,
					'user_email'   => $email,
					'user_pass'    => $password,
					'first_name'   => $first_name,
					'last_name'    => $last_name,
					'display_name' => trim( $first_name . ' ' . $last_name ),
					'role'         => 'subscriber',
				)
			);

			if ( is_wp_error( $user_id ) ) {
				return new WP_Error(
					'pathway_dash_user_creation_failed',
					$user_id->get_error_message(),
					array( 'status' => 500 )
				);
			}

			$created = true;
		}

		// ---- 3. State meta + Student ID. ----

		update_user_meta( $user_id, Pathway_Dashboard_Account::STATE_META, $state );

		$user       = get_userdata( $user_id );
		$student_id = Pathway_Dashboard_Account::get_student_id( $user );

		// ---- 4. Find the courses and enroll. ----

		if ( $course_id > 0 ) {
			$course = get_post( $course_id );

			if ( ! $course || 'sfwd-courses' !== $course->post_type || 'publish' !== $course->post_status ) {
				return new WP_Error(
					'pathway_dash_course_not_found',
					__( 'Course not found.', 'pathway-student-dashboard' ),
					array( 'status' => 404 )
				);
			}

			$matched_courses = array( $course );
		} else {
			$matched_courses = $this->find_courses_for_state( $state );
		}

		$courses_payload = array();
		$newly_enrolled  = array();

		foreach ( $matched_courses as $course ) {
			$already = function_exists( 'sfwd_lms_has_access' )
				? sfwd_lms_has_access( $course->ID, $user_id )
				: false;

			if ( ! $already ) {
				ld_update_course_access( $user_id, $course->ID );
				$newly_enrolled[] = $course;

				Pathway_Dashboard_Notifications_DB::add(
					$user_id,
					'course_enrolled',
					__( 'Welcome to your course!', 'pathway-student-dashboard' ),
					/* translators: %s: course title. */
					sprintf( __( 'You are enrolled in %s. Let\'s get started!', 'pathway-student-dashboard' ), $course->post_title ),
					(string) get_permalink( $course )
				);
			}

			$courses_payload[] = array(
				'id'               => $course->ID,
				'title'            => $course->post_title,
				'already_enrolled' => (bool) $already,
			);
		}

		// ---- 5. Emails. ----

		if ( ! empty( $newly_enrolled ) ) {
			$this->send_welcome_email( $user, $newly_enrolled, $created );
		} elseif ( empty( $matched_courses ) ) {
			$this->send_no_match_alert( $user, $state );
		}

		// ---- 6. Response. ----

		return rest_ensure_response(
			array(
				'success'        => true,
				'user_id'        => $user_id,
				'created'        => $created,
				'state'          => $state,
				'student_id'     => $student_id,
				'courses'        => $courses_payload,
				'matched_count'  => count( $matched_courses ),
				'enrolled_count' => count( $newly_enrolled ),
			)
		);
	}

	/**
	 * Returns published courses whose state meta contains a state.
	 *
	 * @param string $state Uppercase 2-letter code (e.g. "FL").
	 * @return WP_Post[]
	 */
	private function find_courses_for_state( $state ) {
		$state_lower = strtolower( $state );
		$matched     = array();

		$courses = get_posts(
			array(
				'post_type'      => 'sfwd-courses',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
			)
		);

		foreach ( $courses as $course ) {
			$codes = $this->normalize_state_list(
				get_post_meta( $course->ID, self::COURSE_STATE_META, false )
			);

			if ( in_array( $state_lower, $codes, true ) ) {
				$matched[] = $course;
			}
		}

		return $matched;
	}

	/**
	 * Flattens a course's state meta into a clean lowercase list.
	 *
	 * Handles every shape the meta may take (nested arrays from the
	 * multi-select field, plain arrays, or a single string) and
	 * lowercases each code so comparison is case-insensitive.
	 *
	 * @param mixed $raw Raw meta value(s).
	 * @return string[] e.g. [ 'ca', 'tx', 'fl' ]
	 */
	private function normalize_state_list( $raw ) {
		$flat = array();
		$raw  = (array) $raw; // array_walk_recursive needs a real variable (by reference).

		array_walk_recursive(
			$raw,
			static function ( $value ) use ( &$flat ) {
				$value = strtolower( trim( (string) $value ) );

				if ( '' !== $value ) {
					$flat[] = $value;
				}
			}
		);

		return array_unique( $flat );
	}

	/**
	 * Sends the welcome / new-course email (login link, no password).
	 *
	 * @param WP_User   $user    Student.
	 * @param WP_Post[] $courses Newly enrolled courses.
	 * @param bool      $created Whether the account was just created.
	 * @return void
	 */
	private function send_welcome_email( $user, $courses, $created ) {
		$login_url = pathway_dash()->login->get_login_url();
		$titles    = wp_list_pluck( $courses, 'post_title' );
		$single    = 1 === count( $titles );

		// Multiple courses are listed one per line in the email body.
		$course_list = implode(
			"\n\n",
			array_map(
				static function ( $title ) {
					return '• ' . $title;
				},
				$titles
			)
		);

		if ( $created ) {
			$subject = $single
				/* translators: %s: course title. */
				? sprintf( __( 'Welcome to Pathway Dental Academy — %s is ready!', 'pathway-student-dashboard' ), $titles[0] )
				: __( 'Welcome to Pathway Dental Academy — your courses are ready!', 'pathway-student-dashboard' );

			$heading = $single
				? __( 'Your account and course are ready!', 'pathway-student-dashboard' )
				: __( 'Your account and courses are ready!', 'pathway-student-dashboard' );

			$body = sprintf(
				/* translators: %s: student email address. */
				__( 'Welcome aboard! Your student account has been created and you are enrolled in:', 'pathway-student-dashboard' )
					. "\n\n%s\n\n" .
					__( 'Sign in with your email address (%s) and the password you chose during checkout to start learning.', 'pathway-student-dashboard' ),
				$course_list,
				$user->user_email
			);
		} else {
			$subject = $single
				/* translators: %s: course title. */
				? sprintf( __( 'New course enrolled — %s', 'pathway-student-dashboard' ), $titles[0] )
				: __( 'New courses enrolled — let\'s start!', 'pathway-student-dashboard' );

			$heading = __( 'New course enrolled — let\'s start!', 'pathway-student-dashboard' );

			$body = __( 'Great news — the following has been added to your account:', 'pathway-student-dashboard' )
				. "\n\n" . $course_list . "\n\n"
				. __( 'Sign in to pick up where you left off.', 'pathway-student-dashboard' );
		}

		Pathway_Dashboard_Notifications_Email::send(
			$user,
			$subject,
			$heading,
			$body,
			__( 'Start Learning', 'pathway-student-dashboard' ),
			$login_url
		);
	}

	/**
	 * Alerts the site admin when a paid signup matched no course,
	 * so the student can be enrolled manually.
	 *
	 * @param WP_User $user  Student that was created/updated.
	 * @param string  $state Uppercase state code from the webhook.
	 * @return void
	 */
	private function send_no_match_alert( $user, $state ) {
		/**
		 * Filters the recipient of "no course matched" alerts.
		 *
		 * @param string $email Recipient address (admin email by default).
		 */
		$to = apply_filters( 'pathway_dash_enroll_alert_email', get_option( 'admin_email' ) );

		$subject = sprintf(
			/* translators: %s: state code. */
			__( '[Action needed] Enrollment received but no course matched state %s', 'pathway-student-dashboard' ),
			$state
		);

		$body = sprintf(
			/* translators: 1: display name, 2: email, 3: state code, 4: user edit URL. */
			__(
				'A signup came in from the main website, but no course on the LMS has this state in its "state" meta field.

Student: %1$s (%2$s)
State: %3$s

The account was created and the student received NO course yet. Please enroll them manually: %4$s',
				'pathway-student-dashboard'
			),
			$user->display_name,
			$user->user_email,
			$state,
			admin_url( 'user-edit.php?user_id=' . $user->ID )
		);

		wp_mail( $to, $subject, $body );
	}
}
