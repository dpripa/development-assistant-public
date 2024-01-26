<?php
namespace WPDevAssist;

defined( 'ABSPATH' ) || exit;

class MailHog {
	public function __construct() {
		if (
			'yes' !== get_option( Setting::REDIRECT_TO_MAIL_HOG_KEY, Setting::REDIRECT_TO_MAIL_HOG_DEFAULT ) ||
			! Plugin\Env::is_dev()
		) {
			return;
		}

		add_action( 'phpmailer_init', array( $this, 'change_phpmailer_props' ) );
	}

	/**
	 * @param $phpmailer \PHPMailer
	 */
	public function change_phpmailer_props( $phpmailer ): void {
		$phpmailer->Host     = '127.0.0.1'; // phpcs:ignore
		$phpmailer->Port     = 1025; // phpcs:ignore
		$phpmailer->SMTPAuth = false; // phpcs:ignore
		$phpmailer->isSMTP();
	}
}
