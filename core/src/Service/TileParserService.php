<?php

namespace App\Service;

use App\DTO\ProductDto;
use App\Exception\PageNotFoundException;
use Symfony\Component\DomCrawler\Crawler;

class TileParserService
{
    /**
     * Парсит HTML-содержимое страницы продукта и извлекает данные.
     * Использует Symfony DomCrawler с XPath в качестве основного механизма поиска.
     *
     * @param string $html Сырой HTML-контент страницы
     * @return ProductDto
     * @throws PageNotFoundException Если страница возвращает ошибку 404
     */
    public function parse(string $html): ProductDto
    {
        $crawler = new Crawler($html);

        // 1. Определяем 404-страницу через XPath: ищем элемент с классом "error-pages-code", содержащий текст "404"
        $errorNode = $crawler->filterXPath('//*[contains(@class, "error-pages-code")]');
        if ($errorNode->count() > 0 && str_contains(trim($errorNode->first()->text()), '404')) {
            throw new PageNotFoundException('Product page not found (HTTP 404)');
        }

        // 2. Получаем цену через XPath: элемент с классом "js-price-tag" и атрибутом data-price-raw
        $price = 0.0;
        $priceNode = $crawler->filterXPath(
            '//*[contains(concat(" ", normalize-space(@class), " "), " js-price-tag ") and @data-price-raw]'
        );
        if ($priceNode->count() > 0) {
            $rawPrice = $priceNode->first()->attr('data-price-raw');
            if ($rawPrice !== null && $rawPrice !== '') {
                $price = (float) $rawPrice;
            }
        }

        // 3. Получаем URL и запасную цену из блока JSON-LD (<script type="application/ld+json">)
        $url = '';
        $crawler->filterXPath('//script[@type="application/ld+json"]')->each(
            function (Crawler $node) use (&$price, &$url): void {
                $data = json_decode($node->text(), true);
                if (!is_array($data) || ($data['@type'] ?? '') !== 'Product') {
                    return;
                }
                if ($price === 0.0 && isset($data['offers']['price'])) {
                    $price = (float) $data['offers']['price'];
                }
                if (empty($url) && !empty($data['offers']['url'])) {
                    $url = (string) $data['offers']['url'];
                }
            }
        );

        // 4. Запасной вариант: ищем канонический URL через XPath (тег <link rel="canonical">)
        if (empty($url)) {
            $canonicalNode = $crawler->filterXPath('//link[@rel="canonical"]');
            if ($canonicalNode->count() > 0) {
                $url = (string) $canonicalNode->first()->attr('href');
            }
        }

        // 5. Запасной вариант: ищем URL в мета-теге og:url через XPath
        if (empty($url)) {
            $ogUrlNode = $crawler->filterXPath('//meta[@property="og:url"]');
            if ($ogUrlNode->count() > 0) {
                $url = (string) $ogUrlNode->first()->attr('content');
            }
        }

        // 6. Извлекаем фабрику, коллекцию и артикул из найденного URL
        $factory = '';
        $collection = '';
        $article = '';

        if (!empty($url) && preg_match('#/tile/([^/]+)/([^/]+)/a/([^/?]+)#', $url, $matches)) {
            $factory    = $matches[1];
            $collection = $matches[2];
            $article    = $matches[3];
        }

        return new ProductDto(
            price: $price,
            factory: $factory,
            collection: $collection,
            article: $article,
        );
    }
}
