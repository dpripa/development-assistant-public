<?php
namespace WPDevAssist\Setting;

use Exception;
use WPDevAssist\ActionQuery;
use WPDevAssist\Asset;
use WPDevAssist\Fs;
use WPDevAssist\Model\Link;
use WPDevAssist\Notice;
use WPDevAssist\Setting;
use const WPDevAssist\KEY;

defined( 'ABSPATH' ) || exit;

class DebugLog extends Page {
	public const KEY = KEY . '_debug_log';

	protected const LOG_FILE_PATH = WP_CONTENT_DIR . '/debug.log';

	protected const ORIGINAL_EXISTENCE_KEY     = KEY . '_original_debug_log_existence';
	protected const ORIGINAL_EXISTENCE_DEFAULT = 'yes';

	public const DELETE_LOG_QUERY_KEY   = KEY . '_delete_debug_log';
	public const DOWNLOAD_LOG_QUERY_KEY = KEY . '_download_debug_log';

	protected const NORMAL_SIZE = MB_IN_BYTES * 10;

	public function __construct() {
		parent::__construct();
		ActionQuery::add( static::DELETE_LOG_QUERY_KEY, array( $this, 'handle_delete_file' ) );
		ActionQuery::add( static::DOWNLOAD_LOG_QUERY_KEY, array( $this, 'handle_download_file' ) );
		add_action( 'admin_head', array( $this, 'render_notice_disabled_logs' ) );
	}

	public function add_page(): void {
		$page_title = __( 'debug.log', 'development-assistant' );

		add_submenu_page(
			KEY,
			$page_title,
			$page_title,
			'administrator',
			static::KEY,
			array( $this, 'render_page' )
		);
	}

	public function add_sections(): void {}

	public function render_content(): void {
		?>
		<div class="da-debug-log">
			<?php $this->render_actions(); ?>
			<div class="da-debug-log__container">
				<?php
				if ( static::is_file_exists() ) {
					$log = Fs::read( static::LOG_FILE_PATH );

					if ( $log ) {
						?>
						<div class="da-debug-log__content">
							<?php echo wp_kses( nl2br( $log ), array( 'br' => array() ) ); ?>
						</div>
						<?php
					} else {
						?>
						<div class="da-debug-log__content da-debug-log__content_error">
							<?php echo esc_html__( 'Can\'t read the log file.', 'development-assistant' ); ?>
						</div>
						<?php
					}
					?>
				<?php } else { ?>
					<div class="da-debug-log__content da-debug-log__content_empty">
						<?php echo esc_html__( 'Log is empty.', 'development-assistant' ); ?>
					</div>
				<?php } ?>
			</div>
		</div>
		<?php
	}

	protected function render_actions(): void {
		$link_delete_log   = ActionQuery::get_url( static::DELETE_LOG_QUERY_KEY, static::get_page_url() );
		$link_download_log = ActionQuery::get_url( static::DOWNLOAD_LOG_QUERY_KEY, static::get_page_url() );
		$file_exists       = static::is_file_exists();
		?>
		<ul class="da-debug-log__actions">
			<li>
					<a
						class="button button-primary <?php echo $file_exists ? '' : 'button-disabled'; ?>"
						<?php echo $file_exists ? 'href="' . esc_url( $link_download_log ) . '"' : ''; ?>
					>
						<?php echo esc_html__( 'Download', 'development-assistant' ); ?>
					</a>
				</li>
				<li>
					<?php
					( new Link(
						__( 'Delete file', 'development-assistant' ),
						$link_delete_log,
						static::get_deletion_confirmation_massage(),
						false,
						'button button-secondary' . $file_exists ? '' : ' button-disabled'
					) )->render();
					?>
				</li>
				<?php
				if ( static::is_file_exists() ) {
					?>
					<li>
						<?php $this->render_file_size(); ?>
					</li>
					<?php
				}

				if ( 'yes' === get_option( Setting\DevEnv::ENABLE_KEY, Setting\DevEnv::ENABLE_DEFAULT ) ) {
					?>
					<li>
						<?php $this->render_direct_access_status(); ?>
					</li>
				<?php } ?>
		</ul>
		<?php
	}

	protected function render_file_size(): void {
		$file_size = filesize( static::LOG_FILE_PATH );
		$is_large  = static::NORMAL_SIZE <= $file_size;
		?>
		<div class="da-debug-log__status <?php echo $is_large ? 'da-debug-log__status_error' : ''; ?>">
			<span class="dashicons dashicons-cloud"></span>
			<span>
				<?php
				echo esc_html( size_format( $file_size ) );

				if ( $is_large ) {
					echo ' (' . esc_html__( 'size is large', 'development-assistant' ) . ')';
				}
				?>
			</span>
		</div>
		<?php
	}

	protected function render_direct_access_status(): void {
		$is_direct_access_disabled = 'yes' === get_option( Setting::DISABLE_DIRECT_ACCESS_TO_LOG_KEY, Setting::DISABLE_DIRECT_ACCESS_TO_LOG_DEFAULT );

		if ( $is_direct_access_disabled ) {
			$icon_class = 'dashicons-lock';
			$message    = esc_html__( 'Direct access to the file via %s is disabled.', 'development-assistant' );
		} else {
			$icon_class = 'dashicons-unlock';
			$message    = esc_html__( 'Direct access to the file via %s is enabled.', 'development-assistant' );
			$url        = ActionQuery::get_url( Setting::DISABLE_DIRECT_ACCESS_TO_LOG_QUERY_KEY );
			$message   .= ' <a href="' . esc_url( $url ) . '">' . esc_html__( 'Disable', 'development-assistant' ) . '</a>';
		}
		?>
		<div class="da-debug-log__status <?php echo $is_direct_access_disabled ? 'da-debug-log__status_success' : 'da-debug-log__status_error'; ?>">
			<span class="dashicons <?php echo esc_attr( $icon_class ); ?>"></span>
			<span>
				<?php
				echo wp_kses_post(
					sprintf(
						$message,
						'<a href="' . static::get_public_url() . '" target="_blank">' . esc_html__( 'the link', 'development-assistant' ) . '</a>'
					)
				);
				?>
			</span>
		</div>
		<?php
	}

	public static function get_deletion_confirmation_massage(): string {
		return __( 'Are you sure to delete the debug.log file? This action is irreversible.', 'development-assistant' );
	}

	public function handle_delete_file(): void {
		static::delete_file();
		Notice::add_transient( 'Log file deleted.', 'success' );
	}

	public function handle_download_file(): void {
		$filename = str_replace(
			'.',
			'_',
			str_replace( array( 'http://', 'https://' ), '', home_url() )
		) . '_debug.log';

		header( 'Content-Type: text/plain' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Content-Length: ' . filesize( static::LOG_FILE_PATH ) );
		flush();
		readfile( static::LOG_FILE_PATH ); // phpcs:ignore

		exit;
	}

	public static function store_original_file_existence(): void {
		update_option(
			static::ORIGINAL_EXISTENCE_KEY,
			static::is_file_exists() ? 'yes' : 'no'
		);
	}

	public static function delete_file_if_originally_not_exists(): void {
		if (
			'yes' === get_option( static::ORIGINAL_EXISTENCE_KEY, static::ORIGINAL_EXISTENCE_DEFAULT ) &&
			static::is_file_exists()
		) {
			static::delete_file();
		}

		delete_option( static::ORIGINAL_EXISTENCE_KEY );
	}

	public static function is_file_exists(): bool {
		return file_exists( static::LOG_FILE_PATH );
	}

	protected static function delete_file(): void {
		if ( ! current_user_can( 'administrator' ) || ! static::is_file_exists() ) {
			return;
		}

		if ( ! unlink( static::LOG_FILE_PATH ) ) {
			Notice::add_transient( 'Can\'t delete the ' . static::LOG_FILE_PATH . '.', 'error' );
		}
	}

	public static function get_public_url(): string {
		return WP_CONTENT_URL . '/debug.log';
	}

	public function render_notice_disabled_logs(): void {
		if (
			! static::is_current() ||
			(
				'yes' === get_option( Setting::ENABLE_WP_DEBUG_KEY, Setting::ENABLE_WP_DEBUG_DEFAULT ) &&
				'yes' === get_option( Setting::ENABLE_WP_DEBUG_LOG_KEY, Setting::ENABLE_WP_DEBUG_LOG_DEFAULT )
			)
		) {
			return;
		}

		$message  = __( 'Logging is disabled.', 'development-assistant' );
		$url      = ActionQuery::get_url( Setting::ENABLE_DEBUG_LOG_QUERY_KEY );
		$message .= ' <a href="' . $url . '">' . __( 'Enable', 'development-assistant' ) . '</a>';

		Notice::render( $message, 'error', false );
	}

	/**
	 * @throws Exception
	 */
	public function enqueue_assets(): void {
		if ( ! static::is_current() ) {
			return;
		}

		Asset::enqueue_style( 'debug-log' );
	}
}
