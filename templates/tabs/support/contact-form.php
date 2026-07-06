<?php
/**
 * Message form card — renders the Fluent Forms shortcode,
 * restyled by assets/css/tabs/support.css.
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="pathway-dash__card pd-support-form">
	<h2 class="pd-support__heading"><?php esc_html_e( 'Send us a message', 'pathway-student-dashboard' ); ?></h2>

	<?php echo do_shortcode( Pathway_Dashboard_Support::get_form_shortcode() ); ?>
</div>
