/**
 * Lesson Notes tab — selection, live search, and AJAX edit/delete.
 *
 * Note data is embedded as JSON on each list item (data-note); the
 * viewer column is rendered client-side from that data. Saves and
 * deletes go through admin-ajax with a nonce.
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var wrap = document.getElementById( 'pathway-dash-notes' );

		if ( ! wrap ) {
			return;
		}

		var settings = window.pathwayDash || {};

		var searchInput = document.getElementById( 'pathway-dash-notes-search' );
		var groupsWrap = document.getElementById( 'pathway-dash-notes-groups' );
		var noResults = document.getElementById( 'pathway-dash-notes-no-results' );

		var placeholder = document.getElementById( 'pathway-dash-note-placeholder' );
		var content = document.getElementById( 'pathway-dash-note-content' );
		var titleEl = document.getElementById( 'pathway-dash-note-title' );
		var lessonEl = document.getElementById( 'pathway-dash-note-lesson' );
		var dateEl = document.getElementById( 'pathway-dash-note-date' );
		var bodyEl = document.getElementById( 'pathway-dash-note-body' );
		var responseWrap = document.getElementById( 'pathway-dash-note-response' );
		var responseBody = document.getElementById( 'pathway-dash-note-response-body' );

		var form = document.getElementById( 'pathway-dash-note-form' );
		var formTitle = document.getElementById( 'pathway-dash-note-form-title' );
		var formContent = document.getElementById( 'pathway-dash-note-form-content' );
		var formError = document.getElementById( 'pathway-dash-note-form-error' );
		var saveBtn = document.getElementById( 'pathway-dash-note-save' );
		var editBtn = document.getElementById( 'pathway-dash-note-edit' );
		var deleteBtn = document.getElementById( 'pathway-dash-note-delete' );
		var cancelBtn = document.getElementById( 'pathway-dash-note-cancel' );

		var currentNote = null;

		/* ---- WYSIWYG editor (TinyMCE via wp.editor) ---- */

		var EDITOR_ID = 'pathway-dash-note-form-content';
		var editorActive = false;

		function initEditor( html ) {
			removeEditor();
			formContent.value = html;

			if ( window.wp && wp.editor && wp.editor.initialize ) {
				wp.editor.initialize( EDITOR_ID, {
					tinymce: {
						menubar: false,
						statusbar: false,
						toolbar1: 'bold italic bullist numlist blockquote link unlink',
						height: 240,
						branding: false,
					},
					quicktags: false,
					mediaButtons: false,
				} );
				editorActive = true;
			}
		}

		function removeEditor() {
			if ( editorActive && window.wp && wp.editor && wp.editor.remove ) {
				wp.editor.remove( EDITOR_ID );
				editorActive = false;
			}
		}

		function getEditorContent() {
			if ( editorActive && window.tinymce && window.tinymce.get( EDITOR_ID ) ) {
				return window.tinymce.get( EDITOR_ID ).getContent();
			}

			return formContent.value;
		}

		/* ---- Rendering ---- */

		function showViewer( note ) {
			currentNote = note;

			placeholder.hidden = true;
			content.hidden = false;

			titleEl.textContent = note.title;
			lessonEl.textContent = note.lesson_title;
			dateEl.textContent = note.date;
			bodyEl.innerHTML = note.content_html;

			if ( note.admin_response ) {
				responseBody.textContent = note.admin_response;
				responseWrap.hidden = false;
			} else {
				responseWrap.hidden = true;
			}

			exitEditMode();

			wrap.querySelectorAll( '.pd-notes__item' ).forEach( function ( item ) {
				item.classList.toggle(
					'is-active',
					parseInt( item.getAttribute( 'data-note-id' ), 10 ) === note.id
				);
			} );
		}

		function enterEditMode() {
			formTitle.value = currentNote.title;
			formError.hidden = true;
			bodyEl.hidden = true;
			responseWrap.hidden = true;
			editBtn.hidden = true;
			deleteBtn.hidden = true;
			form.hidden = false;

			// The editor must be initialized after the form is visible,
			// otherwise TinyMCE miscalculates its size.
			initEditor( currentNote.content_raw );
		}

		function exitEditMode() {
			removeEditor();
			form.hidden = true;
			bodyEl.hidden = false;
			responseWrap.hidden = ! ( currentNote && currentNote.admin_response );
			editBtn.hidden = false;
			deleteBtn.hidden = false;
		}

		/* ---- Selection ---- */

		wrap.addEventListener( 'click', function ( event ) {
			var item = event.target.closest( '.pd-notes__item' );

			if ( ! item ) {
				return;
			}

			try {
				showViewer( JSON.parse( item.getAttribute( 'data-note' ) ) );
			} catch ( e ) {
				return;
			}

			// On small screens the viewer sits below the list.
			if ( window.matchMedia( '(max-width: 1024px)' ).matches ) {
				content.closest( '.pd-notes__viewer' ).scrollIntoView( { behavior: 'smooth', block: 'start' } );
			}
		} );

		/* ---- Search ---- */

		searchInput.addEventListener( 'input', function () {
			var term = searchInput.value.trim().toLowerCase();
			var anyVisible = false;

			groupsWrap.querySelectorAll( '.pd-notes__group' ).forEach( function ( group ) {
				var groupVisible = false;

				group.querySelectorAll( '.pd-notes__item' ).forEach( function ( item ) {
					var haystack = ( item.textContent + ' ' + group.querySelector( '.pd-notes__group-title' ).textContent ).toLowerCase();
					var match = term === '' || haystack.indexOf( term ) !== -1;

					item.hidden = ! match;

					if ( match ) {
						groupVisible = true;
					}
				} );

				group.hidden = ! groupVisible;

				if ( groupVisible ) {
					anyVisible = true;
				}
			} );

			noResults.hidden = anyVisible;
		} );

		/* ---- AJAX helpers ---- */

		function ajaxPost( action, fields ) {
			var body = new URLSearchParams();
			body.append( 'action', action );
			body.append( 'nonce', settings.nonce );

			Object.keys( fields ).forEach( function ( key ) {
				body.append( key, fields[ key ] );
			} );

			return fetch( settings.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: body.toString(),
			} ).then( function ( response ) {
				return response.json();
			} );
		}

		/* ---- Edit / save ---- */

		editBtn.addEventListener( 'click', enterEditMode );
		cancelBtn.addEventListener( 'click', exitEditMode );

		form.addEventListener( 'submit', function ( event ) {
			event.preventDefault();

			saveBtn.disabled = true;

			ajaxPost( 'pathway_dash_save_note', {
				note_id: currentNote.id,
				title: formTitle.value,
				content: getEditorContent(),
			} )
				.then( function ( result ) {
					saveBtn.disabled = false;

					if ( ! result.success ) {
						formError.textContent = ( result.data && result.data.message ) || 'Could not save the note.';
						formError.hidden = false;
						return;
					}

					var note = result.data.note;
					var item = wrap.querySelector( '.pd-notes__item[data-note-id="' + note.id + '"]' );

					if ( item ) {
						item.setAttribute( 'data-note', JSON.stringify( note ) );
						item.querySelector( '.pd-notes__item-title' ).textContent = note.title;
						item.querySelector( '.pd-notes__item-date' ).textContent = note.date;

						var plain = document.createElement( 'div' );
						plain.innerHTML = note.content_html;
						var words = plain.textContent.trim().split( /\s+/ ).slice( 0, 12 ).join( ' ' );
						item.querySelector( '.pd-notes__item-excerpt' ).textContent = words;
					}

					showViewer( note );
				} )
				.catch( function () {
					saveBtn.disabled = false;
					formError.textContent = 'Could not save the note.';
					formError.hidden = false;
				} );
		} );

		/* ---- Delete ---- */

		deleteBtn.addEventListener( 'click', function () {
			if ( ! currentNote || ! window.confirm( 'Delete this note? This cannot be undone.' ) ) {
				return;
			}

			deleteBtn.disabled = true;

			ajaxPost( 'pathway_dash_delete_note', { note_id: currentNote.id } )
				.then( function ( result ) {
					deleteBtn.disabled = false;

					if ( ! result.success ) {
						return;
					}

					var item = wrap.querySelector( '.pd-notes__item[data-note-id="' + currentNote.id + '"]' );

					if ( item ) {
						var group = item.closest( '.pd-notes__group' );
						item.remove();

						if ( group && ! group.querySelector( '.pd-notes__item' ) ) {
							group.remove();
						}
					}

					currentNote = null;
					content.hidden = true;
					placeholder.hidden = false;
				} )
				.catch( function () {
					deleteBtn.disabled = false;
				} );
		} );
	} );
} )();
