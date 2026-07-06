<?php
/**
 * Resources data service.
 *
 * Reads the pathway_resource CPT (created via ACF) and filters
 * resources by the student's enrolled courses. ACF fields:
 * resource_type, resource_file, resource_url, resource_description,
 * related_course (multiple sfwd-courses, empty = visible to all).
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Pathway_Dashboard_Resources
 */
class Pathway_Dashboard_Resources {

	/**
	 * The resource post type slug.
	 */
	const POST_TYPE = 'pathway_resource';

	/**
	 * Resource type slugs mapped to icon keys and display labels.
	 *
	 * @var array<string, array>
	 */
	const TYPES = array(
		'pdf'   => array(
			'icon'  => 'file',
			'label' => 'PDF',
		),
		'doc'   => array(
			'icon'  => 'file',
			'label' => 'Document',
		),
		'sheet' => array(
			'icon'  => 'sheet',
			'label' => 'Sheet',
		),
		'video' => array(
			'icon'  => 'video',
			'label' => 'Video',
		),
		'link'  => array(
			'icon'  => 'external',
			'label' => 'Link',
		),
	);

	/**
	 * Returns resource cards visible to a user.
	 *
	 * A resource with no related_course is visible to everyone;
	 * otherwise the user must be enrolled in one of its courses.
	 *
	 * @param int $user_id User ID.
	 * @return array[]
	 */
	public static function get_resources_for_user( $user_id ) {
		$query = new WP_Query(
			array(
				'post_type'      => self::POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		if ( empty( $query->posts ) ) {
			return array();
		}

		$enrolled  = Pathway_Dashboard_Courses::get_enrolled_course_ids( $user_id );
		$resources = array();

		foreach ( $query->posts as $resource_post ) {
			$related = self::get_related_course_ids( $resource_post->ID );

			if ( ! empty( $related ) && empty( array_intersect( $related, $enrolled ) ) ) {
				continue;
			}

			$resources[] = self::format_resource( $resource_post );
		}

		return $resources;
	}

	/**
	 * Returns the course IDs a resource is restricted to.
	 *
	 * ACF stores a multiple post-object field as an array of IDs.
	 *
	 * @param int $resource_id Resource post ID.
	 * @return int[] Empty when the resource is unrestricted.
	 */
	private static function get_related_course_ids( $resource_id ) {
		$raw = get_post_meta( $resource_id, 'related_course', true );

		if ( empty( $raw ) ) {
			return array();
		}

		return array_values( array_filter( array_map( 'absint', (array) $raw ) ) );
	}

	/**
	 * Builds display data for a single resource card.
	 *
	 * @param WP_Post $resource_post Resource post.
	 * @return array
	 */
	private static function format_resource( $resource_post ) {
		$type      = (string) get_post_meta( $resource_post->ID, 'resource_type', true );
		$type_info = isset( self::TYPES[ $type ] ) ? self::TYPES[ $type ] : array(
			'icon'  => 'folder',
			'label' => strtoupper( $type ),
		);

		$file_id  = absint( get_post_meta( $resource_post->ID, 'resource_file', true ) );
		$file_url = $file_id ? (string) wp_get_attachment_url( $file_id ) : '';
		$ext      = '';
		$size     = '';

		if ( $file_id && $file_url ) {
			$path = get_attached_file( $file_id );

			if ( $path && file_exists( $path ) ) {
				$size = size_format( (float) filesize( $path ), 1 );
			}

			$ext = strtoupper( pathinfo( wp_parse_url( $file_url, PHP_URL_PATH ), PATHINFO_EXTENSION ) );
		}

		$external_url = (string) get_post_meta( $resource_post->ID, 'resource_url', true );

		// A file wins over an external URL when both are set.
		if ( $file_url ) {
			$action_url   = $file_url;
			$action_label = __( 'Download', 'pathway-student-dashboard' );
			$is_external  = false;
		} elseif ( $external_url ) {
			$action_url   = $external_url;
			$action_label = __( 'Open Link', 'pathway-student-dashboard' );
			$is_external  = true;
		} else {
			$action_url   = '';
			$action_label = '';
			$is_external  = false;
		}

		return array(
			'id'           => $resource_post->ID,
			'title'        => $resource_post->post_title,
			'description'  => (string) get_post_meta( $resource_post->ID, 'resource_description', true ),
			'type'         => $type,
			'type_label'   => $type_info['label'],
			'icon'         => $type_info['icon'],
			'ext'          => $ext,
			'size'         => $size,
			'action_url'   => $action_url,
			'action_label' => $action_label,
			'is_external'  => $is_external,
		);
	}
}
