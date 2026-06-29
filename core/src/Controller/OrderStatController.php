<?php

namespace App\Controller;

use App\DTO\OrderStatGroupBy;
use App\Repository\OrderRepository;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/orders')]
class OrderStatController extends AbstractController
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
    ) {}

    /**
     * GET /api/orders/stats?group_by=month&page=1&per_page=10
     *
     * Возвращает количество заказов, сгруппированных по периоду (день / месяц / год),
     * с пагинацией.
     */
    #[Route('/stats', name: 'app_order_stats', methods: ['GET'])]
    #[OA\Get(
        path: '/api/orders/stats',
        summary: 'Статистика заказов по периодам',
        description: 'Возвращает количество заказов, сгруппированных по дням, месяцам или годам, с поддержкой пагинации.',
        tags: ['Orders'],
    )]
    #[OA\Parameter(
        name: 'group_by',
        in: 'query',
        description: 'Группировка: day | month | year (по умолчанию month)',
        required: false,
        schema: new OA\Schema(type: 'string', enum: ['day', 'month', 'year'], default: 'month'),
    )]
    #[OA\Parameter(
        name: 'page',
        in: 'query',
        description: 'Номер страницы (начиная с 1)',
        required: false,
        schema: new OA\Schema(type: 'integer', minimum: 1, default: 1),
    )]
    #[OA\Parameter(
        name: 'per_page',
        in: 'query',
        description: 'Количество записей на странице (1–100)',
        required: false,
        schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100, default: 10),
    )]
    #[OA\Response(
        response: 200,
        description: 'Успешный ответ с пагинированной статистикой',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'group_by',    type: 'string',  example: 'month'),
                new OA\Property(property: 'page',        type: 'integer', example: 1),
                new OA\Property(property: 'per_page',    type: 'integer', example: 10),
                new OA\Property(property: 'total',       type: 'integer', example: 42, description: 'Всего уникальных периодов'),
                new OA\Property(property: 'total_pages', type: 'integer', example: 5),
                new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'period', type: 'string',  example: '2024-06'),
                            new OA\Property(property: 'count',  type: 'integer', example: 138),
                        ]
                    )
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Некорректные параметры запроса',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', type: 'string', example: 'Invalid group_by value. Allowed: day, month, year'),
            ]
        )
    )]
    public function stats(Request $request): JsonResponse
    {
        // --- Параметр group_by ---
        $groupByRaw = $request->query->getString('group_by', 'month');
        $groupBy    = OrderStatGroupBy::tryFrom($groupByRaw);

        if ($groupBy === null) {
            return new JsonResponse(
                ['error' => 'Invalid group_by value. Allowed: day, month, year'],
                Response::HTTP_BAD_REQUEST
            );
        }

        // --- Пагинация ---
        $page    = max(1, $request->query->getInt('page', 1));
        $perPage = min(100, max(1, $request->query->getInt('per_page', 10)));

        // --- Запрос ---
        $result     = $this->orderRepository->countGroupedByPeriod($groupBy->dateFormat(), $page, $perPage);
        $total      = $result['total'];
        $totalPages = $perPage > 0 ? (int) ceil($total / $perPage) : 0;

        return new JsonResponse([
            'group_by'    => $groupBy->value,
            'page'        => $page,
            'per_page'    => $perPage,
            'total'       => $total,
            'total_pages' => $totalPages,
            'data'        => $result['items'],
        ]);
    }
}
