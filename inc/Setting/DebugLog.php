<?php
namespace WPDevAssist\Setting;

use WPDevAssist\Plugin;
use const WPDevAssist\KEY;

defined( 'ABSPATH' ) || exit;

class DebugLog {
	protected const KEY                        = KEY . '_debug_log';
	protected const LOG_FILE_PATH              = WP_CONTENT_DIR . '/debug.log';
	protected const ORIGINAL_EXISTENCE_KEY     = KEY . '_original_debug_log_existence';
	protected const ORIGINAL_EXISTENCE_DEFAULT = 'yes';
	protected const DELETE_LOG_QUERY_KEY       = KEY . '_delete_debug_log';

	public function __construct() {
		if ( file_exists( static::LOG_FILE_PATH ) ) {
			add_action( 'admin_menu', array( $this, 'add_page' ) );
			add_action( 'admin_init', array( $this, 'delete_file_by_link' ) );
		}
	}

	public function add_page(): void {
		add_submenu_page(
			KEY,
			__( 'debug.log', 'wpda-development-assistant' ),
			__( 'debug.log', 'wpda-development-assistant' ),
			'manage_options',
			static::KEY,
			array( $this, 'render_page' )
		);
	}

	public function render_page(): void {
		$link_delete_log = static::get_page_url() . '&' . static::DELETE_LOG_QUERY_KEY . '=yes';
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<a
				href="<?php echo esc_url( $link_delete_log ); ?>"
				onclick="return confirm('<?php echo esc_html__( 'Are you sure?', 'wpda-development-assistant' ); ?>')"
			>
				<?php echo esc_html__( 'Delete file', 'wpda-development-assistant' ); ?>
			</a>
			<div style="margin-top: 10px; font-family: monospace;">
				<?php
				echo wp_kses( nl2br( file_get_contents( static::LOG_FILE_PATH ) ), array( 'br' => array() ) ); // phpcs:ignore
				?>
			</div>
		</div>
		<?php
	}

	public function delete_file_by_link(): void {
		if (
			empty( $_GET[ static::DELETE_LOG_QUERY_KEY ] ) || // phpcs:ignore
			'yes' !== sanitize_text_field( wp_unslash( $_GET[ static::DELETE_LOG_QUERY_KEY ] ) ) // phpcs:ignore
		) {
			return;
		}

		static::delete_file();
		wp_safe_redirect( static::get_page_url() );
	}

	public static function store_original_file_existence(): void {
		update_option(
			static::ORIGINAL_EXISTENCE_KEY,
			file_exists( static::LOG_FILE_PATH ) ? 'yes' : 'no'
		);
	}

	public static function delete_file_if_originally_not_exists(): void {
		if (
			'yes' !== get_option( static::ORIGINAL_EXISTENCE_KEY, static::ORIGINAL_EXISTENCE_DEFAULT ) ||
			! file_exists( static::LOG_FILE_PATH )
		) {
			return;
		}

		static::delete_file();
		delete_option( static::ORIGINAL_EXISTENCE_KEY );
	}

	protected static function delete_file(): void {
		if ( ! current_user_can( 'manage_options' ) || ! file_exists( static::LOG_FILE_PATH ) ) {
			return;
		}

		if ( ! unlink( static::LOG_FILE_PATH ) ) {
			Plugin\Notice::add_transient( 'Can\'t delete the ' . static::LOG_FILE_PATH, 'error' );
		}
	}

	protected function get_page_url(): string {
		return Plugin\Url::get_admin( 'admin' ) . '?page=' . KEY;
	}
}
