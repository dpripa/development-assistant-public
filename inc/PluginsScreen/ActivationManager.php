<?php
namespace WPDevAssist\PluginsScreen;

use WPDevAssist\ActionQuery;
use WPDevAssist\Notice;
use const WPDevAssist\KEY;
use const WPDevAssist\ROOT_FILE;

defined( 'ABSPATH' ) || exit;

class ActivationManager {
	protected const DEACTIVATION_QUERY_KEY = KEY . '_deactivate_plugins';
	protected const ACTIVATION_QUERY_KEY   = KEY . '_activate_plugins';
	protected const BULK_DEACTIVATION_KEY  = KEY . '_bulk_deactivate_plugins';

	public const DEACTIVATION_RESET_QUERY_KEY = KEY . '_deactivation_reset';
	public const DEACTIVATED_KEY              = KEY . '_temporarily_deactivated_plugins';

	public function __construct() {
		ActionQuery::add( static::DEACTIVATION_QUERY_KEY, array( $this, 'handle_deactivation' ) );
		ActionQuery::add( static::ACTIVATION_QUERY_KEY, array( $this, 'handle_activation' ), false );
		add_action( 'activate_plugin', array( $this, 'remove_temporarily_deactivated' ) );
		add_filter( 'bulk_actions-plugins', array( $this, 'add_bulk_deactivation' ) );
		add_filter( 'handle_bulk_actions-plugins', array( $this, 'handle_bulk_deactivation' ), 10, 3 );
	}

	public static function get_deactivation_url( array $plugins ): string {
		return ActionQuery::get_url(
			static::DEACTIVATION_QUERY_KEY,
			get_admin_url( null, 'plugins.php' ),
			implode( ',', $plugins )
		);
	}

	public static function get_activation_url(): string {
		return ActionQuery::get_url( static::ACTIVATION_QUERY_KEY, get_admin_url( null, 'plugins.php' ) );
	}

	public static function is_temporarily_deactivated( string $plugin_file ): bool {
		return in_array( $plugin_file, get_option( static::DEACTIVATED_KEY, array() ), true );
	}

	public static function deactivate_plugins( array $plugins ): void {
		$previous = get_option( static::DEACTIVATED_KEY, array() );

		foreach ( $plugins as $plugin_key => $plugin ) {
			if (
				plugin_basename( ROOT_FILE ) !== $plugin &&
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

		if ( empty( $plugins ) ) {
			delete_option( static::DEACTIVATED_KEY );

			return;
		}

		if ( ! activate_plugins( $plugins ) ) {
			Notice::add_transient( __( 'Can\'t activate the plugin(s).', 'development-assistant' ), 'error' );

		} else {
			delete_option( static::DEACTIVATED_KEY );
		}
	}

	public function handle_deactivation( array $data ): void {
		$plugins = explode( ',', sanitize_text_field( wp_unslash( $data[ static::DEACTIVATION_QUERY_KEY ] ) ) );

		if ( empty( $plugins ) ) {
			return;
		}

		static::deactivate_plugins( $plugins );
		Notice::add_transient( __( 'Plugin temporarily deactivated.', 'development-assistant' ), 'success' );
	}

	public function handle_activation( array $data ): void {
		static::activate_plugins();

		$redirect_to = get_admin_url( null, 'plugins.php' );

		if (
			isset( $data[ static::DEACTIVATION_RESET_QUERY_KEY ] ) &&
			sanitize_text_field( wp_unslash( $data[ static::DEACTIVATION_RESET_QUERY_KEY ] ) )
		) {
			$redirect_to = $this->handle_dev_assist_deactivation( $redirect_to );

		} else {
			Notice::add_transient( __( 'Plugin(s) activated.', 'development-assistant' ), 'success' );
		}

		wp_safe_redirect( $redirect_to );

		exit;
	}

	protected function handle_dev_assist_deactivation( string $redirect_to ): string {
		deactivate_plugins( array( ROOT_FILE ) );

		return add_query_arg( array( 'deactivate' => 'yes' ), $redirect_to );
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

	public function handle_bulk_deactivation( string $redirect_to, string $do_action, array $plugins ): string {
		if ( static::BULK_DEACTIVATION_KEY !== $do_action ) {
			return $redirect_to;
		}

		static::deactivate_plugins( $plugins );
		Notice::add_transient( __( 'Plugin(s) temporarily deactivated.', 'development-assistant' ), 'success' );

		return get_admin_url( null, 'plugins.php' );
	}
}
