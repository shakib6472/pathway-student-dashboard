<?php
/**
 * Notification bell + dropdown panel.
 *
 * Renders the newest notifications server-side; the JS in
 * assets/js/notifications.js handles opening, mark-as-read,
 * and badge updates.
 *
 * @var WP_User $user Current user.
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pathway_dash_items  = Pathway_Dashboard_Notifications_DB::get_for_user( $user->ID );
$pathway_dash_unread = Pathway_Dashboard_Notifications_DB::unread_count( $user->ID );

$pathway_dash_type_icons = array(
	'lesson_completed' => 'check',
	'course_completed' => 'award',
	'course_enrolled'  => 'book',
	'quiz_passed'      => 'award',
	'quiz_failed'      => 'pen',
	'resource_added'   => 'folder',
);
?>
<div class="pathway-dash__bell-wrap">

	<button
		type="button"
		class="pathway-dash__bell"
		id="pathway-dash-bell"
		aria-haspopup="true"
		aria-expanded="false"
		aria-label="<?php esc_attr_e( 'Notifications', 'pathway-student-dashboard' ); ?>"
	>
		<?php echo pathway_dash_icon( 'bell' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?>
		<span class="pathway-dash__bell-badge" id="pathway-dash-bell-badge" <?php echo $pathway_dash_unread ? '' : 'hidden'; ?>>
			<?php echo esc_html( $pathway_dash_unread > 9 ? '9+' : $pathway_dash_unread ); ?>
		</span>
	</button>

	<div class="pathway-dash__bell-panel" id="pathway-dash-bell-panel" hidden>

		<div class="pathway-dash__bell-panel-head">
			<span class="pathway-dash__bell-panel-title"><?php esc_html_e( 'Notifications', 'pathway-student-dashboard' ); ?></span>
			<button type="button" class="pathway-dash__bell-read-all" id="pathway-dash-bell-read-all" <?php echo $pathway_dash_unread ? '' : 'hidden'; ?>>
				<?php esc_html_e( 'Mark all as read', 'pathway-student-dashboard' ); ?>
			</button>
		</div>

		<div class="pathway-dash__bell-list">
			<?php if ( empty( $pathway_dash_items ) ) : ?>

				<p class="pathway-dash__bell-empty"><?php esc_html_e( 'No notifications yet. Your progress updates will show up here.', 'pathway-student-dashboard' ); ?></p>

			<?php else : ?>

				<?php foreach ( $pathway_dash_items as $pathway_dash_item ) : ?>
					<?php
					$pathway_dash_icon_key = isset( $pathway_dash_type_icons[ $pathway_dash_item->type ] ) ? $pathway_dash_type_icons[ $pathway_dash_item->type ] : 'bell';
					$pathway_dash_tag      = $pathway_dash_item->link ? 'a' : 'div';
					?>
					<<?php echo $pathway_dash_tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- controlled tag name. ?>
						class="pathway-dash__bell-item<?php echo $pathway_dash_item->is_read ? '' : ' is-unread'; ?>"
						data-notification-id="<?php echo esc_attr( $pathway_dash_item->id ); ?>"
						<?php if ( $pathway_dash_item->link ) : ?>
							href="<?php echo esc_url( $pathway_dash_item->link ); ?>"
						<?php endif; ?>
					>
						<span class="pathway-dash__bell-item-icon">
							<?php echo pathway_dash_icon( $pathway_dash_icon_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?>
						</span>
						<span class="pathway-dash__bell-item-body">
							<span class="pathway-dash__bell-item-title"><?php echo esc_html( $pathway_dash_item->title ); ?></span>
							<span class="pathway-dash__bell-item-message"><?php echo esc_html( $pathway_dash_item->message ); ?></span>
							<span class="pathway-dash__bell-item-time">
								<?php
								/* translators: %s: human-readable time difference. */
								printf( esc_html__( '%s ago', 'pathway-student-dashboard' ), esc_html( human_time_diff( strtotime( $pathway_dash_item->created_at ), current_time( 'timestamp' ) ) ) );
								?>
							</span>
						</span>
						<span class="pathway-dash__bell-item-dot" <?php echo $pathway_dash_item->is_read ? 'hidden' : ''; ?>></span>
					</<?php echo $pathway_dash_tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- controlled tag name. ?>>
				<?php endforeach; ?>

			<?php endif; ?>
		</div>

	</div>

</div>
