<?php
namespace WPDevAssist;

defined( 'ABSPATH' ) || exit;

class PluginsScreen {
	protected const COLUMN_KEY = KEY . '_dev_actions';

	public function __construct() {
		new PluginsScreen\ActivationManager();
		new PluginsScreen\Downloader();

		add_filter( 'plugin_action_links_' . NAME . '/' . NAME . '.php', array( $this, 'add_plugin_actions' ) );
		add_filter( 'network_admin_plugin_action_links_' . NAME . '/' . NAME . '.php', array( $this, 'add_plugin_actions' ) );
		add_filter( 'manage_plugins_columns', array( $this, 'add_column' ) );
		add_action( 'manage_plugins_custom_column', array( $this, 'render_column' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );

		if ( 'yes' === get_option( Setting::ACTIVE_PLUGINS_FIRST_KEY, Setting::ACTIVE_PLUGINS_FIRST_DEFAULT ) ) {
			add_action( 'admin_head-plugins.php', array( $this, 'sort_plugins_by_status' ) );
		}
	}

	public function add_plugin_actions( array $actions ): array {
		return array_merge(
			array(
				'settings' => '<a href="' . Setting::get_page_url() . '">' . esc_html__( 'Settings', 'development-assistant' ) . '</a>',
			),
			$actions
		);
	}

	public function add_column( array $columns ): array {
		$columns[ static::COLUMN_KEY ] = __( 'Development Assistant', 'development-assistant' );

		return $columns;
	}

	public function render_column( string $column_name, string $plugin_file ): void {
		if (
			static::COLUMN_KEY !== $column_name ||
			NAME . '/' . NAME . '.php' === $plugin_file
		) {
			return;
		}
		?>
		<ul class="da-dev-actions-list">
			<?php if ( is_plugin_active( $plugin_file ) ) { ?>
				<li>
					<a href="<?php echo esc_url( PluginsScreen\ActivationManager::get_deactivation_url( array( $plugin_file ) ) ); ?>">
						<?php echo esc_html__( 'Temporarily deactivate', 'development-assistant' ); ?>
					</a>
				</li>
				<?php
			} elseif ( PluginsScreen\ActivationManager::is_temporarily_deactivated( $plugin_file ) ) {
				?>
				<li><?php echo esc_html__( 'Temporarily deactivated', 'development-assistant' ); ?></li>
				<?php
			}

			if ( current_user_can( 'edit_plugins' ) ) {
				?>
				<li>
					<a href="<?php echo esc_url( PluginsScreen\Downloader::get_url( $plugin_file ) ); ?>">
						<?php echo esc_html__( 'Download', 'development-assistant' ); ?>
					</a>
				</li>
			<?php } ?>
		</ul>
		<?php
	}

	public function enqueue_assets(): void {
		global $current_screen;

		if ( 'plugins' !== $current_screen->id ) {
			return;
		}

		Plugin\Asset::enqueue_style( 'plugins-screen' );
		Plugin\Asset::enqueue_script(
			'plugins-screen',
			array(),
			array(
				'has_deactivated_plugins'      => empty( get_option( PluginsScreen\ActivationManager::DEACTIVATED_KEY ) ) ? 'no' : 'yes',
				'plugin_activation_url'        => PluginsScreen\ActivationManager::get_activation_url(),
				'plugin_activation_title'      => __( 'Activate temporarily deactivated plugins', 'development-assistant' ),
				'reset'                        => get_option( Setting::RESET_KEY, Setting::RESET_DEFAULT ),
				'deactivation_reset_query_key' => PluginsScreen\ActivationManager::DEACTIVATION_RESET_QUERY_KEY,
			)
		);
	}

	public function sort_plugins_by_status(): void {
		global $wp_list_table, $status;

		if ( ! in_array( $status, array( 'active', 'inactive', 'recently_activated', 'mustuse' ), true ) ) {
			uksort(
				$wp_list_table->items,
				function ( $a, $b ): int {
					global $wp_list_table;

					$a_active = is_plugin_active( $a );
					$b_active = is_plugin_active( $b );

					if ( $a_active && ! $b_active ) {
						return -1;
					} elseif ( ! $a_active && $b_active ) {
						return 1;
					} else {
						return strcasecmp( $wp_list_table->items[ $a ]['Name'], $wp_list_table->items[ $b ]['Name'] );
					}
				}
			);
		}
	}
}
