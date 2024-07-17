<?php

declare( strict_types = 1 );

namespace Northrook\HTML;

use Northrook\Core\Trait\PropertyAccessor;
use Northrook\Minify;

/**
 * @property-read string html
 * @property-read string innerHtml
 * @property-read array  attributes
 */
readonly class HtmlNode
{
    use PropertyAccessor;

    public \DOMDocument $dom;

    public function __construct(
        ?string $html = null,
    ) {
        $this->dom = new \DOMDocument();
        if ( $html ) {
            $this->loadHtml( $html );
        }
    }

    public function __get( string $property ) {
        return match ( $property ) {
            'html'       => $this->getHtml(),
            'innerHtml'  => $this->getInnerHtml(),
            'attributes' => $this->getAttributes(),
        };
    }

    public function loadHtml( string $string ) : void {
        $html = Minify::HTML( $string )->toString();
        $this->dom->loadHTML( $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR );
    }

    protected function getHtml() : string {
        return $this->dom->saveHTML();
    }

    protected function getInnerHtml() : string {
        $html = '';
        foreach ( $this->getChildNodes() as $childNode ) {
            $html .= $childNode->ownerDocument->saveHTML( $childNode );
        }
        return $html;
    }

    /**
     * @return \DOMElement[]
     */
    public function getChildNodes() : array {
        return iterator_to_array( $this->dom->firstElementChild->childNodes );
    }

    protected function getAttributes() : array {

        $attributes = [];

        $node = $this->dom->firstElementChild;

        if ( !$node ) {
            return $attributes;
        }

        foreach ( $node->attributes as $attribute ) {
            $attributes[ $attribute->nodeName ] = $attribute->nodeValue;
        }

        return $attributes;
    }

    public static function extractAttributes( string $html ) : array {

        $html = Minify::HTML( $html )->toString();

        if ( false === str_starts_with( $html, '<' ) && false === str_starts_with( $html, '>' ) ) {
            $html = "<div $html>";
        }
        else {
            $html = strstr( $html, '>', true ) . '>';
            $html = preg_replace(
                pattern     : '/^<(\w.+):\w+? /',
                replacement : '<$1 ',
                subject     : $html,
            );
        }

        return ( new HtmlNode( $html ) )->getAttributes();
    }
}