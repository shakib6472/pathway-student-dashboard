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
</nav>
