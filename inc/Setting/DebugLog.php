<?php
namespace WPDevAssist\Setting;

use WPDevAssist\Plugin;
use WPDevAssist\Setting;
use const WPDevAssist\KEY;

defined( 'ABSPATH' ) || exit;

class DebugLog {
	protected const KEY                        = KEY . '_debug_log';
	protected const LOG_FILE_PATH              = WP_CONTENT_DIR . '/debug.log';
	protected const ORIGINAL_EXISTENCE_KEY     = KEY . '_original_debug_log_existence';
	protected const ORIGINAL_EXISTENCE_DEFAULT = 'yes';
	protected const DELETE_LOG_QUERY_KEY       = KEY . '_delete_debug_log';

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_page' ) );
		add_action( 'admin_init', array( $this, 'handle_delete_file' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	public function add_page(): void {
		add_submenu_page(
			KEY,
			__( 'debug.log', 'development-assistant' ),
			__( 'debug.log', 'development-assistant' ),
			'manage_options',
			static::KEY,
			array( $this, 'render_page' )
		);
	}

	public function render_page(): void {
		$link_delete_log   = wp_nonce_url(
			static::get_page_url() . '&' . static::DELETE_LOG_QUERY_KEY . '=yes',
			static::DELETE_LOG_QUERY_KEY
		);
		$link_download_log = content_url( 'debug.log' );
		$download_log_name = str_replace(
			'.',
			'_',
			str_replace( array( 'http://', 'https://' ), '', home_url() )
		) . '_debug.log';
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<?php if ( file_exists( static::LOG_FILE_PATH ) ) { ?>
				<ul class="da-debug-log-actions">
					<li>
						<a href="<?php echo esc_url( $link_download_log ); ?>" download="<?php echo esc_attr( $download_log_name ); ?>">
							<?php echo esc_html__( 'Download', 'development-assistant' ); ?>
						</a>
					</li>
					<li>
						<a
							href="<?php echo esc_url( $link_delete_log ); ?>"
							onclick="return confirm('<?php echo esc_html__( 'Are you sure?', 'development-assistant' ); ?>')"
						>
							<?php echo esc_html__( 'Delete file', 'development-assistant' ); ?>
						</a>
					</li>
				</ul>
			<?php } ?>
			<div style="margin-top: 10px;">
				<?php if ( file_exists( static::LOG_FILE_PATH ) ) { ?>
					<div style="font-family: monospace;">
						<?php echo wp_kses( nl2br( file_get_contents( static::LOG_FILE_PATH ) ), array( 'br' => array() ) ); // phpcs:ignore ?>
					</div>
				<?php } else { ?>
					<div style="font-style: italic;">
						<?php echo esc_html__( 'Log is empty.', 'development-assistant' ); ?>
					</div>
				<?php } ?>
			</div>
		</div>
		<?php
	}

	public function handle_delete_file(): void {
		if (
			empty( $_GET['_wpnonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), static::DELETE_LOG_QUERY_KEY ) ||
			empty( $_GET[ static::DELETE_LOG_QUERY_KEY ] ) ||
			'yes' !== sanitize_text_field( wp_unslash( $_GET[ static::DELETE_LOG_QUERY_KEY ] ) )
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
			'yes' === get_option( static::ORIGINAL_EXISTENCE_KEY, static::ORIGINAL_EXISTENCE_DEFAULT ) &&
			file_exists( static::LOG_FILE_PATH )
		) {
			static::delete_file();
		}

		delete_option( static::ORIGINAL_EXISTENCE_KEY );
	}

	protected static function delete_file(): void {
		if ( ! current_user_can( 'manage_options' ) || ! file_exists( static::LOG_FILE_PATH ) ) {
			return;
		}

		if ( ! unlink( static::LOG_FILE_PATH ) ) {
			Plugin\Notice::add_transient( 'Can\'t delete the ' . static::LOG_FILE_PATH . '.', 'error' );
		}
	}

	public static function get_page_url(): string {
		return Plugin\Url::get_admin( 'admin' ) . '?page=' . static::KEY;
	}

	public function enqueue_assets(): void {
		global $current_screen;

		if ( Setting::get_menu_title( true ) . '_page_' . static::KEY !== $current_screen->id ) {
			return;
		}

		Plugin\Asset::enqueue_style( 'debug-log' );
	}
}
