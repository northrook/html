<?php

namespace HTML;

use InvalidArgumentException;
use Northrook\Logger\Log;
use Stringable;
use Support\Normalize;
use voku\helper\ASCII;

final class Attributes extends \stdClass implements Stringable
{
    public function __construct(array $attributes)
    {
        foreach ($attributes as $name => $value) {
            $this->set($name, $value);
        }
    }

    public function __get(string $name) : null
    {
        return null;
    }

    public function toArray() : array
    {

    }

    public function toString() : string
    {

    }

    public function __toString() : string
    {
        // TODO: Implement __toString() method.
    }

    public function get( string $name ) : null|string|array
    {
        return $this->$name ?? null;
    }

    private function name(string $name) : string
    {
        return \strtolower(
            match ($name) {
                'classes' => 'class',
                'styles'  => 'style',
                default   => $name
            },
        );
    }

    public function __set(string $name, $value) : void
    {
        $name = $this->name($name);

        if (\is_array($value)) {
            $value = match ($name) {
                'class' => $this->classes(set : $value),
                'style' => $this->styles(set : $value),
                default => trim(\implode(' ', $value)),
            };
        }

        if (\is_bool($value)) {
            $value = $value ? 'true' : 'false';
        }

        $this->{$name} = match ($name) {
            'id'    => $this->id($value),
            'class' => $this->classes(set : $value),
            'style' => $this->styles(set : $value),
            default => $value,
        };
    }

    public function add(
        string | array               $attribute = null,
        string | array | bool | null $value = null,
        bool                         $prepend = false,
    ) : self {
        foreach ($this->attribute($attribute, $value) as $name => $value) {
            if (\is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }

            $this->{$name} = match ($name) {
                'id'    => $this->id($value),
                'class' => $this->classes(set : $value),
                'style' => $this->styles(set : $value),
                default => $value,
            };
        }
        return $this;
    }

    public function set(
        string | array               $attribute,
        string | array | bool | null $value = null,
    ) : self {
        foreach ($this->attribute($attribute, $value) as $name => $value) {
            $this->{$name} = $value;
        }
        return $this;
    }

    public function merge(array $attributes) : self
    {
        foreach ($attributes as $attribute => $value) {
            $this->add($attribute, $value);
        }
        return $this;
    }

    /**
     * @param null|string[]  $set
     * @param string         $separator
     *
     * @return ?string
     */
    public function id(null | string | array $set, string $separator = '-') : ?string
    {
        if (!$set) {
            return $this->id ?? null;
        }

        // Stringify
        $set = \is_array($set) ? \implode($separator, $set) : $set;

        // Normalize international characters if possible
        if (\class_exists(ASCII::class)) {
            $set = ASCII::to_slugify($set, $separator);
        }
        // Replace non-alphanumeric characters with the separator
        else {
            $set = (string) \preg_replace("/[^a-z0-9{$separator}]+/i", $separator, $set);
        }

        // Convert to lowercase, and remove leading and trailing separators
        return \strtolower(\trim($set, $separator));
    }

    /**
     * @param null|string[]  $add
     * @param null|string[]  $set
     * @param bool           $prepend
     *
     * @return string[]
     */
    public function classes(
        null | string | array $add = null,
        null | string | array $set = null,
        bool                  $prepend = false,
    ) : array {
        if (!isset($this->class)) {
            $this->class = [];
        }

        if (!$add && !$set) {
            return $this->class;
        }

        if (\is_array($add)) {
            $add = \implode(' ', $add);
        }

        if (\is_array($set)) {
            $set = \implode(' ', $set);
        }

        $classes = \explode(' ', \trim($add . ' ' . $set));

        if ($add) {
            return $this->class = \array_unique(
                $prepend
                            ? \array_merge($classes, $this->class)
                            : \array_merge($this->class, $classes),
            );
        }

        return $this->class = \array_unique($classes);
    }

    /**
     * @param null|string[]  $add
     * @param null|string[]  $set
     * @param bool           $prepend
     *
     * @return string[]
     */
    public function styles(
        null | string | array $add = null,
        null | string | array $set = null,
        bool                  $prepend = false,
    ) : array {
        if (!isset($this->style)) {
            $this->style = [];
        }

        if (!$add && !$set) {
            return $this->style;
        }

        if (\is_array($add)) {
            $add = \implode('; ', $add);
        }

        if (\is_array($set)) {
            $set = \implode('; ', $set);
        }

        $styles = [];

        foreach (\explode(';', \trim($add . '; ' . $set, " \n\r\t\v\0;")) as $style) {
            if (!\str_contains($style, ':')) {
                Log::Error(
                    'The style {style} was parsed, but it {error}. The style was skipped.',
                    [
                                'style' => $style,
                                'error' => 'has no declaration separator',
                        ],
                );

                continue;
            }
            [ $property, $value ] = \explode(':', $style);
            $styles[ \trim($property, " \t\n\r\0\x0B,") ] = \trim($value, " \t\n\r\0\x0B,;");
        }

        dump($styles);

        if ($add) {
            return $this->style = $prepend
                    ? \array_merge($styles, $this->style)
                    : \array_merge($this->style, $styles);
        }

        return $this->style = $styles;
    }

    /**
     * @param string|array<string, null|bool|string|array<array-key, mixed>>  $attribute
     * @param null|string|array<array-key, mixed>|bool                        $value
     *
     * @return array<string, null|bool|string|array<array-key, mixed>>
     */
    protected function attribute(
        string | array               $attribute,
        string | array | bool | null $value = null,
    ) : array {
        return \is_string($attribute) ? [ $attribute => $value ] : $attribute;
    }
}
