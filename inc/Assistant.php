<?php
namespace WPDevAssist;

use Exception;

defined( 'ABSPATH' ) || exit;

class Assistant {
	public const TITLE_HOOK = KEY . '_assistant_panel_title';

	public function __construct() {
		add_action( 'admin_init', array( $this, 'init' ), 1 );
	}

	/**
	 * @return Assistant\Section[]
	 */
	protected function get_sections(): array {
		$sections = array(
			new Assistant\WPDebug(),
		);

		if ( 'yes' === get_option( Setting\DevEnv::ENABLE_KEY, Setting\DevEnv::ENABLE_DEFAULT ) ) {
			$sections[] = new Assistant\MailHog();
		}

		if (
			apply_filters( Setting\SupportUser::ENABLE_HOOK, true ) &&
			'yes' === get_option( Setting\SupportUser::ENABLE_KEY, Setting\SupportUser::ENABLE_DEFAULT )
		) {
			$sections[] = new Assistant\SupportUser();
		}

		return $sections;
	}

	public function init(): void {
		if (
			! current_user_can( 'administrator' ) ||
			'yes' !== get_option( Setting::ENABLE_ASSISTANT_KEY, Setting::ENABLE_ASSISTANT_DEFAULT )
		) {
			return;
		}

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_notices', array( $this, 'render' ) );
	}

	public function render(): void {
		global $pagenow;

		$sections          = $this->get_sections();
		$is_forced_be_open = false;

		foreach ( $sections as $section ) {
			if ( $section->is_forces_panel_be_open() ) {
				$is_forced_be_open = true;
				break;
			}
		}

		$is_open = $is_forced_be_open ||
			( 'yes' === get_option( Setting::ASSISTANT_OPENED_ON_WP_DASHBOARD_KEY, Setting::ASSISTANT_OPENED_ON_WP_DASHBOARD_DEFAULT ) && 'index.php' === $pagenow );
		?>
		<div class="da-assistant <?php echo $is_open ? 'da-assistant_open' : ''; ?>">
			<button class="da-assistant__header" type="button">
				<span class="da-assistant__header-content">
					<span class="da-assistant__icon dashicons dashicons-pets"></span>
					<span class="da-assistant__title">
						<?php
						echo esc_html(
							apply_filters(
								static::TITLE_HOOK,
								__( 'Assistant Panel', 'development-assistant' )
							)
						);
						?>
					</span>
					<span class="da-assistant__statuses">
						<?php
						foreach ( $sections as $section ) {
							if ( $section->configure_status() ) {
								$section->render_status();
							}
						}
						?>
					</span>
				</span>
				<span class="da-assistant__arrow-down"></span>
			</button>
			<?php
			foreach ( $sections as $section ) {
				$section->render();
			}
			?>
		</div>
		<?php
	}

	/**
	 * @throws Exception
	 */
	public function enqueue_scripts(): void {
		Asset::enqueue_style( 'assistant' );
		Asset::enqueue_script( 'assistant', array( 'jquery' ) );
	}
}
