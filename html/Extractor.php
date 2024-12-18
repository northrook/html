<?php

declare(strict_types = 1);

namespace Northrook\HTML;

use Support\Filesystem;
use Symfony\Component\DomCrawler\Crawler;
use function Assert\isUrl;

final class Extractor
{

    public readonly Crawler $dom;

    private string $rawHtml;

    public function __construct(private readonly string $source)
    {
        trigger_deprecation( __METHOD__, 'html', 'Deprected.' );
        $this->rawHtml = match (true) {
            isUrl($source)                              => $this->getRemoteSource($source),
            Filesystem::exists($this->source)           => Filesystem::read($source),
            preg_match('/<\/?[a-z][\s\S]*>/i', $source) => $source,
            default                                       => throw new \InvalidArgumentException(
                'Invalid extractor source.',
            ),
        };

        $this->dom = new Crawler($this->rawHtml);
    }

    private function getRemoteSource(string $source) : string
    {
        trigger_deprecation(__METHOD__, 'html', 'Deprected.');
        $ch = curl_init($source);

        // set url
        curl_setopt($ch, CURLOPT_URL, "example.com");

        //return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt(
            $ch,
            CURLOPT_USERAGENT,
            'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13',
        );

        // $output contains the output string
        $output = curl_exec($ch);

        dump(curl_error($ch));

        // close curl resource to free up system resources
        curl_close($ch);

        return $output;
    }

    public function extract(string $selector = 'body') : string
    {
        trigger_deprecation(__METHOD__, 'html', 'Deprected.');
        $html = $this->dom->filter($selector)->text();

        $html = \str_replace([ '. ' ], [ ".\n" ], $html);

        return $html;
    }

}
