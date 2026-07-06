<?php
/**
 * Progress Analytics tab.
 *
 * Overall progress ring, weekly activity chart, per-module
 * completion, and quiz scores.
 *
 * @var WP_User $user Current user.
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pathway_dash_course_ids = Pathway_Dashboard_Courses::get_enrolled_course_ids( $user->ID );

$pathway_dash_courses = array();

foreach ( $pathway_dash_course_ids as $pathway_dash_course_id ) {
	$pathway_dash_courses[ $pathway_dash_course_id ] = Pathway_Dashboard_Courses::get_course_card( $pathway_dash_course_id, $user->ID );
}

$pathway_dash_stats  = Pathway_Dashboard_Stats::get_stats( $pathway_dash_courses, $user->ID );
$pathway_dash_weekly = Pathway_Dashboard_Analytics::get_weekly_activity( $user->ID );
$pathway_dash_quizzes = Pathway_Dashboard_Analytics::get_quiz_history( $user->ID );
?>
<header class="pathway-dash__page-header">
	<h1 class="pathway-dash__page-title"><?php esc_html_e( 'Progress Analytics', 'pathway-student-dashboard' ); ?></h1>
	<p class="pathway-dash__page-subtitle"><?php esc_html_e( 'Track your learning journey', 'pathway-student-dashboard' ); ?></p>
</header>

<?php if ( empty( $pathway_dash_courses ) ) : ?>

	<div class="pathway-dash__card pd-empty">
		<h2 class="pd-empty__title"><?php esc_html_e( 'No data yet', 'pathway-student-dashboard' ); ?></h2>
		<p class="pd-empty__text"><?php esc_html_e( 'Your analytics will appear here once you start a course.', 'pathway-student-dashboard' ); ?></p>
	</div>

<?php else : ?>

	<div class="pd-analytics__row">
		<?php pathway_dash_template( 'tabs/progress/overview', array( 'stats' => $pathway_dash_stats ) ); ?>
		<?php pathway_dash_template( 'tabs/progress/weekly-chart', array( 'weekly' => $pathway_dash_weekly ) ); ?>
	</div>

	<?php
	pathway_dash_template(
		'tabs/progress/modules',
		array(
			'courses' => $pathway_dash_courses,
			'user'    => $user,
		)
	);
	?>

	<?php pathway_dash_template( 'tabs/progress/quiz-scores', array( 'quizzes' => $pathway_dash_quizzes ) ); ?>

<?php endif; ?>
