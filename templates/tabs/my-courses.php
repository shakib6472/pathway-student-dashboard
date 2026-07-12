<?php
/**
 * My Courses tab.
 *
 * Welcome header, Continue Learning card, stats row, and the
 * enrolled courses list.
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

$pathway_dash_continue = Pathway_Dashboard_Courses::pick_continue_learning( $pathway_dash_courses, $user->ID );
$pathway_dash_stats    = Pathway_Dashboard_Stats::get_stats( $pathway_dash_courses, $user->ID );

if ( $pathway_dash_continue ) {
	/* translators: %s: course title. */
	$pathway_dash_subtitle = sprintf( __( 'Continue your %s', 'pathway-student-dashboard' ), $pathway_dash_continue['title'] );
} else {
	$pathway_dash_subtitle = __( 'Your training at a glance', 'pathway-student-dashboard' );
}
?>
<?php
// First name wrapped with a hand-drawn yellow underline + sparkle.
$pathway_dash_name_html = '<span class="pd-name-underline">'
	. esc_html( pathway_dash_get_user_first_name( $user ) )
	. '<svg class="pd-name-underline__stroke" viewBox="0 0 200 14" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" aria-hidden="true"><path d="M4 9 C 45 13, 95 3, 135 7 S 185 11, 196 6" fill="none" stroke="currentColor" stroke-width="5" stroke-linecap="round"/></svg>'
	. '</span>'
	. '<span class="pd-welcome-sparkle">' . pathway_dash_icon( 'sparkle' ) . '</span>';

$pathway_dash_kses = array(
	'span' => array( 'class' => true ),
	'svg'  => array(
		'class'               => true,
		'viewbox'             => true,
		'xmlns'               => true,
		'preserveaspectratio' => true,
		'aria-hidden'         => true,
		'focusable'           => true,
		'fill'                => true,
	),
	'path' => array(
		'd'              => true,
		'fill'           => true,
		'stroke'         => true,
		'stroke-width'   => true,
		'stroke-linecap' => true,
	),
);
?>
<header class="pathway-dash__page-header">
	<h1 class="pathway-dash__page-title">
		<?php
		/* translators: %s: user first name. */
		echo wp_kses( sprintf( __( 'Welcome back, %s', 'pathway-student-dashboard' ), $pathway_dash_name_html ), $pathway_dash_kses );
		?>
	</h1>
	<p class="pathway-dash__page-subtitle"><?php echo esc_html( $pathway_dash_subtitle ); ?></p>

	<?php $pathway_dash_streak = Pathway_Dashboard_Analytics::get_streak_days( $user->ID ); ?>
	<?php if ( $pathway_dash_streak >= 2 ) : ?>
		<span class="pd-streak">
			<?php echo pathway_dash_icon( 'flame' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?>
			<?php
			/* translators: %d: consecutive learning days. */
			printf( esc_html__( '%d-day learning streak!', 'pathway-student-dashboard' ), (int) $pathway_dash_streak );
			?>
		</span>
	<?php endif; ?>
</header>

<?php if ( empty( $pathway_dash_courses ) ) : ?>

	<div class="pathway-dash__card pd-empty">
		<h2 class="pd-empty__title"><?php esc_html_e( 'No courses yet', 'pathway-student-dashboard' ); ?></h2>
		<p class="pd-empty__text"><?php esc_html_e( 'Once you enroll in a course, it will show up here with your progress.', 'pathway-student-dashboard' ); ?></p>
		<?php
		/**
		 * Filters the URL of the "Browse Courses" button on the empty state.
		 *
		 * @param string $url Course archive URL by default.
		 */
		$pathway_dash_browse_url = apply_filters( 'pathway_dash_browse_courses_url', get_post_type_archive_link( 'sfwd-courses' ) );
		?>
		<?php if ( $pathway_dash_browse_url ) : ?>
			<a class="pathway-dash__btn pathway-dash__btn--primary" href="<?php echo esc_url( $pathway_dash_browse_url ); ?>">
				<?php esc_html_e( 'Browse Courses', 'pathway-student-dashboard' ); ?>
			</a>
		<?php endif; ?>
	</div>

<?php else : ?>

	<?php if ( $pathway_dash_continue ) : ?>
		<?php pathway_dash_template( 'tabs/my-courses/resume-card', array( 'course' => $pathway_dash_continue ) ); ?>
	<?php endif; ?>

	<?php pathway_dash_template( 'tabs/my-courses/stats-row', array( 'stats' => $pathway_dash_stats ) ); ?>

	<?php pathway_dash_template( 'tabs/my-courses/course-list', array( 'courses' => $pathway_dash_courses ) ); ?>

<?php endif; ?>
