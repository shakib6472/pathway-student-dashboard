<?php
/**
 * Uninstall handler.
 *
 * Runs when the plugin is deleted from the Plugins screen.
 * Removes the notifications table and plugin options. Student IDs,
 * states, and profile photos in user meta are left intact since
 * they are enrollment records the academy may still need.
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if not called by WordPress during uninstall.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- schema removal on uninstall.
$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'pathway_dash_notifications' );

delete_option( 'pathway_dash_db_version' );
