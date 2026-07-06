<?php
/**
 * Quiz scores list.
 *
 * @var array[] $quizzes Attempts from Pathway_Dashboard_Analytics::get_quiz_history().
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="pathway-dash__card pd-quiz-scores">
	<h2 class="pd-analytics__heading"><?php esc_html_e( 'Quiz Scores', 'pathway-student-dashboard' ); ?></h2>

	<?php if ( empty( $quizzes ) ) : ?>

		<p class="pd-quiz-scores__empty"><?php esc_html_e( 'No quiz attempts yet. Your scores will appear here after your first quiz.', 'pathway-student-dashboard' ); ?></p>

	<?php else : ?>

		<ul class="pd-quiz-scores__list">
			<?php foreach ( $quizzes as $pathway_dash_quiz ) : ?>
				<li class="pd-quiz-scores__item">

					<div class="pd-quiz-scores__info">
						<span class="pd-quiz-scores__title"><?php echo esc_html( $pathway_dash_quiz['title'] ); ?></span>
						<span class="pd-quiz-scores__meta">
							<?php if ( $pathway_dash_quiz['course_title'] ) : ?>
								<?php echo esc_html( $pathway_dash_quiz['course_title'] ); ?>
								<?php if ( $pathway_dash_quiz['time'] ) : ?>
									·
								<?php endif; ?>
							<?php endif; ?>
							<?php if ( $pathway_dash_quiz['time'] ) : ?>
								<?php echo esc_html( wp_date( get_option( 'date_format' ), $pathway_dash_quiz['time'] ) ); ?>
							<?php endif; ?>
						</span>
					</div>

					<span class="pd-quiz-scores__badge<?php echo $pathway_dash_quiz['passed'] ? ' is-passed' : ' is-failed'; ?>">
						<?php echo esc_html( $pathway_dash_quiz['percentage'] ); ?>%
						· <?php echo $pathway_dash_quiz['passed'] ? esc_html__( 'Passed', 'pathway-student-dashboard' ) : esc_html__( 'Failed', 'pathway-student-dashboard' ); ?>
					</span>

				</li>
			<?php endforeach; ?>
		</ul>

	<?php endif; ?>
</div>
