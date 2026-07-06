<?php
/**
 * Profile form: photo, first/last name, email.
 *
 * @var WP_User $user Current user.
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pathway_dash_photo    = Pathway_Dashboard_Account::get_photo_url( $user->ID );
$pathway_dash_initials = pathway_dash_get_user_initials( $user );
?>
<div class="pathway-dash__card pd-account__card">
	<h2 class="pd-account__heading"><?php esc_html_e( 'Profile', 'pathway-student-dashboard' ); ?></h2>

	<div class="pd-account__photo-row">
		<div class="pd-account__avatar" id="pathway-dash-account-avatar" data-initials="<?php echo esc_attr( $pathway_dash_initials ); ?>">
			<?php if ( $pathway_dash_photo ) : ?>
				<img src="<?php echo esc_url( $pathway_dash_photo ); ?>" alt="" />
			<?php else : ?>
				<?php echo esc_html( $pathway_dash_initials ); ?>
			<?php endif; ?>
		</div>
		<div class="pd-account__photo-actions">
			<input type="file" id="pathway-dash-photo-input" accept="image/jpeg,image/png,image/gif,image/webp" hidden />
			<button type="button" class="pathway-dash__btn pathway-dash__btn--outline" id="pathway-dash-photo-upload">
				<?php esc_html_e( 'Upload Photo', 'pathway-student-dashboard' ); ?>
			</button>
			<button type="button" class="pathway-dash__btn pathway-dash__btn--outline pd-account__photo-remove" id="pathway-dash-photo-remove" <?php echo $pathway_dash_photo ? '' : 'hidden'; ?>>
				<?php esc_html_e( 'Remove', 'pathway-student-dashboard' ); ?>
			</button>
			<p class="pd-account__hint"><?php esc_html_e( 'JPG, PNG, GIF, or WebP. Max 2 MB.', 'pathway-student-dashboard' ); ?></p>
			<p class="pd-account__msg" id="pathway-dash-photo-msg" hidden></p>
		</div>
	</div>

	<form id="pathway-dash-profile-form" data-pd-action="pathway_dash_save_profile">
		<div class="pd-account__grid">
			<label class="pd-account__field">
				<span class="pd-account__label"><?php esc_html_e( 'First Name', 'pathway-student-dashboard' ); ?></span>
				<input type="text" name="first_name" class="pd-account__input" value="<?php echo esc_attr( $user->first_name ); ?>" required />
			</label>
			<label class="pd-account__field">
				<span class="pd-account__label"><?php esc_html_e( 'Last Name', 'pathway-student-dashboard' ); ?></span>
				<input type="text" name="last_name" class="pd-account__input" value="<?php echo esc_attr( $user->last_name ); ?>" />
			</label>
		</div>

		<label class="pd-account__field">
			<span class="pd-account__label"><?php esc_html_e( 'Email Address', 'pathway-student-dashboard' ); ?></span>
			<input type="email" name="email" class="pd-account__input" value="<?php echo esc_attr( $user->user_email ); ?>" required />
		</label>

		<p class="pd-account__msg" hidden></p>

		<button type="submit" class="pathway-dash__btn pathway-dash__btn--primary">
			<?php esc_html_e( 'Save Profile', 'pathway-student-dashboard' ); ?>
		</button>
	</form>
</div>
