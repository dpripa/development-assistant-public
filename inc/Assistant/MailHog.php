<?php
namespace WPDevAssist\Assistant;

use WPDevAssist\ActionQuery;
use WPDevAssist\Setting;

defined( 'ABSPATH' ) || exit;

class MailHog extends Section {
	protected bool $is_enabled;
	protected bool $is_detected;

	public function __construct() {
		$this->is_enabled  = 'yes' === get_option( Setting\DevEnv::REDIRECT_TO_MAIL_HOG_KEY, Setting\DevEnv::REDIRECT_TO_MAIL_HOG_DEFAULT );
		$this->is_detected = \WPDevAssist\MailHog::is_http_host_exists();

		parent::__construct();
	}

	protected function set_title(): void {
		$this->title = __( 'MailHog', 'development-assistant' );
	}

	protected function set_content(): void {
		if ( $this->is_enabled ) {
			if ( $this->is_detected ) {
				$this->content = __( 'MailHog was successfully detected on your server.', 'development-assistant' );
			} else {
				$this->content = __( 'MailHog was not detected on your server.', 'development-assistant' );
			}
		} else {
			$this->content = __( 'MailHog is a mail testing tool that captures emails sent by your website and displays them in a web interface. It is useful for testing email functionality during development without sending emails to real users.', 'development-assistant' );
		}
	}

	protected function set_controls(): void {
		if ( $this->is_enabled ) {
			if ( $this->is_detected ) {
				$this->controls[] = new Control(
					__( 'Go to inbox', 'development-assistant' ),
					\WPDevAssist\MailHog::get_http_host(),
					'',
					true
				);
				$this->controls[] = new Control(
					__( 'Send a test email', 'development-assistant' ),
					ActionQuery::get_url( \WPDevAssist\MailHog::SEND_TEST_EMAIL_QUERY_KEY ),
				);
			} else {
				$this->controls[] = new Control(
					__( 'Go to settings', 'development-assistant' ),
					Setting\DevEnv::get_url(),
				);
			}
		} else {
			$this->controls[] = new Control(
				__( 'Enable redirect emails', 'development-assistant' ),
				ActionQuery::get_url( Setting\DevEnv::REDIRECT_TO_MAIL_HOG_QUERY_KEY ),
			);
		}
	}

	public function configure_status(): bool {
		if ( $this->is_enabled ) {
			if ( $this->is_detected ) {
				$this->status_level       = 'success';
				$this->status_description = __( 'Enabled', 'development-assistant' );
			} else {
				$this->status_level       = 'error';
				$this->status_description = __( 'Not detected', 'development-assistant' );
			}
		} else {
			$this->status_level       = 'warning';
			$this->status_description = __( 'Disabled', 'development-assistant' );
		}

		return true;
	}
}
