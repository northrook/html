<?php

namespace Northrook\HTML;

use Northrook\HTML\Formatter\FormatterFunctionsTrait;
use Northrook\HTML\Formatter\HtmlTextFormatter;
use Northrook\HTML\Formatter\Newline;
use Northrook\HTML\Formatter\Purpose;
use Northrook\HTML\Element\Attributes;
use Northrook\Minify;
use function Northrook\escapeHtmlText;
use function Northrook\stringEncode;
use function Northrook\stringStripTags;
use const Northrook\EMPTY_STRING;

/**
 * All static functions _must_ return string.
 */
class Format
{
    use FormatterFunctionsTrait;

    private array $textStringCallbacks = [];

    public static function title(
            string  $string,
            Purpose $type,
    ) : string
    {
        $string = trim( $string );

        $string = match ( $type ) {
            Purpose::Title     => ucfirst( $string ), // Limit to `<title>`
            Purpose::Paragraph => ucfirst( $string ), // Limit to recommended paragraph length
            default            => $string
        };

        return escapeHtmlText( $string );
    }

    public static function nl2p( string $string ) : string
    {
        return Format::newline( $string, Newline::Paragraph );
    }

    public static function nl2s( string $string ) : string
    {
        return Format::newline( $string, Newline::Span );
    }

    public static function inline( string $string ) : string
    {
        if ( !$string ) {
            return EMPTY_STRING;
        }


        $html = Minify::HTML( $string );

        $html = new HtmlTextFormatter( $html );

        return $html
                ->wrapTrailingWords()
                ->insertWbr()
                ->toString();
    }

    public static function newline(
            string  $string,
            Newline $strategy = Newline::Auto,
            array   $attributes = [],
    ) : string
    {
        // Trim the provided string.
        $string = \trim( $string );

        // Bail early if the string was empty, null, or nothing but whitespace.
        if ( !$string ) {
            return EMPTY_STRING;
        }

        // Explode the string into lines.
        $lines = Format::explodeLinebreaks( $string );

        return match ( $strategy ) {
            Newline::Paragraph => Format::implodeWrap( $lines, 'p', $attributes ),
            Newline::Span      => Format::implodeWrap( $lines, 'span', $attributes ),
            Newline::Auto      => \count( $lines ) === 1
                    ? Format::implodeWrap( $lines, 'span', $attributes )
                    : Format::implodeWrap( $lines, 'p', $attributes ),
        };
    }

    /**
     * @param string  $string
     * @param array   $attributes
     *
     * @return string
     */
    public static function backtickCodeTags( string $string, array $attributes = [] ) : string
    {
        // TODO: Code highlighter | Use tempest/highlight
        return \preg_replace_callback(
                '/`(\S.*?)`/m',
                static fn( $matches ) => new Element( 'code', $attributes, escapeHtmlText( $matches[ 1 ] ) ),
                $string,
        );
    }

    public static function textContent( string $string ) : string
    {
        $html = new HtmlTextFormatter( $string );

        return $html
                ->wrapTrailingWords()
                ->insertWbr()
                ->toString();
    }

    private static function hyphenLineBreakOpportunity( string $string, bool | int &$count = 0 ) : string
    {
        return \preg_replace( '#\w(-)\w#m', '<wbr/>$1', $string, count : $count );
    }

    public static function whiteSpaceWrap( string $string, ?int $maxTail = null ) : string
    {
        $maxTail ??= 12;

        if ( \strlen( $string ) < $maxTail ) {
            return '<span style="white-space: nowrap">' . \trim( $string ) . '</span>';
        }

        $exploded = \array_reverse( \explode( ' ', $string ) );
        $pieces   = [];
        $length   = 0;

        foreach ( $exploded as $index => $pieceString ) {
            $length   += \strlen( $pieceString );
            $pieces[] = $pieceString;
            unset( $exploded[ $index ] );

            if ( $length >= $maxTail ) {
                break;
            }
        }

        $exploded = \array_reverse( $exploded );
        $pieces   = \array_reverse( $pieces );

        $string = \implode( ' ', $exploded )
                  . ' <span class="inline-runt">' . \trim( \implode( ' ', $pieces ) ) . '</span>';

        return $string;
    }
}