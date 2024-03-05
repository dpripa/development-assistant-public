<?php
namespace WPDevAssist;

use WPDevAssist\Setting\Control;

defined( 'ABSPATH' ) || exit;

class Setting {
	public const ENABLE_WP_DEBUG_KEY             = KEY . '_enable_wp_debug';
	public const ENABLE_WP_DEBUG_DEFAULT         = 'no';
	public const ENABLE_WP_DEBUG_LOG_KEY         = KEY . '_enable_wp_debug_log';
	public const ENABLE_WP_DEBUG_LOG_DEFAULT     = 'no';
	public const ENABLE_WP_DEBUG_DISPLAY_KEY     = KEY . '_enable_wp_debug_display';
	public const ENABLE_WP_DEBUG_DISPLAY_DEFAULT = 'no';
	public const ACTIVE_PLUGINS_FIRST_KEY        = KEY . '_active_plugins_first';
	public const ACTIVE_PLUGINS_FIRST_DEFAULT    = 'yes';
	public const REDIRECT_TO_MAIL_HOG_KEY        = KEY . '_redirect_to_mail_hog_key';
	public const REDIRECT_TO_MAIL_HOG_DEFAULT    = 'no';
	public const RESET_KEY                       = KEY . '_reset';
	public const RESET_DEFAULT                   = 'yes';

	protected const KEYS = array(
		self::ENABLE_WP_DEBUG_KEY,
		self::ENABLE_WP_DEBUG_LOG_KEY,
		self::ENABLE_WP_DEBUG_DISPLAY_KEY,
		self::ACTIVE_PLUGINS_FIRST_KEY,
		self::REDIRECT_TO_MAIL_HOG_KEY,
		self::RESET_KEY,
	);

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_page' ) );
		add_action( 'admin_init', array( $this, 'add_settings' ) );
		add_action( 'updated_option', array( $this, 'render_notice_saved' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );

		new Setting\DebugLog();
	}

	public static function get_menu_title( bool $lowercase = false ): string {
		$title = __( 'DevAssistant', 'development-assistant' );

		return $lowercase ? mb_strtolower( $title ) : $title;
	}

	public function add_page(): void {
		$page_title = __( 'Development Assistant Settings', 'development-assistant' );

		add_menu_page(
			$page_title,
			static::get_menu_title(),
			'manage_options',
			KEY,
			array( $this, 'render_page' ),
			'dashicons-pets',
			999
		);

		add_submenu_page(
			KEY,
			$page_title,
			__( 'Settings', 'development-assistant' ),
			'manage_options',
			KEY,
			array( $this, 'render_page' )
		);
	}

	public function render_page(): void {
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form method="post" action="<?php echo esc_url( get_admin_url( null, 'options.php' ) ); ?>">
				<?php
				settings_fields( KEY );
				do_settings_sections( KEY );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	public function add_settings(): void {
		$this->add_wp_debug_settings( KEY . '_debug' );
		$this->add_plugin_screen_settings( KEY . '_plugins_screen' );
		$this->add_mail_hog_settings( KEY . '_mail_hog' );
		$this->add_reset_settings( KEY . '_reset' );
	}

	public function render_notice_saved(): void {
		if (
			1 < did_action( 'updated_option' ) ||
			empty( $_POST['_wpnonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), KEY . '-options' )
		) {
			return;
		}

		Plugin\Notice::add_transient( __( 'Settings saved.', 'development-assistant' ), 'success' );
	}

	public function enqueue_assets(): void {
		global $current_screen;

		if ( 'toplevel_page_' . KEY !== $current_screen->id ) {
			return;
		}

		Plugin\Asset::enqueue_style( 'setting' );
	}

	protected function add_wp_debug_settings( string $key ): void {
		add_settings_section(
			$key,
			esc_html__( 'WP Debug', 'development-assistant' ),
			array( $this, 'render_wp_debug_description' ),
			KEY
		);

		register_setting(
			KEY,
			static::ENABLE_WP_DEBUG_KEY,
			''
		);
		add_settings_field(
			static::ENABLE_WP_DEBUG_KEY,
			wp_kses( __( 'Enable <code>WP_DEBUG</code>', 'development-assistant' ), array( 'code' => array() ) ),
			array( Control\Checkbox::class, 'render' ),
			KEY,
			$key,
			array(
				'name'    => static::ENABLE_WP_DEBUG_KEY,
				'default' => static::ENABLE_WP_DEBUG_DEFAULT,
			)
		);

		register_setting(
			KEY,
			static::ENABLE_WP_DEBUG_LOG_KEY,
			''
		);
		add_settings_field(
			static::ENABLE_WP_DEBUG_LOG_KEY,
			wp_kses( __( 'Enable <code>WP_DEBUG_LOG</code>', 'development-assistant' ), array( 'code' => array() ) ),
			array( Control\Checkbox::class, 'render' ),
			KEY,
			$key,
			array(
				'name'    => static::ENABLE_WP_DEBUG_LOG_KEY,
				'default' => static::ENABLE_WP_DEBUG_LOG_DEFAULT,
			)
		);

		register_setting(
			KEY,
			static::ENABLE_WP_DEBUG_DISPLAY_KEY,
			''
		);
		add_settings_field(
			static::ENABLE_WP_DEBUG_DISPLAY_KEY,
			wp_kses( __( 'Enable <code>WP_DEBUG_DISPLAY</code>', 'development-assistant' ), array( 'code' => array() ) ),
			array( Control\Checkbox::class, 'render' ),
			KEY,
			$key,
			array(
				'name'    => static::ENABLE_WP_DEBUG_DISPLAY_KEY,
				'default' => static::ENABLE_WP_DEBUG_DISPLAY_DEFAULT,
			)
		);
	}

	public function render_wp_debug_description(): void {
		?>
		<div>
			<?php
			echo sprintf(
				esc_html__( 'These options allow you to safely control the debug constants without the need to manually edit the %s.', 'development-assistant' ),
				'<code>wp-config.php</code>'
			);
			?>
			<div style="margin-top: 5px;">
				<a href="<?php echo esc_url( Setting\DebugLog::get_page_url() ); ?>">
					<?php
					echo sprintf(
						esc_html__( 'Go to %s', 'development-assistant' ),
						'<code>debug.log</code>'
					);
					?>
				</a>
			</div>
		</div>
		<?php
	}

	protected function add_plugin_screen_settings( string $key ): void {
		add_settings_section(
			$key,
			esc_html__( 'Plugins Screen', 'development-assistant' ),
			'',
			KEY
		);

		register_setting(
			KEY,
			static::ACTIVE_PLUGINS_FIRST_KEY,
			'sanitize_text_field'
		);
		add_settings_field(
			static::ACTIVE_PLUGINS_FIRST_KEY,
			esc_html__( 'Show active plugins first', 'development-assistant' ),
			array( Control\Checkbox::class, 'render' ),
			KEY,
			$key,
			array(
				'name'    => static::ACTIVE_PLUGINS_FIRST_KEY,
				'default' => static::ACTIVE_PLUGINS_FIRST_DEFAULT,
			)
		);
	}

	protected function add_mail_hog_settings( string $key ): void {
		$info_link = $this->get_title_info_link(
			'https://github.com/mailhog/MailHog',
			__( 'What it is?', 'development-assistant' )
		);

		add_settings_section(
			$key,
			esc_html__( 'MailHog', 'development-assistant' ) . $info_link,
			'',
			KEY
		);

		$args = array(
			'name'    => static::REDIRECT_TO_MAIL_HOG_KEY,
			'default' => static::REDIRECT_TO_MAIL_HOG_DEFAULT,
		);

		if ( ! Plugin\Env::is_dev() ) {
			$args['disabled']    = true;
			$args['description'] = __( 'MailHog isn\'t available in the production environment.', 'development-assistant' );
		}

		register_setting(
			KEY,
			static::REDIRECT_TO_MAIL_HOG_KEY,
			'sanitize_text_field'
		);
		add_settings_field(
			static::REDIRECT_TO_MAIL_HOG_KEY,
			esc_html__( 'Redirect emails to MailHog', 'development-assistant' ),
			array( Control\Checkbox::class, 'render' ),
			KEY,
			$key,
			$args
		);
	}

	protected function add_reset_settings( string $key ): void {
		add_settings_section(
			$key,
			esc_html__( 'Reset', 'development-assistant' ),
			array( $this, 'render_reset_description' ),
			KEY
		);

		register_setting(
			KEY,
			static::RESET_KEY,
			'sanitize_text_field'
		);
		add_settings_field(
			static::RESET_KEY,
			esc_html__( 'Reset plugin data when deactivated', 'development-assistant' ),
			array( Control\Checkbox::class, 'render' ),
			KEY,
			$key,
			array(
				'name'    => static::RESET_KEY,
				'default' => static::RESET_DEFAULT,
			)
		);
	}

	public function render_reset_description() {
		?>
		<?php echo esc_html__( 'It will make look like the plugin was never installed and will undo any changes that may have been made using it. In details:', 'development-assistant' ); ?>
		<ul class="da-setting-list">
			<li><?php echo esc_html__( 'all plugin setting and data that was added to database will be deleted', 'development-assistant' ); ?>;</li>
			<li><?php echo esc_html__( 'all debug constants will be reset to the states specified before the plugin was activated', 'development-assistant' ); ?>;</li>
			<li><?php echo esc_html__( 'the debug.log file will be deleted if it didn\'t exist before the plugin was activated', 'development-assistant' ); ?>;</li>
			<li><?php echo esc_html__( 'all temporarily deactivated plugins will be activated', 'development-assistant' ); ?>.</li>
		</ul>
		<?php
	}

	protected function get_title_info_link( string $href, string $title ): string {
		return '<a
			href="' . esc_url( $href ) . '"
			class="dashicons dashicons-info-outline"
			style="margin-left: 7px; width: 18px; height: 18px; font-size: 18px; text-decoration: none;"
			target="_blank"
			title="' . esc_attr( $title ) . '"
		></a>';
	}

	public static function add_default_options(): void {
		update_option(
			static::ENABLE_WP_DEBUG_KEY,
			WPDebug::is_debug_enabled() ? 'yes' : 'no'
		);
		update_option(
			static::ENABLE_WP_DEBUG_LOG_KEY,
			WPDebug::is_debug_log_enabled() ? 'yes' : 'no'
		);
		update_option(
			static::ENABLE_WP_DEBUG_DISPLAY_KEY,
			WPDebug::is_debug_display_enabled() ? 'yes' : 'no'
		);
	}

	public static function get_page_url(): string {
		return Plugin\Url::get_admin( 'admin' ) . '?page=' . KEY;
	}

	public static function reset(): void {
		foreach ( static::KEYS as $setting_key ) {
			delete_option( $setting_key );
		}
	}
}
