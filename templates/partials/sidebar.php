<?php
/**
 * Sidebar partial: desktop tab navigation.
 *
 * @var array[] $tabs        Tab registry.
 * @var string  $default_tab Initially active tab slug.
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<nav class="pathway-dash__sidebar" role="tablist" aria-label="<?php esc_attr_e( 'Dashboard sections', 'pathway-student-dashboard' ); ?>">
	<?php foreach ( $tabs as $slug => $tab ) : ?>
		<button
			type="button"
			class="pathway-dash__nav-item<?php echo $slug === $default_tab ? ' is-active' : ''; ?>"
			id="pathway-dash-tab-<?php echo esc_attr( $slug ); ?>"
			role="tab"
			data-pathway-tab="<?php echo esc_attr( $slug ); ?>"
			aria-controls="pathway-dash-panel-<?php echo esc_attr( $slug ); ?>"
			aria-selected="<?php echo $slug === $default_tab ? 'true' : 'false'; ?>"
		>
			<?php echo pathway_dash_icon( $tab['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?>
			<span><?php echo esc_html( $tab['label'] ); ?></span>
		</button>
	<?php endforeach; ?>

	<div class="pd-sidebar-help">
		<span class="pd-sidebar-help__icon"><?php echo pathway_dash_icon( 'help' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?></span>
		<p class="pd-sidebar-help__title"><?php esc_html_e( 'Need help?', 'pathway-student-dashboard' ); ?></p>
		<p class="pd-sidebar-help__text"><?php esc_html_e( 'Questions about your course? We are here for you.', 'pathway-student-dashboard' ); ?></p>
		<button type="button" class="pathway-dash__btn pathway-dash__btn--primary pd-sidebar-help__btn" data-pathway-tab="support">
			<?php esc_html_e( 'Contact Support', 'pathway-student-dashboard' ); ?>
		</button>
	</div>
</nav>
