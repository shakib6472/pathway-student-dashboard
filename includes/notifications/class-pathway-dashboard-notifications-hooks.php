<?php
/**
 * Event listeners that create notifications (and emails).
 *
 * Events: lesson completed, quiz completed (pass/fail), course
 * completed (+ certificate, with email), and new resource published.
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Pathway_Dashboard_Notifications_Hooks
 */
class Pathway_Dashboard_Notifications_Hooks {

	/**
	 * Max users notified when a resource is published.
	 */
	const RESOURCE_NOTIFY_CAP = 500;

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'learndash_lesson_completed', array( $this, 'on_lesson_completed' ) );
		add_action( 'learndash_course_completed', array( $this, 'on_course_completed' ) );
		add_action( 'learndash_quiz_completed', array( $this, 'on_quiz_completed' ), 10, 2 );
		add_action( 'transition_post_status', array( $this, 'on_resource_published' ), 10, 3 );
	}

	/**
	 * Lesson completed: bell notification only.
	 *
	 * @param array $data LearnDash lesson data (user, course, lesson, progress).
	 * @return void
	 */
	public function on_lesson_completed( $data ) {
		if ( empty( $data['user'] ) || empty( $data['lesson'] ) ) {
			return;
		}

		$course_title = ! empty( $data['course'] ) ? $data['course']->post_title : '';

		Pathway_Dashboard_Notifications_DB::add(
			$data['user']->ID,
			'lesson_completed',
			__( 'Lesson completed', 'pathway-student-dashboard' ),
			$course_title
				/* translators: 1: lesson title, 2: course title. */
				? sprintf( __( 'You completed "%1$s" in %2$s.', 'pathway-student-dashboard' ), $data['lesson']->post_title, $course_title )
				/* translators: %s: lesson title. */
				: sprintf( __( 'You completed "%s".', 'pathway-student-dashboard' ), $data['lesson']->post_title ),
			! empty( $data['course'] ) ? (string) get_permalink( $data['course'] ) : ''
		);
	}

	/**
	 * Course completed: bell notification + congratulations email,
	 * including the certificate link when one is earned.
	 *
	 * @param array $data LearnDash course data (user, course, progress, course_completed).
	 * @return void
	 */
	public function on_course_completed( $data ) {
		if ( empty( $data['user'] ) || empty( $data['course'] ) ) {
			return;
		}

		$user   = $data['user'];
		$course = $data['course'];

		$cert_url = '';

		if ( function_exists( 'learndash_get_course_certificate_link' ) ) {
			$cert_url = (string) learndash_get_course_certificate_link( $course->ID, $user->ID );
		}

		Pathway_Dashboard_Notifications_DB::add(
			$user->ID,
			'course_completed',
			__( 'Course completed!', 'pathway-student-dashboard' ),
			$cert_url
				/* translators: %s: course title. */
				? sprintf( __( 'Congratulations! You finished %s — your certificate is ready.', 'pathway-student-dashboard' ), $course->post_title )
				/* translators: %s: course title. */
				: sprintf( __( 'Congratulations! You finished %s.', 'pathway-student-dashboard' ), $course->post_title ),
			$cert_url ? $cert_url : (string) get_permalink( $course )
		);

		/* translators: %s: course title. */
		$subject = sprintf( __( 'Congratulations — you completed %s!', 'pathway-student-dashboard' ), $course->post_title );

		$body = sprintf(
			/* translators: %s: course title. */
			__( 'You have successfully completed "%s". Great work — this is a big milestone in your dental assistant training.', 'pathway-student-dashboard' ),
			$course->post_title
		);

		if ( $cert_url ) {
			$body .= "\n\n" . __( 'Your certificate of completion is ready to download.', 'pathway-student-dashboard' );
		}

		Pathway_Dashboard_Notifications_Email::send(
			$user,
			$subject,
			__( 'Course completed!', 'pathway-student-dashboard' ),
			$body,
			$cert_url ? __( 'Download Certificate', 'pathway-student-dashboard' ) : __( 'View Course', 'pathway-student-dashboard' ),
			$cert_url ? $cert_url : (string) get_permalink( $course )
		);
	}

	/**
	 * Quiz completed: bell notification (pass or fail).
	 *
	 * @param array   $quizdata LearnDash quiz data.
	 * @param WP_User $user     User who took the quiz.
	 * @return void
	 */
	public function on_quiz_completed( $quizdata, $user ) {
		if ( ! $user instanceof WP_User || empty( $quizdata['quiz'] ) ) {
			return;
		}

		$quiz_title = $quizdata['quiz'] instanceof WP_Post
			? $quizdata['quiz']->post_title
			: get_the_title( absint( $quizdata['quiz'] ) );

		$percentage = isset( $quizdata['percentage'] ) ? (int) round( (float) $quizdata['percentage'] ) : 0;
		$passed     = ! empty( $quizdata['pass'] );

		Pathway_Dashboard_Notifications_DB::add(
			$user->ID,
			$passed ? 'quiz_passed' : 'quiz_failed',
			$passed ? __( 'Quiz passed', 'pathway-student-dashboard' ) : __( 'Quiz attempt', 'pathway-student-dashboard' ),
			$passed
				/* translators: 1: quiz title, 2: score percentage. */
				? sprintf( __( 'You passed "%1$s" with %2$d%%.', 'pathway-student-dashboard' ), $quiz_title, $percentage )
				/* translators: 1: quiz title, 2: score percentage. */
				: sprintf( __( 'You scored %2$d%% on "%1$s". You can retake it anytime.', 'pathway-student-dashboard' ), $quiz_title, $percentage ),
			''
		);
	}

	/**
	 * New resource published: notify enrolled students.
	 *
	 * Resources restricted to courses notify those courses' students;
	 * unrestricted resources notify students of every course.
	 *
	 * @param string  $new_status New post status.
	 * @param string  $old_status Old post status.
	 * @param WP_Post $post       Post object.
	 * @return void
	 */
	public function on_resource_published( $new_status, $old_status, $post ) {
		if (
			Pathway_Dashboard_Resources::POST_TYPE !== $post->post_type
			|| 'publish' !== $new_status
			|| 'publish' === $old_status
			|| ! function_exists( 'learndash_get_users_for_course' )
		) {
			return;
		}

		$course_ids = get_post_meta( $post->ID, 'related_course', true );
		$course_ids = array_values( array_filter( array_map( 'absint', (array) $course_ids ) ) );

		if ( empty( $course_ids ) && function_exists( 'learndash_get_courses' ) ) {
			$course_ids = wp_list_pluck( (array) get_posts( array(
				'post_type'      => 'sfwd-courses',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
			) ), null );
		}

		$user_ids = array();

		foreach ( $course_ids as $course_id ) {
			$query = learndash_get_users_for_course( $course_id, array( 'fields' => 'ID' ), false );

			if ( $query instanceof WP_User_Query ) {
				$user_ids = array_merge( $user_ids, array_map( 'absint', (array) $query->get_results() ) );
			}

			if ( count( $user_ids ) > self::RESOURCE_NOTIFY_CAP ) {
				break;
			}
		}

		$user_ids = array_slice( array_unique( $user_ids ), 0, self::RESOURCE_NOTIFY_CAP );

		foreach ( $user_ids as $user_id ) {
			Pathway_Dashboard_Notifications_DB::add(
				$user_id,
				'resource_added',
				__( 'New resource available', 'pathway-student-dashboard' ),
				/* translators: %s: resource title. */
				sprintf( __( '"%s" was added to your resources.', 'pathway-student-dashboard' ), $post->post_title ),
				''
			);
		}
	}
}
