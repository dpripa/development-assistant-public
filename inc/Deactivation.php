<?php
namespace WPDevAssist;

defined( 'ABSPATH' ) || exit;

class Deactivation {
	public function __construct() {
		if ( 'yes' === get_option( Setting::RESET_KEY, Setting::RESET_DEFAULT ) ) {
			register_deactivation_hook( ROOT_FILE, array( $this, 'reset' ) );
		}
	}

	public function reset(): void {
		WPDebug::reset_config_const();
		Setting\DebugLog::delete_file_if_originally_not_exists();
		Setting::reset();
	}
}
