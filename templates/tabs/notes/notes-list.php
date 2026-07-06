<?php
/**
 * Notes list column: search bar + notes grouped by lesson.
 *
 * Each note button carries its full data as JSON in data-note,
 * which the tab script uses to render the viewer column.
 *
 * @var array[] $groups Groups from Pathway_Dashboard_Notes::get_notes_grouped().
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="pd-notes__list-col">

	<div class="pd-notes__search">
		<input
			type="search"
			id="pathway-dash-notes-search"
			class="pd-notes__search-input"
			placeholder="<?php esc_attr_e( 'Search notes…', 'pathway-student-dashboard' ); ?>"
			aria-label="<?php esc_attr_e( 'Search notes', 'pathway-student-dashboard' ); ?>"
		/>
	</div>

	<div class="pd-notes__groups" id="pathway-dash-notes-groups">
		<?php foreach ( $groups as $pathway_dash_group ) : ?>
			<div class="pd-notes__group" data-lesson="<?php echo esc_attr( $pathway_dash_group['lesson_id'] ); ?>">
				<h3 class="pd-notes__group-title"><?php echo esc_html( $pathway_dash_group['lesson_title'] ); ?></h3>

				<?php foreach ( $pathway_dash_group['notes'] as $pathway_dash_note ) : ?>
					<button
						type="button"
						class="pd-notes__item"
						data-note="<?php echo esc_attr( wp_json_encode( $pathway_dash_note ) ); ?>"
						data-note-id="<?php echo esc_attr( $pathway_dash_note['id'] ); ?>"
					>
						<span class="pd-notes__item-title"><?php echo esc_html( $pathway_dash_note['title'] ); ?></span>
						<span class="pd-notes__item-excerpt"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $pathway_dash_note['content_raw'] ), 12 ) ); ?></span>
						<span class="pd-notes__item-date"><?php echo esc_html( $pathway_dash_note['date'] ); ?></span>
					</button>
				<?php endforeach; ?>
			</div>
		<?php endforeach; ?>

		<p class="pd-notes__no-results" id="pathway-dash-notes-no-results" hidden>
			<?php esc_html_e( 'No notes match your search.', 'pathway-student-dashboard' ); ?>
		</p>
	</div>

</div>
