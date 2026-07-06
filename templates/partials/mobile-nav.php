<?php
/**
 * Mobile bottom navigation partial.
 *
 * Shows only the tabs flagged with in_mobile => true (5 items).
 * The remaining tabs are available from the avatar menu.
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

$pathway_dash_mobile_tabs = array_filter(
	$tabs,
	static function ( $tab ) {
		return ! empty( $tab['in_mobile'] );
	}
);
?>
<nav class="pathway-dash__mobile-nav" aria-label="<?php esc_attr_e( 'Dashboard sections', 'pathway-student-dashboard' ); ?>">
	<?php foreach ( $pathway_dash_mobile_tabs as $slug => $tab ) : ?>
		<button
			type="button"
			class="pathway-dash__mobile-nav-item<?php echo $slug === $default_tab ? ' is-active' : ''; ?>"
			data-pathway-tab="<?php echo esc_attr( $slug ); ?>"
			aria-label="<?php echo esc_attr( $tab['label'] ); ?>"
		>
			<?php echo pathway_dash_icon( $tab['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?>
			<span><?php echo esc_html( $tab['short'] ); ?></span>
		</button>
	<?php endforeach; ?>
</nav>
