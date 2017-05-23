<?php

/**
 * @class Email
 *
 * Contains functionality related to sending emails
 *
 * @since 1.0
 *
 */

namespace BrownBox\Express\Helper;

class Email {

	/**
	 * Send an email
	 *
	 * @param string $to
	 * @param string $subject
	 * @param string $content
	 * @param bool $enable_html
	 *
	 * @access public
	 * @return bool
	 * @static
	 */
	public static function send_email( $to, $subject, $content, $enable_html = true ) {

		if ( $enable_html ) {
			add_filter( 'wp_mail_content_type', function() { return 'text/html'; } );
		}

		$result = wp_mail( $to, $subject, $content );

		return $result;

	}

}


