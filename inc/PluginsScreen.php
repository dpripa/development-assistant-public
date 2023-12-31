<?php
namespace WPDevAssist;

defined( 'ABSPATH' ) || exit;

class PluginsScreen {
	public function __construct() {
		if ( 'yes' === get_option( Setting::ACTIVE_PLUGINS_FIRST_KEY, Setting::ACTIVE_PLUGINS_FIRST_DEFAULT ) ) {
			add_action( 'admin_head-plugins.php', array( $this, 'sort_plugins_by_status' ) );
		}
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
