<?php
namespace WPDevAssist;

defined( 'ABSPATH' ) || exit;

class WPDebug {
	protected const CONFIG_FILE_PATH               = ABSPATH . 'wp-config.php';
	protected const ORIGINAL_DEBUG_VALUE_KEY       = KEY . '_original_wp_debug_value';
	protected const ORIGINAL_DEBUG_VALUE_DEFAULT   = 'disabled';
	protected const ORIGINAL_LOG_VALUE_KEY         = KEY . '_original_wp_debug_log_value';
	protected const ORIGINAL_LOG_VALUE_DEFAULT     = 'disabled';
	protected const ORIGINAL_DISPLAY_VALUE_KEY     = KEY . '_original_wp_debug_display_value';
	protected const ORIGINAL_DISPLAY_VALUE_DEFAULT = 'disabled';

	public function __construct() {
		if ( ! is_admin() ) {
			return;
		}

		$this->render_notice();
		$this->toggle_debug_mode();
	}

	protected function render_notice(): void {
		$debug_enabled   = get_option( Setting::ENABLE_WP_DEBUG_KEY, Setting::ENABLE_WP_DEBUG_DEFAULT );
		$log_enabled     = get_option( Setting::ENABLE_WP_DEBUG_LOG_KEY, Setting::ENABLE_WP_DEBUG_LOG_DEFAULT );
		$display_enabled = get_option( Setting::ENABLE_WP_DEBUG_DISPLAY_KEY, Setting::ENABLE_WP_DEBUG_DISPLAY_DEFAULT );
		$is_dev_env      = Plugin\Env::is_dev();
		$condition_value = $is_dev_env ? 'yes' : 'no';
		$const_names     = '';

		if ( $debug_enabled !== $condition_value ) {
			$const_names .= '<code>WP_DEBUG</code>';
		}

		if ( $log_enabled !== $condition_value ) {
			$const_names .= ( $const_names ? ', ' : '' ) . '<code>WP_DEBUG_LOG</code>';
		}

		if ( $display_enabled !== $condition_value ) {
			$const_names .= ( $const_names ? ', ' : '' ) . '<code>WP_DEBUG_DISPLAY</code>';
		}

		if ( empty( $const_names ) ) {
			return;
		}

		if ( $is_dev_env ) {
			$message = sprintf(
				__( 'Disabled %s was detected in the development environment.', 'wpda-development-assistant' ),
				$const_names
			);
		} else {
			$message = sprintf(
				__( 'Enabled %1$s was detected in the production environment. %2$s', 'wpda-development-assistant' ),
				$const_names,
				'<b>' .
				__( 'Don\'t leave it enabled unless you are debugging to avoid the performance and security issues!', 'wpda-development-assistant' ) .
				'</b>'
			);
		}

		StatusNotice::render( $message );
	}

	protected function toggle_debug_mode(): void {
		$is_debug_setting_enabled   = 'yes' === get_option( Setting::ENABLE_WP_DEBUG_KEY, Setting::ENABLE_WP_DEBUG_DEFAULT );
		$is_log_setting_enabled     = 'yes' === get_option( Setting::ENABLE_WP_DEBUG_LOG_KEY, Setting::ENABLE_WP_DEBUG_DISPLAY_KEY );
		$is_display_setting_enabled = 'yes' === get_option( Setting::ENABLE_WP_DEBUG_DISPLAY_KEY, Setting::ENABLE_WP_DEBUG_DISPLAY_DEFAULT );

		if (
			static::is_debug_enabled() === $is_debug_setting_enabled &&
			static::is_debug_log_enabled() === $is_log_setting_enabled &&
			static::is_debug_display_enabled() === $is_display_setting_enabled
		) {
			return;
		}

		$config_content = static::read_config_content();

		if ( empty( $config_content ) ) {
			Plugin\Notice::add_transient( 'Can\'t read the ' . static::CONFIG_FILE_PATH, 'error' );
		}

		if ( static::is_debug_enabled() !== $is_debug_setting_enabled ) {
			$config_content = static::update_config_const(
				'WP_DEBUG',
				$is_debug_setting_enabled ? 'enabled' : 'disabled',
				$config_content
			);
		}

		if ( static::is_debug_log_enabled() !== $is_log_setting_enabled ) {
			$config_content = static::update_config_const(
				'WP_DEBUG_LOG',
				$is_log_setting_enabled ? 'enabled' : 'disabled',
				$config_content
			);
		}

		if ( static::is_debug_display_enabled() !== $is_display_setting_enabled ) {
			$config_content = static::update_config_const(
				'WP_DEBUG_DISPLAY',
				$is_display_setting_enabled ? 'enabled' : 'disabled',
				$config_content
			);
		}

		if ( empty( static::write_config_content( $config_content ) ) ) {
			Plugin\Notice::add_transient( 'Can\'t write the ' . static::CONFIG_FILE_PATH, 'error' );
		}
	}

	protected static function read_config_content(): string {
		if ( file_exists( static::CONFIG_FILE_PATH ) ) {
			$file     = fopen( static::CONFIG_FILE_PATH, 'r' ); // phpcs:ignore
			$response = '';

			fseek( $file, -1048576, SEEK_END );

			while ( ! feof( $file ) ) {
				$response .= fgets( $file );
			}

			fclose( $file ); // phpcs:ignore

			return $response;
		}

		return '';
	}

	protected static function update_config_const( string $name, string $value, string $config_content ): string {
		$search = array(
			"define( '" . $name . "', true );",
			"define( '" . $name . "', false );",
			"define('" . $name . "', true);",
			"define('" . $name . "', false);",
		);

		if ( 'enabled' === $value ) {
			$config_content = str_replace(
				$search,
				"define( '" . $name . "', true );",
				$config_content,
				$count
			);

			if ( $count ) {
				return $config_content;
			}

			return str_replace(
				'$table_prefix',
				"define('" . $name . "', true);" . "\r\n" . '$table_prefix', // phpcs:ignore
				$config_content
			);
		} elseif ( 'missing' === $value ) {
			return str_replace(
				$search,
				'',
				$config_content
			);
		}

		return str_replace( $search, "define( '" . $name . "', false );", $config_content );
	}

	protected static function write_config_content( string $content ): string {
		$output = error_log( '/*test*/', '3', static::CONFIG_FILE_PATH ); // phpcs:ignore

		if ( $output ) {
			unlink( static::CONFIG_FILE_PATH );
			error_log( $content, '3', static::CONFIG_FILE_PATH ); // phpcs:ignore
			chmod( static::CONFIG_FILE_PATH, 0600 );
		}

		return $output;
	}

	public static function is_debug_enabled(): bool {
		return defined( 'WP_DEBUG' ) && WP_DEBUG;
	}

	public static function is_debug_log_enabled(): bool {
		return defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG;
	}

	public static function is_debug_display_enabled(): bool {
		return defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY;
	}

	public static function store_original_config_const(): void {
		update_option(
			static::ORIGINAL_DEBUG_VALUE_KEY,
			defined( 'WP_DEBUG' ) ? ( WP_DEBUG ? 'enabled' : 'disabled' ) : 'missing'
		);
		update_option(
			static::ORIGINAL_LOG_VALUE_KEY,
			defined( 'WP_DEBUG_LOG' ) ? ( WP_DEBUG_LOG ? 'enabled' : 'disabled' ) : 'missing'
		);
		update_option(
			static::ORIGINAL_DISPLAY_VALUE_KEY,
			defined( 'WP_DEBUG_DISPLAY' ) ? ( WP_DEBUG_DISPLAY ? 'enabled' : 'disabled' ) : 'missing'
		);
	}

	public static function reset_config_const(): void {
		$config_content = static::read_config_content();

		if ( empty( $config_content ) ) {
			Plugin\Notice::add_transient( 'Can\'t read the ' . static::CONFIG_FILE_PATH, 'error' );
		}

		$config_content = static::update_config_const(
			'WP_DEBUG',
			get_option( static::ORIGINAL_DEBUG_VALUE_KEY, static::ORIGINAL_DEBUG_VALUE_DEFAULT ),
			$config_content
		);
		$config_content = static::update_config_const(
			'WP_DEBUG_LOG',
			get_option( static::ORIGINAL_LOG_VALUE_KEY, static::ORIGINAL_LOG_VALUE_DEFAULT ),
			$config_content
		);
		$config_content = static::update_config_const(
			'WP_DEBUG_DISPLAY',
			get_option( static::ORIGINAL_DISPLAY_VALUE_KEY, static::ORIGINAL_DISPLAY_VALUE_DEFAULT ),
			$config_content
		);

		if ( empty( static::write_config_content( $config_content ) ) ) {
			Plugin\Notice::add_transient( 'Can\'t write the ' . static::CONFIG_FILE_PATH, 'error' );

			return;
		}

		delete_option( static::ORIGINAL_DEBUG_VALUE_KEY );
		delete_option( static::ORIGINAL_LOG_VALUE_KEY );
		delete_option( static::ORIGINAL_DISPLAY_VALUE_KEY );
	}
}
