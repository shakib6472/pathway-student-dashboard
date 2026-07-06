<?php
/**
 * Lesson Notes tab.
 *
 * Two-column layout: searchable notes list grouped by lesson on the
 * left, the selected note (view/edit/delete) on the right.
 *
 * @var WP_User $user Current user.
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pathway_dash_note_groups = Pathway_Dashboard_Notes::get_notes_grouped( $user->ID );
?>
<header class="pathway-dash__page-header">
	<h1 class="pathway-dash__page-title"><?php esc_html_e( 'Lesson Notes', 'pathway-student-dashboard' ); ?></h1>
	<p class="pathway-dash__page-subtitle"><?php esc_html_e( 'Your notes, organized by lesson', 'pathway-student-dashboard' ); ?></p>
</header>

<?php if ( empty( $pathway_dash_note_groups ) ) : ?>

	<div class="pathway-dash__card pd-empty">
		<h2 class="pd-empty__title"><?php esc_html_e( 'No notes yet', 'pathway-student-dashboard' ); ?></h2>
		<p class="pd-empty__text"><?php esc_html_e( 'Take notes while watching a lesson and they will show up here, organized by lesson.', 'pathway-student-dashboard' ); ?></p>
	</div>

<?php else : ?>

	<div class="pd-notes" id="pathway-dash-notes">
		<?php pathway_dash_template( 'tabs/notes/notes-list', array( 'groups' => $pathway_dash_note_groups ) ); ?>
		<?php pathway_dash_template( 'tabs/notes/note-viewer', array() ); ?>
	</div>

<?php endif; ?>
