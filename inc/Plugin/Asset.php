<?php
namespace WPDevAssist\Plugin;

use const WPDevAssist\KEY;

defined( 'ABSPATH' ) || exit;

class Asset {
	protected const KEY        = KEY;
	protected const ASSET_DIR  = 'asset';
	protected const SCRIPT_DIR = 'script';
	protected const STYLE_DIR  = 'style';
	protected const POSTFIX    = '.min';

	public static function enqueue_script( string $name, array $deps = array(), array $args = array(), bool $in_footer = true ): void {
		$key      = static::get_key( $name );
		$filename = $name . static::POSTFIX . '.js';
		$rel      = static::ASSET_DIR . '/' . static::SCRIPT_DIR . '/' . $filename;
		$url      = static::get_url( $rel );
		$path     = static::get_path( $rel );

		if ( ! file_exists( $path ) ) {
			throw new \Exception( "The \"$path\" script asset file does not exist" );
		}

		wp_enqueue_script( $key, $url, $deps, filemtime( $path ), $in_footer );

		if ( $args ) {
			wp_localize_script( $key, $key, $args );
		}
	}

	public static function enqueue_inline_script( string $parent_name, string $js_code, string $position = 'after' ): void {
		wp_add_inline_script( static::KEY . "_$parent_name", $js_code, $position );
	}

	public static function enqueue_style( string $name, array $deps = array(), /* ?string|?array */ $addition = null ): void { // phpcs:ignore
		$key      = static::get_key( $name );
		$filename = $name . static::POSTFIX . '.css';
		$rel      = static::ASSET_DIR . '/' . static::STYLE_DIR . '/' . $filename;
		$url      = static::get_url( $rel );
		$path     = static::get_path( $rel );

		if ( ! file_exists( $path ) ) {
			throw new \Exception( "The \"$path\" style asset file does not exist" );
		}

		wp_enqueue_style( $key, $url, $deps, filemtime( $path ) );

		if ( is_string( $addition ) ) {
			wp_add_inline_style( $key, $addition );

		} elseif ( is_array( $addition ) ) {
			$css_vars = ':root{';

			foreach ( $addition as $var_name => $var_val ) {
				$css_vars .= '--' . str_replace( '_', '-', static::KEY . "_$var_name" ) . ':' . $var_val . ';';
			}

			wp_add_inline_style( $key, "$css_vars}" );

		} elseif ( ! is_null( $addition ) ) {
			throw new \Exception( 'The $addition parameter must be a string, array or null' );
		}
	}

	public static function enqueue_external_script( string $name, string $url, bool $in_footer = true ): void {
		wp_enqueue_script( static::get_key( $name ), $url, false, null, $in_footer ); // phpcs:ignore
	}

	public static function enqueue_external_style( string $name, string $url ): void {
		wp_enqueue_style( static::get_key( $name ), $url, false, null ); // phpcs:ignore
	}

	protected static function get_url( string $rel ): string {
		return Fs::get_url( $rel );
	}

	protected static function get_path( string $rel ): string {
		return Fs::get_path( $rel );
	}

	protected static function get_key( string $name ): string {
		return static::KEY . '_' . str_replace( '-', '_', $name );
	}
}
