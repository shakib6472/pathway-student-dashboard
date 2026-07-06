<?php
/**
 * Module-by-module completion, grouped by course.
 *
 * @var array[] $courses Course cards keyed by course ID.
 * @var WP_User $user    Current user.
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php foreach ( $courses as $pathway_dash_course ) : ?>
	<?php
	$pathway_dash_modules = Pathway_Dashboard_Analytics::get_course_modules( $pathway_dash_course['id'], $user->ID );

	if ( empty( $pathway_dash_modules ) ) {
		continue;
	}
	?>
	<div class="pathway-dash__card pd-modules">
		<h2 class="pd-analytics__heading"><?php echo esc_html( $pathway_dash_course['title'] ); ?></h2>

		<ul class="pd-modules__list">
			<?php foreach ( $pathway_dash_modules as $pathway_dash_index => $pathway_dash_module ) : ?>
				<li class="pd-modules__item<?php echo $pathway_dash_module['completed'] ? ' is-completed' : ''; ?>">

					<span class="pd-modules__status">
						<?php if ( $pathway_dash_module['completed'] ) : ?>
							<?php echo pathway_dash_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?>
						<?php else : ?>
							<?php echo esc_html( $pathway_dash_index + 1 ); ?>
						<?php endif; ?>
					</span>

					<span class="pd-modules__title"><?php echo esc_html( $pathway_dash_module['title'] ); ?></span>

					<span class="pd-modules__progress">
						<span class="pd-progress" role="progressbar" aria-valuenow="<?php echo esc_attr( $pathway_dash_module['percentage'] ); ?>" aria-valuemin="0" aria-valuemax="100">
							<span class="pd-progress__bar" style="width: <?php echo esc_attr( $pathway_dash_module['percentage'] ); ?>%;"></span>
						</span>
						<span class="pd-modules__pct"><?php echo esc_html( $pathway_dash_module['percentage'] ); ?>%</span>
					</span>

				</li>
			<?php endforeach; ?>
		</ul>
	</div>
<?php endforeach; ?>
