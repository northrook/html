<?php

declare(strict_types = 1);

namespace Northrook\HTML;

use JetBrains\PhpStorm\Language;
use Northrook\Logger\Log;
use Northrook\Minify;
use Support\PropertyAccessor;
use Support\Str;
use const Support\EMPTY_STRING;

/**
 * @template AttributeName of non-empty-string
 * @template AttributeValue of string
 *
 * @property-read string $html
 * @property-read string $innerHtml
 * @property-read array  $attributes
 */
readonly class HtmlNode
{
    use PropertyAccessor;

    public \DOMDocument $dom;

    public function __construct(
        ?string      $html = null,
        private bool $validate = false,
    ) {
        trigger_deprecation(__METHOD__, 'html', 'Deprected.');
        $this->dom = new \DOMDocument("1.0", "UTF-8");
        if ($html) {
            $this->load($html);
        }
    }

    public function __get(string $property) : string | array | null
    {
        return match ($property) {
            'html'       => $this->getHtml(),
            'innerHtml'  => $this->getInnerHtml(),
            'attributes' => $this->getAttributes(),
            default      => throw new \RuntimeException("Undefined property: $property"),
        };
    }

    final public function load(
        #[Language('HTML')]
        string $string,
    ) : self {
        try {
            $html = Str::encode($string);
            $this->dom->loadHTML(
                source  : '<div>' . \str_replace("\r\n", "\n", $html) . '</div>',
                options : LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD,
            );
            $this->dom->encoding = 'UTF-8';
        } catch (\Exception $exception) {
            $this->errorHandler($exception);
        }

        return $this;
    }

    // public function loadHtml( string $string ) : void
    // {
    //     $html = Minify::HTML( $string, false )->toString();
    //     $this->dom->loadHTML( $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR );
    // }

    /**
     * @param string  $appendTo
     *
     * @return string
     */
    public function getHtml(string $appendTo = EMPTY_STRING) : string
    {
        foreach ($this->getChildNodes() as $node) {
            $appendTo .= $this->dom->saveXML($node, options : LIBXML_NOXMLDECL);
        }

        // foreach ( Tag::SELF_CLOSING as $tag ) {
        //     $content = \str_replace( "<$tag></$tag>", "<$tag/>", $content );
        // }
        return $appendTo;
    }

    protected function getInnerHtml() : string
    {
        trigger_error('Deprecated', E_USER_DEPRECATED);
        $html = '';
        foreach ($this->getChildNodes() as $childNode) {
            $html .= $childNode->ownerDocument->saveHTML($childNode);
        }
        return $html;
    }

    /**
     * @return \DOMElement[]
     */
    public function getChildNodes() : array
    {
        return \iterator_to_array($this->dom->documentElement?->childNodes ?? []);
    }

    /**
     * @return \DOMNodeList|iterable
     */
    public function iterateChildNodes() : \DOMNodeList | iterable
    {
        return $this->dom->documentElement?->childNodes ?? [];
    }

    protected function getAttributes() : array
    {
        $attributes = [];

        $node = $this->dom->firstElementChild;

        if (!$node) {
            return $attributes;
        }

        foreach ($node->attributes as $attribute) {
            $attributes[ $attribute->nodeName ] = $attribute->nodeValue;
        }

        return $attributes;
    }

    /**
     * @param string  $html
     *
     * @return array<AttributeName, AttributeValue>
     */
    public static function extractAttributes(string $html) : array
    {
        if (!$html = Str::squish($html, false)) {
            return [];
        }

        if (false === \str_starts_with($html, '<')
             &&
             false === \str_starts_with($html, '>')) {
            $html = "<div $html>";
        } else {
            $html = \strstr($html, '>', true) . '>';
            $html = \preg_replace(
                pattern     : '/^<(\w.+):\w+? /',
                replacement : '<$1 ',
                subject     : $html,
            );
        }

        return (new HtmlNode($html))->getAttributes();
    }

    public static function unwrap(string $html, string ...$tags) : string
    {
        trigger_deprecation(__METHOD__, 'html', 'Deprected.');
        $proceed = false;
        foreach ($tags as $tag) {
            if (\str_starts_with($html, "<$tag")) {
                $proceed = true;
            }
        }
        if (!$proceed) {
            return $html;
        }

        $element = new static($html);

        foreach ($element->iterateChildNodes() as $childNode) {
            if (!\in_array($childNode->nodeName, $tags)) {
                continue;
            };

            foreach ($childNode->childNodes as $nestedChild) {
                $childNode->parentNode->insertBefore($nestedChild->cloneNode(true), $childNode);
            }
            $childNode->parentNode->removeChild($childNode);
        }

        return $element->getHtml();
    }

    private function errorHandler(\Exception $exception) : void
    {
        if ($exception instanceof \ErrorException) {
            $severity = $exception->getSeverity();
            $message  = $exception->getMessage();

            if (\str_contains($message, ' invalid in Entity, ')) {
                return;
            }

            //: We will likely downright skip all down the line
            // if ( $severity === E_WARNING ) {
            //     return;
            // }
        }

        Log::exception($exception);

        if ($this->validate) {
            dump($exception);
            return;
        }
    }
}
