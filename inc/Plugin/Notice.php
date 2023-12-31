<?php
namespace WPDevAssist\Plugin;

use const WPDevAssist\KEY;

defined( 'ABSPATH' ) || exit;

class Notice {
	protected static $key = KEY . '_admin_transient_notices';

	public function __construct() {
		add_action( 'admin_init', array( $this, 'render_transients' ) );
	}

	public function render_transients(): void {
		$notices = static::get_transients();

		if ( empty( $notices ) ) {
			return;
		}

		foreach ( $notices as $level => $messages ) {
			foreach ( $messages as $message ) {
				static::render( $message, $level );
			}
		}

		delete_option( static::$key );
	}

	protected static function get_transients(): array {
		return get_option( static::$key, array() );
	}

	public static function add_transient( string $message, string $level = 'warning' ): void {
		$notices             = static::get_transients();
		$notices[ $level ][] = $message;

		update_option( static::$key, $notices );
	}

	public static function render( string $message, string $level = 'warning' ): void {
		add_action(
			'admin_notices',
			function () use ( $message, $level ): void {
				?>
				<div class="notice notice-<?php echo esc_attr( $level ); ?> is-dismissible" style="padding-top: 10px; padding-bottom: 10px;">
					<?php echo wp_kses_post( $message ); ?>
				</div>
				<?php
			}
		);
	}
}
