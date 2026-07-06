<?php
/**
 * Topbar partial: logo, greeting, notification bell, avatar menu.
 *
 * @var WP_User $user       Current user.
 * @var string  $first_name Greeting name.
 * @var string  $initials   Avatar initials.
 * @var array[] $tabs       Tab registry (for the avatar menu links).
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Tabs that are not in the mobile bottom nav are exposed in the avatar menu.
$pathway_dash_menu_tabs = array_filter(
	$tabs,
	static function ( $tab ) {
		return empty( $tab['in_mobile'] );
	}
);
?>
<header class="pathway-dash__topbar">

	<div class="pathway-dash__logo">
		<?php if ( has_custom_logo() ) : ?>
			<?php echo get_custom_logo(); ?>
		<?php else : ?>
			<span class="pathway-dash__logo-text"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
		<?php endif; ?>
	</div>

	<div class="pathway-dash__topbar-actions">

		<span class="pathway-dash__greeting">
			<?php esc_html_e( 'Hi,', 'pathway-student-dashboard' ); ?>
			<span id="pathway-dash-greeting-name"><?php echo esc_html( $first_name ); ?></span>
		</span>

		<button
			type="button"
			class="pathway-dash__bell"
			id="pathway-dash-bell"
			aria-label="<?php esc_attr_e( 'Notifications', 'pathway-student-dashboard' ); ?>"
		>
			<?php echo pathway_dash_icon( 'bell' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?>
			<span class="pathway-dash__bell-badge" hidden></span>
		</button>

		<div class="pathway-dash__avatar-wrap">
			<?php $pathway_dash_photo = Pathway_Dashboard_Account::get_photo_url( $user->ID ); ?>
			<button
				type="button"
				class="pathway-dash__avatar"
				id="pathway-dash-avatar"
				data-initials="<?php echo esc_attr( $initials ); ?>"
				aria-haspopup="true"
				aria-expanded="false"
				aria-label="<?php esc_attr_e( 'Account menu', 'pathway-student-dashboard' ); ?>"
			>
				<?php if ( $pathway_dash_photo ) : ?>
					<img src="<?php echo esc_url( $pathway_dash_photo ); ?>" alt="" />
				<?php else : ?>
					<?php echo esc_html( $initials ); ?>
				<?php endif; ?>
			</button>

			<div class="pathway-dash__avatar-menu" id="pathway-dash-avatar-menu" hidden>
				<div class="pathway-dash__avatar-menu-header">
					<span class="pathway-dash__avatar-menu-name"><?php echo esc_html( $user->display_name ); ?></span>
					<span class="pathway-dash__avatar-menu-email"><?php echo esc_html( $user->user_email ); ?></span>
				</div>

				<?php foreach ( $pathway_dash_menu_tabs as $slug => $tab ) : ?>
					<a class="pathway-dash__avatar-menu-item" href="#<?php echo esc_attr( $slug ); ?>" data-pathway-tab="<?php echo esc_attr( $slug ); ?>">
						<?php echo pathway_dash_icon( $tab['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?>
						<span><?php echo esc_html( $tab['label'] ); ?></span>
					</a>
				<?php endforeach; ?>

				<a class="pathway-dash__avatar-menu-item pathway-dash__avatar-menu-item--logout" href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>">
					<?php echo pathway_dash_icon( 'logout' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?>
					<span><?php esc_html_e( 'Log Out', 'pathway-student-dashboard' ); ?></span>
				</a>
			</div>
		</div>

	</div>

</header>
