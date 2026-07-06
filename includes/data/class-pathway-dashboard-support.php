<?php
/**
 * Support tab content: FAQs, contact details, and the
 * Fluent Forms shortcode. Everything is filterable so it can be
 * adjusted without touching templates.
 *
 * @package Pathway_Student_Dashboard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Pathway_Dashboard_Support
 */
class Pathway_Dashboard_Support {

	/**
	 * Returns the FAQ items.
	 *
	 * @return array[] [ { question: string, answer: string }, ... ]
	 */
	public static function get_faqs() {
		$faqs = array(
			array(
				'question' => __( 'How do I resume my course where I left off?', 'pathway-student-dashboard' ),
				'answer'   => __( 'Open the My Courses tab and click the Resume Course button on the Continue Learning card. It always takes you to your next incomplete lesson.', 'pathway-student-dashboard' ),
			),
			array(
				'question' => __( 'When do I receive my certificate?', 'pathway-student-dashboard' ),
				'answer'   => __( 'Your certificate unlocks automatically as soon as you complete 100% of the course, including all quizzes. You can download it as a PDF from the Certificates tab.', 'pathway-student-dashboard' ),
			),
			array(
				'question' => __( 'How long do I have access to my course?', 'pathway-student-dashboard' ),
				'answer'   => __( 'Your enrollment gives you full access to the course content for the duration stated on the course page. Contact support if you need an extension.', 'pathway-student-dashboard' ),
			),
			array(
				'question' => __( 'Can I retake a quiz if I fail?', 'pathway-student-dashboard' ),
				'answer'   => __( 'Yes. Open the quiz again from the lesson page to retake it. Your scores are listed in the Progress Analytics tab.', 'pathway-student-dashboard' ),
			),
			array(
				'question' => __( 'How do I change my email or password?', 'pathway-student-dashboard' ),
				'answer'   => __( 'Go to the Account tab. You can update your name, email address, profile photo, and password there.', 'pathway-student-dashboard' ),
			),
			array(
				'question' => __( 'Is my training state-approved?', 'pathway-student-dashboard' ),
				'answer'   => __( 'Yes — Pathway Dental Academy programs are designed to meet your state\'s dental assistant training requirements. Your enrolled state is shown in the Account tab.', 'pathway-student-dashboard' ),
			),
		);

		/**
		 * Filters the Support tab FAQ items.
		 *
		 * @param array[] $faqs FAQ items with question/answer keys.
		 */
		return apply_filters( 'pathway_dash_faqs', $faqs );
	}

	/**
	 * Returns the contact card details.
	 *
	 * @return array { email: string, response_time: string }
	 */
	public static function get_contact() {
		$contact = array(
			'email'         => get_option( 'admin_email' ),
			'response_time' => __( 'We usually reply within 1–2 business days.', 'pathway-student-dashboard' ),
		);

		/**
		 * Filters the Support tab contact details.
		 *
		 * @param array $contact Contact details.
		 */
		return apply_filters( 'pathway_dash_support_contact', $contact );
	}

	/**
	 * Returns the message form shortcode (Fluent Forms).
	 *
	 * @return string
	 */
	public static function get_form_shortcode() {
		/**
		 * Filters the Support tab form shortcode.
		 *
		 * @param string $shortcode Shortcode rendered in the message form card.
		 */
		return apply_filters( 'pathway_dash_support_form_shortcode', '[fluentform id="1"]' );
	}
}
