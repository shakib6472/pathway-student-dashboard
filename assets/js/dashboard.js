/**
 * Pathway Student Dashboard — front-end behavior.
 *
 * Phase 1: tab switching (with URL hash persistence) and the
 * avatar dropdown menu. Later phases add AJAX features on top.
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var root = document.getElementById( 'pathway-dash' );

		if ( ! root ) {
			return;
		}

		var settings = window.pathwayDash || {};
		var validTabs = settings.tabs || [];
		var defaultTab = settings.defaultTab || 'my-courses';

		/* ---- Tab switching ---- */

		/**
		 * Activates a tab: toggles panels, nav states, and the URL hash.
		 *
		 * @param {string}  slug       Tab slug.
		 * @param {boolean} updateHash Whether to write the slug to the URL hash.
		 */
		function activateTab( slug, updateHash ) {
			if ( validTabs.indexOf( slug ) === -1 ) {
				return;
			}

			root.querySelectorAll( '.pathway-dash__panel' ).forEach( function ( panel ) {
				var isActive = panel.id === 'pathway-dash-panel-' + slug;
				panel.classList.toggle( 'is-active', isActive );
				panel.hidden = ! isActive;
			} );

			root.querySelectorAll( '[data-pathway-tab]' ).forEach( function ( item ) {
				var isActive = item.getAttribute( 'data-pathway-tab' ) === slug;
				item.classList.toggle( 'is-active', isActive );

				if ( item.getAttribute( 'role' ) === 'tab' ) {
					item.setAttribute( 'aria-selected', isActive ? 'true' : 'false' );
				}
			} );

			if ( updateHash ) {
				// replaceState avoids polluting browser history on every tab click.
				window.history.replaceState( null, '', '#' + slug );
			}

			// Let tab modules (charts etc., later phases) react to activation.
			root.dispatchEvent( new CustomEvent( 'pathwayDash:tabChanged', { detail: { tab: slug } } ) );
		}

		root.addEventListener( 'click', function ( event ) {
			var trigger = event.target.closest( '[data-pathway-tab]' );

			if ( ! trigger ) {
				return;
			}

			event.preventDefault();
			activateTab( trigger.getAttribute( 'data-pathway-tab' ), true );
			closeAvatarMenu();
		} );

		// Restore the tab from the URL hash on load (e.g. after refresh).
		var initialTab = window.location.hash.replace( '#', '' );
		activateTab( validTabs.indexOf( initialTab ) !== -1 ? initialTab : defaultTab, false );

		// Support external links to a tab (e.g. site menu linking to /dashboard/#support).
		window.addEventListener( 'hashchange', function () {
			activateTab( window.location.hash.replace( '#', '' ), false );
		} );

		/* ---- Avatar dropdown ---- */

		var avatarBtn = document.getElementById( 'pathway-dash-avatar' );
		var avatarMenu = document.getElementById( 'pathway-dash-avatar-menu' );

		function closeAvatarMenu() {
			if ( avatarMenu && ! avatarMenu.hidden ) {
				avatarMenu.hidden = true;
				avatarBtn.setAttribute( 'aria-expanded', 'false' );
			}
		}

		if ( avatarBtn && avatarMenu ) {
			avatarBtn.addEventListener( 'click', function ( event ) {
				event.stopPropagation();
				var willOpen = avatarMenu.hidden;
				avatarMenu.hidden = ! willOpen;
				avatarBtn.setAttribute( 'aria-expanded', willOpen ? 'true' : 'false' );
			} );

			document.addEventListener( 'click', function ( event ) {
				if ( ! avatarMenu.hidden && ! avatarMenu.contains( event.target ) ) {
					closeAvatarMenu();
				}
			} );

			document.addEventListener( 'keydown', function ( event ) {
				if ( event.key === 'Escape' ) {
					closeAvatarMenu();
				}
			} );
		}
	} );
} )();
