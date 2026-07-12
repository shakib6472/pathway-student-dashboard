<?php
/**
 * Branded HTML email sender for notification events.
 *
 * Delivery goes through wp_mail(), so FluentSMTP (or any other
 * SMTP plugin) handles the actual transport.
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Pathway_Dashboard_Notifications_Email
 */
class Pathway_Dashboard_Notifications_Email {

	/**
	 * Sends a branded HTML email.
	 *
	 * @param WP_User $user     Recipient.
	 * @param string  $subject  Email subject.
	 * @param string  $heading  Big heading inside the email.
	 * @param string  $body     Body text (plain; paragraphs split on \n\n).
	 * @param string  $cta_text Optional button label.
	 * @param string  $cta_url  Optional button URL.
	 * @return void
	 */
	public static function send( $user, $subject, $heading, $body, $cta_text = '', $cta_url = '' ) {
		if ( ! $user instanceof WP_User || ! is_email( $user->user_email ) ) {
			return;
		}

		$html = self::render( $user, $heading, $body, $cta_text, $cta_url );

		wp_mail(
			$user->user_email,
			$subject,
			$html,
			array( 'Content-Type: text/html; charset=UTF-8' )
		);
	}

	/**
	 * Renders the email HTML with inline styles (email-client safe).
	 *
	 * @param WP_User $user     Recipient.
	 * @param string  $heading  Heading.
	 * @param string  $body     Body text.
	 * @param string  $cta_text Button label.
	 * @param string  $cta_url  Button URL.
	 * @return string
	 */
	private static function render( $user, $heading, $body, $cta_text, $cta_url ) {
		$site_name  = get_bloginfo( 'name' );
		$first_name = $user->first_name ? $user->first_name : $user->display_name;

		$paragraphs = '';

		foreach ( explode( "\n\n", $body ) as $paragraph ) {
			$paragraphs .= '<p style="margin:0 0 16px;font-size:15px;line-height:1.6;color:#3C5454;">' . esc_html( $paragraph ) . '</p>';
		}

		$button = '';

		if ( $cta_text && $cta_url ) {
			$button = '<p style="margin:24px 0 8px;"><a href="' . esc_url( $cta_url ) . '" style="display:inline-block;padding:12px 28px;background:#003054;color:#ffffff;text-decoration:none;border-radius:10px;font-weight:600;font-size:14px;">' . esc_html( $cta_text ) . '</a></p>';
		}

		return '<!DOCTYPE html><html><body style="margin:0;padding:0;background:#F4F9F8;font-family:Arial,Helvetica,sans-serif;">'
			. '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F4F9F8;padding:32px 16px;"><tr><td align="center">'
			. '<table role="presentation" width="560" cellpadding="0" cellspacing="0" style="max-width:560px;width:100%;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #E6EFEC;">'
			. '<tr><td style="background:#003054;padding:20px 32px;">'
			. '<span style="color:#ffffff;font-size:18px;font-weight:700;letter-spacing:0.02em;">' . esc_html( $site_name ) . '</span>'
			. '</td></tr>'
			. '<tr><td style="padding:32px;">'
			. '<h1 style="margin:0 0 8px;font-size:22px;color:#003054;">' . esc_html( $heading ) . '</h1>'
			. '<p style="margin:0 0 20px;font-size:15px;color:#6C9090;">' . esc_html( sprintf( /* translators: %s: first name. */ __( 'Hi %s,', 'pathway-student-dashboard' ), $first_name ) ) . '</p>'
			. $paragraphs
			. $button
			. '</td></tr>'
			. '<tr><td style="padding:18px 32px;background:#F4F9F8;border-top:1px solid #E6EFEC;">'
			. '<p style="margin:0;font-size:12px;color:#6C9090;">' . esc_html( $site_name ) . '</p>'
			. '</td></tr>'
			. '</table></td></tr></table></body></html>';
	}
}
