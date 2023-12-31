<?php
namespace WPDevAssist;

defined( 'ABSPATH' ) || exit;

class StatusNotice {
	public function __construct() {
		if ( ! static::is_destination_page() ) {
			return;
		}

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	public static function render( string $message, string $level = 'default' ): void {
		if ( ! static::is_destination_page() ) {
			return;
		}

		$content  = '<div style="font-weight: bold;">' . __( 'WPDA', KEY ) . '</div>';
		$content .= $message;

		Plugin\Notice::render( $content, "$level wpda-status" );
	}

	public function enqueue_assets(): void {
		Plugin\Asset::enqueue_style( 'status-notice' );
		Plugin\Asset::enqueue_script( 'status-notice' );
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
