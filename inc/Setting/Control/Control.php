<?php
namespace WPDevAssist\Setting\Control;

defined( 'ABSPATH' ) || exit;

abstract class Control {
	protected const REQUIRED_ARGS = array();

	protected static function validate_required_args( array $args ): void {
		foreach ( static::REQUIRED_ARGS as $required_arg ) {
			if ( empty( $args[ $required_arg ] ) ) {
				throw new \Exception( "The \"$required_arg\" argument is required" );
			}
		}
	}
}
