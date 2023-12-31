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
		WPDebug::store_original_config_const();
	}
}
