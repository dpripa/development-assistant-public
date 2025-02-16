<?php
namespace WPDevAssist\Setting;

use Exception;
use WPDevAssist\Asset;

defined( 'ABSPATH' ) || exit;

abstract class Page extends BasePage {
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		parent::__construct();

		foreach ( static::get_tabs() as $tab ) {
			new $tab();
		}
	}

	public static function get_toplevel_title( bool $lowercase = false ): string {
		$title = __( 'DevAssistant', 'development-assistant' );

		return $lowercase ? mb_strtolower( $title ) : $title;
	}

	abstract public function add_page(): void;

	protected function get_general_tab_title(): string {
		return __( 'General', 'development-assistant' );
	}

	/**
	 * @return string[]
	 */
	protected static function get_tabs(): array {
		return array();
	}

	public function render_page(): void {
		?>
		<div class="da-setting-page wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<?php
			$this->render_tabs();
			$this->render_content();
			?>
		</div>
		<?php
	}

	protected function render_tabs(): void {
		$tabs = static::get_tabs();

		if ( $tabs ) {
			?>
			<div class="nav-tab-wrapper">
				<?php
				$this->render_tab_link( static::get_page_url(), $this->get_general_tab_title(), static::is_current() );

				foreach ( $tabs as $tab ) {
					/** @var Tab $tab */
					$this->render_tab_link( $tab::get_url(), $tab::get_title(), $tab::is_current() );
				}
				?>
			</div>
			<?php
		}
	}

	protected function render_tab_link( string $url, string $title, bool $is_active ): void {
		if ( $is_active ) {
			?>
			<span class="nav-tab nav-tab-active" style="cursor: default;">
				<?php echo esc_html( $title ); ?>
			</span>
			<?php
		} else {
			?>
			<a href="<?php echo esc_url( $url ); ?>" class="nav-tab">
				<?php echo esc_html( $title ); ?>
			</a>
			<?php
		}
	}

	protected function render_content(): void {
		$option_group = isset( $_GET['tab'] ) ? // phpcs:ignore
			sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : // phpcs:ignore
			(
				isset( $_GET['page'] ) ? // phpcs:ignore
					sanitize_text_field( wp_unslash( $_GET['page'] ) ) : // phpcs:ignore
					''
			);
		?>
		<form method="post" action="<?php echo esc_url( get_admin_url( null, 'options.php' ) ); ?>">
			<?php
			settings_fields( $option_group );
			do_settings_sections( $option_group );
			submit_button();
			?>
		</form>
		<?php
	}

	public static function get_page_url(): string {
		return add_query_arg( array( 'page' => static::KEY ), get_admin_url( null, 'admin.php' ) );
	}

	public static function add_default_options(): void {
		foreach ( static::get_tabs() as $tab ) {
			/** @var Tab $tab */
			$tab::add_default_options();
		}
	}

	/**
	 * @throws Exception
	 */
	public function enqueue_assets(): void {
		if ( ! static::is_setting_page() ) {
			return;
		}

		Asset::enqueue_style( 'setting' );
	}

	public static function is_current(): bool {
		return static::is_setting_page() && empty( $_GET['tab'] ); // phpcs:ignore
	}

	public static function reset(): void {
		foreach ( static::get_tabs() as $tab ) {
			/** @var Tab $tab */
			$tab::reset();
		}

		parent::reset();
	}
}
