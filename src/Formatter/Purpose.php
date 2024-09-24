<?php

declare( strict_types = 1 );

namespace Northrook\HTML\Formatter;

enum Purpose
{
    /** Document `<title>` */
    case Title;

    /** A primary headline */
    case Headline;

    /** A single standard length `<p>` */
    case Paragraph;

    /** Blurb */
    case Blurb;

    /** Primary page title */
    case H1;

    /** Section heading */
    case H2;

    /** Subheading */
    case H3;
    case H4;
    /** Subheading */
    case H5;
    /** Subheading */
}