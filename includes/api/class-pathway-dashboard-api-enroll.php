<?php
/**
 * Enrollment webhook.
 *
 * POST /wp-json/pathway/v1/enroll
 *
 * Called by the main website after a purchase: creates (or reuses)
 * the student account, enrolls it in the course, stores the state,
 * and sends a welcome email with a login link (never the password).
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
					'course_id'  => array(
						'type'     => 'integer',
						'required' => true,
					),
					'state'      => array(
						'type'     => 'string',
						'required' => false,
						'default'  => '',
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
		$state      = strtoupper( sanitize_text_field( $request['state'] ) );

		// ---- Validation. ----

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

		$course = get_post( $course_id );

		if ( ! $course || 'sfwd-courses' !== $course->post_type || 'publish' !== $course->post_status ) {
			return new WP_Error(
				'pathway_dash_course_not_found',
				__( 'Course not found.', 'pathway-student-dashboard' ),
				array( 'status' => 404 )
			);
		}

		if ( ! function_exists( 'ld_update_course_access' ) ) {
			return new WP_Error(
				'pathway_dash_learndash_missing',
				__( 'LearnDash is not active on this site.', 'pathway-student-dashboard' ),
				array( 'status' => 500 )
			);
		}

		// ---- Find or create the user. ----

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

		// ---- State + student ID. ----

		if ( isset( Pathway_Dashboard_Account::STATES[ $state ] ) ) {
			update_user_meta( $user_id, Pathway_Dashboard_Account::STATE_META, $state );
		}

		$user       = get_userdata( $user_id );
		$student_id = Pathway_Dashboard_Account::get_student_id( $user );

		// ---- Enroll. ----

		$already_enrolled = function_exists( 'sfwd_lms_has_access' )
			? sfwd_lms_has_access( $course_id, $user_id )
			: false;

		if ( ! $already_enrolled ) {
			ld_update_course_access( $user_id, $course_id );
		}

		// ---- Notify the student. ----

		if ( ! $already_enrolled ) {
			$this->send_welcome_email( $user, $course, $created );

			Pathway_Dashboard_Notifications_DB::add(
				$user_id,
				'course_enrolled',
				__( 'Welcome to your course!', 'pathway-student-dashboard' ),
				/* translators: %s: course title. */
				sprintf( __( 'You are enrolled in %s. Let\'s get started!', 'pathway-student-dashboard' ), $course->post_title ),
				(string) get_permalink( $course )
			);
		}

		// ---- Response. ----

		return rest_ensure_response(
			array(
				'success'          => true,
				'user_id'          => $user_id,
				'created'          => $created,
				'already_enrolled' => (bool) $already_enrolled,
				'enrolled'         => true,
				'student_id'       => $student_id,
				'course_id'        => $course_id,
			)
		);
	}

	/**
	 * Sends the welcome / new-course email (login link, no password).
	 *
	 * @param WP_User $user    Student.
	 * @param WP_Post $course  Course.
	 * @param bool    $created Whether the account was just created.
	 * @return void
	 */
	private function send_welcome_email( $user, $course, $created ) {
		$login_url = pathway_dash()->login->get_login_url();

		if ( $created ) {
			/* translators: %s: course title. */
			$subject = sprintf( __( 'Welcome to Pathway Dental Academy — %s is ready!', 'pathway-student-dashboard' ), $course->post_title );
			$heading = __( 'Your account and course are ready!', 'pathway-student-dashboard' );
			$body    = sprintf(
				/* translators: 1: course title, 2: email address. */
				__( 'Welcome aboard! Your student account has been created and you are enrolled in "%1$s".

Sign in with your email address (%2$s) and the password you chose during checkout to start learning.', 'pathway-student-dashboard' ),
				$course->post_title,
				$user->user_email
			);
		} else {
			/* translators: %s: course title. */
			$subject = sprintf( __( 'New course enrolled — %s', 'pathway-student-dashboard' ), $course->post_title );
			$heading = __( 'New course enrolled — let\'s start!', 'pathway-student-dashboard' );
			$body    = sprintf(
				/* translators: %s: course title. */
				__( 'Great news — "%s" has been added to your account. Sign in to pick up where you left off.', 'pathway-student-dashboard' ),
				$course->post_title
			);
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
}
