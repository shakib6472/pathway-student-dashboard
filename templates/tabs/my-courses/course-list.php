<?php
/**
 * Enrolled courses list.
 *
 * @var array[] $courses Course cards keyed by course ID.
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pathway_dash_status_labels = array(
	'not-started' => __( 'Not started', 'pathway-student-dashboard' ),
	'in-progress' => __( 'In progress', 'pathway-student-dashboard' ),
	'completed'   => __( 'Completed', 'pathway-student-dashboard' ),
);
?>
<section class="pd-course-list">
	<h2 class="pd-course-list__heading"><?php esc_html_e( 'Enrolled Courses', 'pathway-student-dashboard' ); ?></h2>

	<?php foreach ( $courses as $pathway_dash_course ) : ?>
		<?php
		/**
		 * Filters the badge text shown after the enrollment date
		 * (e.g. "State-approved"). Return an empty string to hide it.
		 *
		 * @param string $text      Badge text.
		 * @param int    $course_id Course ID.
		 */
		$pathway_dash_badge = apply_filters(
			'pathway_dash_course_badge_text',
			__( 'State-approved', 'pathway-student-dashboard' ),
			$pathway_dash_course['id']
		);

		$pathway_dash_status = $pathway_dash_status_labels[ $pathway_dash_course['status'] ];
		?>
		<div class="pathway-dash__card pd-course">

			<div class="pd-course__icon">
				<?php if ( $pathway_dash_course['thumbnail'] ) : ?>
					<img src="<?php echo esc_url( $pathway_dash_course['thumbnail'] ); ?>" alt="" loading="lazy" />
				<?php else : ?>
					<?php echo pathway_dash_icon( 'book' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?>
				<?php endif; ?>
			</div>

			<div class="pd-course__info">
				<h3 class="pd-course__title"><?php echo esc_html( $pathway_dash_course['title'] ); ?></h3>
				<p class="pd-course__meta">
					<?php if ( $pathway_dash_course['enrolled_ts'] ) : ?>
						<?php
						/* translators: %s: enrollment date. */
						printf( esc_html__( 'Enrolled %s', 'pathway-student-dashboard' ), esc_html( date_i18n( get_option( 'date_format' ), $pathway_dash_course['enrolled_ts'] ) ) );
						?>
						<?php if ( $pathway_dash_badge ) : ?>
							<span class="pd-course__meta-sep">·</span>
						<?php endif; ?>
					<?php endif; ?>
					<?php echo esc_html( $pathway_dash_badge ); ?>
				</p>
			</div>

			<div class="pd-course__progress">
				<div class="pd-progress" role="progressbar" aria-valuenow="<?php echo esc_attr( $pathway_dash_course['progress']['percentage'] ); ?>" aria-valuemin="0" aria-valuemax="100">
					<div class="pd-progress__bar" style="width: <?php echo esc_attr( $pathway_dash_course['progress']['percentage'] ); ?>%;"></div>
				</div>
				<span class="pd-course__progress-text">
					<?php echo esc_html( $pathway_dash_course['progress']['percentage'] ); ?>% · <?php echo esc_html( $pathway_dash_status ); ?>
				</span>
			</div>

			<div class="pd-course__action">
				<a class="pathway-dash__btn pathway-dash__btn--outline" href="<?php echo esc_url( $pathway_dash_course['url'] ); ?>">
					<?php esc_html_e( 'View Course', 'pathway-student-dashboard' ); ?>
				</a>
			</div>

		</div>
	<?php endforeach; ?>
</section>
