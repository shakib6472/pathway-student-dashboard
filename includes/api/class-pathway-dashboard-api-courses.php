<?php
/**
 * Courses REST endpoint.
 *
 * GET /wp-json/pathway/v1/courses      — all courses (metadata only)
 * GET /wp-json/pathway/v1/courses/{id} — a single course
 *
 * Lessons/topics are intentionally excluded; the main website only
 * needs catalog metadata. Pricing is handled statically on the main
 * site, so no price fields are exposed.
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Pathway_Dashboard_Api_Courses
 */
class Pathway_Dashboard_Api_Courses {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Registers the REST routes.
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			'pathway/v1',
			'/courses',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_courses' ),
				'permission_callback' => array( 'Pathway_Dashboard_Api_Keys', 'verify_request' ),
			)
		);

		register_rest_route(
			'pathway/v1',
			'/courses/(?P<id>\d+)',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_course' ),
				'permission_callback' => array( 'Pathway_Dashboard_Api_Keys', 'verify_request' ),
				'args'                => array(
					'id' => array(
						'type'     => 'integer',
						'required' => true,
					),
				),
			)
		);
	}

	/**
	 * Returns all published courses.
	 *
	 * @return WP_REST_Response
	 */
	public function get_courses() {
		$posts = get_posts(
			array(
				'post_type'      => 'sfwd-courses',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);

		return rest_ensure_response( array_map( array( $this, 'format_course' ), $posts ) );
	}

	/**
	 * Returns one course by ID.
	 *
	 * @param WP_REST_Request $request Request with the course id.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_course( $request ) {
		$course = get_post( absint( $request['id'] ) );

		if ( ! $course || 'sfwd-courses' !== $course->post_type || 'publish' !== $course->post_status ) {
			return new WP_Error(
				'pathway_dash_course_not_found',
				__( 'Course not found.', 'pathway-student-dashboard' ),
				array( 'status' => 404 )
			);
		}

		return rest_ensure_response( $this->format_course( $course ) );
	}

	/**
	 * Builds the public metadata payload for a course.
	 *
	 * @param WP_Post $course Course post.
	 * @return array
	 */
	private function format_course( $course ) {
		$excerpt = $course->post_excerpt;

		if ( '' === trim( $excerpt ) ) {
			$excerpt = wp_trim_words( wp_strip_all_tags( $course->post_content ), 40 );
		}

		return array(
			'id'              => $course->ID,
			'title'           => $course->post_title,
			'slug'            => $course->post_name,
			'url'             => (string) get_permalink( $course ),
			'excerpt'         => $excerpt,
			'thumbnail'       => (string) get_the_post_thumbnail_url( $course, 'large' ),
			'hours'           => absint( get_post_meta( $course->ID, Pathway_Dashboard_Stats::COURSE_HOURS_META, true ) ),
			'has_certificate' => Pathway_Dashboard_Certificates::course_has_certificate( $course->ID ),
			'created'         => $course->post_date_gmt,
			'modified'        => $course->post_modified_gmt,
		);
	}
}
