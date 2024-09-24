<?php

namespace Northrook\HTML;

use function Northrook\escapeHtmlText;

final class HtmlFormat
{

    /**
     * Headings, subheadings.
     *
     * A short title.
     *
     * @param null|string|\Stringable  $string
     *
     * @return null|string
     */
    public static function title(
            null | string | \Stringable $string,
    ) : ?string
    {
        return $string ? \ucfirst( escapeHtmlText( $string ) ) : null;
    }

    /**
     * Content intended for a single line.
     *
     * @param null|string|\Stringable  $string
     *
     * @return null|string
     */
    public static function inline(
            null | string | \Stringable $string,
    ) : ?string
    {
        return $string ? \ucfirst( escapeHtmlText( $string ) ) : null;
    }

    /**
     * Generic content string, will auto-generate paragraphs if necessary.
     *
     * @param null|string|\Stringable  $string
     *
     * @return null|string
     */
    public static function content(
            null | string | \Stringable $string,
    ) : ?string
    {
        return $string ? \ucfirst( escapeHtmlText( $string ) ) : null;
    }
}