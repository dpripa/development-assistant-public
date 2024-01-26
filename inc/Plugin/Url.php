<?php
namespace WPDevAssist\Plugin;

defined( 'ABSPATH' ) || exit;

class Url {
	public static function get_current(): string {
		$path = add_query_arg( null, null );

		return static::get_home( $path );
	}

	public static function get_home( string $path = '/' ): string {
		return home_url( $path );
	}

	public static function get_admin( string $slug = '', ?int $blog_id = 0 ): string {
		if ( 0 === $blog_id ) {
			$blog_id = is_multisite() ? get_current_blog_id() : null;
		}

		return get_admin_url( $blog_id, $slug ? "$slug.php" : '' );
	}
}
