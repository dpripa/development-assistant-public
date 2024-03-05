<?php
namespace WPDevAssist\PluginsScreen;

use WPDevAssist\Plugin;
use const WPDevAssist\KEY;
use const WPDevAssist\NAME;

defined( 'ABSPATH' ) || exit;

class ActivationManager {
	protected const DEACTIVATION_QUERY_KEY = KEY . '_deactivate_plugins';
	protected const ACTIVATION_QUERY_KEY   = KEY . '_activate_plugins';
	protected const BULK_DEACTIVATION_KEY  = KEY . '_bulk_deactivate_plugins';

	public const DEACTIVATION_RESET_QUERY_KEY = KEY . '_deactivation_reset';
	public const DEACTIVATED_KEY              = KEY . '_temporarily_deactivated_plugins';

	public function __construct() {
		add_action( 'admin_init', array( $this, 'handle_deactivation' ) );
		add_action( 'admin_init', array( $this, 'handle_activation' ) );
		add_action( 'activate_plugin', array( $this, 'remove_temporarily_deactivated' ) );
		add_filter( 'bulk_actions-plugins', array( $this, 'add_bulk_deactivation' ) );
		add_filter( 'handle_bulk_actions-plugins', array( $this, 'handle_bulk_deactivation' ), 10, 3 );
	}

	public static function get_deactivation_url( array $plugins ): string {
		return wp_nonce_url(
			add_query_arg(
				array( static::DEACTIVATION_QUERY_KEY => implode( ',', $plugins ) ),
				Plugin\Url::get_admin( 'plugins' )
			),
			static::DEACTIVATION_QUERY_KEY
		);
	}

	public static function get_activation_url(): string {
		return wp_nonce_url(
			add_query_arg(
				array( static::ACTIVATION_QUERY_KEY => 'yes' ),
				Plugin\Url::get_admin( 'plugins' )
			),
			static::ACTIVATION_QUERY_KEY
		);
	}

	public static function is_temporarily_deactivated( string $plugin_file ): bool {
		return in_array( $plugin_file, get_option( static::DEACTIVATED_KEY, array() ), true );
	}

	public static function deactivate_plugins( array $plugins ): void {
		$previous = get_option( static::DEACTIVATED_KEY, array() );

		foreach ( $plugins as $plugin_key => $plugin ) {
			if (
				NAME . '/' . NAME . '.php' !== $plugin &&
				is_plugin_active( $plugin )
			) {
				continue;
			}

			unset( $plugins[ $plugin_key ] );
		}

		deactivate_plugins( $plugins );
		update_option( static::DEACTIVATED_KEY, array_merge( $previous, $plugins ) );
	}

	public static function activate_plugins(): void {
		$plugins = get_option( static::DEACTIVATED_KEY, array() );

		delete_option( static::DEACTIVATED_KEY );

		if ( empty( $plugins ) ) {
			return;
		}

		activate_plugins( $plugins );
	}

	public function handle_deactivation(): void {
		if (
			empty( $_GET['_wpnonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), static::DEACTIVATION_QUERY_KEY ) ||
			empty( $_GET[ static::DEACTIVATION_QUERY_KEY ] )
		) {
			return;
		}

		$plugins = explode( ',', sanitize_text_field( wp_unslash( $_GET[ static::DEACTIVATION_QUERY_KEY ] ) ) );

		if ( empty( $plugins ) ) {
			return;
		}

		static::deactivate_plugins( $plugins );
		Plugin\Notice::add_transient( __( 'Plugin(s) temporarily deactivated.', 'development-assistant' ), 'success' );
		wp_safe_redirect( Plugin\Url::get_admin( 'plugins' ) );
	}

	public function handle_activation(): void {
		if (
			empty( $_GET['_wpnonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), static::ACTIVATION_QUERY_KEY ) ||
			empty( $_GET[ static::ACTIVATION_QUERY_KEY ] ) ||
			'yes' !== sanitize_text_field( wp_unslash( $_GET[ static::ACTIVATION_QUERY_KEY ] ) )
		) {
			return;
		}

		static::activate_plugins();

		$redirect_to = Plugin\Url::get_admin( 'plugins' );

		if (
			isset( $_GET[ static::DEACTIVATION_RESET_QUERY_KEY ] ) ||
			sanitize_text_field( wp_unslash( $_GET[ static::DEACTIVATION_RESET_QUERY_KEY ] ) )
		) {
			deactivate_plugins( array( NAME . '/' . NAME . '.php' ) );

			$redirect_to = add_query_arg( array( 'deactivate' => 'yes' ), $redirect_to );

		} else {
			Plugin\Notice::add_transient( __( 'Plugin(s) activated.', 'development-assistant' ), 'success' );
		}

		wp_safe_redirect( $redirect_to );
	}

	public function remove_temporarily_deactivated( string $plugin_file ): void {
		if ( isset( $_GET[ static::ACTIVATION_QUERY_KEY ] ) ) { // phpcs:ignore
			return;
		}

		$plugins = get_option( static::DEACTIVATED_KEY, array() );

		if ( ! in_array( $plugin_file, $plugins, true ) ) {
			return;
		}

		foreach ( $plugins as $plugin_key => $plugin ) {
			if ( $plugin !== $plugin_file ) {
				continue;
			}

			unset( $plugins[ $plugin_key ] );
			break;
		}

		update_option( static::DEACTIVATED_KEY, array_values( $plugins ) );
	}

	public function add_bulk_deactivation( array $actions ): array {
		$actions[ static::BULK_DEACTIVATION_KEY ] = __( 'Temporarily deactivate', 'development-assistant' );

		return $actions;
	}

	public function handle_bulk_deactivation( string $redirect_to, string $doaction, $plugins ): string {
		if ( static::BULK_DEACTIVATION_KEY !== $doaction ) {
			return $redirect_to;
		}

		static::deactivate_plugins( $plugins );
		Plugin\Notice::add_transient( __( 'Plugin(s) temporarily deactivated.', 'development-assistant' ), 'success' );

		return Plugin\Url::get_admin( 'plugins' );
	}
}
