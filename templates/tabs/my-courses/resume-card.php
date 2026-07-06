<?php
/**
 * "Continue Learning" resume card.
 *
 * @var array $course Course card data from Pathway_Dashboard_Courses::get_course_card().
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pathway_dash_progress = $course['progress'];
?>
<div class="pathway-dash__card pd-resume">

	<div class="pd-resume__thumb">
		<?php if ( $course['thumbnail'] ) : ?>
			<img src="<?php echo esc_url( $course['thumbnail'] ); ?>" alt="<?php echo esc_attr( $course['title'] ); ?>" loading="lazy" />
		<?php else : ?>
			<span class="pd-resume__thumb-fallback"><?php echo pathway_dash_icon( 'book' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?></span>
		<?php endif; ?>
	</div>

	<div class="pd-resume__body">
		<span class="pd-resume__eyebrow"><?php esc_html_e( 'Continue Learning', 'pathway-student-dashboard' ); ?></span>
		<h2 class="pd-resume__title"><?php echo esc_html( $course['title'] ); ?></h2>

		<?php if ( $course['resume']['label'] ) : ?>
			<p class="pd-resume__step"><?php echo esc_html( $course['resume']['label'] ); ?></p>
		<?php endif; ?>

		<div class="pd-progress" role="progressbar" aria-valuenow="<?php echo esc_attr( $pathway_dash_progress['percentage'] ); ?>" aria-valuemin="0" aria-valuemax="100">
			<div class="pd-progress__bar" style="width: <?php echo esc_attr( $pathway_dash_progress['percentage'] ); ?>%;"></div>
		</div>

		<div class="pd-resume__meta">
			<span>
				<?php
				/* translators: %d: completion percentage. */
				printf( esc_html__( '%d%% complete', 'pathway-student-dashboard' ), (int) $pathway_dash_progress['percentage'] );
				?>
			</span>
			<span>
				<?php
				/* translators: 1: completed lessons, 2: total lessons. */
				printf( esc_html__( '%1$d of %2$d lessons', 'pathway-student-dashboard' ), (int) $pathway_dash_progress['completed'], (int) $pathway_dash_progress['total'] );
				?>
			</span>
		</div>
	</div>

	<div class="pd-resume__action">
		<a class="pathway-dash__btn pathway-dash__btn--primary" href="<?php echo esc_url( $course['resume']['url'] ); ?>">
			<?php esc_html_e( 'Resume Course', 'pathway-student-dashboard' ); ?>
		</a>
	</div>

</div>
