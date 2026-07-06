<?php
/**
 * Support tab.
 *
 * FAQ accordion on the left; contact card and message form on
 * the right.
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
	<h1 class="pathway-dash__page-title"><?php esc_html_e( 'Support', 'pathway-student-dashboard' ); ?></h1>
	<p class="pathway-dash__page-subtitle"><?php esc_html_e( 'FAQs and help when you need it', 'pathway-student-dashboard' ); ?></p>
</header>

<div class="pd-support">

	<div class="pd-support__main">
		<?php pathway_dash_template( 'tabs/support/faq', array( 'faqs' => Pathway_Dashboard_Support::get_faqs() ) ); ?>
	</div>

	<div class="pd-support__side">
		<?php pathway_dash_template( 'tabs/support/contact-card', array( 'contact' => Pathway_Dashboard_Support::get_contact() ) ); ?>
		<?php pathway_dash_template( 'tabs/support/contact-form', array() ); ?>
	</div>

</div>
