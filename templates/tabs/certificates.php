<?php
/**
 * Certificates tab.
 *
 * @var WP_User $user Current user.
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pathway_dash_course_ids = Pathway_Dashboard_Courses::get_enrolled_course_ids( $user->ID );

$pathway_dash_courses = array();

foreach ( $pathway_dash_course_ids as $pathway_dash_course_id ) {
	$pathway_dash_courses[ $pathway_dash_course_id ] = Pathway_Dashboard_Courses::get_course_card( $pathway_dash_course_id, $user->ID );
}

$pathway_dash_certificates = Pathway_Dashboard_Certificates::get_certificates( $pathway_dash_courses, $user->ID );
?>
<header class="pathway-dash__page-header">
	<h1 class="pathway-dash__page-title"><?php esc_html_e( 'Certificates', 'pathway-student-dashboard' ); ?></h1>
	<p class="pathway-dash__page-subtitle"><?php esc_html_e( 'Your earned certificates', 'pathway-student-dashboard' ); ?></p>
</header>

<?php if ( empty( $pathway_dash_certificates ) ) : ?>

	<div class="pathway-dash__card pd-empty">
		<h2 class="pd-empty__title"><?php esc_html_e( 'No certificates available', 'pathway-student-dashboard' ); ?></h2>
		<p class="pd-empty__text"><?php esc_html_e( 'Certificates will appear here once you enroll in a course that offers one.', 'pathway-student-dashboard' ); ?></p>
	</div>

<?php else : ?>

	<div class="pd-certs">
		<?php foreach ( $pathway_dash_certificates as $pathway_dash_cert ) : ?>
			<?php pathway_dash_template( 'tabs/certificates/certificate-card', array( 'cert' => $pathway_dash_cert ) ); ?>
		<?php endforeach; ?>
	</div>

<?php endif; ?>
