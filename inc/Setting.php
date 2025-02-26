<?php
namespace WPDevAssist;

use WPDevAssist\Setting\Control;
use WPDevAssist\Setting\DebugLog;

defined( 'ABSPATH' ) || exit;

class Setting extends Setting\Page {
	public const KEY = KEY;

	public const ENABLE_WP_DEBUG_KEY                      = KEY . '_enable_wp_debug';
	public const ENABLE_WP_DEBUG_DEFAULT                  = 'no';
	public const ENABLE_WP_DEBUG_LOG_KEY                  = KEY . '_enable_wp_debug_log';
	public const ENABLE_WP_DEBUG_LOG_DEFAULT              = 'no';
	public const ENABLE_WP_DEBUG_DISPLAY_KEY              = KEY . '_enable_wp_debug_display';
	public const ENABLE_WP_DEBUG_DISPLAY_DEFAULT          = 'no';
	public const DISABLE_DIRECT_ACCESS_TO_LOG_KEY         = KEY . '_disable_direct_access_to_log';
	public const DISABLE_DIRECT_ACCESS_TO_LOG_DEFAULT     = 'no';
	public const ENABLE_ASSISTANT_KEY                     = KEY . '_enable_assistant';
	public const ENABLE_ASSISTANT_DEFAULT                 = 'yes';
	public const ASSISTANT_OPENED_ON_WP_DASHBOARD_KEY     = KEY . '_expanded_on_wp_dashboard';
	public const ASSISTANT_OPENED_ON_WP_DASHBOARD_DEFAULT = 'yes';
	public const ACTIVE_PLUGINS_FIRST_KEY                 = KEY . '_active_plugins_first';
	public const ACTIVE_PLUGINS_FIRST_DEFAULT             = 'yes';
	public const RESET_KEY                                = KEY . '_reset';
	public const RESET_DEFAULT                            = 'yes';

	protected const SETTING_KEYS = array(
		self::ENABLE_WP_DEBUG_KEY,
		self::ENABLE_WP_DEBUG_LOG_KEY,
		self::ENABLE_WP_DEBUG_DISPLAY_KEY,
		self::DISABLE_DIRECT_ACCESS_TO_LOG_KEY,
		self::ENABLE_ASSISTANT_KEY,
		self::ASSISTANT_OPENED_ON_WP_DASHBOARD_KEY,
		self::ACTIVE_PLUGINS_FIRST_KEY,
		self::RESET_KEY,
	);

	public const TOGGLE_DEBUG_MODE_QUERY_KEY            = KEY . '_toggle_debug_mode';
	public const DISABLE_DIRECT_ACCESS_TO_LOG_QUERY_KEY = KEY . '_disable_direct_access_to_log';
	public const ENABLE_DEBUG_LOG_QUERY_KEY             = KEY . '_enable_log';
	public const DISABLE_DEBUG_DISPLAY_QUERY_KEY        = KEY . '_disable_debug_display';

	public const PAGE_TITLE_HOOK = KEY . '_settings_page_title';

	public function __construct() {
		parent::__construct();
		ActionQuery::add( static::TOGGLE_DEBUG_MODE_QUERY_KEY, array( $this, 'handle_toggle_debug_mode' ) );
		ActionQuery::add( static::DISABLE_DIRECT_ACCESS_TO_LOG_QUERY_KEY, array( $this, 'handle_disable_direct_access_to_log' ) );
		ActionQuery::add( static::DISABLE_DEBUG_DISPLAY_QUERY_KEY, array( $this, 'handle_disable_debug_display' ) );
		ActionQuery::add( static::ENABLE_DEBUG_LOG_QUERY_KEY, array( $this, 'handle_enable_debug_log' ) );

		new Setting\DebugLog();
		new Setting\SupportUser();
	}

	public function add_page(): void {
		$page_title = apply_filters(
			static::PAGE_TITLE_HOOK,
			__( 'Development Assistant', 'development-assistant' )
		);

		add_menu_page(
			$page_title,
			static::get_toplevel_title(),
			'administrator',
			KEY,
			array( $this, 'render_page' ),
			'dashicons-pets',
			999
		);
		add_submenu_page(
			KEY,
			$page_title,
			__( 'Settings', 'development-assistant' ),
			'administrator',
			KEY,
			array( $this, 'render_page' )
		);
	}

	protected static function get_tabs(): array {
		return array(
			Setting\DevEnv::class,
		);
	}

	public function add_sections(): void {
		$this->add_wp_debug_section( KEY . '_debug' );
		$this->add_assistant_section( KEY . '_assistant' );
		$this->add_plugin_screen_section( KEY . '_plugins_screen' );
		$this->add_reset_section( KEY . '_reset' );
	}

	protected function add_wp_debug_section( string $section_key ): void {
		$this->add_section(
			$section_key,
			esc_html__( 'WP Debug', 'development-assistant' ),
			array( $this, 'render_wp_debug_description' )
		);
		$this->add_setting(
			$section_key,
			static::ENABLE_WP_DEBUG_KEY,
			wp_kses( __( 'Enable <code>WP_DEBUG</code>', 'development-assistant' ), array( 'code' => array() ) ),
			Control\Checkbox::class,
			static::ENABLE_WP_DEBUG_DEFAULT
		);
		$this->add_setting(
			$section_key,
			static::ENABLE_WP_DEBUG_LOG_KEY,
			wp_kses( __( 'Enable <code>WP_DEBUG_LOG</code>', 'development-assistant' ), array( 'code' => array() ) ),
			Control\Checkbox::class,
			static::ENABLE_WP_DEBUG_LOG_DEFAULT
		);

		$args = array();

		if ( 'yes' !== get_option( Setting\DevEnv::ENABLE_KEY, Setting\DevEnv::ENABLE_DEFAULT ) ) {
			$args['description'] = '<b class="da-setting__error-text">' . esc_html__( 'Warning!', 'development-assistant' ) . '</b> ' . wp_kses( __( 'Enabling error display may cause the entire interface blocking due to the display of these error messages, as well as a critical security issues. <b>Highly recommended to keep it disabled in production environment.</b>', 'development-assistant' ), array( 'b' => array() ) );
		}

		$this->add_setting(
			$section_key,
			static::ENABLE_WP_DEBUG_DISPLAY_KEY,
			wp_kses( __( 'Enable <code>WP_DEBUG_DISPLAY</code>', 'development-assistant' ), array( 'code' => array() ) ),
			Control\Checkbox::class,
			static::ENABLE_WP_DEBUG_DISPLAY_DEFAULT,
			$args
		);

		$args = array(
			'description' => sprintf(
				wp_kses( __( 'Public access via %s to the <code>debug.log</code> file will be disabled.', 'development-assistant' ), array( 'code' => array() ) ),
				'<a href="' . esc_url( DebugLog::get_public_url() ) . '" target="_blank">' . esc_html__( 'the link', 'development-assistant' ) . '</a>'
			),
		);

		if ( ! Htaccess::exists() ) {
			$args['disabled']    = true;
			$args['description'] = wp_kses( __( '<code>.htaccess</code> file is required (only supported on Apache HTTP Server).', 'development-assistant' ), array( 'code' => array() ) );
		}

		$this->add_setting(
			$section_key,
			static::DISABLE_DIRECT_ACCESS_TO_LOG_KEY,
			wp_kses( __( 'Disable direct access', 'development-assistant' ), array( 'code' => array() ) ),
			Control\Checkbox::class,
			static::DISABLE_DIRECT_ACCESS_TO_LOG_DEFAULT,
			$args
		);
	}

	protected function render_wp_debug_description(): void {
		echo wp_kses( __( 'These options allow you to safely control the debug constants without the need to manually edit the <code>wp-config.php</code>.', 'development-assistant' ), array( 'code' => array() ) );
		?>
		<div style="margin-top: 5px;">
			<a href="<?php echo esc_url( Setting\DebugLog::get_page_url() ); ?>">
				<?php
				echo wp_kses( __( 'Go to <code>debug.log</code>', 'development-assistant' ), array( 'code' => array() ) );
				?>
			</a>
		</div>
		<?php
	}

	protected function add_assistant_section( string $section_key ): void {
		$this->add_section(
			$section_key,
			esc_html__( 'Assistant Panel', 'development-assistant' )
		);
		$this->add_setting(
			$section_key,
			static::ENABLE_ASSISTANT_KEY,
			esc_html__( 'Enable Assistant Panel', 'development-assistant' ),
			Control\Checkbox::class,
			static::ENABLE_ASSISTANT_DEFAULT
		);
		$this->add_setting(
			$section_key,
			static::ASSISTANT_OPENED_ON_WP_DASHBOARD_KEY,
			esc_html__( 'Opened by default on the WordPress Dashboard', 'development-assistant' ),
			Control\Checkbox::class,
			static::ASSISTANT_OPENED_ON_WP_DASHBOARD_DEFAULT
		);
	}

	protected function add_plugin_screen_section( string $section_key ): void {
		$this->add_section(
			$section_key,
			esc_html__( 'Plugins Screen', 'development-assistant' )
		);
		$this->add_setting(
			$section_key,
			static::ACTIVE_PLUGINS_FIRST_KEY,
			esc_html__( 'Show active plugins first', 'development-assistant' ),
			Control\Checkbox::class,
			static::ACTIVE_PLUGINS_FIRST_DEFAULT
		);
	}

	protected function add_reset_section( string $key ): void {
		$this->add_section(
			$key,
			esc_html__( 'Reset', 'development-assistant' )
		);
		$this->add_setting(
			$key,
			static::RESET_KEY,
			esc_html__( 'Reset plugin data when deactivated', 'development-assistant' ),
			Control\Checkbox::class,
			static::RESET_DEFAULT,
			array(
				'description' => sprintf(
					esc_html__( 'It\'ll make look like the plugin was never installed and will undo any possible changes that may have been made using it %s.', 'development-assistant' ),
					'<i>' . esc_html__( '(the only exception is deleted data or files, it cannot be recovered)', 'development-assistant' ) . '</i>'
				),
			),
		);
	}

	public static function add_default_options(): void {
		parent::add_default_options();

		if ( ! in_array( get_option( static::ENABLE_WP_DEBUG_KEY ), array( 'yes', 'no' ), true ) ) {
			update_option(
				static::ENABLE_WP_DEBUG_KEY,
				WPDebug::is_debug_enabled() ? 'yes' : 'no'
			);
		}

		if ( ! in_array( get_option( static::ENABLE_WP_DEBUG_LOG_KEY ), array( 'yes', 'no' ), true ) ) {
			update_option(
				static::ENABLE_WP_DEBUG_LOG_KEY,
				WPDebug::is_debug_log_enabled() ? 'yes' : 'no'
			);
		}

		if ( ! in_array( get_option( static::ENABLE_WP_DEBUG_DISPLAY_KEY ), array( 'yes', 'no' ), true ) ) {
			update_option(
				static::ENABLE_WP_DEBUG_DISPLAY_KEY,
				WPDebug::is_debug_display_enabled() ? 'yes' : 'no'
			);
		}
	}

	public function handle_toggle_debug_mode( array $data ): void {
		$value      = sanitize_text_field( wp_unslash( $data[ static::TOGGLE_DEBUG_MODE_QUERY_KEY ] ) );
		$is_dev_env = 'yes' === get_option( Setting\DevEnv::ENABLE_KEY, Setting\DevEnv::ENABLE_DEFAULT );

		if ( 'yes' !== $value && 'no' !== $value ) {
			return;
		}

		update_option( static::ENABLE_WP_DEBUG_KEY, $value );
		update_option( static::ENABLE_WP_DEBUG_LOG_KEY, $value );

		if ( $is_dev_env || 'yes' !== $value ) {
			update_option( static::ENABLE_WP_DEBUG_DISPLAY_KEY, $value );
		}

		if ( ! $is_dev_env && Htaccess::exists() && 'yes' === $value ) {
			update_option( static::DISABLE_DIRECT_ACCESS_TO_LOG_KEY, 'yes' );
		}

		if ( 'yes' === $value ) {
			$message = __( 'Debug mode enabled.', 'development-assistant' );
		} else {
			$message = __( 'Debug mode disabled.', 'development-assistant' );
		}

		Notice::add_transient( $message, 'success' );
	}

	public function handle_disable_direct_access_to_log(): void {
		if ( ! Htaccess::exists() ) {
			return;
		}

		update_option( static::DISABLE_DIRECT_ACCESS_TO_LOG_KEY, 'yes' );
		Notice::add_transient( __( 'Direct access to the <code>debug.log</code> file disabled.', 'development-assistant' ), 'success' );
	}

	public function handle_disable_debug_display(): void {
		update_option( static::ENABLE_WP_DEBUG_DISPLAY_KEY, 'no' );
		Notice::add_transient( __( '<code>WP_DEBUG_DISPLAY</code> disabled.', 'development-assistant' ), 'success' );
	}

	public function handle_enable_debug_log(): void {
		update_option( static::ENABLE_WP_DEBUG_KEY, 'yes' );
		update_option( static::ENABLE_WP_DEBUG_LOG_KEY, 'yes' );
		Notice::add_transient( __( '<code>WP_DEBUG_LOG</code> enabled.', 'development-assistant' ), 'success' );
	}
}
