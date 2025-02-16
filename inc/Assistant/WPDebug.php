<?php
namespace WPDevAssist\Assistant;

use WPDevAssist\ActionQuery;
use WPDevAssist\Htaccess;
use WPDevAssist\Setting;
use WPDevAssist\Setting\DebugLog;

defined( 'ABSPATH' ) || exit;

class WPDebug extends Section {
	protected array $checked_constants = array();
	protected bool $is_dev_env;
	protected bool $is_debug_log_exists;
	protected bool $is_htaccess_exists;
	protected bool $is_disabled_direct_access_to_log;
	protected string $debug_enabled;
	protected string $log_enabled;
	protected string $display_enabled;

	public function __construct() {
		$this->is_dev_env                       = 'yes' === get_option( Setting\DevEnv::ENABLE_KEY, Setting\DevEnv::ENABLE_DEFAULT );
		$this->is_debug_log_exists              = DebugLog::is_file_exists();
		$this->is_htaccess_exists               = Htaccess::exists();
		$this->is_disabled_direct_access_to_log = 'yes' === get_option( Setting::DISABLE_DIRECT_ACCESS_TO_LOG_KEY, Setting::DISABLE_DIRECT_ACCESS_TO_LOG_DEFAULT );
		$this->debug_enabled                    = get_option( Setting::ENABLE_WP_DEBUG_KEY, Setting::ENABLE_WP_DEBUG_DEFAULT );
		$this->log_enabled                      = get_option( Setting::ENABLE_WP_DEBUG_LOG_KEY, Setting::ENABLE_WP_DEBUG_LOG_DEFAULT );
		$this->display_enabled                  = get_option( Setting::ENABLE_WP_DEBUG_DISPLAY_KEY, Setting::ENABLE_WP_DEBUG_DISPLAY_DEFAULT );

		$this->check_constants();
		parent::__construct();
	}

	protected function set_title(): void {
		$this->title = __( 'WP Debug', 'development-assistant' );
	}

	protected function set_content(): void {
		$checked_constants = array_reduce(
			$this->checked_constants,
			function ( string $result, string $const_name ): string {
				return $result . ( $result ? ', ' : '' ) . "<code>$const_name</code>";
			},
			''
		);

		if ( $this->is_dev_env ) {
			if ( $this->checked_constants ) {
				$this->content .= sprintf( __( 'Disabled %s was detected in the development environment.', 'development-assistant' ), $checked_constants );
				$this->content .= '<br><b>' . __( 'Don\'t forget to enable this when you start developing.', 'development-assistant' ) . '</b>';
			} else {
				$this->content = __( 'Everything is fine, debug mode is enabled in your development environment.', 'development-assistant' );
			}

			return;
		}

		if ( $this->checked_constants ) {
			$this->content .= sprintf( __( 'Enabled %s was detected in the production environment.', 'development-assistant' ), $checked_constants );
			$this->content .= '<br><b>' . __( 'Don\'t leave it enabled unless you are debugging to avoid the performance issues.', 'development-assistant' ) . '</b>';

			if ( ! $this->is_disabled_direct_access_to_log && $this->is_htaccess_exists ) {
				$this->content .= '<br>' . __( 'Also, for security reasons, it\'s highly recommended to disable direct access to the <code>debug.log</code> file.', 'development-assistant' );
			} else {
				$this->content .= '<br>' . __( 'Direct access to the <code>debug.log</code> file is disabled.', 'development-assistant' );
			}

			if ( 'yes' === $this->display_enabled ) {
				$this->content .= '<br><span class="da-assistant__error-message"><b>' . __( 'Warning!', 'development-assistant' ) . '</b> ' . __( 'Enabled <code>WP_DEBUG_DISPLAY</code> may cause the entire interface blocking due to the display of error messages, as well as a critical security issues. <b>Highly recommended to disable it in production environment.</b>', 'development-assistant' ) . '</span>';
			}
		} else {
			$this->content .= __( 'Everything is fine, debug mode is disabled, error information isn\'t displayed or logged.', 'development-assistant' );

			if ( $this->is_debug_log_exists ) {
				if ( $this->is_disabled_direct_access_to_log ) {
					$this->content .= '<br>' . __( 'The <code>debug.log</code> file still exists, but it is protected from direct access, so don\'t worry.', 'development-assistant' );
				} else {
					if ( $this->is_htaccess_exists ) {
						$this->content .= '<br><b>' . __( 'But <code>debug.log</code> file still exists, it\'s important to delete it or disable direct access.', 'development-assistant' ) . '</b>';
					} else {
						$this->content .= '<br><b>' . __( 'But <code>debug.log</code> file still exists, it\'s important to delete it.', 'development-assistant' ) . '</b>';
					}
				}
			}
		}
	}

	protected function check_constants(): void {

		$condition_value = $this->is_dev_env ? 'yes' : 'no';

		if ( $this->debug_enabled !== $condition_value ) {
			$this->checked_constants[] = 'WP_DEBUG';
		}

		if ( $this->log_enabled !== $condition_value ) {
			$this->checked_constants[] = 'WP_DEBUG_LOG';
		}

		if ( $this->display_enabled !== $condition_value ) {
			$this->checked_constants[] = 'WP_DEBUG_DISPLAY';
		}
	}

	protected function set_controls(): void {
		if ( $this->is_debug_log_exists ) {
			$this->controls[] = new Control(
				__( 'Go to <code>debug.log</code>', 'development-assistant' ),
				DebugLog::get_page_url()
			);
		}

		if ( ! $this->is_dev_env && 'yes' === $this->display_enabled ) {
			$this->controls[] = new Control(
				__( 'Disable <code>WP_DEBUG_DISPLAY</code>', 'development-assistant' ),
				ActionQuery::get_url( Setting::DISABLE_DEBUG_DISPLAY_QUERY_KEY ),
			);
		}

		if (
			( $this->is_dev_env && empty( $this->checked_constants ) ) ||
			( ! $this->is_dev_env && $this->checked_constants )
		) {
			$this->controls[] = new Control(
				__( 'Disable debug mode', 'development-assistant' ),
				ActionQuery::get_url( Setting::TOGGLE_DEBUG_MODE_QUERY_KEY, null, 'no' ),
				__( 'Are you sure to disable debug mode?', 'development-assistant' )
			);
		} else {
			$this->controls[] = new Control(
				__( 'Enable debug mode', 'development-assistant' ),
				ActionQuery::get_url( Setting::TOGGLE_DEBUG_MODE_QUERY_KEY ),
				__( 'Are you sure to enable debug mode?', 'development-assistant' )
			);
		}

		if (
			( $this->checked_constants || $this->is_debug_log_exists ) &&
			! $this->is_disabled_direct_access_to_log && ! $this->is_dev_env && $this->is_htaccess_exists
		) {
			$this->controls[] = new Control(
				__( 'Disable direct access to <code>debug.log</code>', 'development-assistant' ),
				ActionQuery::get_url( Setting::DISABLE_DIRECT_ACCESS_TO_LOG_QUERY_KEY ),
			);
		}

		if (
			( ! $this->checked_constants || ! $this->is_disabled_direct_access_to_log ) &&
			$this->is_debug_log_exists && ! $this->is_dev_env
		) {
			$this->controls[] = new Control(
				__( 'Delete log file', 'development-assistant' ),
				ActionQuery::get_url( DebugLog::DELETE_LOG_QUERY_KEY ),
				DebugLog::get_deletion_confirmation_massage()
			);
		}
	}

	public function configure_status(): bool {
		if ( $this->is_dev_env ) {
			if ( $this->checked_constants ) {
				$this->status_level       = 'error';
				$this->status_description = __( 'Disabled', 'development-assistant' );
			} else {
				$this->status_level       = 'success';
				$this->status_description = __( 'Enabled', 'development-assistant' );
			}

			return true;
		}

		if ( $this->checked_constants ) {
			if ( ! $this->is_disabled_direct_access_to_log && $this->is_htaccess_exists ) {
				$this->status_level       = 'error';
				$this->status_description = __( 'Enabled with direct access to logs', 'development-assistant' );
			} else {
				if ( 'yes' === $this->display_enabled ) {
					$this->status_level = 'error';
				} else {
					$this->status_level = 'warning';
				}
				$this->status_description = __( 'Enabled', 'development-assistant' );
			}
		} else {
			if ( $this->is_debug_log_exists && ! $this->is_disabled_direct_access_to_log ) {
				$this->status_level       = 'error';
				$this->status_description = __( 'Disabled, but logs exists', 'development-assistant' );
			} else {
				$this->status_level       = 'success';
				$this->status_description = __( 'Disabled', 'development-assistant' );
			}
		}

		return true;
	}
}
