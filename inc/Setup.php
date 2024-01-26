<?php
namespace WPDevAssist;

defined( 'ABSPATH' ) || exit;

class Setup {
	public function __construct() {
		new Activation();
		new Deactivation();
		new Plugin();

		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}

	public function init(): void {
		load_plugin_textdomain( KEY, false, Plugin\Fs::get_path( 'lang' ) );

		new Setting();
		new WPDebug();
		new PluginsScreen();
		new MailHog();
	}
}
