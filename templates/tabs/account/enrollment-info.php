<?php
/**
 * Enrollment info card: state, enrolled date, student ID.
 *
 * @var WP_User $user Current user.
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pathway_dash_state       = Pathway_Dashboard_Account::get_state( $user->ID );
$pathway_dash_student_id  = Pathway_Dashboard_Account::get_student_id( $user );
$pathway_dash_enrolled_ts = Pathway_Dashboard_Account::get_enrolled_date( $user->ID );
?>
<div class="pathway-dash__card pd-account__card pd-enrollment">
	<h2 class="pd-account__heading"><?php esc_html_e( 'Enrollment Info', 'pathway-student-dashboard' ); ?></h2>

	<dl class="pd-enrollment__list">

		<div class="pd-enrollment__row">
			<dt><?php esc_html_e( 'State', 'pathway-student-dashboard' ); ?></dt>
			<dd><?php echo esc_html( $pathway_dash_state['name'] ); ?> <span class="pd-enrollment__code">(<?php echo esc_html( $pathway_dash_state['code'] ); ?>)</span></dd>
		</div>

		<div class="pd-enrollment__row">
			<dt><?php esc_html_e( 'Enrolled', 'pathway-student-dashboard' ); ?></dt>
			<dd>
				<?php if ( $pathway_dash_enrolled_ts ) : ?>
					<?php echo esc_html( wp_date( get_option( 'date_format' ), $pathway_dash_enrolled_ts ) ); ?>
				<?php else : ?>
					<?php esc_html_e( 'Not enrolled yet', 'pathway-student-dashboard' ); ?>
				<?php endif; ?>
			</dd>
		</div>

		<div class="pd-enrollment__row">
			<dt><?php esc_html_e( 'Student ID', 'pathway-student-dashboard' ); ?></dt>
			<dd><code class="pd-enrollment__id"><?php echo esc_html( $pathway_dash_student_id ); ?></code></dd>
		</div>

	</dl>

	<p class="pd-account__hint"><?php esc_html_e( 'Need to update your enrollment details? Contact support from the Support tab.', 'pathway-student-dashboard' ); ?></p>
</div>
