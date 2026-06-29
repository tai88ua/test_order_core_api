<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: 'orders', options: ['comment' => 'Хранит информацию о заказах'])]
#[ORM\Index(columns: ['delivery_country'], name: 'IDX_1')]
#[ORM\Index(columns: ['user_id'], name: 'IDX_2')]
#[ORM\Index(columns: ['create_date'], name: 'IDX_3')]
#[ORM\Index(columns: ['create_date', 'status'], name: 'IDX_4')]
#[ORM\Index(columns: ['hash'], name: 'IDX_5')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 32, options: ['comment' => 'hash заказа'])]
    private string $hash;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $userId = null;

    #[ORM\Column(type: Types::STRING, length: 64, options: ['comment' => 'уникальный хеш пользователя'])]
    private string $token;

    #[ORM\Column(type: Types::STRING, length: 10, nullable: true, options: ['comment' => 'Номер заказа'])]
    private ?string $number = null;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 1, 'comment' => 'Статус заказа'])]
    private int $status = 1;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => 'контактный E-mail'])]
    private ?string $email = null;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0, 'comment' => 'Частное лицо или плательщик НДС'])]
    private int $vatType = 0;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => 'НДС-номер'])]
    private ?string $vatNumber = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true, options: ['comment' => 'Индивидуальный налоговый номер налогоплательщика'])]
    private ?string $taxNumber = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true, options: ['comment' => 'Процент скидки'])]
    private ?int $discount = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true, options: ['comment' => 'Стоимость доставки'])]
    private ?float $delivery = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true, options: ['default' => 0, 'comment' => 'Тип доставки: 0 - адрес клинта, 1 - адрес склада'])]
    private ?int $deliveryType = 0;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true, options: ['comment' => 'Минимальный срок доставки'])]
    private ?\DateTime $deliveryTimeMin = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true, options: ['comment' => 'Максимальный срок доставки'])]
    private ?\DateTime $deliveryTimeMax = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true, options: ['comment' => 'Минимальный срок доставки подтверждённый производителем'])]
    private ?\DateTime $deliveryTimeConfirmMin = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true, options: ['comment' => 'Максимальный срок доставки подтверждённый производителем'])]
    private ?\DateTime $deliveryTimeConfirmMax = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true, options: ['comment' => 'Минимальный срок доставки'])]
    private ?\DateTime $deliveryTimeFastPayMin = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true, options: ['comment' => 'Максимальный срок доставки'])]
    private ?\DateTime $deliveryTimeFastPayMax = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true, options: ['comment' => 'Прошлый минимальный срок доставки'])]
    private ?\DateTime $deliveryOldTimeMin = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true, options: ['comment' => 'Прошлый максимальный срок доставки'])]
    private ?\DateTime $deliveryOldTimeMax = null;

    #[ORM\Column(type: Types::STRING, length: 20, nullable: true)]
    private ?string $deliveryIndex = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $deliveryCountry = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    private ?string $deliveryRegion = null;

    #[ORM\Column(type: Types::STRING, length: 200, nullable: true)]
    private ?string $deliveryCity = null;

    #[ORM\Column(type: Types::STRING, length: 300, nullable: true)]
    private ?string $deliveryAddress = null;

    #[ORM\Column(type: Types::STRING, length: 200, nullable: true)]
    private ?string $deliveryBuilding = null;

    #[ORM\Column(type: Types::STRING, length: 20, nullable: true)]
    private ?string $deliveryPhoneCode = null;

    #[ORM\Column(type: Types::STRING, length: 20, nullable: true)]
    private ?string $deliveryPhone = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true, options: ['comment' => 'Пол клиента'])]
    private ?int $sex = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => 'Имя клиента'])]
    private ?string $clientName = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => 'Фамилия клиента'])]
    private ?string $clientSurname = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => 'Название компании'])]
    private ?string $companyName = null;

    #[ORM\Column(type: Types::SMALLINT, options: ['comment' => 'Выбранный тип оплаты'])]
    private int $payType;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => 'Дата до которой действует текущая цена заказа'])]
    private ?\DateTime $payDateExecution = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => 'Дата сдвига предполагаемого расчета доставки'])]
    private ?\DateTime $offsetDate = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true, options: ['comment' => 'тип причина сдвига сроков 1 - каникулы на фабрике, 2 - фабрика уточняет сроки пр-ва, 3 - другое'])]
    private ?int $offsetReason = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => 'Предполагаемая дата поставки'])]
    private ?\DateTime $proposedDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => 'Предполагаемая дата отгрузки'])]
    private ?\DateTime $shipDate = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true, options: ['comment' => 'Номер треккинга'])]
    private ?string $trackingNumber = null;

    #[ORM\Column(type: Types::STRING, length: 20, nullable: true, options: ['comment' => 'Имя менеджера сопровождающего заказ'])]
    private ?string $managerName = null;

    #[ORM\Column(type: Types::STRING, length: 30, nullable: true, options: ['comment' => 'Email менеджера сопровождающего заказ'])]
    private ?string $managerEmail = null;

    #[ORM\Column(type: Types::STRING, length: 20, nullable: true, options: ['comment' => 'Телефон менеджера сопровождающего заказ'])]
    private ?string $managerPhone = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true, options: ['comment' => 'Название транспортной компании'])]
    private ?string $carrierName = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => 'Контактные данные транспортной компании'])]
    private ?string $carrierContactData = null;

    #[ORM\Column(type: Types::STRING, length: 5, options: ['comment' => 'локаль из которой был оформлен заказ'])]
    private string $locale;

    #[ORM\Column(type: Types::FLOAT, nullable: true, options: ['default' => 1, 'comment' => 'курс на момент оплаты'])]
    private ?float $curRate = 1;

    #[ORM\Column(type: Types::STRING, length: 3, options: ['default' => 'EUR', 'comment' => 'валюта при которой был оформлен заказ'])]
    private string $currency = 'EUR';

    #[ORM\Column(type: Types::STRING, length: 3, options: ['default' => 'm', 'comment' => 'ед. изм. в которой был оформлен заказ'])]
    private string $measure = 'm';

    #[ORM\Column(type: Types::STRING, length: 200, options: ['comment' => 'Название заказа'])]
    private string $name;

    #[ORM\Column(type: Types::STRING, length: 1000, nullable: true, options: ['comment' => 'Дополнительная информация'])]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, options: ['comment' => 'Дата создания'])]
    private \DateTime $createDate;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => 'Дата изменения'])]
    private ?\DateTime $updateDate = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => 'Данные склада: адрес, название, часы работы'])]
    private ?string $warehouseData = null;

    #[ORM\Column(type: Types::SMALLINT, options: ['default' => 1, 'comment' => 'если true то заказ не будет сброшен в следствии изменений'])]
    private int $step = 1;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['default' => 1, 'comment' => 'Адреса плательщика и получателя совпадают (false - разные, true - одинаковые )'])]
    private ?bool $addressEqual = true;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => 'Запрашивался ли счет на банковский перевод'])]
    private ?bool $bankTransferRequested = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => 'Если true то заказ отправлен в работу'])]
    private ?bool $acceptPay = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => 'Конечная дата согласования сроков поставки'])]
    private ?\DateTime $cancelDate = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true, options: ['comment' => 'Общий вес брутто заказа'])]
    private ?float $weightGross = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => 'Оставлен отзыв по коллекциям в заказе'])]
    private ?bool $productReview = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true, options: ['comment' => 'Метка зеркала на котором создается заказ'])]
    private ?int $mirror = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => 'метка массовой обработки'])]
    private ?bool $process = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => 'Фактическая дата поставки'])]
    private ?\DateTime $factDate = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true, options: ['comment' => 'Фиксирует вход клиента на страницу отзыва и последующие клики'])]
    private ?int $entranceReview = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['default' => 0, 'comment' => 'Если true, то оплату посчитать в евро'])]
    private ?bool $paymentEuro = false;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => 'установлена спец цена по заказу'])]
    private ?bool $specPrice = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => 'Показывать спец. сообщение'])]
    private ?bool $showMsg = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true, options: ['comment' => 'Стоимость доставки в евро'])]
    private ?float $deliveryPriceEuro = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $addressPayer = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => 'Расчетная дата поставки'])]
    private ?\DateTime $sendingDate = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true, options: ['default' => 0, 'comment' => 'Тип расчета: 0 - ручной, 1 - автоматический'])]
    private ?int $deliveryCalculateType = 0;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true, options: ['comment' => 'Дата полной оплаты заказа'])]
    private ?\DateTime $fullPaymentDate = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => 'Реквизиты банка для возврата средств'])]
    private ?string $bankDetails = null;

    #[ORM\Column(type: Types::STRING, length: 30, nullable: true, options: ['comment' => 'Квартира/офис'])]
    private ?string $deliveryApartmentOffice = null;

    /** @var Collection<int, OrderArticle> */
    #[ORM\OneToMany(targetEntity: OrderArticle::class, mappedBy: 'order', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $articles;

    public function __construct()
    {
        $this->articles = new ArrayCollection();
        $this->createDate = new \DateTime();
    }

    public function getId(): ?int { return $this->id; }

    public function getHash(): string { return $this->hash; }
    public function setHash(string $hash): static { $this->hash = $hash; return $this; }

    public function getUserId(): ?int { return $this->userId; }
    public function setUserId(?int $userId): static { $this->userId = $userId; return $this; }

    public function getToken(): string { return $this->token; }
    public function setToken(string $token): static { $this->token = $token; return $this; }

    public function getNumber(): ?string { return $this->number; }
    public function setNumber(?string $number): static { $this->number = $number; return $this; }

    public function getStatus(): int { return $this->status; }
    public function setStatus(int $status): static { $this->status = $status; return $this; }

    public function getEmail(): ?string { return $this->email; }
    public function setEmail(?string $email): static { $this->email = $email; return $this; }

    public function getVatType(): int { return $this->vatType; }
    public function setVatType(int $vatType): static { $this->vatType = $vatType; return $this; }

    public function getVatNumber(): ?string { return $this->vatNumber; }
    public function setVatNumber(?string $vatNumber): static { $this->vatNumber = $vatNumber; return $this; }

    public function getTaxNumber(): ?string { return $this->taxNumber; }
    public function setTaxNumber(?string $taxNumber): static { $this->taxNumber = $taxNumber; return $this; }

    public function getDiscount(): ?int { return $this->discount; }
    public function setDiscount(?int $discount): static { $this->discount = $discount; return $this; }

    public function getDelivery(): ?float { return $this->delivery; }
    public function setDelivery(?float $delivery): static { $this->delivery = $delivery; return $this; }

    public function getDeliveryType(): ?int { return $this->deliveryType; }
    public function setDeliveryType(?int $deliveryType): static { $this->deliveryType = $deliveryType; return $this; }

    public function getDeliveryTimeMin(): ?\DateTime { return $this->deliveryTimeMin; }
    public function setDeliveryTimeMin(?\DateTime $v): static { $this->deliveryTimeMin = $v; return $this; }

    public function getDeliveryTimeMax(): ?\DateTime { return $this->deliveryTimeMax; }
    public function setDeliveryTimeMax(?\DateTime $v): static { $this->deliveryTimeMax = $v; return $this; }

    public function getDeliveryTimeConfirmMin(): ?\DateTime { return $this->deliveryTimeConfirmMin; }
    public function setDeliveryTimeConfirmMin(?\DateTime $v): static { $this->deliveryTimeConfirmMin = $v; return $this; }

    public function getDeliveryTimeConfirmMax(): ?\DateTime { return $this->deliveryTimeConfirmMax; }
    public function setDeliveryTimeConfirmMax(?\DateTime $v): static { $this->deliveryTimeConfirmMax = $v; return $this; }

    public function getDeliveryTimeFastPayMin(): ?\DateTime { return $this->deliveryTimeFastPayMin; }
    public function setDeliveryTimeFastPayMin(?\DateTime $v): static { $this->deliveryTimeFastPayMin = $v; return $this; }

    public function getDeliveryTimeFastPayMax(): ?\DateTime { return $this->deliveryTimeFastPayMax; }
    public function setDeliveryTimeFastPayMax(?\DateTime $v): static { $this->deliveryTimeFastPayMax = $v; return $this; }

    public function getDeliveryOldTimeMin(): ?\DateTime { return $this->deliveryOldTimeMin; }
    public function setDeliveryOldTimeMin(?\DateTime $v): static { $this->deliveryOldTimeMin = $v; return $this; }

    public function getDeliveryOldTimeMax(): ?\DateTime { return $this->deliveryOldTimeMax; }
    public function setDeliveryOldTimeMax(?\DateTime $v): static { $this->deliveryOldTimeMax = $v; return $this; }

    public function getDeliveryIndex(): ?string { return $this->deliveryIndex; }
    public function setDeliveryIndex(?string $v): static { $this->deliveryIndex = $v; return $this; }

    public function getDeliveryCountry(): ?int { return $this->deliveryCountry; }
    public function setDeliveryCountry(?int $v): static { $this->deliveryCountry = $v; return $this; }

    public function getDeliveryRegion(): ?string { return $this->deliveryRegion; }
    public function setDeliveryRegion(?string $v): static { $this->deliveryRegion = $v; return $this; }

    public function getDeliveryCity(): ?string { return $this->deliveryCity; }
    public function setDeliveryCity(?string $v): static { $this->deliveryCity = $v; return $this; }

    public function getDeliveryAddress(): ?string { return $this->deliveryAddress; }
    public function setDeliveryAddress(?string $v): static { $this->deliveryAddress = $v; return $this; }

    public function getDeliveryBuilding(): ?string { return $this->deliveryBuilding; }
    public function setDeliveryBuilding(?string $v): static { $this->deliveryBuilding = $v; return $this; }

    public function getDeliveryPhoneCode(): ?string { return $this->deliveryPhoneCode; }
    public function setDeliveryPhoneCode(?string $v): static { $this->deliveryPhoneCode = $v; return $this; }

    public function getDeliveryPhone(): ?string { return $this->deliveryPhone; }
    public function setDeliveryPhone(?string $v): static { $this->deliveryPhone = $v; return $this; }

    public function getSex(): ?int { return $this->sex; }
    public function setSex(?int $sex): static { $this->sex = $sex; return $this; }

    public function getClientName(): ?string { return $this->clientName; }
    public function setClientName(?string $v): static { $this->clientName = $v; return $this; }

    public function getClientSurname(): ?string { return $this->clientSurname; }
    public function setClientSurname(?string $v): static { $this->clientSurname = $v; return $this; }

    public function getCompanyName(): ?string { return $this->companyName; }
    public function setCompanyName(?string $v): static { $this->companyName = $v; return $this; }

    public function getPayType(): int { return $this->payType; }
    public function setPayType(int $payType): static { $this->payType = $payType; return $this; }

    public function getPayDateExecution(): ?\DateTime { return $this->payDateExecution; }
    public function setPayDateExecution(?\DateTime $v): static { $this->payDateExecution = $v; return $this; }

    public function getOffsetDate(): ?\DateTime { return $this->offsetDate; }
    public function setOffsetDate(?\DateTime $v): static { $this->offsetDate = $v; return $this; }

    public function getOffsetReason(): ?int { return $this->offsetReason; }
    public function setOffsetReason(?int $v): static { $this->offsetReason = $v; return $this; }

    public function getProposedDate(): ?\DateTime { return $this->proposedDate; }
    public function setProposedDate(?\DateTime $v): static { $this->proposedDate = $v; return $this; }

    public function getShipDate(): ?\DateTime { return $this->shipDate; }
    public function setShipDate(?\DateTime $v): static { $this->shipDate = $v; return $this; }

    public function getTrackingNumber(): ?string { return $this->trackingNumber; }
    public function setTrackingNumber(?string $v): static { $this->trackingNumber = $v; return $this; }

    public function getManagerName(): ?string { return $this->managerName; }
    public function setManagerName(?string $v): static { $this->managerName = $v; return $this; }

    public function getManagerEmail(): ?string { return $this->managerEmail; }
    public function setManagerEmail(?string $v): static { $this->managerEmail = $v; return $this; }

    public function getManagerPhone(): ?string { return $this->managerPhone; }
    public function setManagerPhone(?string $v): static { $this->managerPhone = $v; return $this; }

    public function getCarrierName(): ?string { return $this->carrierName; }
    public function setCarrierName(?string $v): static { $this->carrierName = $v; return $this; }

    public function getCarrierContactData(): ?string { return $this->carrierContactData; }
    public function setCarrierContactData(?string $v): static { $this->carrierContactData = $v; return $this; }

    public function getLocale(): string { return $this->locale; }
    public function setLocale(string $locale): static { $this->locale = $locale; return $this; }

    public function getCurRate(): ?float { return $this->curRate; }
    public function setCurRate(?float $v): static { $this->curRate = $v; return $this; }

    public function getCurrency(): string { return $this->currency; }
    public function setCurrency(string $currency): static { $this->currency = $currency; return $this; }

    public function getMeasure(): string { return $this->measure; }
    public function setMeasure(string $measure): static { $this->measure = $measure; return $this; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $v): static { $this->description = $v; return $this; }

    public function getCreateDate(): \DateTime { return $this->createDate; }
    public function setCreateDate(\DateTime $v): static { $this->createDate = $v; return $this; }

    public function getUpdateDate(): ?\DateTime { return $this->updateDate; }
    public function setUpdateDate(?\DateTime $v): static { $this->updateDate = $v; return $this; }

    public function getWarehouseData(): ?string { return $this->warehouseData; }
    public function setWarehouseData(?string $v): static { $this->warehouseData = $v; return $this; }

    public function getStep(): int { return $this->step; }
    public function setStep(int $step): static { $this->step = $step; return $this; }

    public function getAddressEqual(): ?bool { return $this->addressEqual; }
    public function setAddressEqual(?bool $v): static { $this->addressEqual = $v; return $this; }

    public function getBankTransferRequested(): ?bool { return $this->bankTransferRequested; }
    public function setBankTransferRequested(?bool $v): static { $this->bankTransferRequested = $v; return $this; }

    public function getAcceptPay(): ?bool { return $this->acceptPay; }
    public function setAcceptPay(?bool $v): static { $this->acceptPay = $v; return $this; }

    public function getCancelDate(): ?\DateTime { return $this->cancelDate; }
    public function setCancelDate(?\DateTime $v): static { $this->cancelDate = $v; return $this; }

    public function getWeightGross(): ?float { return $this->weightGross; }
    public function setWeightGross(?float $v): static { $this->weightGross = $v; return $this; }

    public function getProductReview(): ?bool { return $this->productReview; }
    public function setProductReview(?bool $v): static { $this->productReview = $v; return $this; }

    public function getMirror(): ?int { return $this->mirror; }
    public function setMirror(?int $v): static { $this->mirror = $v; return $this; }

    public function getProcess(): ?bool { return $this->process; }
    public function setProcess(?bool $v): static { $this->process = $v; return $this; }

    public function getFactDate(): ?\DateTime { return $this->factDate; }
    public function setFactDate(?\DateTime $v): static { $this->factDate = $v; return $this; }

    public function getEntranceReview(): ?int { return $this->entranceReview; }
    public function setEntranceReview(?int $v): static { $this->entranceReview = $v; return $this; }

    public function getPaymentEuro(): ?bool { return $this->paymentEuro; }
    public function setPaymentEuro(?bool $v): static { $this->paymentEuro = $v; return $this; }

    public function getSpecPrice(): ?bool { return $this->specPrice; }
    public function setSpecPrice(?bool $v): static { $this->specPrice = $v; return $this; }

    public function getShowMsg(): ?bool { return $this->showMsg; }
    public function setShowMsg(?bool $v): static { $this->showMsg = $v; return $this; }

    public function getDeliveryPriceEuro(): ?float { return $this->deliveryPriceEuro; }
    public function setDeliveryPriceEuro(?float $v): static { $this->deliveryPriceEuro = $v; return $this; }

    public function getAddressPayer(): ?int { return $this->addressPayer; }
    public function setAddressPayer(?int $v): static { $this->addressPayer = $v; return $this; }

    public function getSendingDate(): ?\DateTime { return $this->sendingDate; }
    public function setSendingDate(?\DateTime $v): static { $this->sendingDate = $v; return $this; }

    public function getDeliveryCalculateType(): ?int { return $this->deliveryCalculateType; }
    public function setDeliveryCalculateType(?int $v): static { $this->deliveryCalculateType = $v; return $this; }

    public function getFullPaymentDate(): ?\DateTime { return $this->fullPaymentDate; }
    public function setFullPaymentDate(?\DateTime $v): static { $this->fullPaymentDate = $v; return $this; }

    public function getBankDetails(): ?string { return $this->bankDetails; }
    public function setBankDetails(?string $v): static { $this->bankDetails = $v; return $this; }

    public function getDeliveryApartmentOffice(): ?string { return $this->deliveryApartmentOffice; }
    public function setDeliveryApartmentOffice(?string $v): static { $this->deliveryApartmentOffice = $v; return $this; }

    /** @return Collection<int, OrderArticle> */
    public function getArticles(): Collection { return $this->articles; }

    public function addArticle(OrderArticle $article): static
    {
        if (!$this->articles->contains($article)) {
            $this->articles->add($article);
            $article->setOrder($this);
        }
        return $this;
    }

    public function removeArticle(OrderArticle $article): static
    {
        if ($this->articles->removeElement($article)) {
            if ($article->getOrder() === $this) {
                $article->setOrder(null);
            }
        }
        return $this;
    }
}
