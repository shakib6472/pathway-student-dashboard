<?php
/**
 * Login prompt shown to logged-out visitors.
 *
 * @var string $login_url URL to the login page, redirecting back here.
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="pathway-dash pathway-dash--login">
	<div class="pathway-dash__login-card">
		<div class="pathway-dash__login-icon">
			<?php echo pathway_dash_icon( 'user' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?>
		</div>
		<h2 class="pathway-dash__login-title"><?php esc_html_e( 'Student Dashboard', 'pathway-student-dashboard' ); ?></h2>
		<p class="pathway-dash__login-text"><?php esc_html_e( 'Please log in to access your courses, progress, and certificates.', 'pathway-student-dashboard' ); ?></p>
		<a class="pathway-dash__btn pathway-dash__btn--primary" href="<?php echo esc_url( $login_url ); ?>">
			<?php esc_html_e( 'Log In', 'pathway-student-dashboard' ); ?>
		</a>
	</div>
</div>
