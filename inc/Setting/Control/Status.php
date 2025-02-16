<?php
namespace WPDevAssist\Setting\Control;

use Exception;

defined( 'ABSPATH' ) || exit;

class Status extends Control {
	protected const REQUIRED_ARGS = array(
		'is_success',
		'success_title',
		'failure_title',
	);

	/**
	 * @throws Exception
	 */
	public static function render( array $args ): void {
		static::validate_required_args( $args );

		if ( isset( $args['disabled'] ) && $args['disabled'] ) {
			$status_classname = 'da-setting-status__label_disabled';
		} elseif ( $args['is_success'] ) {
			$status_classname = 'da-setting-status__label_success';
		} else {
			$status_classname = 'da-setting-status__label_failure';
		}
		?>
		<div class="da-setting-status">
			<span class="da-setting-status__label <?php echo esc_attr( $status_classname ); ?>">
				<?php
				if ( isset( $args['disabled'] ) && $args['disabled'] ) {
					if ( isset( $args['disabled_title'] ) ) {
						echo wp_kses_post( $args['disabled_title'] );
					} else {
						echo esc_html__( 'Disabled', 'wp-dev-assist' );
					}
				} elseif ( $args['is_success'] ) {
					echo wp_kses_post( $args['success_title'] );
				} else {
					echo wp_kses_post( $args['failure_title'] );
				}
				?>
			</span>
			<?php if ( isset( $args['description'] ) && $args['description'] ) { ?>
				<div class="da-setting-status__description">
					<?php echo wp_kses_post( $args['description'] ); ?>
				</div>
			<?php } ?>
		</div>
		<?php
	}
}
