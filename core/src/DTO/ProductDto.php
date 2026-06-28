<?php

namespace App\DTO;

readonly class ProductDto
{
    public function __construct(
        public float $price,
        public string $factory,
        public string $collection,
        public string $article,
    ) {}
}
