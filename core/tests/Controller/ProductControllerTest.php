<?php

namespace App\Tests\Controller;

use App\Controller\ProductController;
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

        // Create a subclass to override fetchHtml so we don't hit the network
        $controller = new class($tileParserService, $htmlContent) extends ProductController {
            public function __construct(
                TileParserService $tileParserService,
                private readonly string $mockHtml
            ) {
                parent::__construct($tileParserService);
            }

            protected function fetchHtml(string $url): string|false
            {
                return $this->mockHtml;
            }
        };

        $request = new Request([
            'factory' => 'marca-corona',
            'collection' => 'arteseta',
            'article' => 'k263-arteseta-camoscio-s000628660'
        ]);

        $response = $controller->getPrice($request);

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
        $controller = new ProductController($tileParserService);

        $request = new Request([
            'factory' => 'marca-corona',
        ]);

        $response = $controller->getPrice($request);

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }
}
