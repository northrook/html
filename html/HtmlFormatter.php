<?php

namespace Northrook\HTML;

use Northrook\HTML\Formatter\Newline;
use Northrook\HTML\Element\Attributes;

class HtmlFormatter implements \Stringable
{
    public function __construct(
        protected string $string,
    ) {
        trigger_deprecation(__METHOD__, 'html', 'Deprected.');}

    final public function newline( Newline $strategy = Newline::Auto ) : HtmlFormatter {
        trigger_deprecation(__METHOD__, 'html', 'Deprected.');
        $this->string = Format::newline( $this->string, $strategy );
        return $this;
    }

    final public function getString() : string {
        trigger_deprecation(__METHOD__, 'html', 'Deprected.');
        return $this->string;
    }


    final public function backtickTags( array $attributes = [] ) : HtmlFormatter {
        $this->string = Format::backtickCodeTags( $this->string, $attributes );
        trigger_deprecation(__METHOD__, 'html', 'Deprected.');
        return $this;
    }

    final public function url( ?callable $callback = null ) : HtmlFormatter {
        // match `<a ..>` tags and perform default callback or provided callback
        // tel:
        // Spacing, XX XX XX XX, XXXX XXX XXX etc
        // Areacode detector, 00XX, +XX etc


        return $this;
    }

    public function hyphenatedWbr() : self {
        return $this;
    }

    public function newlinePreferredWrap() : self {
        return $this;
    }

    public function __toString() : string {
        return $this->string;
    }
}
