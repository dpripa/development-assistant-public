<?php
namespace WPDevAssist;

defined( 'ABSPATH' ) || exit;

class Activation {
	public function __construct() {
		register_activation_hook( ROOT_FILE, array( $this, 'setup' ) );
	}

	public function setup(): void {
		Setting::add_default_options();
		Setting\DebugLog::store_original_file_existence();
		Setting\SupportUser::add_default_options();
		WPDebug::store_original_config_const();

		if ( 'yes' === get_option( Setting::DISABLE_DIRECT_ACCESS_TO_LOG_KEY, Setting::DISABLE_DIRECT_ACCESS_TO_LOG_DEFAULT ) ) {
			WPDebug::add_htaccess_directives();
		}
	}
}
