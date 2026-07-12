<?php
/**
 * "Pathway Settings" admin page.
 *
 * Home for the API key, endpoint reference, and any future
 * plugin settings.
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Pathway_Dashboard_Settings_Page
 */
class Pathway_Dashboard_Settings_Page {

	/**
	 * Menu slug.
	 */
	const SLUG = 'pathway-settings';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_init', array( $this, 'maybe_regenerate_key' ) );
	}

	/**
	 * Adds the top-level menu.
	 *
	 * @return void
	 */
	public function register_menu() {
		add_menu_page(
			__( 'Pathway Settings', 'pathway-student-dashboard' ),
			__( 'Pathway Settings', 'pathway-student-dashboard' ),
			'manage_options',
			self::SLUG,
			array( $this, 'render' ),
			'dashicons-welcome-learn-more',
			58
		);
	}

	/**
	 * Handles the "Regenerate key" action.
	 *
	 * @return void
	 */
	public function maybe_regenerate_key() {
		if (
			! isset( $_POST['pathway_dash_regenerate_key'] )
			|| ! current_user_can( 'manage_options' )
		) {
			return;
		}

		check_admin_referer( 'pathway_dash_regenerate_key' );

		Pathway_Dashboard_Api_Keys::regenerate();

		add_action(
			'admin_notices',
			static function () {
				printf(
					'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
					esc_html__( 'A new API key was generated. Update it on the main website — the old key no longer works.', 'pathway-student-dashboard' )
				);
			}
		);
	}

	/**
	 * Renders the settings page.
	 *
	 * @return void
	 */
	public function render() {
		$api_key     = Pathway_Dashboard_Api_Keys::get_key();
		$courses_url = rest_url( 'pathway/v1/courses' );
		$enroll_url  = rest_url( 'pathway/v1/enroll' );
		$docs_path   = 'wp-content/plugins/pathway-student-dashboard/docs/API-DOCUMENTATION.md';
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Pathway Settings', 'pathway-student-dashboard' ); ?></h1>

			<h2><?php esc_html_e( 'API Access', 'pathway-student-dashboard' ); ?></h2>
			<p><?php esc_html_e( 'The main website authenticates with this key, sent in the X-Pathway-Api-Key header. Keep it secret.', 'pathway-student-dashboard' ); ?></p>

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'API Key', 'pathway-student-dashboard' ); ?></th>
					<td>
						<input
							type="text"
							class="regular-text code"
							value="<?php echo esc_attr( $api_key ); ?>"
							readonly
							onfocus="this.select();"
						/>
						<p class="description"><?php esc_html_e( 'Click the field to select the key, then copy it.', 'pathway-student-dashboard' ); ?></p>
					</td>
				</tr>
			</table>

			<form method="post" onsubmit="return confirm( '<?php echo esc_js( __( 'Regenerate the API key? The main website will stop working until it is updated with the new key.', 'pathway-student-dashboard' ) ); ?>' );">
				<?php wp_nonce_field( 'pathway_dash_regenerate_key' ); ?>
				<input type="hidden" name="pathway_dash_regenerate_key" value="1" />
				<?php submit_button( __( 'Regenerate Key', 'pathway-student-dashboard' ), 'secondary' ); ?>
			</form>

			<hr />

			<h2><?php esc_html_e( 'Endpoints', 'pathway-student-dashboard' ); ?></h2>
			<table class="widefat striped" style="max-width: 860px;">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Method', 'pathway-student-dashboard' ); ?></th>
						<th><?php esc_html_e( 'URL', 'pathway-student-dashboard' ); ?></th>
						<th><?php esc_html_e( 'Purpose', 'pathway-student-dashboard' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><code>GET</code></td>
						<td><code><?php echo esc_html( $courses_url ); ?></code></td>
						<td><?php esc_html_e( 'All courses as JSON (metadata only).', 'pathway-student-dashboard' ); ?></td>
					</tr>
					<tr>
						<td><code>GET</code></td>
						<td><code><?php echo esc_html( $courses_url ); ?>/{id}</code></td>
						<td><?php esc_html_e( 'A single course.', 'pathway-student-dashboard' ); ?></td>
					</tr>
					<tr>
						<td><code>POST</code></td>
						<td><code><?php echo esc_html( $enroll_url ); ?></code></td>
						<td><?php esc_html_e( 'Enrollment webhook: creates the student, enrolls, and emails a welcome message.', 'pathway-student-dashboard' ); ?></td>
					</tr>
				</tbody>
			</table>

			<p style="margin-top: 16px;">
				<?php
				printf(
					/* translators: %s: documentation file path. */
					esc_html__( 'Full developer documentation with sample code: %s', 'pathway-student-dashboard' ),
					'<code>' . esc_html( $docs_path ) . '</code>'
				);
				?>
			</p>
		</div>
		<?php
	}
}
