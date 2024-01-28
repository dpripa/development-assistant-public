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
		<div class="wpda-setting-checkbox">
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
					<span class="wpda-setting-checkbox__description">
						<?php echo wp_kses( $args['description'], array( 'span' => array( 'class' => array() ) ) ); ?>
					</span>
					<?php
				}
				?>
			</label>
		</div>
		<?php
	}
}
