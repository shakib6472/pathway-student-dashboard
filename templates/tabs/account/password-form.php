<?php
/**
 * Password change form.
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="pathway-dash__card pd-account__card">
	<h2 class="pd-account__heading"><?php esc_html_e( 'Change Password', 'pathway-student-dashboard' ); ?></h2>

	<form id="pathway-dash-password-form" data-pd-action="pathway_dash_change_password" data-pd-reset="true">
		<label class="pd-account__field">
			<span class="pd-account__label"><?php esc_html_e( 'Current Password', 'pathway-student-dashboard' ); ?></span>
			<input type="password" name="current_password" class="pd-account__input" autocomplete="current-password" required />
		</label>

		<div class="pd-account__grid">
			<label class="pd-account__field">
				<span class="pd-account__label"><?php esc_html_e( 'New Password', 'pathway-student-dashboard' ); ?></span>
				<input type="password" name="new_password" class="pd-account__input" autocomplete="new-password" minlength="8" required />
			</label>
			<label class="pd-account__field">
				<span class="pd-account__label"><?php esc_html_e( 'Confirm New Password', 'pathway-student-dashboard' ); ?></span>
				<input type="password" name="confirm_password" class="pd-account__input" autocomplete="new-password" minlength="8" required />
			</label>
		</div>

		<p class="pd-account__hint"><?php esc_html_e( 'At least 8 characters.', 'pathway-student-dashboard' ); ?></p>
		<p class="pd-account__msg" hidden></p>

		<button type="submit" class="pathway-dash__btn pathway-dash__btn--primary">
			<?php esc_html_e( 'Update Password', 'pathway-student-dashboard' ); ?>
		</button>
	</form>
</div>
