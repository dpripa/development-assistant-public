<?php
namespace WPDevAssist;

defined( 'ABSPATH' ) || exit;

class StatusNotice {
	public static function render( string $message, string $level = 'default' ): void {
		if ( ! static::is_destination_page() ) {
			return;
		}

		$content  = '<div style="font-weight: bold;">' . __( 'WPDA', 'wpda-development-assistant' ) . '</div>';
		$content .= $message;

		Plugin\Notice::render( $content, "$level wpda-status" );
	}

	protected static function is_destination_page(): bool {
		global $pagenow;

		return 'index.php' === $pagenow ||
			'plugins.php' === $pagenow ||
			(
				'admin.php' === $pagenow &&
				isset( $_GET['page'] ) && KEY === $_GET['page'] // phpcs:ignore
			);
	}
}
