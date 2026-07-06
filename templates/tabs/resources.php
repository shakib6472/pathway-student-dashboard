<?php
/**
 * Resources tab.
 *
 * @var WP_User $user Current user.
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pathway_dash_resources = Pathway_Dashboard_Resources::get_resources_for_user( $user->ID );
?>
<header class="pathway-dash__page-header">
	<h1 class="pathway-dash__page-title"><?php esc_html_e( 'Resources', 'pathway-student-dashboard' ); ?></h1>
	<p class="pathway-dash__page-subtitle"><?php esc_html_e( 'Downloads and reference materials', 'pathway-student-dashboard' ); ?></p>
</header>

<?php if ( empty( $pathway_dash_resources ) ) : ?>

	<div class="pathway-dash__card pd-empty">
		<h2 class="pd-empty__title"><?php esc_html_e( 'No resources yet', 'pathway-student-dashboard' ); ?></h2>
		<p class="pd-empty__text"><?php esc_html_e( 'Study materials for your courses will appear here once they are published.', 'pathway-student-dashboard' ); ?></p>
	</div>

<?php else : ?>

	<div class="pd-resources">
		<?php foreach ( $pathway_dash_resources as $pathway_dash_resource ) : ?>
			<?php pathway_dash_template( 'tabs/resources/resource-card', array( 'resource' => $pathway_dash_resource ) ); ?>
		<?php endforeach; ?>
	</div>

<?php endif; ?>
