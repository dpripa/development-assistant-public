<?php
namespace WPDevAssist;

defined( 'ABSPATH' ) || exit;

class Fs {
	public static function get_url( string $rel = '', bool $stamp = false ): string {
		$url = plugin_dir_url( ROOT_FILE );
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
		$path = plugin_dir_path( ROOT_FILE );

		return $rel ? "$path{$rel}" : rtrim( $path, '/\\' );
	}

	public static function write( string $path, string $content, int $permissions = 0600 ): bool {
		$output = error_log( '/*test*/', '3', $path ); // phpcs:ignore

		if ( ! $output ) {
			Notice::add_transient( sprintf( __( 'Can\'t write the %s.', 'development-assistant' ), $path ), 'error' );

			return false;
		}

		unlink( $path );
		error_log( $content, '3', $path ); // phpcs:ignore
		chmod( $path, $permissions );

		return true;
	}

	/**
	 * @return string|bool
	 */
	public static function read( string $path ) {
		if ( ! file_exists( $path ) ) {
			Notice::add_transient( sprintf( __( 'Can\'t read the %s.', 'development-assistant' ), $path ), 'error' );

			return false;
		}

		$file     = fopen( $path , 'r' ); // phpcs:ignore
		$response = '';

		fseek( $file, -1048576, SEEK_END );

		while ( ! feof( $file ) ) {
			$response .= fgets( $file );
		}

		fclose( $file ); // phpcs:ignore

		return $response;
	}
}
