<?php
namespace WPDevAssist\Setting;

use WPDevAssist\ActionQuery;
use WPDevAssist\MailHog;
use WPDevAssist\Notice;
use WPDevAssist\Setting;
use const WPDevAssist\KEY;

defined( 'ABSPATH' ) || exit;

class DevEnv extends Tab {
	public const PAGE_KEY = Setting::KEY;
	public const KEY      = self::PAGE_KEY . '_dev_env';

	public const ENABLE_KEY                   = KEY . '_force_dev_env';
	public const ENABLE_DEFAULT               = 'no';
	public const REDIRECT_TO_MAIL_HOG_KEY     = KEY . '_redirect_to_mail_hog';
	public const REDIRECT_TO_MAIL_HOG_DEFAULT = 'no';
	public const MAIL_HOG_HTTP_HOST_KEY       = KEY . '_mail_hog_http_address';
	public const MAIL_HOG_HTTP_HOST_DEFAULT   = '127.0.0.1:8025/mailhog';
	public const MAIL_HOG_SMTP_HOST_KEY       = KEY . '_mail_hog_smtp_address';
	public const MAIL_HOG_SMTP_HOST_DEFAULT   = '127.0.0.1:1025';

	protected const SETTING_KEYS = array(
		self::ENABLE_KEY,
		self::REDIRECT_TO_MAIL_HOG_KEY,
		self::MAIL_HOG_HTTP_HOST_KEY,
		self::MAIL_HOG_SMTP_HOST_KEY,
	);

	public const REDIRECT_TO_MAIL_HOG_QUERY_KEY = KEY . '_redirect_to_mail_hog';

	protected const DEV_HOSTS = array(
		'localhost',
		'local',
		'loc',
		'development',
		'dev',
		'mamp',
	);

	protected const DEV_ENVS = array(
		'development',
		'local',
	);

	public function __construct() {
		parent::__construct();
		ActionQuery::add( static::REDIRECT_TO_MAIL_HOG_QUERY_KEY, array( $this, 'handle_redirect_to_mail_hog' ) );
	}

	public static function get_title(): string {
		return __( 'Development Environment', 'development-assistant' );
	}

	public function add_sections(): void {
		$this->add_general_section( static::KEY . '_general' );
		$this->add_mail_hog_section( static::KEY . '_mail_hog' );
	}

	protected function add_general_section( string $section_key ) {
		$this->add_section(
			$section_key,
			'',
			array( $this, 'render_general_description' )
		);
		$this->add_setting(
			$section_key,
			static::ENABLE_KEY,
			__( 'Consider as a development environment', 'development-assistant' ),
			Control\Checkbox::class,
			static::ENABLE_DEFAULT,
			array(
				'description' => '<b class="da-setting__error-text">' . esc_html__( 'Warning!', 'development-assistant' ) . '</b> ' . esc_html__( 'The development environment is detected automatically, change this setting only if you are sure that autodetection failed.', 'development-assistant' ),
			)
		);
	}

	protected function render_general_description(): void {
		?>
		<b><?php echo esc_html__( 'Note!', 'development-assistant' ); ?></b> <?php echo esc_html__( 'These settings are for the development environment only.', 'development-assistant' ); ?>
		<br>
		<b><?php echo esc_html__( 'Please avoid using if the site isn\'t deployed in a development environment or you don\'t understand what it\'s about.', 'development-assistant' ); ?></b>
		<?php
	}

	protected function add_mail_hog_section( string $section_key ): void {
		$info_link  = $this->get_title_info_link(
			'https://github.com/mailhog/MailHog',
			__( 'What it is?', 'development-assistant' )
		);
		$is_dev_env = 'yes' === get_option( static::ENABLE_KEY, static::ENABLE_DEFAULT );

		$this->add_section(
			$section_key,
			esc_html__( 'MailHog', 'development-assistant' ) . $info_link
		);
		$this->add_setting(
			$section_key,
			static::REDIRECT_TO_MAIL_HOG_KEY,
			esc_html__( 'Redirect emails to MailHog', 'development-assistant' ),
			Control\Checkbox::class,
			static::REDIRECT_TO_MAIL_HOG_DEFAULT,
			array(
				'disabled'    => ! $is_dev_env,
				'description' => $is_dev_env ? '' : esc_html__( 'MailHog isn\'t available in the production environment.', 'development-assistant' ),
			)
		);

		$is_enabled          = MailHog::is_enabled();
		$is_http_host_exists = MailHog::is_http_host_exists();
		$status_description  = '';

		if ( $is_enabled ) {
			if ( $is_http_host_exists ) {
				$status_description = sprintf(
					esc_html__( 'To check if the SMTP host is responding, %1$s and check if it arrives in the %2$s.', 'development-assistant' ),
					'<a href="' . ActionQuery::get_url( MailHog::SEND_TEST_EMAIL_QUERY_KEY ) . '">' . esc_html__( 'send a test email', 'development-assistant' ) . '</a>',
					'<a href="' . MailHog::get_http_host() . '" target="_blank">' . esc_html__( 'MailHog UI', 'development-assistant' ) . '</a>'
				);
			} else {
				$status_description = esc_html__( 'It looks like MailHog isn\'t configured on your server, or you specified the wrong hosts.', 'development-assistant' );
			}
		}

		$this->add_setting(
			$section_key,
			'',
			esc_html__( 'Status', 'development-assistant' ),
			Control\Status::class,
			'',
			array(
				'disabled'      => ! $is_enabled,
				'is_success'    => $is_http_host_exists,
				'success_title' => esc_html__( 'HTTP host responds', 'development-assistant' ),
				'failure_title' => esc_html__( 'Not configured', 'development-assistant' ),
				'description'   => $status_description,
			)
		);
		$this->add_setting(
			$section_key,
			static::MAIL_HOG_HTTP_HOST_KEY,
			esc_html__( 'HTTP host', 'development-assistant' ),
			Control\Text::class,
			static::MAIL_HOG_HTTP_HOST_DEFAULT,
			array(
				'disabled'    => ! $is_enabled,
				'description' => $is_enabled ? wp_kses( __( 'Relative URL treating as <code>http://</code>, specify <code>https://</code> explicitly if needed.', 'development-assistant' ), array( 'code' => array() ) ) : '',
			)
		);
		$this->add_setting(
			$section_key,
			static::MAIL_HOG_SMTP_HOST_KEY,
			esc_html__( 'SMTP host', 'development-assistant' ),
			Control\Text::class,
			static::MAIL_HOG_SMTP_HOST_DEFAULT,
			array(
				'disabled'    => ! $is_enabled,
				'description' => $is_enabled ? wp_kses( __( 'Relative URL treating as <code>smtp://</code>, if you are sure there should be a different protocol, then specify it explicitly.', 'development-assistant' ), array( 'code' => array() ) ) : '',
			)
		);
	}

	public static function add_default_options(): void {
		if ( static::is_detected_dev_env() && ! in_array( get_option( static::ENABLE_KEY ), array( 'yes', 'no' ), true ) ) {
			update_option( static::ENABLE_KEY, 'yes' );
		}
	}

	public static function is_detected_dev_env(): bool {
		$host      = explode( '.', wp_parse_url( home_url(), PHP_URL_HOST ) );
		$root_host = end( $host );

		return in_array( $root_host, static::DEV_HOSTS, true ) ||
			in_array( wp_get_environment_type(), static::DEV_ENVS, true ) ||
			( defined( 'WP_ENVIRONMENT' ) && in_array( WP_ENVIRONMENT, static::DEV_ENVS, true ) );
	}

	public function handle_redirect_to_mail_hog(): void {
		update_option( static::REDIRECT_TO_MAIL_HOG_QUERY_KEY, 'yes' );
		Notice::add_transient( __( 'Redirect emails to MailHog enabled.', 'development-assistant' ), 'success' );
	}
}
