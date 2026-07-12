<?php
/**
 * Notification storage.
 *
 * A small custom table holds per-user notifications. Rows are
 * pruned to the newest 50 per user on insert.
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Pathway_Dashboard_Notifications_DB
 */
class Pathway_Dashboard_Notifications_DB {

	/**
	 * Option storing the installed schema version.
	 */
	const DB_VERSION_OPTION = 'pathway_dash_db_version';

	/**
	 * Current schema version. Bump to trigger dbDelta on update.
	 */
	const DB_VERSION = '1.0';

	/**
	 * Maximum stored notifications per user.
	 */
	const MAX_PER_USER = 50;

	/**
	 * Returns the prefixed table name.
	 *
	 * @return string
	 */
	public static function table() {
		global $wpdb;

		return $wpdb->prefix . 'pathway_dash_notifications';
	}

	/**
	 * Creates or updates the table. Runs on activation and when
	 * DB_VERSION changes.
	 *
	 * @return void
	 */
	public static function install() {
		global $wpdb;

		if ( get_option( self::DB_VERSION_OPTION ) === self::DB_VERSION ) {
			return;
		}

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table           = self::table();
		$charset_collate = $wpdb->get_charset_collate();

		dbDelta(
			"CREATE TABLE {$table} (
				id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				user_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
				type VARCHAR(40) NOT NULL DEFAULT '',
				title VARCHAR(255) NOT NULL DEFAULT '',
				message TEXT NOT NULL,
				link VARCHAR(255) NOT NULL DEFAULT '',
				is_read TINYINT(1) NOT NULL DEFAULT 0,
				created_at DATETIME NOT NULL,
				PRIMARY KEY  (id),
				KEY user_read (user_id, is_read)
			) {$charset_collate};"
		);

		update_option( self::DB_VERSION_OPTION, self::DB_VERSION );
	}

	/**
	 * Adds a notification for a user.
	 *
	 * @param int    $user_id User ID.
	 * @param string $type    Type slug (e.g. lesson_completed).
	 * @param string $title   Short title.
	 * @param string $message One-line message.
	 * @param string $link    Optional target URL.
	 * @return void
	 */
	public static function add( $user_id, $type, $title, $message, $link = '' ) {
		global $wpdb;

		$user_id = absint( $user_id );

		if ( ! $user_id ) {
			return;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- custom plugin table.
		$wpdb->insert(
			self::table(),
			array(
				'user_id'    => $user_id,
				'type'       => sanitize_key( $type ),
				'title'      => sanitize_text_field( $title ),
				'message'    => sanitize_text_field( $message ),
				'link'       => esc_url_raw( $link ),
				'is_read'    => 0,
				'created_at' => current_time( 'mysql' ),
			),
			array( '%d', '%s', '%s', '%s', '%s', '%d', '%s' )
		);

		self::prune( $user_id );
	}

	/**
	 * Deletes a user's oldest rows beyond MAX_PER_USER.
	 *
	 * @param int $user_id User ID.
	 * @return void
	 */
	private static function prune( $user_id ) {
		global $wpdb;

		$table = self::table();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- custom plugin table.
		$cutoff_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$table} WHERE user_id = %d ORDER BY id DESC LIMIT %d, 1", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table from self::table().
				$user_id,
				self::MAX_PER_USER - 1
			)
		);

		if ( $cutoff_id ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- custom plugin table.
			$wpdb->query(
				$wpdb->prepare(
					"DELETE FROM {$table} WHERE user_id = %d AND id < %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table from self::table().
					$user_id,
					$cutoff_id
				)
			);
		}
	}

	/**
	 * Returns a user's newest notifications.
	 *
	 * @param int $user_id User ID.
	 * @param int $limit   Max rows.
	 * @return object[]
	 */
	public static function get_for_user( $user_id, $limit = 15 ) {
		global $wpdb;

		$table = self::table();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- custom plugin table.
		return (array) $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE user_id = %d ORDER BY id DESC LIMIT %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table from self::table().
				absint( $user_id ),
				absint( $limit )
			)
		);
	}

	/**
	 * Returns a user's unread count.
	 *
	 * @param int $user_id User ID.
	 * @return int
	 */
	public static function unread_count( $user_id ) {
		global $wpdb;

		$table = self::table();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- custom plugin table.
		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE user_id = %d AND is_read = 0", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table from self::table().
				absint( $user_id )
			)
		);
	}

	/**
	 * Marks one notification as read (scoped to its owner).
	 *
	 * @param int $notification_id Row ID.
	 * @param int $user_id         Owner user ID.
	 * @return void
	 */
	public static function mark_read( $notification_id, $user_id ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- custom plugin table.
		$wpdb->update(
			self::table(),
			array( 'is_read' => 1 ),
			array(
				'id'      => absint( $notification_id ),
				'user_id' => absint( $user_id ),
			),
			array( '%d' ),
			array( '%d', '%d' )
		);
	}

	/**
	 * Marks all of a user's notifications as read.
	 *
	 * @param int $user_id User ID.
	 * @return void
	 */
	public static function mark_all_read( $user_id ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- custom plugin table.
		$wpdb->update(
			self::table(),
			array( 'is_read' => 1 ),
			array( 'user_id' => absint( $user_id ) ),
			array( '%d' ),
			array( '%d' )
		);
	}
}
