<?php
/**
 * Single certificate card (locked or earned).
 *
 * @var array $cert Card data from Pathway_Dashboard_Certificates::get_certificates().
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="pathway-dash__card pd-cert<?php echo $cert['earned'] ? ' is-earned' : ' is-locked'; ?>">

	<div class="pd-cert__preview">
		<?php if ( $cert['thumbnail'] ) : ?>
			<img src="<?php echo esc_url( $cert['thumbnail'] ); ?>" alt="" loading="lazy" />
		<?php endif; ?>

		<?php if ( $cert['earned'] ) : ?>
			<span class="pd-cert__medal">
				<?php echo pathway_dash_icon( 'award' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?>
			</span>
		<?php else : ?>
			<span class="pd-cert__lock">
				<?php echo pathway_dash_icon( 'lock' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?>
			</span>
		<?php endif; ?>
	</div>

	<div class="pd-cert__body">
		<h2 class="pd-cert__title"><?php echo esc_html( $cert['title'] ); ?></h2>

		<?php if ( $cert['earned'] ) : ?>

			<p class="pd-cert__meta">
				<?php if ( $cert['completed_ts'] ) : ?>
					<?php
					/* translators: %s: completion date. */
					printf( esc_html__( 'Completed %s', 'pathway-student-dashboard' ), esc_html( wp_date( get_option( 'date_format' ), $cert['completed_ts'] ) ) );
					?>
				<?php else : ?>
					<?php esc_html_e( 'Course completed', 'pathway-student-dashboard' ); ?>
				<?php endif; ?>
			</p>

			<a class="pathway-dash__btn pathway-dash__btn--primary pd-cert__btn" href="<?php echo esc_url( $cert['url'] ); ?>" target="_blank" rel="noopener">
				<?php echo pathway_dash_icon( 'download' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?>
				<?php esc_html_e( 'Download PDF', 'pathway-student-dashboard' ); ?>
			</a>

		<?php else : ?>

			<p class="pd-cert__meta"><?php esc_html_e( 'Complete the course to unlock your certificate.', 'pathway-student-dashboard' ); ?></p>

			<div class="pd-progress" role="progressbar" aria-valuenow="<?php echo esc_attr( $cert['percentage'] ); ?>" aria-valuemin="0" aria-valuemax="100">
				<div class="pd-progress__bar" style="width: <?php echo esc_attr( $cert['percentage'] ); ?>%;"></div>
			</div>

			<span class="pd-cert__pct">
				<?php
				/* translators: %d: completion percentage. */
				printf( esc_html__( '%d%% complete', 'pathway-student-dashboard' ), (int) $cert['percentage'] );
				?>
			</span>

		<?php endif; ?>
	</div>

</div>
