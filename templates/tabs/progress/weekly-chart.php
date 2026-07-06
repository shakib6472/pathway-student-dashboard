<?php
/**
 * Weekly activity chart card (rendered by Chart.js).
 *
 * @var array[] $weekly Days from Pathway_Dashboard_Analytics::get_weekly_activity().
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pathway_dash_chart_data = array(
	'labels' => wp_list_pluck( $weekly, 'label' ),
	'values' => wp_list_pluck( $weekly, 'minutes' ),
);
?>
<div class="pathway-dash__card pd-chart-card">
	<h2 class="pd-analytics__heading"><?php esc_html_e( 'Weekly Activity', 'pathway-student-dashboard' ); ?></h2>
	<p class="pd-chart-card__caption"><?php esc_html_e( 'Minutes spent learning, last 7 days', 'pathway-student-dashboard' ); ?></p>

	<div class="pd-chart-card__canvas">
		<canvas
			id="pathway-dash-weekly-chart"
			data-chart="<?php echo esc_attr( wp_json_encode( $pathway_dash_chart_data ) ); ?>"
		></canvas>
	</div>
</div>
