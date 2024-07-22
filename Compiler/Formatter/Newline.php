<?php

declare( strict_types = 1 );

namespace Northrook\HTML\Compiler\Formatter;

enum Newline
{
    /** Wrap each new line in a paragraph */
    case Paragraph;
    /** Wrap each new line in a span */
    case Span;
    /** Wrap single lines in a span, and multiple lines in paragraphs */
    case Auto;
}