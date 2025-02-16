<?php
namespace WPDevAssist;

defined( 'ABSPATH' ) || exit;

class Notice {
	protected const KEY = KEY . '_admin_transient_notices';

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

		delete_option( static::KEY );
	}

	protected static function get_transients(): array {
		return get_option( static::KEY, array() );
	}

	public static function add_transient( string $message, string $level = 'warning' ): void {
		$notices             = static::get_transients();
		$notices[ $level ][] = $message;

		update_option( static::KEY, $notices );
	}

	public static function render( string $message, string $level = 'warning', bool $is_dismissible = true ): void {
		add_action(
			'admin_notices',
			function () use ( $message, $level, $is_dismissible ): void {
				?>
				<div
					class="notice notice-<?php echo esc_attr( $level ) . ( $is_dismissible ? ' is-dismissible' : '' ); ?>"
					style="padding-top: 10px; padding-bottom: 10px;"
				>
					<?php echo wp_kses_post( $message ); ?>
				</div>
				<?php
			}
		);
	}

	public static function reset(): void {
		delete_option( static::KEY );
	}
}
