<?php
namespace WPDevAssist;

defined( 'ABSPATH' ) || exit;

class Plugin {
	public function __construct() {
		new Plugin\Env();
		new Plugin\Notice();
	}
}
