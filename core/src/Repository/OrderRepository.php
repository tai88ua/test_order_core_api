<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    /**
     * Returns paginated order counts grouped by a date period (day / month / year).
     *
     * Uses native SQL DATE_FORMAT so the grouping happens entirely in the database.
     *
     * @param string $dateFormat  MySQL DATE_FORMAT pattern, e.g. '%Y-%m-%d'
     * @param int    $page        Current page (1-based)
     * @param int    $perPage     Items per page
     * @return array{total: int, items: array<array{period: string, count: int}>}
     */
    public function countGroupedByPeriod(string $dateFormat, int $page, int $perPage): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $table = $this->getClassMetadata()->getTableName(); // 'orders'

        // Total number of distinct periods
        $totalSql = "
            SELECT COUNT(*) AS cnt
            FROM (
                SELECT DATE_FORMAT(create_date, :fmt) AS period
                FROM {$table}
                GROUP BY period
            ) AS sub
        ";
        $total = (int) $conn->fetchOne($totalSql, ['fmt' => $dateFormat]);

        // Paginated result
        $offset = ($page - 1) * $perPage;
        $dataSql = "
            SELECT DATE_FORMAT(create_date, :fmt) AS period, COUNT(*) AS cnt
            FROM {$table}
            GROUP BY period
            ORDER BY period DESC
            LIMIT :lim OFFSET :off
        ";

        $rows = $conn->fetchAllAssociative($dataSql, [
            'fmt' => $dateFormat,
            'lim' => $perPage,
            'off' => $offset,
        ], [
            'lim' => \Doctrine\DBAL\ParameterType::INTEGER,
            'off' => \Doctrine\DBAL\ParameterType::INTEGER,
        ]);

        $items = array_map(
            static fn(array $row) => ['period' => $row['period'], 'count' => (int) $row['cnt']],
            $rows
        );

        return ['total' => $total, 'items' => $items];
    }
}
