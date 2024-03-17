<?php
/**
 * Plugin Name: Development Assistant
 * Plugin URI: https://devassistant.dpripa.com
 * Description: A professional toolkit for development and customer support of plugins and themes.
 * Version: 1.1.2
 * Text Domain: development-assistant
 * Author: Dmitry Pripa
 * Author URI: https://dpripa.com
 * Requires PHP: 7.2.0
 * Requires at least: 5.0.0
 * License: GPL-2.0-or-later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
namespace WPDevAssist;

defined( 'ABSPATH' ) || exit;

const KEY       = 'wp_dev_assist';
const NAME      = 'development-assistant';
const ROOT_FILE = __FILE__;

$autoload = __DIR__ . '/vendor/autoload.php';

if ( ! file_exists( $autoload ) ) {
	throw new \Exception( 'Autoloader not exists' );
}

require_once $autoload;

new Setup();
