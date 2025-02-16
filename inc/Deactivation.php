<?php
namespace WPDevAssist;

use Exception;

defined( 'ABSPATH' ) || exit;

class Deactivation {
	public function __construct() {
		register_deactivation_hook( ROOT_FILE, array( $this, 'reset' ) );
	}

	/**
	 * @throws Exception
	 */
	public function reset(): void {
		Notice::reset();
		WPDebug::remove_htaccess_directives();

		if ( 'yes' !== get_option( Setting::RESET_KEY, Setting::RESET_DEFAULT ) ) {
			return;
		}

		WPDebug::reset_config_const();
		Setting\DebugLog::delete_file_if_originally_not_exists();
		Setting\SupportUser::reset();
		Setting::reset();
	}
}
