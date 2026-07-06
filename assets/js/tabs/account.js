/**
 * Account tab — profile save, password change, and photo
 * upload/removal over admin-ajax.
 *
 * Forms with [data-pd-action] are submitted generically; the photo
 * controls have their own handlers because they send files.
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var panel = document.getElementById( 'pathway-dash-panel-account' );

		if ( ! panel ) {
			return;
		}

		var settings = window.pathwayDash || {};

		function showMessage( el, text, isError ) {
			el.textContent = text;
			el.classList.toggle( 'is-error', !! isError );
			el.hidden = false;

			window.clearTimeout( el._pdTimer );
			el._pdTimer = window.setTimeout( function () {
				el.hidden = true;
			}, 5000 );
		}

		function ajaxSend( formData ) {
			formData.append( 'nonce', settings.nonce );

			return fetch( settings.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				body: formData,
			} ).then( function ( response ) {
				return response.json();
			} );
		}

		/* ---- Generic forms (profile, password) ---- */

		panel.querySelectorAll( 'form[data-pd-action]' ).forEach( function ( form ) {
			form.addEventListener( 'submit', function ( event ) {
				event.preventDefault();

				var msg = form.querySelector( '.pd-account__msg' );
				var submitBtn = form.querySelector( '[type="submit"]' );
				var formData = new FormData( form );

				formData.append( 'action', form.getAttribute( 'data-pd-action' ) );
				submitBtn.disabled = true;

				ajaxSend( formData )
					.then( function ( result ) {
						submitBtn.disabled = false;

						if ( ! result.success ) {
							showMessage( msg, ( result.data && result.data.message ) || 'Something went wrong.', true );
							return;
						}

						showMessage( msg, result.data.message, false );

						if ( form.getAttribute( 'data-pd-reset' ) ) {
							form.reset();
						}

						// Profile save: sync the topbar greeting and avatar initials.
						if ( result.data.first_name ) {
							var greeting = document.getElementById( 'pathway-dash-greeting-name' );

							if ( greeting ) {
								greeting.textContent = result.data.first_name;
							}

							updateInitials( result.data.initials );
						}
					} )
					.catch( function () {
						submitBtn.disabled = false;
						showMessage( msg, 'Something went wrong.', true );
					} );
			} );
		} );

		/* ---- Profile photo ---- */

		var photoInput = document.getElementById( 'pathway-dash-photo-input' );
		var uploadBtn = document.getElementById( 'pathway-dash-photo-upload' );
		var removeBtn = document.getElementById( 'pathway-dash-photo-remove' );
		var photoMsg = document.getElementById( 'pathway-dash-photo-msg' );
		var accountAvatar = document.getElementById( 'pathway-dash-account-avatar' );
		var topbarAvatar = document.getElementById( 'pathway-dash-avatar' );

		function setAvatarPhoto( url ) {
			[ accountAvatar, topbarAvatar ].forEach( function ( el ) {
				if ( ! el ) {
					return;
				}

				if ( url ) {
					var img = el.querySelector( 'img' );

					if ( ! img ) {
						img = document.createElement( 'img' );
						el.textContent = '';
						el.appendChild( img );
					}

					img.src = url;
				} else {
					el.textContent = el.getAttribute( 'data-initials' ) || '';
				}
			} );
		}

		function updateInitials( initials ) {
			[ accountAvatar, topbarAvatar ].forEach( function ( el ) {
				if ( ! el ) {
					return;
				}

				el.setAttribute( 'data-initials', initials );

				if ( ! el.querySelector( 'img' ) ) {
					el.textContent = initials;
				}
			} );
		}

		if ( uploadBtn && photoInput ) {
			uploadBtn.addEventListener( 'click', function () {
				photoInput.click();
			} );

			photoInput.addEventListener( 'change', function () {
				var file = photoInput.files[ 0 ];

				if ( ! file ) {
					return;
				}

				if ( file.size > 2097152 ) {
					showMessage( photoMsg, 'The photo must be smaller than 2 MB.', true );
					photoInput.value = '';
					return;
				}

				var formData = new FormData();
				formData.append( 'action', 'pathway_dash_upload_photo' );
				formData.append( 'photo', file );

				uploadBtn.disabled = true;

				ajaxSend( formData )
					.then( function ( result ) {
						uploadBtn.disabled = false;
						photoInput.value = '';

						if ( ! result.success ) {
							showMessage( photoMsg, ( result.data && result.data.message ) || 'Upload failed.', true );
							return;
						}

						setAvatarPhoto( result.data.url );
						removeBtn.hidden = false;
						showMessage( photoMsg, result.data.message, false );
					} )
					.catch( function () {
						uploadBtn.disabled = false;
						showMessage( photoMsg, 'Upload failed.', true );
					} );
			} );
		}

		if ( removeBtn ) {
			removeBtn.addEventListener( 'click', function () {
				var formData = new FormData();
				formData.append( 'action', 'pathway_dash_remove_photo' );

				removeBtn.disabled = true;

				ajaxSend( formData )
					.then( function ( result ) {
						removeBtn.disabled = false;

						if ( ! result.success ) {
							return;
						}

						setAvatarPhoto( '' );
						removeBtn.hidden = true;
						showMessage( photoMsg, result.data.message, false );
					} )
					.catch( function () {
						removeBtn.disabled = false;
					} );
			} );
		}
	} );
} )();
