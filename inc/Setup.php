<?php
namespace WPDevAssist;

defined( 'ABSPATH' ) || exit;

class Setup {
	public function __construct() {
		new Activation();
		new Deactivation();
		new Notice();
		new Assistant();

		add_action( 'plugins_loaded', array( $this, 'init' ) );
		add_action( 'init', array( $this, 'load_textdomain' ) );
	}

	public function init(): void {
		new Setting();
		new WPDebug();
		new PluginsScreen();
		new MailHog();
	}

	public function load_textdomain(): void {
		load_plugin_textdomain( 'development-assistant', false, Fs::get_path( 'lang' ) );
	}
}
