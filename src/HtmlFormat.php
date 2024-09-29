<?php

namespace Northrook\HTML;

use function String\escapeHtml;

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
        return $string ? \ucfirst( escapeHtml( $string ) ) : null;
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
        return $string ? \ucfirst( escapeHtml( $string ) ) : null;
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
        return $string ? \ucfirst( escapeHtml( $string ) ) : null;
    }
}