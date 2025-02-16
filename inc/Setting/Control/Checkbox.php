<?php
namespace WPDevAssist\Setting\Control;

use Exception;

defined( 'ABSPATH' ) || exit;

class Checkbox extends Control {
	/**
	 * @throws Exception
	 */
	public static function render( array $args ): void {
		static::validate_required_args( $args );

		$value       = get_option( $args['name'], $args['default'] );
		$is_disabled = isset( $args['disabled'] ) && $args['disabled'];
		?>
		<div class="da-setting-checkbox <?php echo $is_disabled ? 'da-setting-checkbox_disabled' : ''; ?>">
			<input
				type="hidden"
				name="<?php echo esc_attr( $args['name'] ); ?>"
				value="no"
			>
			<label>
				<span>
					<input
						type="checkbox"
						name="<?php echo esc_attr( $args['name'] ); ?>"
						value="yes"
						<?php checked( 'yes', $value ); ?>
						<?php disabled( true, $is_disabled ); ?>
					>
				</span>
				<?php
				if ( isset( $args['description'] ) && $args['description'] ) {
					?>
					<span class="da-setting-checkbox__description">
						<?php echo wp_kses_post( $args['description'] ); ?>
					</span>
					<?php
				}
				?>
			</label>
		</div>
		<?php
	}
}
