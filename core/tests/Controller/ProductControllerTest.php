<?php

namespace App\Tests\Controller;

use App\Controller\ProductController;
use App\Service\ScraperService;
use App\Service\TileParserService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductControllerTest extends TestCase
{
    public function testGetPriceSuccess(): void
    {
        $htmlPath = __DIR__ . '/../fixtures/tile_page.html';
        $this->assertFileExists($htmlPath);
        $htmlContent = file_get_contents($htmlPath);

        $tileParserService = new TileParserService();

        // Mock ScraperService
        $scraperService = $this->createMock(ScraperService::class);
        $scraperService->method('fetchHtml')
            ->willReturn($htmlContent);

        $controller = new ProductController($tileParserService);

        $request = new Request([
            'factory' => 'marca-corona',
            'collection' => 'arteseta',
            'article' => 'k263-arteseta-camoscio-s000628660'
        ]);

        $response = $controller->getPrice($request, $scraperService);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertSame(59.99, $data['price']);
        $this->assertSame('marca-corona', $data['factory']);
        $this->assertSame('arteseta', $data['collection']);
        $this->assertSame('k263-arteseta-camoscio-s000628660', $data['article']);
    }

    public function testGetPriceMissingParams(): void
    {
        $tileParserService = $this->createMock(TileParserService::class);
        $scraperService = $this->createMock(ScraperService::class);
        $controller = new ProductController($tileParserService);

        $request = new Request([
            'factory' => 'marca-corona',
        ]);

        $response = $controller->getPrice($request, $scraperService);

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }
}
