<?php
namespace WPDevAssist\Setting\Control;

use Exception;

defined( 'ABSPATH' ) || exit;

abstract class Control {
	protected const REQUIRED_ARGS = array(
		'name',
		'default',
	);

	/**
	 * @throws Exception
	 */
	protected static function validate_required_args( array $args ): void {
		foreach ( static::REQUIRED_ARGS as $required_arg ) {
			if ( ! isset( $args[ $required_arg ] ) ) {
				throw new Exception( "The \"$required_arg\" argument is required" );
			}
		}
	}
}
