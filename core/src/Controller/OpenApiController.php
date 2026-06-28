<?php

namespace App\Controller;

use OpenApi\Attributes as OA;
use OpenApi\Generator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class OpenApiController extends AbstractController
{
    #[Route('/api/doc.json', name: 'app_openapi_json', methods: ['GET'])]
    public function spec(): JsonResponse
    {
        try {
            $openapi = Generator::scan([
                __DIR__ . '/..',
            ]);

            $apiUrl = $_ENV['API_URL'] ?? 'http://localhost:8190';
            $openapi->servers = [
                new \OpenApi\Annotations\Server([
                    'url' => $apiUrl,
                    'description' => 'Docker environment'
                ])
            ];

            return new JsonResponse(
                json_decode($openapi->toJson(), true),
                Response::HTTP_OK
            );
        } catch (\Throwable $e) {
            return new JsonResponse([
                'error' => 'Failed to generate OpenAPI spec: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
