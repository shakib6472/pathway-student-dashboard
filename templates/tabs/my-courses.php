<?php
/**
 * My Courses tab. Full implementation lands in Phase 2.
 *
 * @var WP_User $user Current user.
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<header class="pathway-dash__page-header">
	<h1 class="pathway-dash__page-title">
		<?php
		/* translators: %s: user first name. */
		printf( esc_html__( 'Welcome back, %s', 'pathway-student-dashboard' ), esc_html( pathway_dash_get_user_first_name( $user ) ) );
		?>
	</h1>
	<p class="pathway-dash__page-subtitle"><?php esc_html_e( 'Continue your training', 'pathway-student-dashboard' ); ?></p>
</header>

<div class="pathway-dash__card pathway-dash__placeholder">
	<p><?php esc_html_e( 'Your courses will appear here soon.', 'pathway-student-dashboard' ); ?></p>
</div>
