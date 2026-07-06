/**
 * Progress Analytics tab — weekly activity chart.
 *
 * Initializes Chart.js lazily: on load if the tab is already active,
 * otherwise the first time the user opens the tab (a chart drawn
 * inside a hidden panel would get zero dimensions).
 */
( function () {
	'use strict';

	var initialized = false;

	function initChart() {
		if ( initialized || typeof window.Chart === 'undefined' ) {
			return;
		}

		var canvas = document.getElementById( 'pathway-dash-weekly-chart' );

		if ( ! canvas ) {
			return;
		}

		var data;

		try {
			data = JSON.parse( canvas.getAttribute( 'data-chart' ) );
		} catch ( e ) {
			return;
		}

		initialized = true;

		var styles = window.getComputedStyle( canvas.closest( '.pathway-dash' ) );
		var teal = styles.getPropertyValue( '--pd-teal' ).trim() || '#6C9090';
		var tealLight = styles.getPropertyValue( '--pd-teal-light' ).trim() || '#84B4B4';
		var navy = styles.getPropertyValue( '--pd-navy' ).trim() || '#003054';
		var border = styles.getPropertyValue( '--pd-border' ).trim() || '#E6EFEC';

		new window.Chart( canvas, {
			type: 'bar',
			data: {
				labels: data.labels,
				datasets: [
					{
						data: data.values,
						backgroundColor: tealLight,
						hoverBackgroundColor: teal,
						borderRadius: 6,
						maxBarThickness: 42,
					},
				],
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				plugins: {
					legend: { display: false },
					tooltip: {
						backgroundColor: navy,
						padding: 10,
						displayColors: false,
						callbacks: {
							label: function ( ctx ) {
								return ctx.parsed.y + ' min';
							},
						},
					},
				},
				scales: {
					x: {
						grid: { display: false },
						ticks: { color: teal, font: { family: 'Inter', size: 12 } },
					},
					y: {
						beginAtZero: true,
						grid: { color: border },
						border: { display: false },
						ticks: {
							color: teal,
							font: { family: 'Inter', size: 12 },
							precision: 0,
						},
					},
				},
			},
		} );
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		var root = document.getElementById( 'pathway-dash' );

		if ( ! root ) {
			return;
		}

		var panel = document.getElementById( 'pathway-dash-panel-progress' );

		if ( panel && ! panel.hidden ) {
			initChart();
		}

		root.addEventListener( 'pathwayDash:tabChanged', function ( event ) {
			if ( event.detail && event.detail.tab === 'progress' ) {
				initChart();
			}
		} );
	} );
} )();
