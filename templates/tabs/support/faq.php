<?php
/**
 * FAQ accordion (native <details>/<summary>, no JS needed).
 *
 * @var array[] $faqs Items from Pathway_Dashboard_Support::get_faqs().
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="pathway-dash__card pd-faq">
	<h2 class="pd-support__heading"><?php esc_html_e( 'Frequently Asked Questions', 'pathway-student-dashboard' ); ?></h2>

	<?php foreach ( $faqs as $pathway_dash_index => $pathway_dash_faq ) : ?>
		<details class="pd-faq__item" <?php echo 0 === $pathway_dash_index ? 'open' : ''; ?>>
			<summary class="pd-faq__question">
				<span><?php echo esc_html( $pathway_dash_faq['question'] ); ?></span>
				<?php echo pathway_dash_icon( 'chevron' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?>
			</summary>
			<div class="pd-faq__answer">
				<?php echo esc_html( $pathway_dash_faq['answer'] ); ?>
			</div>
		</details>
	<?php endforeach; ?>
</div>
