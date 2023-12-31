<?php
namespace WPDevAssist\Plugin;

defined( 'ABSPATH' ) || exit;

class Str {
	public static function generate_random( int $length = 16, string $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ' ): string {
		$pieces = array();
		$max    = mb_strlen( $keyspace, '8bit' ) - 1;

		for ( $i = 0; $i < $length; ++ $i ) {
			$pieces[] = $keyspace[ random_int( 0, $max ) ];
		}

		return implode( '', $pieces );
	}

	public static function truncate( string $string, int $length = 100, array $args = array() ): string {
		$args = wp_parse_args(
			$args,
			array(
				'ending' => '...',
				'exact'  => false,
				'html'   => true,
			)
		);

		if ( $args['html'] ) {
			if ( mb_strlen( preg_replace( '/<.*?>/', '', $string ) ) <= $length ) {
				return $string;
			}

			$total_length = mb_strlen( wp_strip_all_tags( $args['ending'] ) );
			$open_tags    = array();
			$truncate     = '';

			preg_match_all( '/(<\/?([\w+]+)[^>]*>)?([^<>]*)/', $string, $tags, PREG_SET_ORDER );

			foreach ( $tags as $tag ) {
				if ( ! preg_match( '/img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param/s', $tag[2] ) ) {
					if ( preg_match( '/<[\w]+[^>]*>/s', $tag[0] ) ) {
						array_unshift( $open_tags, $tag[2] );

					} elseif ( preg_match( '/<\/([\w]+)[^>]*>/s', $tag[0], $close_tag ) ) {
						$pos = array_search( $close_tag[1], $open_tags, true );

						if ( false !== $pos ) {
							array_splice( $open_tags, $pos, 1 );
						}
					}
				}
				$truncate .= $tag[1];

				$content_length = mb_strlen( preg_replace( '/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $tag[3] ) );

				if ( $content_length + $total_length > $length ) {
					$left            = $length - $total_length;
					$entities_length = 0;

					if ( preg_match_all( '/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', $tag[3], $entities, PREG_OFFSET_CAPTURE ) ) {
						foreach ( $entities[0] as $entity ) {
							if ( $entity[1] + 1 - $entities_length <= $left ) {
								$left--;
								$entities_length += mb_strlen( $entity[0] );

							} else {
								break;
							}
						}
					}

					$truncate .= mb_substr( $tag[3], 0, $left + $entities_length );
					break;

				} else {
					$truncate     .= $tag[3];
					$total_length += $content_length;
				}

				if ( $total_length >= $length ) {
					break;
				}
			}
		} else {
			if ( mb_strlen( $string ) <= $length ) {
				return $string;

			} else {
				$truncate = mb_substr( $string, 0, $length - mb_strlen( $args['ending'] ) );
			}
		}

		if ( ! $args['exact'] ) {
			$spacepos = mb_strrpos( $truncate, ' ' );

			if ( isset( $spacepos ) ) {
				if ( $args['html'] ) {
					$bits = mb_substr( $truncate, $spacepos );
					preg_match_all( '/<\/([a-z]+)>/', $bits, $dropped_tags, PREG_SET_ORDER );

					if ( ! empty( $dropped_tags ) ) {
						foreach ( $dropped_tags as $closing_tag ) {
							if ( ! in_array( $closing_tag[1], $open_tags, true ) ) {
								array_unshift( $open_tags, $closing_tag[1] );
							}
						}
					}
				}
				$truncate = mb_substr( $truncate, 0, $spacepos );
			}
		}

		$truncate .= $args['ending'];

		if ( $args['html'] ) {
			foreach ( $open_tags as $tag ) {
				$truncate .= "</$tag>";
			}
		}

		return $truncate;
	}
}
