<?php
namespace WPDevAssist\Assistant;

use WPDevAssist\ActionQuery;
use WPDevAssist\Setting;

defined( 'ABSPATH' ) || exit;

class SupportUser extends Section {
	protected bool $is_created;
	protected bool $is_current_user_support;

	public function __construct() {
		$this->is_created              = 0 < get_option( Setting\SupportUser::ID_KEY, Setting\SupportUser::ID_DEFAULT );
		$this->is_current_user_support = Setting\SupportUser::is_current_user();

		parent::__construct();
	}

	protected function set_title(): void {
		$this->title = __( 'Support User', 'development-assistant' );
	}

	protected function set_content(): void {
		if ( $this->is_created ) {
			if ( $this->is_current_user_support ) {
				$this->content .= __( 'You are logged in as a support user.', 'development-assistant' );
			} else {
				$this->content .= __( 'The support user exists.', 'development-assistant' );
			}

			$this->content .= Setting\SupportUser::get_details(
				'da-assistant__simple-list',
				! $this->is_current_user_support
			);
		} else {
			$this->content .= __( 'Support user not created. You don\'t share administrative access with third-parties.', 'development-assistant' );
		}
	}

	protected function set_controls(): void {
		if ( $this->is_created ) {
			if ( Setting\SupportUser::is_allowed_continue_existence() ) {
				$this->controls[] = new Control(
					__( 'Continue existence', 'development-assistant' ),
					ActionQuery::get_url( Setting\SupportUser::UPDATE_CREATE_AT_QUERY_KEY ),
				);
			}

			if ( ! $this->is_current_user_support ) {
				$this->controls[] = new Control(
					__( 'Recreate user', 'development-assistant' ),
					ActionQuery::get_url( Setting\SupportUser::RECREATE_QUERY_KEY, Setting\SupportUser::get_page_url() ),
					Setting\SupportUser::get_recreation_confirmation_massage()
				);
				$this->controls[] = new Control(
					__( 'Delete user', 'development-assistant' ),
					ActionQuery::get_url( Setting\SupportUser::DELETE_QUERY_KEY ),
					Setting\SupportUser::get_deletion_confirmation_massage()
				);
			}
		} else {
			$this->controls[] = new Control(
				__( 'Create user in one click', 'development-assistant' ),
				ActionQuery::get_url( Setting\SupportUser::CREATE_QUERY_KEY, Setting\SupportUser::get_page_url() )
			);
		}
	}

	public function configure_status(): bool {
		if ( $this->is_created ) {
			if ( Setting\SupportUser::is_allowed_continue_existence() ) {
				if ( 1 < intval( get_option( Setting\SupportUser::DELETE_AFTER_DAYS_KEY, Setting\SupportUser::DELETE_AFTER_DAYS_DEFAULT ) ) ) {
					$this->status_level = 'error';
				} else {
					$this->status_level = 'warning';
				}

				$this->status_description = __( 'Will lose access soon', 'development-assistant' );
			} else {
				$this->status_level       = 'warning';
				$this->status_description = __( 'Enabled', 'development-assistant' );
			}
		} else {
			$this->status_level       = 'success';
			$this->status_description = __( 'Disabled', 'development-assistant' );
		}

		return true;
	}
}
