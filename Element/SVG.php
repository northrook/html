<?php

namespace Northrook\HTML\Element;

use Northrook\HTML\Element;

class SVG extends Element
{
    public function __construct( array $attributes = [], mixed $content = null ) {
        parent::__construct( 'svg', $attributes, $content );
    }

    /**
     * @param SVG|array  $attributes
     *
     * @return null|array{'min-x':int, 'min-y':int, 'width':int, 'height':int }
     */
    public static function parseViewbox( SVG | array $attributes ) : ?array {
        if ( $attributes instanceof SVG ) {
            $attributes = $attributes->attributes->toArray();
        }

        if ( !isset( $attributes[ 'viewbox' ] ) ) {
            return null;
        }

        $viewbox = explode( ' ', $attributes[ 'viewbox' ] );

        if ( count( $viewbox ) !== 4 ) {
            throw new \LogicException(
                'The SVG viewbox attribute is malformed. It should contain 4 values, but ' . count(
                    $viewbox,
                ) . ' was found.',
            );
        }

        return \array_fill_keys( [ 'min-x', 'min-y', 'width', 'height' ], $viewbox );
    }
}