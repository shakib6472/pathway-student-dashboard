/**
 * Login screen — password visibility toggle.
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var input = document.getElementById( 'pathway-login-password' );
		var eye = document.getElementById( 'pathway-login-eye' );

		if ( ! input || ! eye ) {
			return;
		}

		eye.addEventListener( 'click', function () {
			var show = input.type === 'password';

			input.type = show ? 'text' : 'password';
			eye.classList.toggle( 'is-visible', show );
			eye.setAttribute( 'aria-label', show ? 'Hide password' : 'Show password' );
		} );
	} );
} )();
