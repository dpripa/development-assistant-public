<?php
namespace WPDevAssist;

use Exception;

defined( 'ABSPATH' ) || exit;

class WPDebug {
	protected const CONFIG_FILE_PATH               = ABSPATH . 'wp-config.php';
	protected const ORIGINAL_DEBUG_VALUE_KEY       = KEY . '_original_wp_debug_value';
	protected const ORIGINAL_DEBUG_VALUE_DEFAULT   = 'disabled';
	protected const ORIGINAL_LOG_VALUE_KEY         = KEY . '_original_wp_debug_log_value';
	protected const ORIGINAL_LOG_VALUE_DEFAULT     = 'disabled';
	protected const ORIGINAL_DISPLAY_VALUE_KEY     = KEY . '_original_wp_debug_display_value';
	protected const ORIGINAL_DISPLAY_VALUE_DEFAULT = 'disabled';
	protected const HTACCESS_MARKER                = KEY . '_debug_log';

	/**
	 * @throws Exception
	 */
	public function __construct() {
		if ( ! is_admin() ) {
			return;
		}

		$this->toggle_debug_mode();

		add_action( 'update_option_' . Setting::DISABLE_DIRECT_ACCESS_TO_LOG_KEY, array( $this, 'replace_htaccess_directives' ), 10, 2 );
	}

	/**
	 * @throws Exception
	 */
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
			Notice::add_transient(
				sprintf(
					__( 'Can\'t read the %s.', 'development-assistant' ),
					static::CONFIG_FILE_PATH
				),
				'error'
			);

			return;
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

		static::write_config_content( $config_content );
	}

	/**
	 * @throws Exception
	 */
	protected static function update_config_const( string $name, string $value, string $config_content ): string {
		$search = array(
			"define( '" . $name . "', true );",
			"define( '" . $name . "', false );",
			"define('" . $name . "', true);",
			"define('" . $name . "', false);",
			'const ' . $name . ' = true;',
			'const ' . $name . ' = false;',
			'const ' . $name . '=true;',
			'const ' . $name . '=false;',
		);

		switch ( $value ) {
			case 'disabled':
				return str_replace( $search, "define( '" . $name . "', false );", $config_content );

			case 'enabled':
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
					"define( '" . $name . "', true );" . "\r\n" . '$table_prefix', // phpcs:ignore
					$config_content
				);

			case 'missing':
				return str_replace(
					$search,
					'',
					$config_content
				);

			default:
				throw new Exception( "\"$value\" is not an allowed value" );
		}
	}

	/**
	 * @return string|bool
	 */
	protected static function read_config_content() {
		return Fs::read( static::CONFIG_FILE_PATH );
	}

	protected static function write_config_content( string $content ): bool {
		return Fs::write( static::CONFIG_FILE_PATH, $content );
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

	/**
	 * @throws Exception
	 */
	public static function reset_config_const(): void {
		$config_content = static::read_config_content();

		if ( empty( $config_content ) ) {
			Notice::add_transient(
				sprintf(
					__( 'Can\'t read the %s.', 'development-assistant' ),
					static::CONFIG_FILE_PATH
				),
				'error'
			);

			return;
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

		if ( ! static::write_config_content( $config_content ) ) {
			return;
		}

		delete_option( static::ORIGINAL_DEBUG_VALUE_KEY );
		delete_option( static::ORIGINAL_LOG_VALUE_KEY );
		delete_option( static::ORIGINAL_DISPLAY_VALUE_KEY );
	}

	public function replace_htaccess_directives( string $old_value, string $value ): void {
		if ( 'yes' === $value ) {
			if ( ! static::add_htaccess_directives() ) {
				Notice::add_transient(
					__( 'Can\'t add the directives to the .htaccess file', 'development-assistant' ),
					'error'
				);
			}

			return;
		}

		if ( ! static::remove_htaccess_directives() ) {
			Notice::add_transient(
				__( 'Can\'t remove the directives from the .htaccess file', 'development-assistant' ),
				'error'
			);
		}
	}

	public static function add_htaccess_directives(): bool {
		return Htaccess::replace(
			static::HTACCESS_MARKER,
			'<If "%{REQUEST_URI} =~ m#^/wp-content/debug.log#">
			    <IfModule mod_authz_core.c>
					Require all denied
				</IfModule>
				<IfModule !mod_authz_core.c>
					Order deny,allow
					Deny from all
				</IfModule>
			</If>'
		);
	}

	public static function remove_htaccess_directives(): bool {
		return Htaccess::remove( static::HTACCESS_MARKER );
	}
}
