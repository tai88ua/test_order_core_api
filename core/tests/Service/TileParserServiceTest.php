<?php

namespace App\Tests\Service;

use App\Exception\PageNotFoundException;
use App\Service\TileParserService;
use PHPUnit\Framework\TestCase;

class TileParserServiceTest extends TestCase
{
    public function testParse(): void
    {
        $service = new TileParserService();

        $htmlPath = __DIR__ . '/../fixtures/tile_page.html';
        $this->assertFileExists($htmlPath);

        $html = file_get_contents($htmlPath);

        $result = $service->parse($html);

        $this->assertSame(59.99, $result->price);
        $this->assertSame('marca-corona', $result->factory);
        $this->assertSame('arteseta', $result->collection);
        $this->assertSame('k263-arteseta-camoscio-s000628660', $result->article);
    }

    public function testParseGypsum(): void
    {
        $service = new TileParserService();

        $htmlPath = __DIR__ . '/../fixtures/tile_page_gypsum.html';
        $this->assertFileExists($htmlPath);

        $html = file_get_contents($htmlPath);

        $result = $service->parse($html);

        $this->assertSame(63.99, $result->price);
        $this->assertSame('marca-corona', $result->factory);
        $this->assertSame('arteseta', $result->collection);
        $this->assertSame('k267-arteseta-gypsum-riga-s000628669', $result->article);
    }

    // https://tile.expert/it/tile/ceramica-euro/sinfonie-dautore/a/120sibee-beethoven
    public function testParseEuro(): void
    {
        $service = new TileParserService();

        $htmlPath = __DIR__ . '/../fixtures/tile_page_euro.html';
        $this->assertFileExists($htmlPath);

        $html = file_get_contents($htmlPath);

        $result = $service->parse($html);

        $this->assertSame(48.5, $result->price);
        $this->assertSame('ceramica-euro', $result->factory);
        $this->assertSame('sinfonie-dautore', $result->collection);
        $this->assertSame('120simor-morricone', $result->article);
    }

    public function testParsePage2(): void
    {
        $service = new TileParserService();

        $htmlPath = __DIR__ . '/../fixtures/tile_page2.html';
        $this->assertFileExists($htmlPath);

        $html = file_get_contents($htmlPath);

        $result = $service->parse($html);

        $this->assertSame(59.99, $result->price);
        $this->assertSame('marca-corona', $result->factory);
        $this->assertSame('arteseta', $result->collection);
        $this->assertSame('k263-arteseta-camoscio-s000628660', $result->article);
    }

    public function testParse404ThrowsException(): void
    {
        $service = new TileParserService();

        $htmlPath = __DIR__ . '/../fixtures/tile_page404.html';
        $this->assertFileExists($htmlPath);

        $html = file_get_contents($htmlPath);

        $this->expectException(PageNotFoundException::class);
        $this->expectExceptionMessage('Product page not found (HTTP 404)');

        $service->parse($html);
    }
}
