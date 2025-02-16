<?php
namespace WPDevAssist\Setting;

defined( 'ABSPATH' ) || exit;

abstract class Tab extends BasePage {
	public const PAGE_KEY = '';

	abstract public static function get_title(): string;

	public static function get_url(): string {
		return add_query_arg(
			array(
				'page' => static::PAGE_KEY,
				'tab'  => static::KEY,
			),
			get_admin_url( null, 'admin.php' )
		);
	}

	public static function is_current(): bool {
		return isset( $_GET['page'] ) && static::PAGE_KEY === sanitize_text_field( wp_unslash( $_GET['page'] ) ) && // phpcs:ignore
			isset( $_GET['tab'] ) && static::KEY === sanitize_text_field( wp_unslash( $_GET['tab'] ) ); // phpcs:ignore
	}
}
