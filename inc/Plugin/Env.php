<?php
namespace WPDevAssist\Plugin;

defined( 'ABSPATH' ) || exit;

class Env {
	protected const DEV_HOSTS = array(
		'localhost',
		'local',
		'loc',
		'development',
		'dev',
	);

	protected static $root_host;

	public function __construct() {
		$host              = explode( '.', wp_parse_url( Url::get_home(), PHP_URL_HOST ) );
		static::$root_host = end( $host );
	}

	public static function is_dev(): bool {
		return in_array( static::$root_host, static::DEV_HOSTS, true );
	}
}
