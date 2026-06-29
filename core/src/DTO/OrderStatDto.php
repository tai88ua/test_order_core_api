<?php

namespace App\DTO;

readonly class OrderStatDto
{
    /**
     * @param string $period  Значение периода (напр. "2024-06", "2024" или "2024-06-15")
     * @param int    $count   Количество заказов за этот период
     */
    public function __construct(
        public string $period,
        public int    $count,
    ) {}
}
