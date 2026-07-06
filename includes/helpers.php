<?php
/**
 * Shared helper functions.
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns the dashboard tab registry.
 *
 * Each tab: slug => [
 *   'label'      => sidebar label,
 *   'short'      => short label used in the mobile bottom nav,
 *   'icon'       => icon key for pathway_dash_icon(),
 *   'in_mobile'  => whether the tab appears in the mobile bottom nav
 *                   (the rest are reachable from the avatar menu),
 * ]
 *
 * @return array[]
 */
function pathway_dash_get_tabs() {
	$tabs = array(
		'my-courses'   => array(
			'label'     => __( 'My Courses', 'pathway-student-dashboard' ),
			'short'     => __( 'Courses', 'pathway-student-dashboard' ),
			'icon'      => 'book',
			'in_mobile' => true,
		),
		'progress'     => array(
			'label'     => __( 'Progress Analytics', 'pathway-student-dashboard' ),
			'short'     => __( 'Progress', 'pathway-student-dashboard' ),
			'icon'      => 'chart',
			'in_mobile' => true,
		),
		'certificates' => array(
			'label'     => __( 'Certificates', 'pathway-student-dashboard' ),
			'short'     => __( 'Certs', 'pathway-student-dashboard' ),
			'icon'      => 'award',
			'in_mobile' => true,
		),
		'notes'        => array(
			'label'     => __( 'Lesson Notes', 'pathway-student-dashboard' ),
			'short'     => __( 'Notes', 'pathway-student-dashboard' ),
			'icon'      => 'pen',
			'in_mobile' => true,
		),
		'resources'    => array(
			'label'     => __( 'Resources', 'pathway-student-dashboard' ),
			'short'     => __( 'Resources', 'pathway-student-dashboard' ),
			'icon'      => 'folder',
			'in_mobile' => false,
		),
		'account'      => array(
			'label'     => __( 'Account', 'pathway-student-dashboard' ),
			'short'     => __( 'Account', 'pathway-student-dashboard' ),
			'icon'      => 'user',
			'in_mobile' => true,
		),
		'support'      => array(
			'label'     => __( 'Support', 'pathway-student-dashboard' ),
			'short'     => __( 'Support', 'pathway-student-dashboard' ),
			'icon'      => 'help',
			'in_mobile' => false,
		),
	);

	/**
	 * Filters the dashboard tab registry.
	 *
	 * @param array[] $tabs Tab definitions keyed by slug.
	 */
	return apply_filters( 'pathway_dash_tabs', $tabs );
}

/**
 * Returns the default (initially active) tab slug.
 *
 * @return string
 */
function pathway_dash_get_default_tab() {
	return 'my-courses';
}

/**
 * Returns an inline SVG icon.
 *
 * All icons are 24x24 stroke icons drawing with currentColor so they
 * inherit the surrounding text color.
 *
 * @param string $key Icon key.
 * @return string SVG markup, or an empty string for unknown keys.
 */
function pathway_dash_icon( $key ) {
	$attrs = 'xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"';

	$icons = array(
		'book'   => '<svg ' . $attrs . '><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>',
		'chart'  => '<svg ' . $attrs . '><line x1="12" y1="20" x2="12" y2="10"/><line x1="18" y1="20" x2="18" y2="4"/><line x1="6" y1="20" x2="6" y2="16"/></svg>',
		'award'  => '<svg ' . $attrs . '><circle cx="12" cy="8" r="6"/><path d="M15.5 13 17 22l-5-3-5 3 1.5-9"/></svg>',
		'pen'    => '<svg ' . $attrs . '><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>',
		'folder' => '<svg ' . $attrs . '><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>',
		'user'   => '<svg ' . $attrs . '><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
		'help'   => '<svg ' . $attrs . '><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
		'bell'   => '<svg ' . $attrs . '><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>',
		'logout' => '<svg ' . $attrs . '><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>',
		'check'  => '<svg ' . $attrs . '><polyline points="20 6 9 17 4 12"/></svg>',
	);

	return isset( $icons[ $key ] ) ? $icons[ $key ] : '';
}

/**
 * Returns the initials for a user (e.g. "Sarah Miller" => "SM").
 *
 * @param WP_User $user User object.
 * @return string One or two uppercase characters.
 */
function pathway_dash_get_user_initials( $user ) {
	$first = $user->first_name ? $user->first_name : $user->display_name;
	$last  = $user->last_name;

	$initials = mb_substr( trim( $first ), 0, 1 );

	if ( $last ) {
		$initials .= mb_substr( trim( $last ), 0, 1 );
	}

	return mb_strtoupper( $initials );
}

/**
 * Returns the display first name for the greeting ("Hi, Sarah").
 *
 * @param WP_User $user User object.
 * @return string
 */
function pathway_dash_get_user_first_name( $user ) {
	return $user->first_name ? $user->first_name : $user->display_name;
}

/**
 * Loads a plugin template file with variables extracted into scope.
 *
 * @param string $template Relative path inside the templates/ directory, without extension.
 * @param array  $vars     Variables to expose to the template.
 * @return void
 */
function pathway_dash_template( $template, $vars = array() ) {
	$file = PATHWAY_DASH_DIR . 'templates/' . $template . '.php';

	if ( ! file_exists( $file ) ) {
		return;
	}

	// phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- controlled template context.
	extract( $vars, EXTR_SKIP );

	include $file;
}
