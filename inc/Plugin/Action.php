<?php
namespace WPDevAssist\Plugin;

use const WPDevAssist\KEY;

defined( 'ABSPATH' ) || exit;

class Action {
	protected const KEY   = KEY;
	protected const TYPES = array(
		'post' => 'admin_post',
		'ajax' => 'wp_ajax',
	);
	protected const URLS  = array(
		'post' => 'admin-post',
		'ajax' => 'admin-ajax',
	);

	public static function add_post( string $name, callable $callback ): void {
		static::add( 'post', $name, $callback );
	}

	public static function get_post_url( string $name = '' ): string {
		return static::get_url( 'post', $name );
	}

	public static function add_ajax( string $name, callable $callback ): void {
		static::add( 'ajax', $name, $callback );
	}

	public static function get_ajax_url( string $name = '' ): string {
		return static::get_url( 'ajax', $name );
	}

	protected static function add( string $type, string $name, callable $callback ): void {
		add_action( static::TYPES[ $type ] . '_' . static::KEY . '_' . $name, $callback );
		add_action( static::TYPES[ $type ] . '_nopriv_' . static::KEY . '_' . $name, $callback );
	}

	protected static function get_url( string $type, string $name = '' ): string {
		$url = Url::get_admin( static::URLS[ $type ] );

		if ( $name ) {
			if ( ! has_action( static::TYPES[ $type ] . '_' . static::KEY . '_' . $name ) ) {
				throw new \Exception( "The \"$name\" action isn't defined" );
			}

			return add_query_arg( $url, array( 'action' => static::KEY . '_' . $name ) );
		}

		return $url;
	}
}
