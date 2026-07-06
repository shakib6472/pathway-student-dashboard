<?php
/**
 * Plugin Name:       Pathway Student Dashboard
 * Plugin URI:        https://github.com/shakib6472/pathway-student-dashboard
 * Description:       A premium front-end student dashboard for Pathway Dental Academy. Renders via the [pathway_dashboard] shortcode and integrates with LearnDash LMS, Notes by LearnDash, and WooCommerce.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Shakib Shown
 * Author URI:        https://shakib647.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       pathway-student-dashboard
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PATHWAY_DASH_VERSION', '1.0.0' );
define( 'PATHWAY_DASH_FILE', __FILE__ );
define( 'PATHWAY_DASH_DIR', plugin_dir_path( __FILE__ ) );
define( 'PATHWAY_DASH_URL', plugin_dir_url( __FILE__ ) );

require_once PATHWAY_DASH_DIR . 'includes/helpers.php';
require_once PATHWAY_DASH_DIR . 'includes/data/class-pathway-dashboard-courses.php';
require_once PATHWAY_DASH_DIR . 'includes/data/class-pathway-dashboard-stats.php';
require_once PATHWAY_DASH_DIR . 'includes/class-pathway-dashboard-assets.php';
require_once PATHWAY_DASH_DIR . 'includes/class-pathway-dashboard-shortcode.php';
require_once PATHWAY_DASH_DIR . 'includes/class-pathway-dashboard.php';

/**
 * Returns the main plugin instance.
 *
 * @return Pathway_Dashboard
 */
function pathway_dash() {
	return Pathway_Dashboard::instance();
}

pathway_dash();
