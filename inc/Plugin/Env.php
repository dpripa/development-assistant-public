<?php
namespace WPDevAssist\Plugin;

defined( 'ABSPATH' ) || exit;

class Env {
	protected $dev_hosts = array(
		'localhost',
		'local',
		'loc',
		'development',
		'dev',
	);

	protected $dev_envs = array(
		'development',
		'local',
	);

	protected static $is_dev;

	public function __construct() {
		$host           = explode( '.', wp_parse_url( home_url(), PHP_URL_HOST ) );
		$root_host      = end( $host );
		static::$is_dev = in_array( $root_host, $this->dev_hosts, true ) ||
			in_array( wp_get_environment_type(), $this->dev_envs, true );
	}

	public static function is_dev(): bool {
		return static::$is_dev;
	}
}
