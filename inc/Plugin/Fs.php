<?php
namespace WPDevAssist\Plugin;

use const WPDevAssist\ROOT_FILE;

defined( 'ABSPATH' ) || exit;

class Fs {
	protected const ROOT_FILE = ROOT_FILE;

	public static function get_url( string $rel = '', bool $stamp = false ): string {
		$url = plugin_dir_url( static::ROOT_FILE );
		$url = $rel ? ( $url . $rel ) : rtrim( $url, '/\\' );

		if ( $stamp ) {
			$path = static::get_path( $rel );

			if ( ! file_exists( $path ) ) {
				return $url;
			}

			return add_query_arg( array( 'ver' => filemtime( $path ) ), $url );
		}

		return $url;
	}

	public static function get_path( string $rel = '' ): string {
		$path = plugin_dir_path( static::ROOT_FILE );

		return $rel ? "$path{$rel}" : rtrim( $path, '/\\' );
	}
}
