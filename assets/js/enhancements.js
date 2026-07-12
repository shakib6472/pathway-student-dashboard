/**
 * Dashboard micro-interactions: progress bars/ring sweep in and
 * stat numbers count up the first time their panel becomes visible.
 */
( function () {
	'use strict';

	/**
	 * Re-triggers the width/dashoffset transitions from zero.
	 *
	 * @param {Element} panel Panel to animate.
	 */
	function animatePanel( panel ) {
		if ( ! panel || panel.dataset.pdAnimated ) {
			return;
		}

		panel.dataset.pdAnimated = '1';

		panel.querySelectorAll( '.pd-progress__bar' ).forEach( function ( bar ) {
			var width = bar.style.width;

			bar.style.transition = 'none';
			bar.style.width = '0%';

			requestAnimationFrame( function () {
				requestAnimationFrame( function () {
					bar.style.transition = '';
					bar.style.width = width;
				} );
			} );
		} );

		panel.querySelectorAll( '.pd-ring__value' ).forEach( function ( ring ) {
			var offset = ring.getAttribute( 'stroke-dashoffset' );
			var full = ring.getAttribute( 'stroke-dasharray' );

			ring.style.transition = 'none';
			ring.style.strokeDashoffset = full;

			requestAnimationFrame( function () {
				requestAnimationFrame( function () {
					ring.style.transition = '';
					ring.style.strokeDashoffset = offset;
				} );
			} );
		} );

		panel.querySelectorAll( '.pd-stats__value' ).forEach( countUp );
	}

	/**
	 * Counts the first numeric text node of a stat up from zero.
	 *
	 * @param {Element} el Stat value element.
	 */
	function countUp( el ) {
		var node = null;

		el.childNodes.forEach( function ( child ) {
			if ( ! node && child.nodeType === 3 && child.textContent.trim() !== '' ) {
				node = child;
			}
		} );

		if ( ! node ) {
			return;
		}

		var text = node.textContent;
		var match = text.match( /-?\d+(\.\d+)?/ );

		if ( ! match ) {
			return;
		}

		var target = parseFloat( match[ 0 ] );
		var decimals = ( match[ 0 ].split( '.' )[ 1 ] || '' ).length;
		var duration = 900;
		var start = null;

		function step( timestamp ) {
			if ( ! start ) {
				start = timestamp;
			}

			var progress = Math.min( ( timestamp - start ) / duration, 1 );
			var eased = 1 - Math.pow( 1 - progress, 3 );

			node.textContent = text.replace( match[ 0 ], ( target * eased ).toFixed( decimals ) );

			if ( progress < 1 ) {
				requestAnimationFrame( step );
			} else {
				node.textContent = text;
			}
		}

		requestAnimationFrame( step );
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		var root = document.getElementById( 'pathway-dash' );

		if ( ! root || window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
			return;
		}

		animatePanel( root.querySelector( '.pathway-dash__panel.is-active' ) );

		root.addEventListener( 'pathwayDash:tabChanged', function ( event ) {
			animatePanel( document.getElementById( 'pathway-dash-panel-' + event.detail.tab ) );
		} );
	} );
} )();
