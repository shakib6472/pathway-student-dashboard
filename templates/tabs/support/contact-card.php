<?php
/**
 * Contact Support card.
 *
 * @var array $contact Details from Pathway_Dashboard_Support::get_contact().
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="pathway-dash__card pd-contact">

	<div class="pd-contact__icon">
		<?php echo pathway_dash_icon( 'mail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?>
	</div>

	<h2 class="pd-support__heading"><?php esc_html_e( 'Contact Support', 'pathway-student-dashboard' ); ?></h2>

	<p class="pd-contact__text"><?php echo esc_html( $contact['response_time'] ); ?></p>

	<a class="pd-contact__email" href="mailto:<?php echo esc_attr( $contact['email'] ); ?>">
		<?php echo esc_html( $contact['email'] ); ?>
	</a>

</div>
