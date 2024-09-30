<?php

namespace Northrook\HTML\Formatter;

use DOMComment;
use DOMDocument;
use DOMNode;
use DOMNodeList;
use JetBrains\PhpStorm\Language;
use Northrook\HTML\Element\Tag;
use Northrook\HTML\Format;
use Northrook\HTML\HtmlFormat;
use Northrook\Interface\Printable;
use Northrook\Logger\Log;
use Northrook\Trait\PrintableClass;
use Support\Str;
use const Support\EMPTY_STRING;

class HtmlTextFormatter implements Printable
{
    use PrintableClass;

    private readonly \DOMDocument $dom;
    private readonly \DOMXPath    $xPath;

    public function __construct( string $source )
    {
        $this->load( $source );
    }

    public function insertWbr( string | array $at = '-' ) : self
    {
        foreach ( (array) $at as $opportunity ) {
            foreach ( $this->query( '//text()' ) as $textNode ) {
                if ( !\str_contains( $textNode->nodeValue, $opportunity ) ) {
                    continue;
                }

                $textChunk = \explode( $opportunity, $textNode->nodeValue );
                // dump( $textChunk );

                foreach ( $textChunk as $index => $hyphenatedText ) {
                    if ( $index !== \array_key_first( $textChunk ) ) {
                        $hyphenatedText = "-$hyphenatedText";
                    }
                    $textNode->parentNode->insertBefore( $this->createTextNode( $hyphenatedText ), $textNode );
                    if ( $index !== \array_key_last( $textChunk ) ) {
                        $textNode->parentNode->insertBefore( $this->createElement( 'wbr' ), $textNode );
                    }
                }
                // Remove the original text node
                $textNode->parentNode->removeChild( $textNode );
            }
        }

        return $this;
    }

    protected function createTextNode( string $tag ) : \DOMText
    {
        return $this->dom->createTextNode( $tag );
    }

    protected function createElement( string $tag, string $content = EMPTY_STRING ) : \DOMNode
    {
        try {
            $element = $this->dom->createElement( $tag, $content );
        }
        catch ( \Exception $exception ) {
            $this->errorHandler( $exception );
        }
        return $element;
    }

    public function wrapTrailingWords(
            string | array $tag = [ 'p', 'span' ],
            int            $characterTrigger = 12,
    ) : self
    {
        // Bail early if the textContent is too short
        if ( \strlen( $this->dom->textContent ) <= ( $characterTrigger * 2.75 ) ) {
            return $this;
        }

        /** @var \DOMText[] $textNodes */
        $textNodes = $this->query( '//text()' );
        // $nodeCount = \count( $textNodes ) - 1;

        foreach ( $textNodes as $index => $textNode ) {
            if ( $index !== \count( $textNodes ) - 1 ) {
                continue;
            }

            $separator = ". ";
            $string    = \explode( $separator, $textNode->nodeValue );

            if ( \count( $string ) < 2 ) {
                $string    = \explode( ", ", $textNode->nodeValue );
                $separator = ", ";
            }
            $key = \array_key_last( $string );

            $string[ $key ] = Format::whiteSpaceWrap( $string[ $key ] );

            // Create a new document fragment to hold the HTML
            $replacementNode = $this->dom->createDocumentFragment();
            $replacementNode->appendXML( \implode( $separator, $string ) );

            // Replace the old text node with the new fragment
            $textNode->parentNode->replaceChild( $replacementNode, $textNode );
        }

        return $this;
    }

    final protected function query(
            #[Language( 'XPath' )]
            string    $expression,
            ?\DOMNode $context = null,
    ) : DOMNodeList | false
    {
        return ( $this->xPath ??= new \DOMXPath( $this->dom ) )->query( $expression );
    }

    final public function load(
            #[Language( 'HTML' )]
            string $string,
    ) : self
    {
        try {
            $html = Str::encode( $string );
            ( $this->dom ??= new DOMDocument() )->loadHTML(
                    source  : '<div>' . \str_replace( "\r\n", "\n", $html ) . '</div>',
                    options : LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD,
            );
            $this->dom->encoding = 'UTF-8';
        }
        catch ( \Exception $exception ) {
            $this->errorHandler( $exception );
        }

        return $this;
    }

    final public function __toString() : string
    {
        $content = EMPTY_STRING;

        foreach ( $this->dom->documentElement->childNodes as $node ) {
            $content .= $this->dom->saveXML( $node, options : LIBXML_NOXMLDECL );
        }

        // foreach ( Tag::SELF_CLOSING as $tag ) {
        //     $content = \str_replace( "<$tag></$tag>", "<$tag/>", $content );
        // }
        return $content;
    }

    private function errorHandler( \Exception $exception ) : void
    {
        if ( $exception instanceof \ErrorException ) {
            $severity = $exception->getSeverity();
            $message  = $exception->getMessage();

            if ( \str_contains( $message, ' invalid in Entity, ' ) ) {
                return;
            }

            //: We will likely downright skip all down the line
            // if ( $severity === E_WARNING ) {
            //     return;
            // }
        }

        dump( $exception );
        Log::exception( $exception );
    }

}