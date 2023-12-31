<?php
/**
 * Plugin Name: WPDA - Development Assistant
 * Plugin URI: https://github.com/dpripa/wp-dev-assist-public
 * Description: Managing the debug log from the admin panel and more.
 * Version: 1.0.0
 * Text Domain: wp_dev_assist
 * Author: Dmitry Pripa
 * Author URI: https://dpripa.com
 * Requires PHP: 7.2.0
 * Requires at least: 5.0.0
 */
namespace WPDevAssist;

defined( 'ABSPATH' ) || exit;

const KEY       = 'wp_dev_assist';
const ROOT_FILE = __FILE__;

$autoload = __DIR__ . '/vendor/autoload.php';

if ( ! file_exists( $autoload ) ) {
	throw new \Exception( 'Autoloader not exists' );
}

require_once $autoload;

new Setup();
