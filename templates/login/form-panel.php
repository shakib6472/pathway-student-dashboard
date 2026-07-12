<?php
/**
 * Login form panel (inside the hero).
 *
 * Posts to wp-login.php; failed attempts bounce back here via
 * Pathway_Dashboard_Login::redirect_back_on_failure().
 *
 * @var string $redirect      Post-login redirect URL.
 * @var string $status        '' | 'failed' | 'loggedout'.
 * @var bool   $is_logged_in  Whether a user is already logged in.
 * @var string $support_email Support email address.
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="pathway-login__panel">

	<?php if ( $is_logged_in ) : ?>

		<h2 class="pathway-login__panel-title"><?php esc_html_e( 'Welcome Back!', 'pathway-student-dashboard' ); ?></h2>
		<p class="pathway-login__panel-sub"><?php esc_html_e( 'You are already signed in.', 'pathway-student-dashboard' ); ?></p>
		<a class="pathway-login__submit" href="<?php echo esc_url( $redirect ); ?>">
			<?php esc_html_e( 'Go to Dashboard', 'pathway-student-dashboard' ); ?>
		</a>

	<?php else : ?>

		<h2 class="pathway-login__panel-title"><?php esc_html_e( 'Welcome Back!', 'pathway-student-dashboard' ); ?></h2>
		<p class="pathway-login__panel-sub"><?php esc_html_e( 'Sign in to continue to your learning dashboard.', 'pathway-student-dashboard' ); ?></p>

		<?php if ( 'failed' === $status ) : ?>
			<p class="pathway-login__notice pathway-login__notice--error">
				<?php esc_html_e( 'Incorrect email or password. Please try again.', 'pathway-student-dashboard' ); ?>
			</p>
		<?php elseif ( 'loggedout' === $status ) : ?>
			<p class="pathway-login__notice pathway-login__notice--info">
				<?php esc_html_e( 'You have been signed out. See you soon!', 'pathway-student-dashboard' ); ?>
			</p>
		<?php endif; ?>

		<form class="pathway-login__form" method="post" action="<?php echo esc_url( site_url( 'wp-login.php', 'login_post' ) ); ?>">

			<label class="pathway-login__field">
				<span class="screen-reader-text"><?php esc_html_e( 'Email Address', 'pathway-student-dashboard' ); ?></span>
				<span class="pathway-login__field-icon"><?php echo pathway_dash_icon( 'mail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?></span>
				<input type="text" name="log" placeholder="<?php esc_attr_e( 'Email Address', 'pathway-student-dashboard' ); ?>" autocomplete="username" required />
			</label>

			<label class="pathway-login__field">
				<span class="screen-reader-text"><?php esc_html_e( 'Password', 'pathway-student-dashboard' ); ?></span>
				<span class="pathway-login__field-icon"><?php echo pathway_dash_icon( 'lock' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?></span>
				<input type="password" name="pwd" id="pathway-login-password" placeholder="<?php esc_attr_e( 'Password', 'pathway-student-dashboard' ); ?>" autocomplete="current-password" required />
				<button type="button" class="pathway-login__eye" id="pathway-login-eye" aria-label="<?php esc_attr_e( 'Show password', 'pathway-student-dashboard' ); ?>">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
				</button>
			</label>

			<p class="pathway-login__forgot">
				<a href="<?php echo esc_url( wp_lostpassword_url( $redirect ) ); ?>"><?php esc_html_e( 'Forgot Password?', 'pathway-student-dashboard' ); ?></a>
			</p>

			<input type="hidden" name="redirect_to" value="<?php echo esc_url( $redirect ); ?>" />
			<input type="hidden" name="rememberme" value="forever" />

			<button type="submit" class="pathway-login__submit"><?php esc_html_e( 'Sign In', 'pathway-student-dashboard' ); ?></button>

		</form>

		<p class="pathway-login__assist">
			<?php esc_html_e( 'Need assistance?', 'pathway-student-dashboard' ); ?>
			<a href="mailto:<?php echo esc_attr( $support_email ); ?>"><?php esc_html_e( 'Contact Support', 'pathway-student-dashboard' ); ?></a>
		</p>

	<?php endif; ?>

</div>
