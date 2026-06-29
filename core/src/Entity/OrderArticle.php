<?php

namespace App\Entity;

use App\Repository\OrderArticleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderArticleRepository::class)]
#[ORM\Table(name: 'orders_article', options: ['comment' => 'Хранит информацию об артикулах заказа'])]
#[ORM\Index(columns: ['article_id'], name: 'IDX_318C0B7C7294869C')]
#[ORM\Index(columns: ['orders_id'], name: 'IDX_318C0B7C7FC358ED')]
class OrderArticle
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Order::class, inversedBy: 'articles')]
    #[ORM\JoinColumn(name: 'orders_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Order $order = null;

    #[ORM\Column(name: 'article_id', type: Types::INTEGER, nullable: true, options: ['comment' => 'ID коллекции'])]
    private ?int $articleId = null;

    #[ORM\Column(type: Types::FLOAT, options: ['comment' => 'количество артикулов в ед. измерения'])]
    private float $amount;

    #[ORM\Column(type: Types::FLOAT, options: ['comment' => 'Цена на момент оплаты заказа'])]
    private float $price;

    #[ORM\Column(name: 'price_eur', type: Types::FLOAT, nullable: true, options: ['comment' => 'Цена в Евро по заказу'])]
    private ?float $priceEur = null;

    #[ORM\Column(type: Types::STRING, length: 3, nullable: true, options: ['comment' => 'Валюта для которой установлена цена'])]
    private ?string $currency = null;

    #[ORM\Column(type: Types::STRING, length: 2, nullable: true, options: ['comment' => 'Ед. изм. для которой установлена цена'])]
    private ?string $measure = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true, options: ['comment' => 'Минимальный срок доставки'])]
    private ?\DateTime $deliveryTimeMin = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true, options: ['comment' => 'Максимальный срок доставки'])]
    private ?\DateTime $deliveryTimeMax = null;

    #[ORM\Column(type: Types::FLOAT, options: ['comment' => 'вес упаковки'])]
    private float $weight;

    #[ORM\Column(type: Types::SMALLINT, nullable: true, options: ['comment' => 'Кратность палете, 1 - кратно упаковке, 2 - кратно палете, 3 - не меньше палеты'])]
    private ?int $multiplePallet = null;

    #[ORM\Column(type: Types::FLOAT, options: ['comment' => 'Количество кратно которому можно добавлять товар в заказ'])]
    private float $packagingCount;

    #[ORM\Column(type: Types::FLOAT, options: ['comment' => 'количество в палете на момент заказа'])]
    private float $pallet;

    #[ORM\Column(type: Types::FLOAT, options: ['comment' => 'количество в упаковке'])]
    private float $packaging;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => 0, 'comment' => 'Плитка специально для бассейна'])]
    private bool $swimmingPool = false;

    public function getId(): ?int { return $this->id; }

    public function getOrder(): ?Order { return $this->order; }
    public function setOrder(?Order $order): static { $this->order = $order; return $this; }

    public function getArticleId(): ?int { return $this->articleId; }
    public function setArticleId(?int $v): static { $this->articleId = $v; return $this; }

    public function getAmount(): float { return $this->amount; }
    public function setAmount(float $amount): static { $this->amount = $amount; return $this; }

    public function getPrice(): float { return $this->price; }
    public function setPrice(float $price): static { $this->price = $price; return $this; }

    public function getPriceEur(): ?float { return $this->priceEur; }
    public function setPriceEur(?float $v): static { $this->priceEur = $v; return $this; }

    public function getCurrency(): ?string { return $this->currency; }
    public function setCurrency(?string $currency): static { $this->currency = $currency; return $this; }

    public function getMeasure(): ?string { return $this->measure; }
    public function setMeasure(?string $measure): static { $this->measure = $measure; return $this; }

    public function getDeliveryTimeMin(): ?\DateTime { return $this->deliveryTimeMin; }
    public function setDeliveryTimeMin(?\DateTime $v): static { $this->deliveryTimeMin = $v; return $this; }

    public function getDeliveryTimeMax(): ?\DateTime { return $this->deliveryTimeMax; }
    public function setDeliveryTimeMax(?\DateTime $v): static { $this->deliveryTimeMax = $v; return $this; }

    public function getWeight(): float { return $this->weight; }
    public function setWeight(float $weight): static { $this->weight = $weight; return $this; }

    public function getMultiplePallet(): ?int { return $this->multiplePallet; }
    public function setMultiplePallet(?int $v): static { $this->multiplePallet = $v; return $this; }

    public function getPackagingCount(): float { return $this->packagingCount; }
    public function setPackagingCount(float $v): static { $this->packagingCount = $v; return $this; }

    public function getPallet(): float { return $this->pallet; }
    public function setPallet(float $v): static { $this->pallet = $v; return $this; }

    public function getPackaging(): float { return $this->packaging; }
    public function setPackaging(float $v): static { $this->packaging = $v; return $this; }

    public function isSwimmingPool(): bool { return $this->swimmingPool; }
    public function setSwimmingPool(bool $v): static { $this->swimmingPool = $v; return $this; }
}
