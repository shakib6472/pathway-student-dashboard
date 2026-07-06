<?php
/**
 * Uninstall handler.
 *
 * Runs when the plugin is deleted from the Plugins screen.
 * Cleanup of plugin-created data (notification table, options)
 * will be added here as those features are built.
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if not called by WordPress during uninstall.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}
