<?php
/**
 * Stats row: Overall Progress, Lessons Completed, Quiz Average, Hours Logged.
 *
 * @var array $stats Values from Pathway_Dashboard_Stats::get_stats().
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="pd-stats">

	<div class="pd-stats__card">
		<span class="pd-stats__label"><?php esc_html_e( 'Overall Progress', 'pathway-student-dashboard' ); ?></span>
		<span class="pd-stats__value"><?php echo esc_html( $stats['overall_progress'] ); ?>%</span>
	</div>

	<div class="pd-stats__card">
		<span class="pd-stats__label"><?php esc_html_e( 'Lessons Completed', 'pathway-student-dashboard' ); ?></span>
		<span class="pd-stats__value">
			<?php echo esc_html( $stats['completed_steps'] ); ?><small>/<?php echo esc_html( $stats['total_steps'] ); ?></small>
		</span>
	</div>

	<div class="pd-stats__card">
		<span class="pd-stats__label"><?php esc_html_e( 'Quiz Average', 'pathway-student-dashboard' ); ?></span>
		<span class="pd-stats__value">
			<?php if ( null === $stats['quiz_average'] ) : ?>
				<small><?php echo esc_html_x( '—', 'no quiz attempts yet', 'pathway-student-dashboard' ); ?></small>
			<?php else : ?>
				<?php echo esc_html( $stats['quiz_average'] ); ?>%
			<?php endif; ?>
		</span>
	</div>

	<div class="pd-stats__card">
		<span class="pd-stats__label"><?php esc_html_e( 'Hours Logged', 'pathway-student-dashboard' ); ?></span>
		<?php $pathway_dash_time = Pathway_Dashboard_Stats::format_logged_time( $stats['minutes_logged'] ); ?>
		<span class="pd-stats__value">
			<?php echo esc_html( $pathway_dash_time['value'] ); ?><?php if ( $pathway_dash_time['unit'] ) : ?> <small><?php echo esc_html( $pathway_dash_time['unit'] ); ?></small><?php endif; ?><?php if ( $pathway_dash_time['is_hours'] && $stats['hours_total'] > 0 ) : ?><small>/<?php echo esc_html( $stats['hours_total'] ); ?></small><?php endif; ?>
		</span>
	</div>

</div>
