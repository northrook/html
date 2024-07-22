<?php

namespace Northrook\HTML;

use Northrook\HTML\Compiler\Formatter\Newline;
use Northrook\HTML\Compiler\Formatter\Purpose;
use Northrook\HTML\Element\Attributes;
use Northrook\Minify;
use Symfony\Component\HttpFoundation\Test\Constraint\RequestAttributeValueSame;
use function Northrook\escapeHtmlText;

/**
 * @method static nl2auto( ?string $string )
 * @method static nl2span( ?string $string )
 * @method static nl2p( ?string $string )
 */
class Format
{
    public static function __callStatic( string $method, array $arguments ) {
        return match ( $method ) {
            'nl2auto' => Format::newline( \reset( $arguments ) ),
            'nl2span' => Format::newline( \reset( $arguments ), Newline::Span ),
            'nl2p'    => Format::newline( \reset( $arguments ), Newline::Paragraph )
        };
    }

    public static function title(
        string  $string,
        Purpose $type,
    ) : string {

        $string = trim( $string );

        $string = match ( $type ) {
            Purpose::Title     => ucfirst( $string ), // Limit to `<title>`
            Purpose::Paragraph => ucfirst( $string ), // Limit to recommended paragraph length
            default            => $string
        };

        return escapeHtmlText( $string );
    }


    public static function newline( string $string, Newline $strategy = Newline::Auto ) : ?string {

        // Trim the provided string.
        $string = Minify::squish( $string );

        // Bail early if the string was empty, null, or nothing but whitespace.
        if ( !$string ) {
            return null;
        }

        // Explode the string into lines.
        $lines = Format::explodeLinebreaks( $string );

        return match ( $strategy ) {
            Newline::Paragraph => Format::implodeWrap( $lines, 'p' ),
            Newline::Span      => Format::implodeWrap( $lines, 'span' ),
            Newline::Auto      => count( $lines ) === 1
                ? '<span>' . trim( $string ) . '</span>'
                : Format::implodeWrap(
                    $lines, 'p',
                ),
        };
    }

    public static function explodeLinebreaks(
        string $string,
    ) : array {
        $normalise = \str_replace( [ "\r\n", "\r" ], "\n", $string );
        $explode   = \explode( "\n", $normalise );
        return \array_filter( $explode, static fn ( $value ) => \trim( $value ) );
    }

    public static function implodeWrap( array $each, string $tag, array $attributes = [] ) : string {
        $attributes = $attributes ? ' ' . new Attributes( $attributes ) : '';
        return \implode( "", \array_map( fn ( $line ) => "<$tag$attributes>" . \trim( $line ) . "</$tag>", $each, ), );
    }


    /**
     * @param string  $string
     * @param array   $attributes
     *
     * @return string
     */
    public static function backtickCodeTags( string $string, array $attributes = [] ) : string {

        // TODO: Code highlighter | Use tempest/highlight
        return \preg_replace_callback(
            '/`(\S.*?)`/m',
            static fn ( $matches ) => new Element( 'code', $attributes, escapeHtmlText( $matches[ 1 ] ) ),
            $string,
        );
    }
}