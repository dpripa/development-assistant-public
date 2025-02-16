<?php
namespace WPDevAssist\Setting\Control;

use Exception;

defined( 'ABSPATH' ) || exit;

class Text extends Control {
	/**
	 * @throws Exception
	 */
	public static function render( array $args ): void {
		static::validate_required_args( $args );

		$value       = get_option( $args['name'], $args['default'] );
		$is_disabled = isset( $args['disabled'] ) && $args['disabled'];
		$type        = $args['type'] ?? 'text';
		$min         = $args['min'] ?? false;
		$max         = $args['max'] ?? false;
		$step        = $args['step'] ?? false;
		?>
		<div class="da-setting-text">
			<label>
				<span>
					<input
						type="<?php echo esc_attr( $type ); ?>"
						name="<?php echo esc_attr( $args['name'] ); ?>"
						value="<?php echo esc_attr( $value ); ?>"
						<?php echo is_numeric( $min ) ? 'min="' . esc_attr( $min ) . '"' : ''; ?>
						<?php echo is_numeric( $max ) ? 'max="' . esc_attr( $max ) . '"' : ''; ?>
						<?php echo is_numeric( $step ) ? 'step="' . esc_attr( $step ) . '"' : ''; ?>
						<?php disabled( true, $is_disabled ); ?>
					>
					<?php if ( $is_disabled ) { ?>
						<input
							type="hidden"
							name="<?php echo esc_attr( $args['name'] ); ?>"
							value="<?php echo esc_attr( $value ); ?>"
						>
						<?php } ?>
				</span>
				<?php
				if ( isset( $args['description'] ) && $args['description'] ) {
					?>
					<span class="da-setting-text__description">
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
