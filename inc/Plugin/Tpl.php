<?php
namespace WPDevAssist\Plugin;

defined( 'ABSPATH' ) || exit;

class Tpl {
	protected const DIR = 'template';

	public static function get( string $name, array $args = array() ): string {
		ob_start();
		include Fs::get_path( static::DIR . "/$name.php" );

		return ob_get_clean();
	}

	public static function render( string $name, array $args = array() ): void {
		echo static::get( $name, $args ); // phpcs:ignore
	}
}
