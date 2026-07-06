<?php
/**
 * Note viewer column: shows the selected note with edit and
 * delete controls. Populated by assets/js/tabs/notes.js.
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="pathway-dash__card pd-notes__viewer" id="pathway-dash-note-viewer">

	<div class="pd-notes__viewer-placeholder" id="pathway-dash-note-placeholder">
		<?php echo pathway_dash_icon( 'pen' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?>
		<p><?php esc_html_e( 'Select a note to read it', 'pathway-student-dashboard' ); ?></p>
	</div>

	<div class="pd-notes__viewer-content" id="pathway-dash-note-content" hidden>

		<div class="pd-notes__viewer-head">
			<div class="pd-notes__viewer-meta">
				<h2 class="pd-notes__viewer-title" id="pathway-dash-note-title"></h2>
				<p class="pd-notes__viewer-sub">
					<span id="pathway-dash-note-lesson"></span>
					·
					<span id="pathway-dash-note-date"></span>
				</p>
			</div>
			<div class="pd-notes__viewer-actions">
				<button type="button" class="pathway-dash__btn pathway-dash__btn--outline" id="pathway-dash-note-edit">
					<?php esc_html_e( 'Edit', 'pathway-student-dashboard' ); ?>
				</button>
				<button type="button" class="pathway-dash__btn pathway-dash__btn--outline pd-notes__delete" id="pathway-dash-note-delete">
					<?php esc_html_e( 'Delete', 'pathway-student-dashboard' ); ?>
				</button>
			</div>
		</div>

		<div class="pd-notes__viewer-body" id="pathway-dash-note-body"></div>

		<div class="pd-notes__admin-response" id="pathway-dash-note-response" hidden>
			<h3 class="pd-notes__admin-response-title"><?php esc_html_e( 'Instructor response', 'pathway-student-dashboard' ); ?></h3>
			<div id="pathway-dash-note-response-body"></div>
		</div>

		<form class="pd-notes__edit-form" id="pathway-dash-note-form" hidden>
			<label class="pd-notes__field">
				<span class="pd-notes__field-label"><?php esc_html_e( 'Title', 'pathway-student-dashboard' ); ?></span>
				<input type="text" id="pathway-dash-note-form-title" class="pd-notes__input" />
			</label>
			<label class="pd-notes__field">
				<span class="pd-notes__field-label"><?php esc_html_e( 'Note', 'pathway-student-dashboard' ); ?></span>
				<textarea id="pathway-dash-note-form-content" class="pd-notes__textarea" rows="9"></textarea>
			</label>
			<p class="pd-notes__form-error" id="pathway-dash-note-form-error" hidden></p>
			<div class="pd-notes__form-actions">
				<button type="submit" class="pathway-dash__btn pathway-dash__btn--primary" id="pathway-dash-note-save">
					<?php esc_html_e( 'Save Changes', 'pathway-student-dashboard' ); ?>
				</button>
				<button type="button" class="pathway-dash__btn pathway-dash__btn--outline" id="pathway-dash-note-cancel">
					<?php esc_html_e( 'Cancel', 'pathway-student-dashboard' ); ?>
				</button>
			</div>
		</form>

	</div>

</div>
