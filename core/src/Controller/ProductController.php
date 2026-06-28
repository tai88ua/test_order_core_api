<?php

namespace App\Controller;

use App\Service\ScraperService;
use App\Service\TileParserService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends AbstractController
{
    public function __construct(
        private readonly TileParserService $tileParserService
    ) {}

    #[Route('/api/price', name: 'app_product_price', methods: ['GET'])]
    #[OA\Get(
        path: '/api/price',
        summary: 'Получить цену плитки с сайта tile.expert',
        description: 'Загружает страницу продукта с tile.expert по переданным параметрам и возвращает цену вместе с деталями продукта.',
        tags: ['Product'],
    )]
    #[OA\Parameter(
        name: 'factory',
        in: 'query',
        description: 'Название фабрики-производителя (slug)',
        required: true,
        schema: new OA\Schema(type: 'string', example: 'marca-corona')
    )]
    #[OA\Parameter(
        name: 'collection',
        in: 'query',
        description: 'Название коллекции (slug)',
        required: true,
        schema: new OA\Schema(type: 'string', example: 'arteseta')
    )]
    #[OA\Parameter(
        name: 'article',
        in: 'query',
        description: 'Артикул плитки (slug)',
        required: true,
        schema: new OA\Schema(type: 'string', example: 'k263-arteseta-camoscio-s000628660')
    )]
    #[OA\Response(
        response: 200,
        description: 'Успешный ответ с ценой и деталями продукта',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'price', type: 'number', format: 'float', example: 59.99),
                new OA\Property(property: 'factory', type: 'string', example: 'marca-corona'),
                new OA\Property(property: 'collection', type: 'string', example: 'arteseta'),
                new OA\Property(property: 'article', type: 'string', example: 'k263-arteseta-camoscio-s000628660'),
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Отсутствуют обязательные параметры',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', type: 'string', example: 'Missing required query parameters: factory, collection, article'),
            ]
        )
    )]
    #[OA\Response(
        response: 502,
        description: 'Не удалось загрузить страницу с tile.expert',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', type: 'string', example: 'Failed to fetch the page from tile.expert'),
            ]
        )
    )]
    public function getPrice(Request $request, ScraperService $scraper): JsonResponse
    {
        $factory = $request->query->get('factory');
        $collection = $request->query->get('collection');
        $article = $request->query->get('article');

        if (!$factory || !$collection || !$article) {
            return new JsonResponse([
                'error' => 'Missing required query parameters: factory, collection, article'
            ], Response::HTTP_BAD_REQUEST);
        }

        $url = sprintf('https://tile.expert/it/tile/%s/%s/a/%s', $factory, $collection, $article);

        $html = $scraper->fetchHtml($url);
        if ($html === false) {
            return new JsonResponse([
                'error' => 'Failed to fetch the page from tile.expert'
            ], Response::HTTP_BAD_GATEWAY);
        }

        try {
            $productDto = $this->tileParserService->parse($html);

            return new JsonResponse([
                'price' => $productDto->price,
                'factory' => $productDto->factory ?: $factory,
                'collection' => $productDto->collection ?: $collection,
                'article' => $productDto->article ?: $article,
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to parse the page content: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
