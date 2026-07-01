<?php

namespace App\Tests\Controller;

use App\Controller\OrderController;
use App\Entity\Order;
use App\Entity\OrderArticle;
use App\Repository\OrderRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class OrderControllerTest extends TestCase
{
    public function testShowByIdSuccess(): void
    {
        // Mock Order
        $order = new Order();
        $order->setHash('d3b07384d113edec49eaa6238ad5ff00');
        $order->setToken('token123');
        $order->setName('Test Order');
        $order->setPayType(1);
        $order->setLocale('ru');
        $order->setCurrency('RUB');
        $order->setMeasure('m');
        $order->setCreateDate(new \DateTime('2026-06-29 12:00:00'));

        // Add an article
        $article = new OrderArticle();
        $article->setArticleId(101);
        $article->setAmount(5.0);
        $article->setPrice(99.99);
        $article->setWeight(1.2);
        $article->setPackagingCount(1.0);
        $article->setPallet(40.0);
        $article->setPackaging(1.0);
        $order->addArticle($article);

        // Mock OrderRepository
        $orderRepository = $this->createMock(OrderRepository::class);
        $orderRepository->expects($this->once())
            ->method('find')
            ->with(42)
            ->willReturn($order);

        $controller = new OrderController($orderRepository);
        $response = $controller->show('42');

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertSame('d3b07384d113edec49eaa6238ad5ff00', $data['hash']);
        $this->assertSame('Test Order', $data['name']);
        $this->assertCount(1, $data['articles']);
        $this->assertSame(101, $data['articles'][0]['articleId']);
        $this->assertSame(99.99, $data['articles'][0]['price']);
    }

    public function testShowByHashSuccess(): void
    {
        // Mock Order
        $order = new Order();
        $order->setHash('d3b07384d113edec49eaa6238ad5ff00');
        $order->setToken('token123');
        $order->setName('Test Order Hash');
        $order->setPayType(2);
        $order->setLocale('en');
        $order->setCurrency('USD');
        $order->setMeasure('m');
        $order->setCreateDate(new \DateTime('2026-06-29 15:30:00'));

        // Mock OrderRepository
        $orderRepository = $this->createMock(OrderRepository::class);
        $orderRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['hash' => 'd3b07384d113edec49eaa6238ad5ff00'])
            ->willReturn($order);

        $controller = new OrderController($orderRepository);
        $response = $controller->show('d3b07384d113edec49eaa6238ad5ff00');

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertSame('d3b07384d113edec49eaa6238ad5ff00', $data['hash']);
        $this->assertSame('Test Order Hash', $data['name']);
        $this->assertEmpty($data['articles']);
    }

    public function testShowNotFound(): void
    {
        $orderRepository = $this->createMock(OrderRepository::class);
        $orderRepository->expects($this->once())
            ->method('find')
            ->with(999)
            ->willReturn(null);

        $controller = new OrderController($orderRepository);
        $response = $controller->show('999');

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertSame('Order not found', $data['error']);
    }
}
