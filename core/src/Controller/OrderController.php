<?php

namespace App\Controller;

use App\Entity\Order;
use App\Repository\OrderRepository;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/orders')]
class OrderController extends AbstractController
{
    public function __construct(
        private readonly OrderRepository $orderRepository
    ) {}

    /**
     * GET /api/orders/{idOrHash}
     *
     * Возвращает детальную информацию о заказе по его ID или уникальному хэшу (32-символьной строке).
     */
    #[Route('/{idOrHash}', name: 'app_order_show', requirements: ['idOrHash' => '\d+|[a-f0-9]{32}'], methods: ['GET'])]
    #[OA\Get(
        path: '/api/orders/{idOrHash}',
        summary: 'Получить информацию об одном заказе',
        description: 'Возвращает детальную информацию о заказе по его ID (число) или уникальному хэшу (32-символьный md5), включая связанные позиции.',
        tags: ['Orders'],
    )]
    #[OA\Parameter(
        name: 'idOrHash',
        in: 'path',
        description: 'ID заказа (число) или уникальный 32-символьный хэш заказа',
        required: true,
        schema: new OA\Schema(type: 'string', example: '1')
    )]
    #[OA\Response(
        response: 200,
        description: 'Детальная информация о заказе',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'hash', type: 'string', example: 'd3b07384d113edec49eaa6238ad5ff00'),
                new OA\Property(property: 'userId', type: 'integer', nullable: true, example: 42),
                new OA\Property(property: 'token', type: 'string', example: '9a9f1165a25e2e83120111aa7a83f81e3a6a9b6c'),
                new OA\Property(property: 'number', type: 'string', nullable: true, example: '1000000'),
                new OA\Property(property: 'status', type: 'integer', example: 1),
                new OA\Property(property: 'email', type: 'string', nullable: true, example: 'client@example.com'),
                new OA\Property(property: 'vatType', type: 'integer', example: 1),
                new OA\Property(property: 'vatNumber', type: 'string', nullable: true, example: 'IT12345678901'),
                new OA\Property(property: 'taxNumber', type: 'string', nullable: true, example: 'TXX998877'),
                new OA\Property(property: 'discount', type: 'integer', nullable: true, example: 10),
                new OA\Property(property: 'delivery', type: 'number', format: 'float', nullable: true, example: 150.00),
                new OA\Property(property: 'deliveryType', type: 'integer', nullable: true, example: 0),
                new OA\Property(property: 'deliveryTimeMin', type: 'string', format: 'date', nullable: true, example: '2026-07-05'),
                new OA\Property(property: 'deliveryTimeMax', type: 'string', format: 'date', nullable: true, example: '2026-07-10'),
                new OA\Property(property: 'clientName', type: 'string', nullable: true, example: 'John'),
                new OA\Property(property: 'clientSurname', type: 'string', nullable: true, example: 'Doe'),
                new OA\Property(property: 'payType', type: 'integer', example: 1),
                new OA\Property(property: 'locale', type: 'string', example: 'it'),
                new OA\Property(property: 'currency', type: 'string', example: 'EUR'),
                new OA\Property(property: 'measure', type: 'string', example: 'm'),
                new OA\Property(property: 'name', type: 'string', example: 'Order Name'),
                new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Additional notes'),
                new OA\Property(property: 'createDate', type: 'string', format: 'date-time', example: '2026-06-29 22:54:40'),
                new OA\Property(
                    property: 'articles',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'articleId', type: 'integer', nullable: true, example: 123),
                            new OA\Property(property: 'amount', type: 'number', format: 'float', example: 12.5),
                            new OA\Property(property: 'price', type: 'number', format: 'float', example: 45.99),
                            new OA\Property(property: 'priceEur', type: 'number', format: 'float', nullable: true, example: 45.99),
                            new OA\Property(property: 'currency', type: 'string', nullable: true, example: 'EUR'),
                            new OA\Property(property: 'measure', type: 'string', nullable: true, example: 'mq'),
                        ]
                    )
                )
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Заказ не найден',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', type: 'string', example: 'Order not found'),
            ]
        )
    )]
    public function show(string $idOrHash): JsonResponse
    {
        $order = is_numeric($idOrHash)
            ? $this->orderRepository->find((int) $idOrHash)
            : $this->orderRepository->findOneBy(['hash' => $idOrHash]);

        if (!$order) {
            return new JsonResponse(
                ['error' => 'Order not found'],
                Response::HTTP_NOT_FOUND
            );
        }

        return new JsonResponse($this->serializeOrder($order));
    }

    /**
     * Преобразует сущность Order в ассоциативный массив для JSON-ответа.
     */
    private function serializeOrder(Order $order): array
    {
        $articles = [];
        foreach ($order->getArticles() as $article) {
            $articles[] = [
                'id' => $article->getId(),
                'articleId' => $article->getArticleId(),
                'amount' => $article->getAmount(),
                'price' => $article->getPrice(),
                'priceEur' => $article->getPriceEur(),
                'currency' => $article->getCurrency(),
                'measure' => $article->getMeasure(),
                'deliveryTimeMin' => $article->getDeliveryTimeMin()?->format('Y-m-d'),
                'deliveryTimeMax' => $article->getDeliveryTimeMax()?->format('Y-m-d'),
                'weight' => $article->getWeight(),
                'multiplePallet' => $article->getMultiplePallet(),
                'packagingCount' => $article->getPackagingCount(),
                'pallet' => $article->getPallet(),
                'packaging' => $article->getPackaging(),
                'swimmingPool' => $article->isSwimmingPool(),
            ];
        }

        return [
            'id' => $order->getId(),
            'hash' => $order->getHash(),
            'userId' => $order->getUserId(),
            'token' => $order->getToken(),
            'number' => $order->getNumber(),
            'status' => $order->getStatus(),
            'email' => $order->getEmail(),
            'vatType' => $order->getVatType(),
            'vatNumber' => $order->getVatNumber(),
            'taxNumber' => $order->getTaxNumber(),
            'discount' => $order->getDiscount(),
            'delivery' => $order->getDelivery(),
            'deliveryType' => $order->getDeliveryType(),
            'deliveryTimeMin' => $order->getDeliveryTimeMin()?->format('Y-m-d'),
            'deliveryTimeMax' => $order->getDeliveryTimeMax()?->format('Y-m-d'),
            'deliveryTimeConfirmMin' => $order->getDeliveryTimeConfirmMin()?->format('Y-m-d'),
            'deliveryTimeConfirmMax' => $order->getDeliveryTimeConfirmMax()?->format('Y-m-d'),
            'deliveryTimeFastPayMin' => $order->getDeliveryTimeFastPayMin()?->format('Y-m-d'),
            'deliveryTimeFastPayMax' => $order->getDeliveryTimeFastPayMax()?->format('Y-m-d'),
            'deliveryOldTimeMin' => $order->getDeliveryOldTimeMin()?->format('Y-m-d'),
            'deliveryOldTimeMax' => $order->getDeliveryOldTimeMax()?->format('Y-m-d'),
            'deliveryIndex' => $order->getDeliveryIndex(),
            'deliveryCountry' => $order->getDeliveryCountry(),
            'deliveryRegion' => $order->getDeliveryRegion(),
            'deliveryCity' => $order->getDeliveryCity(),
            'deliveryAddress' => $order->getDeliveryAddress(),
            'deliveryBuilding' => $order->getDeliveryBuilding(),
            'deliveryPhoneCode' => $order->getDeliveryPhoneCode(),
            'deliveryPhone' => $order->getDeliveryPhone(),
            'sex' => $order->getSex(),
            'clientName' => $order->getClientName(),
            'clientSurname' => $order->getClientSurname(),
            'companyName' => $order->getCompanyName(),
            'payType' => $order->getPayType(),
            'payDateExecution' => $order->getPayDateExecution()?->format('Y-m-d H:i:s'),
            'offsetDate' => $order->getOffsetDate()?->format('Y-m-d H:i:s'),
            'offsetReason' => $order->getOffsetReason(),
            'proposedDate' => $order->getProposedDate()?->format('Y-m-d H:i:s'),
            'shipDate' => $order->getShipDate()?->format('Y-m-d H:i:s'),
            'trackingNumber' => $order->getTrackingNumber(),
            'managerName' => $order->getManagerName(),
            'managerEmail' => $order->getManagerEmail(),
            'managerPhone' => $order->getManagerPhone(),
            'carrierName' => $order->getCarrierName(),
            'carrierContactData' => $order->getCarrierContactData(),
            'locale' => $order->getLocale(),
            'curRate' => $order->getCurRate(),
            'currency' => $order->getCurrency(),
            'measure' => $order->getMeasure(),
            'name' => $order->getName(),
            'description' => $order->getDescription(),
            'createDate' => $order->getCreateDate()->format('Y-m-d H:i:s'),
            'updateDate' => $order->getUpdateDate()?->format('Y-m-d H:i:s'),
            'warehouseData' => $order->getWarehouseData(),
            'step' => $order->getStep(),
            'addressEqual' => $order->getAddressEqual(),
            'bankTransferRequested' => $order->getBankTransferRequested(),
            'acceptPay' => $order->getAcceptPay(),
            'cancelDate' => $order->getCancelDate()?->format('Y-m-d H:i:s'),
            'weightGross' => $order->getWeightGross(),
            'productReview' => $order->getProductReview(),
            'mirror' => $order->getMirror(),
            'process' => $order->getProcess(),
            'factDate' => $order->getFactDate()?->format('Y-m-d H:i:s'),
            'entranceReview' => $order->getEntranceReview(),
            'paymentEuro' => $order->getPaymentEuro(),
            'specPrice' => $order->getSpecPrice(),
            'showMsg' => $order->getShowMsg(),
            'deliveryPriceEuro' => $order->getDeliveryPriceEuro(),
            'addressPayer' => $order->getAddressPayer(),
            'sendingDate' => $order->getSendingDate()?->format('Y-m-d H:i:s'),
            'deliveryCalculateType' => $order->getDeliveryCalculateType(),
            'fullPaymentDate' => $order->getFullPaymentDate()?->format('Y-m-d'),
            'bankDetails' => $order->getBankDetails(),
            'deliveryApartmentOffice' => $order->getDeliveryApartmentOffice(),
            'articles' => $articles,
        ];
    }
}
