<?php
/**
 * "Not a Pathway Student?" promo banner with decorative
 * sparkles, hand-drawn underline, arrow doodle, and tick marks.
 *
 * @var string $promo_url      Student photo URL.
 * @var string $learn_more_url Learn More button URL.
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// 4-point star sparkle, reused at various sizes.
$pathway_dash_sparkle = '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="currentColor" aria-hidden="true"><path d="M12 1 C13 8 16 11 23 12 C16 13 13 16 12 23 C11 16 8 13 1 12 C8 11 11 8 12 1 Z"/></svg>';
?>
<div class="pathway-login__promo">

	<div class="pathway-login__promo-photo">
		<span class="pathway-login__promo-circle" aria-hidden="true"></span>
		<span class="pathway-login__sparkle pathway-login__sparkle--photo-1" aria-hidden="true"><?php echo $pathway_dash_sparkle; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?></span>
		<span class="pathway-login__sparkle pathway-login__sparkle--photo-2" aria-hidden="true"><?php echo $pathway_dash_sparkle; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?></span>
		<img src="<?php echo esc_url( $promo_url ); ?>" alt="" loading="lazy" />
	</div>

	<div class="pathway-login__promo-body">
		<h2 class="pathway-login__promo-title"><?php esc_html_e( 'Not a Pathway Student?', 'pathway-student-dashboard' ); ?></h2>
		<p class="pathway-login__promo-sub">
			<?php esc_html_e( 'Just a click away from', 'pathway-student-dashboard' ); ?>
			<span class="pathway-login__underline">
				<?php esc_html_e( 'a career in dentistry', 'pathway-student-dashboard' ); ?>
				<svg class="pathway-login__underline-stroke" viewBox="0 0 200 14" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" aria-hidden="true"><path d="M4 9 C 45 13, 95 3, 135 7 S 185 11, 196 6" fill="none" stroke="currentColor" stroke-width="5" stroke-linecap="round"/></svg>
			</span>
		</p>
	</div>

	<span class="pathway-login__sparkle pathway-login__sparkle--mid" aria-hidden="true"><?php echo $pathway_dash_sparkle; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?></span>

	<svg class="pathway-login__arrow" viewBox="0 0 110 70" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M6 58 C 35 64, 75 48, 96 18" fill="none" stroke="currentColor" stroke-width="5" stroke-linecap="round"/><path d="M78 14 L 98 14 L 94 36" fill="none" stroke="currentColor" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/></svg>

	<div class="pathway-login__promo-action">
		<svg class="pathway-login__ticks" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M8 34 L18 14" fill="none" stroke="currentColor" stroke-width="5" stroke-linecap="round"/><path d="M24 36 L34 18" fill="none" stroke="currentColor" stroke-width="5" stroke-linecap="round"/></svg>

		<a class="pathway-login__learn-more" href="<?php echo esc_url( $learn_more_url ); ?>">
			<?php esc_html_e( 'Learn More', 'pathway-student-dashboard' ); ?>
			<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
		</a>

		<span class="pathway-login__dots" aria-hidden="true">
			<svg viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg" fill="currentColor"><circle cx="6" cy="10" r="3"/><circle cx="22" cy="4" r="2.4"/><circle cx="34" cy="16" r="3.4"/><circle cx="14" cy="28" r="2.2"/><circle cx="30" cy="34" r="2.8"/></svg>
		</span>
	</div>

</div>
