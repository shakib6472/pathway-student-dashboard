<?php
/**
 * Account tab.
 *
 * Profile form (photo, name, email), password change, and
 * enrollment info.
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
	<h1 class="pathway-dash__page-title"><?php esc_html_e( 'Account', 'pathway-student-dashboard' ); ?></h1>
	<p class="pathway-dash__page-subtitle"><?php esc_html_e( 'Manage your profile and enrollment details', 'pathway-student-dashboard' ); ?></p>
</header>

<div class="pd-account">

	<div class="pd-account__main">
		<?php pathway_dash_template( 'tabs/account/profile-form', array( 'user' => $user ) ); ?>
		<?php pathway_dash_template( 'tabs/account/password-form', array() ); ?>
	</div>

	<div class="pd-account__side">
		<?php pathway_dash_template( 'tabs/account/enrollment-info', array( 'user' => $user ) ); ?>
	</div>

</div>
