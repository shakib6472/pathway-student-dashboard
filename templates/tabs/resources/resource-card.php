<?php
/**
 * Single resource card.
 *
 * @var array $resource Card data from Pathway_Dashboard_Resources.
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="pathway-dash__card pd-resource">

	<div class="pd-resource__icon pd-resource__icon--<?php echo esc_attr( $resource['type'] ); ?>">
		<?php echo pathway_dash_icon( $resource['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?>
	</div>

	<h2 class="pd-resource__title"><?php echo esc_html( $resource['title'] ); ?></h2>

	<?php if ( $resource['description'] ) : ?>
		<p class="pd-resource__desc"><?php echo esc_html( $resource['description'] ); ?></p>
	<?php endif; ?>

	<p class="pd-resource__meta">
		<span class="pd-resource__badge"><?php echo esc_html( $resource['ext'] ? $resource['ext'] : $resource['type_label'] ); ?></span>
		<?php if ( $resource['size'] ) : ?>
			<span><?php echo esc_html( $resource['size'] ); ?></span>
		<?php endif; ?>
	</p>

	<?php if ( $resource['action_url'] ) : ?>
		<a
			class="pathway-dash__btn pathway-dash__btn--outline pd-resource__btn"
			href="<?php echo esc_url( $resource['action_url'] ); ?>"
			<?php if ( $resource['is_external'] ) : ?>
				target="_blank" rel="noopener"
			<?php else : ?>
				download
			<?php endif; ?>
		>
			<?php echo pathway_dash_icon( $resource['is_external'] ? 'external' : 'download' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?>
			<?php echo esc_html( $resource['action_label'] ); ?>
		</a>
	<?php endif; ?>

</div>
