<?php
/**
 * Dashboard shell template.
 *
 * Available variables (see Pathway_Dashboard_Shortcode::render()):
 *
 * @var WP_User $user        Current user.
 * @var string  $first_name  Greeting name.
 * @var string  $initials    Avatar initials.
 * @var array[] $tabs        Tab registry.
 * @var string  $default_tab Initially active tab slug.
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="pathway-dash" id="pathway-dash">

	<?php
	pathway_dash_template(
		'partials/topbar',
		array(
			'user'       => $user,
			'first_name' => $first_name,
			'initials'   => $initials,
			'tabs'       => $tabs,
		)
	);
	?>

	<div class="pathway-dash__body">

		<?php
		pathway_dash_template(
			'partials/sidebar',
			array(
				'tabs'        => $tabs,
				'default_tab' => $default_tab,
			)
		);
		?>

		<main class="pathway-dash__content" id="pathway-dash-content">
			<?php foreach ( $tabs as $slug => $tab ) : ?>
				<section
					class="pathway-dash__panel<?php echo $slug === $default_tab ? ' is-active' : ''; ?>"
					id="pathway-dash-panel-<?php echo esc_attr( $slug ); ?>"
					role="tabpanel"
					aria-labelledby="pathway-dash-tab-<?php echo esc_attr( $slug ); ?>"
					<?php echo $slug === $default_tab ? '' : 'hidden'; ?>
				>
					<?php pathway_dash_template( 'tabs/' . $slug, array( 'user' => $user ) ); ?>
				</section>
			<?php endforeach; ?>
		</main>

	</div>

	<?php
	pathway_dash_template(
		'partials/mobile-nav',
		array(
			'tabs'        => $tabs,
			'default_tab' => $default_tab,
		)
	);
	?>

</div>
