<?php
namespace WPDevAssist\Setting;

use WPDevAssist\Notice;

defined( 'ABSPATH' ) || exit;

abstract class BasePage {
	public const KEY = '';

	protected const SETTING_KEYS = array();

	public function __construct() {
		add_action( 'admin_init', array( $this, 'add_sections' ) );
		add_action( 'updated_option', array( $this, 'render_notice_saved' ) );
	}

	abstract protected function add_sections(): void;

	public function add_section( string $key, string $title, ?callable $callback = null ): void {
		add_settings_section( $key, $title, $this->get_render_section_description( $callback ), static::KEY );
	}

	protected function get_render_section_description( ?callable $render ): ?callable {
		if ( ! is_callable( $render ) ) {
			return null;
		}

		return function () use ( $render ): void {
			?>
			<div class="da-setting-section__description">
				<?php $render(); ?>
			</div>
			<?php
		};
	}

	/**
	 * @param mixed $default
	 */
	protected function add_setting(
		string $section_key,
		string $key,
		string $title,
		string $control_classname,
		$default,
		array $args = array(),
		?callable $sanitize_callback = null
	): void {
		register_setting(
			static::KEY,
			$key,
			is_callable( $sanitize_callback ) ? $sanitize_callback : 'sanitize_text_field'
		);
		add_settings_field(
			$key,
			$title,
			array( $control_classname, 'render' ),
			static::KEY,
			$section_key,
			wp_parse_args(
				$args,
				array(
					'name'    => $key,
					'default' => $default,
				)
			)
		);
	}

	abstract public static function is_current(): bool;

	public static function add_default_options(): void {}

	protected static function is_setting_page(): bool {
		return isset( $_GET['page'] ) && static::KEY === sanitize_text_field( wp_unslash( $_GET['page'] ) ); // phpcs:ignore
	}

	protected function get_title_info_link( string $url, string $title, string $target = '_blank' ): string {
		return '<a
			class="da-setting-page__title-info-link dashicons dashicons-info-outline"
			href="' . esc_url( $url ) . '"
			title="' . esc_attr( $title ) . '"
			target="' . esc_attr( $target ) . '"
		></a>';
	}

	public static function reset(): void {
		foreach ( static::SETTING_KEYS as $setting_key ) {
			delete_option( $setting_key );
		}
	}

	public function render_notice_saved(): void {
		if (
			1 < did_action( 'updated_option' ) ||
			empty( $_POST['_wpnonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), static::KEY . '-options' )
		) {
			return;
		}

		Notice::add_transient( __( 'Settings saved.', 'development-assistant' ), 'success' );
	}
}
