<?php

namespace App\Service;

use App\Entity\Order;
use App\Entity\OrderArticle;
use Doctrine\ORM\EntityManagerInterface;

class SoapOrderService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {}

    /**
     * Создает заказ из переданных SOAP-данных.
     *
     * @param mixed $orderData Данные заказа
     * @return array Результат выполнения операции
     */
    public function createOrder(mixed $orderData): array
    {
        if (empty($orderData)) {
            throw new \SoapFault('Sender', 'Missing order data parameter');
        }

        // В WSDL режиме параметры метода оборачиваются в объект запроса
        if (is_object($orderData) && isset($orderData->orderData)) {
            $orderData = $orderData->orderData;
        } elseif (is_array($orderData) && isset($orderData['orderData'])) {
            $orderData = $orderData['orderData'];
        }

        $order = new Order();
        $order->setHash($this->getValue($orderData, 'hash') ?: md5(uniqid((string)rand(), true)));
        $order->setUserId($this->getValue($orderData, 'userId') !== null ? (int)$this->getValue($orderData, 'userId') : null);
        $order->setToken($this->getValue($orderData, 'token') ?: sha1(uniqid((string)rand(), true)));
        $order->setNumber($this->getValue($orderData, 'number'));
        $order->setStatus((int)$this->getValue($orderData, 'status', 1));
        $order->setEmail($this->getValue($orderData, 'email'));
        $order->setVatType((int)$this->getValue($orderData, 'vatType', 0));
        $order->setVatNumber($this->getValue($orderData, 'vatNumber'));
        $order->setTaxNumber($this->getValue($orderData, 'taxNumber'));
        $order->setDiscount($this->getValue($orderData, 'discount') !== null ? (int)$this->getValue($orderData, 'discount') : null);
        $order->setDelivery($this->getValue($orderData, 'delivery') !== null ? (float)$this->getValue($orderData, 'delivery') : null);
        $order->setDeliveryType($this->getValue($orderData, 'deliveryType') !== null ? (int)$this->getValue($orderData, 'deliveryType') : 0);
        
        $order->setDeliveryIndex($this->getValue($orderData, 'deliveryIndex'));
        $order->setDeliveryCountry($this->getValue($orderData, 'deliveryCountry') !== null ? (int)$this->getValue($orderData, 'deliveryCountry') : null);
        $order->setDeliveryRegion($this->getValue($orderData, 'deliveryRegion'));
        $order->setDeliveryCity($this->getValue($orderData, 'deliveryCity'));
        $order->setDeliveryAddress($this->getValue($orderData, 'deliveryAddress'));
        $order->setDeliveryBuilding($this->getValue($orderData, 'deliveryBuilding'));
        $order->setDeliveryPhoneCode($this->getValue($orderData, 'deliveryPhoneCode'));
        $order->setDeliveryPhone($this->getValue($orderData, 'deliveryPhone'));
        
        $order->setClientName($this->getValue($orderData, 'clientName'));
        $order->setClientSurname($this->getValue($orderData, 'clientSurname'));
        $order->setCompanyName($this->getValue($orderData, 'companyName'));
        
        $order->setPayType((int)$this->getValue($orderData, 'payType', 1));
        $order->setLocale($this->getValue($orderData, 'locale', 'ru'));
        $order->setCurrency($this->getValue($orderData, 'currency', 'EUR'));
        $order->setMeasure($this->getValue($orderData, 'measure', 'm'));
        $order->setName($this->getValue($orderData, 'name', 'SOAP Order'));
        $order->setDescription($this->getValue($orderData, 'description'));
        $order->setStep(1);
        $order->setWeightGross($this->getValue($orderData, 'weightGross') !== null ? (float)$this->getValue($orderData, 'weightGross') : null);

        // Получаем и нормализуем статьи/товары заказа
        $articlesData = $this->getValue($orderData, 'articles', []);
        
        // В SOAP XML-структуре коллекция может быть обернута в объект-обертку
        if (is_object($articlesData)) {
            $articlesData = $this->getValue($articlesData, 'article', []);
        }

        $articlesList = [];
        if (is_array($articlesData)) {
            // Проверяем, передан ли один элемент как ассоциативный массив или список элементов
            if (isset($articlesData['articleId']) || isset($articlesData->articleId)) {
                $articlesList[] = $articlesData;
            } else {
                $articlesList = $articlesData;
            }
        } elseif (is_object($articlesData)) {
            $articlesList[] = $articlesData;
        }

        foreach ($articlesList as $articleData) {
            $article = new OrderArticle();
            $article->setArticleId((int)$this->getValue($articleData, 'articleId'));
            $article->setAmount((float)$this->getValue($articleData, 'amount', 1.0));
            $article->setPrice((float)$this->getValue($articleData, 'price', 0.0));
            $article->setPriceEur($this->getValue($articleData, 'priceEur') !== null ? (float)$this->getValue($articleData, 'priceEur') : (float)$this->getValue($articleData, 'price', 0.0));
            $article->setCurrency($this->getValue($articleData, 'currency', 'EUR'));
            $article->setMeasure($this->getValue($articleData, 'measure', 'mq'));
            $article->setWeight((float)$this->getValue($articleData, 'weight', 0.0));
            $article->setMultiplePallet($this->getValue($articleData, 'multiplePallet') !== null ? (int)$this->getValue($articleData, 'multiplePallet') : 1);
            $article->setPackagingCount((float)$this->getValue($articleData, 'packagingCount', 1.0));
            $article->setPallet((float)$this->getValue($articleData, 'pallet', 0.0));
            $article->setPackaging((float)$this->getValue($articleData, 'packaging', 1.0));
            $article->setSwimmingPool((bool)$this->getValue($articleData, 'swimmingPool', false));

            $order->addArticle($article);
        }

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        return [
            'return' => [
                'success' => true,
                'orderId' => $order->getId(),
                'hash' => $order->getHash(),
                'message' => 'Order created successfully'
            ]
        ];
    }

    /**
     * Безопасное получение значения из объекта или массива.
     */
    private function getValue(mixed $data, string $key, mixed $default = null): mixed
    {
        if (is_array($data)) {
            return $data[$key] ?? $default;
        }
        if (is_object($data)) {
            return $data->$key ?? $default;
        }
        return $default;
    }
}
