<?php
/**
 * Overall progress ring card.
 *
 * @var array $stats Values from Pathway_Dashboard_Stats::get_stats().
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pathway_dash_pct = (int) $stats['overall_progress'];

// SVG ring geometry: r=52 => circumference = 2 * pi * 52.
$pathway_dash_circumference = 326.73;
$pathway_dash_offset        = $pathway_dash_circumference * ( 1 - $pathway_dash_pct / 100 );
?>
<div class="pathway-dash__card pd-ring-card">
	<h2 class="pd-analytics__heading"><?php esc_html_e( 'Overall Progress', 'pathway-student-dashboard' ); ?></h2>

	<div class="pd-ring">
		<svg viewBox="0 0 120 120" role="img" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: percentage. */ __( '%d percent complete', 'pathway-student-dashboard' ), $pathway_dash_pct ) ); ?>">
			<defs>
				<linearGradient id="pathway-dash-ring-grad" x1="0" y1="0" x2="1" y2="1">
					<stop offset="0" stop-color="#84b4b4" />
					<stop offset="1" stop-color="#1aaeae" />
				</linearGradient>
			</defs>
			<circle class="pd-ring__track" cx="60" cy="60" r="52" />
			<circle
				class="pd-ring__value"
				cx="60"
				cy="60"
				r="52"
				style="stroke: url(#pathway-dash-ring-grad);"
				stroke-dasharray="<?php echo esc_attr( $pathway_dash_circumference ); ?>"
				stroke-dashoffset="<?php echo esc_attr( round( $pathway_dash_offset, 2 ) ); ?>"
			/>
		</svg>
		<div class="pd-ring__center">
			<span class="pd-ring__pct"><?php echo esc_html( $pathway_dash_pct ); ?>%</span>
			<span class="pd-ring__caption"><?php esc_html_e( 'complete', 'pathway-student-dashboard' ); ?></span>
		</div>
	</div>

	<p class="pd-ring-card__meta">
		<?php
		/* translators: 1: completed steps, 2: total steps. */
		printf( esc_html__( '%1$d of %2$d lessons completed', 'pathway-student-dashboard' ), (int) $stats['completed_steps'], (int) $stats['total_steps'] );
		?>
	</p>
</div>
