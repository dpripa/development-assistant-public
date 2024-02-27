<?php
namespace WPDevAssist\Setting\Control;

defined( 'ABSPATH' ) || exit;

class Checkbox extends Control {
	protected const REQUIRED_ARGS = array(
		'name',
		'default',
	);

	public static function render( array $args ): void {
		static::validate_required_args( $args );

		$value = get_option( $args['name'], $args['default'] );
		?>
		<div class="da-setting-checkbox">
			<input
				type="hidden"
				name="<?php echo esc_attr( $args['name'] ); ?>"
				value="no"
			>
			<label>
				<input
					type="checkbox"
					name="<?php echo esc_attr( $args['name'] ); ?>"
					value="yes"
					<?php checked( 'yes', $value ); ?>
					<?php disabled( true, isset( $args['disabled'] ) && $args['disabled'] ); ?>
				>
				<?php
				if ( isset( $args['description'] ) ) {
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
