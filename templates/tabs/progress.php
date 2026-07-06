<?php
/**
 * Progress Analytics tab. Full implementation lands in Phase 3.
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
	<h1 class="pathway-dash__page-title"><?php esc_html_e( 'Progress Analytics', 'pathway-student-dashboard' ); ?></h1>
	<p class="pathway-dash__page-subtitle"><?php esc_html_e( 'Track your learning journey', 'pathway-student-dashboard' ); ?></p>
</header>

<div class="pathway-dash__card pathway-dash__placeholder">
	<p><?php esc_html_e( 'Your progress analytics will appear here soon.', 'pathway-student-dashboard' ); ?></p>
</div>
