<?php
namespace WPDevAssist\Setting\Control;

defined( 'ABSPATH' ) || exit;

class Checkbox {
	protected const REQUIRED_ARGS = array(
		'name',
		'default',
	);

	public static function render( array $args ): void {
		foreach ( static::REQUIRED_ARGS as $required_arg ) {
			if ( empty( $args[ $required_arg ] ) ) {
				throw new \Exception( "The \"$required_arg\" argument is required" );
			}
		}

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
					echo esc_html( $args['description'] );
				}
				?>
			</label>
		</div>
		<?php
	}

}
