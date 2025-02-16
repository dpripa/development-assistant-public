<?php
namespace WPDevAssist;

defined( 'ABSPATH' ) || exit;

class Htaccess {
	protected const PATH = ABSPATH . '.htaccess';

	public static function exists(): bool {
		return file_exists( static::PATH );
	}

	public static function replace( string $marker, string $content ): bool {
		if ( ! static::exists() ) {
			return false;
		}

		$file_content = Fs::read( static::PATH );

		if ( ! $file_content ) {
			return false;
		}

		$pattern = static::get_pattern( $marker );

		if ( ! empty( $content ) ) {
			$content = static::get_pattern( $marker, $content );
		}

		if ( preg_match( $pattern, $file_content ) ) {
			$file_content = preg_replace( $pattern, $content, $file_content );
		} elseif ( ! empty( $content ) ) {
			$file_content .= $content;
		} else {
			return $file_content;
		}

		return Fs::write( static::PATH, $file_content, 0644 );
	}

	public static function remove( string $marker ): bool {
		return static::replace( $marker, '' );
	}

	protected static function get_pattern( string $marker, string $content = '' ): string {
		$pattern = "# BEGIN $marker.*?# END $marker";

		if ( empty( $content ) ) {
			return "/$pattern/s";
		}

		return str_replace( '.*?', "\n$content\n", $pattern );
	}
}
