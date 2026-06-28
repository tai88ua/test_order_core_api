<?php

namespace App\Service;

use App\DTO\ProductDto;
use Symfony\Component\DomCrawler\Crawler;

class TileParserService
{
    /**
     * Parses the HTML content of a tile page and extracts details.
     *
     * @param string $html
     * @return ProductDto
     */
    public function parse(string $html): ProductDto
    {
        $crawler = new Crawler($html);

        // 1. Try to find the price and URL in JSON-LD
        $price = 0.0;
        $url = '';

        $scripts = $crawler->filter('script[type="application/ld+json"]');
        foreach ($scripts as $script) {
            $data = json_decode($script->textContent, true);
            if (is_array($data) && ($data['@type'] ?? '') === 'Product') {
                if (isset($data['offers']['price'])) {
                    $price = (float) $data['offers']['price'];
                }
                if (isset($data['offers']['url'])) {
                    $url = (string) $data['offers']['url'];
                }
                break;
            }
        }

        // 2. If URL is not found in JSON-LD, try canonical link or og:url
        if (empty($url)) {
            $canonical = $crawler->filter('link[rel="canonical"]');
            if ($canonical->count() > 0) {
                $url = (string) $canonical->attr('href');
            }
        }

        if (empty($url)) {
            $ogUrl = $crawler->filter('meta[property="og:url"]');
            if ($ogUrl->count() > 0) {
                $url = (string) $ogUrl->attr('content');
            }
        }

        // 3. Extract factory, collection, and article from the URL
        $factory = '';
        $collection = '';
        $article = '';

        if (!empty($url)) {
            if (preg_match('#/tile/([^/]+)/([^/]+)/a/([^/]+)#', $url, $matches)) {
                $factory = $matches[1];
                $collection = $matches[2];
                $article = $matches[3];
            }
        }

        return new ProductDto(
            price: $price,
            factory: $factory,
            collection: $collection,
            article: $article,
        );
    }
}

