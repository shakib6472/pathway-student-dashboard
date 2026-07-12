/**
 * Notification bell — panel toggle, mark-as-read, badge updates.
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var bell = document.getElementById( 'pathway-dash-bell' );
		var panel = document.getElementById( 'pathway-dash-bell-panel' );
		var badge = document.getElementById( 'pathway-dash-bell-badge' );
		var readAllBtn = document.getElementById( 'pathway-dash-bell-read-all' );

		if ( ! bell || ! panel ) {
			return;
		}

		var settings = window.pathwayDash || {};

		/* ---- Panel toggle ---- */

		function closePanel() {
			if ( ! panel.hidden ) {
				panel.hidden = true;
				bell.setAttribute( 'aria-expanded', 'false' );
			}
		}

		bell.addEventListener( 'click', function ( event ) {
			event.stopPropagation();
			var willOpen = panel.hidden;
			panel.hidden = ! willOpen;
			bell.setAttribute( 'aria-expanded', willOpen ? 'true' : 'false' );
		} );

		document.addEventListener( 'click', function ( event ) {
			if ( ! panel.hidden && ! panel.contains( event.target ) && event.target !== bell ) {
				closePanel();
			}
		} );

		document.addEventListener( 'keydown', function ( event ) {
			if ( event.key === 'Escape' ) {
				closePanel();
			}
		} );

		/* ---- AJAX ---- */

		function ajaxPost( action, fields ) {
			var body = new URLSearchParams();
			body.append( 'action', action );
			body.append( 'nonce', settings.nonce );

			Object.keys( fields || {} ).forEach( function ( key ) {
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

		function updateBadge( unread ) {
			if ( ! badge ) {
				return;
			}

			if ( unread > 0 ) {
				badge.textContent = unread > 9 ? '9+' : String( unread );
				badge.hidden = false;
			} else {
				badge.hidden = true;

				if ( readAllBtn ) {
					readAllBtn.hidden = true;
				}
			}
		}

		function markItemRead( item ) {
			item.classList.remove( 'is-unread' );

			var dot = item.querySelector( '.pathway-dash__bell-item-dot' );

			if ( dot ) {
				dot.hidden = true;
			}
		}

		/* ---- Per-item read (and follow link) ---- */

		panel.addEventListener( 'click', function ( event ) {
			var item = event.target.closest( '.pathway-dash__bell-item' );

			if ( ! item ) {
				return;
			}

			var id = item.getAttribute( 'data-notification-id' );
			var link = item.getAttribute( 'href' );

			if ( item.classList.contains( 'is-unread' ) ) {
				if ( link ) {
					event.preventDefault();
				}

				markItemRead( item );

				ajaxPost( 'pathway_dash_notification_read', { id: id } ).then( function ( result ) {
					if ( result.success ) {
						updateBadge( result.data.unread );
					}

					if ( link ) {
						window.location.href = link;
					}
				} );
			}
		} );

		/* ---- Mark all as read ---- */

		if ( readAllBtn ) {
			readAllBtn.addEventListener( 'click', function () {
				panel.querySelectorAll( '.pathway-dash__bell-item.is-unread' ).forEach( markItemRead );
				updateBadge( 0 );

				ajaxPost( 'pathway_dash_notifications_read_all', {} );
			} );
		}
	} );
} )();
