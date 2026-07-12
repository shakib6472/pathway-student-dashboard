<?php
/**
 * Login screen: brand panel + login form hero, with the
 * "Not a Pathway Student?" promo banner below.
 *
 * @var string $redirect       Post-login redirect URL.
 * @var string $status         '' | 'failed' | 'loggedout'.
 * @var bool   $is_logged_in   Whether a user is already logged in.
 * @var string $support_email  Support email address.
 * @var string $bg_url         Hero background image URL.
 * @var string $promo_url      Promo banner image URL.
 * @var string $learn_more_url Learn More button URL.
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="pathway-login">

	<div class="pathway-login__top">

		<div class="pathway-login__brand">
			<div class="pathway-login__logo">
				<?php if ( has_custom_logo() ) : ?>
					<?php echo get_custom_logo(); ?>
				<?php else : ?>
					<span class="pathway-login__logo-text"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
				<?php endif; ?>
			</div>

			<h1 class="pathway-login__tagline">
				<span><?php esc_html_e( 'Your Path.', 'pathway-student-dashboard' ); ?></span>
				<span><?php esc_html_e( 'Our Purpose.', 'pathway-student-dashboard' ); ?></span>
				<span><?php esc_html_e( 'Your Future.', 'pathway-student-dashboard' ); ?></span>
			</h1>

			<span class="pathway-login__tagline-bar" aria-hidden="true"></span>
		</div>

		<?php
		// The photo is clipped by the union of a giant circle (its
		// left arc forms the curve, touching the left edge mid-height)
		// and a rect covering the right side with rounded corners.
		// objectBoundingBox coordinates (0-1) so the shape scales.
		?>
		<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
			<defs>
				<clipPath id="pathway-login-curve" clipPathUnits="objectBoundingBox">
					<circle cx="0.80" cy="0.45" r="0.80" />
					<rect x="0.25" y="0" width="0.67" height="1" rx="0.022" ry="0.03" />
				</clipPath>
			</defs>
		</svg>

		<?php
		// clip-path must be inline: external stylesheets resolve
		// url(#fragment) against the CSS file in Chrome, so the
		// reference silently fails from login.css. The wrapper's
		// ::before paints the ash shade hugging the curved edge.
		?>
		<div class="pathway-login__hero-wrap">
			<div
				class="pathway-login__hero"
				style="background-image: url('<?php echo esc_url( $bg_url ); ?>'); -webkit-clip-path: url(#pathway-login-curve); clip-path: url(#pathway-login-curve);"
			>
				<div class="pathway-login__hero-overlay"></div>

				<?php
				pathway_dash_template(
					'login/form-panel',
					array(
						'redirect'      => $redirect,
						'status'        => $status,
						'is_logged_in'  => $is_logged_in,
						'support_email' => $support_email,
					)
				);
				?>
			</div>
		</div>

	</div>

	<?php
	pathway_dash_template(
		'login/promo',
		array(
			'promo_url'      => $promo_url,
			'learn_more_url' => $learn_more_url,
		)
	);
	?>

</div>
