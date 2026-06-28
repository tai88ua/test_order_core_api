<?php

namespace App;

use OpenApi\Attributes as OA;

/**
 * Global OpenAPI specification metadata.
 */
#[OA\Info(
    version: '1.0.0',
    description: 'API для получения актуальной цены плитки с сайта tile.expert в реальном времени',
    title: 'Tile Expert Price API',
)]
class OpenApiSpec
{
}
