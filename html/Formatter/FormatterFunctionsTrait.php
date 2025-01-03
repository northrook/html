<?php

declare(strict_types = 1);

namespace Northrook\HTML\Formatter;

use Northrook\HTML\Element\Attributes;
use const Support\EMPTY_STRING;

trait FormatterFunctionsTrait
{
    final protected static function explodeLinebreaks(string $string) : array
    {
        trigger_deprecation(__METHOD__, 'html', 'Deprected.');
        $normalise = \str_replace([ "\r\n", "\r" ], "\n", $string);
        $explode   = \explode("\n", $normalise);
        return \array_filter($explode, static fn ($value) => \trim($value));
    }

    final protected static function implodeWrap(array $each, string $tag, array $attributes = []) : string
    {
        trigger_deprecation(__METHOD__, 'html', 'Deprected.');
        $attributes = empty($attributes)
                ? EMPTY_STRING
                : ' ' . (new Attributes($attributes))->toString();

        return \implode(
            EMPTY_STRING,
            \array_map(
                static fn ($line) => "<$tag$attributes>" . \trim($line) . "</$tag>",
                $each,
            ),
        );
    }
}
