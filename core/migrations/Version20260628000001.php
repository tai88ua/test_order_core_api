<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration: создание таблиц orders и orders_article по dump.sql
 */
final class Version20260628000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Создание таблиц orders и orders_article';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE orders (
                id                         INT AUTO_INCREMENT NOT NULL COMMENT 'hash заказа',
                hash                       VARCHAR(32)              NOT NULL COMMENT 'hash заказа',
                user_id                    INT                      NULL,
                token                      VARCHAR(64)              NOT NULL COMMENT 'уникальный хеш пользователя',
                number                     VARCHAR(10)              NULL COMMENT 'Номер заказа',
                status                     INT        DEFAULT 1     NOT NULL COMMENT 'Статус заказа',
                email                      VARCHAR(100)             NULL COMMENT 'контактный E-mail',
                vat_type                   INT        DEFAULT 0     NOT NULL COMMENT 'Частное лицо или плательщик НДС',
                vat_number                 VARCHAR(100)             NULL COMMENT 'НДС-номер',
                tax_number                 VARCHAR(50)              NULL COMMENT 'Индивидуальный налоговый номер налогоплательщика',
                discount                   SMALLINT                 NULL COMMENT 'Процент скидки',
                delivery                   DOUBLE PRECISION         NULL COMMENT 'Стоимость доставки',
                delivery_type              SMALLINT   DEFAULT 0     NULL COMMENT 'Тип доставки: 0 - адрес клинта, 1 - адрес склада',
                delivery_time_min          DATE                     NULL COMMENT 'Минимальный срок доставки',
                delivery_time_max          DATE                     NULL COMMENT 'Максимальный срок доставки',
                delivery_time_confirm_min  DATE                     NULL COMMENT 'Минимальный срок доставки подтверждённый производителем',
                delivery_time_confirm_max  DATE                     NULL COMMENT 'Максимальный срок доставки подтверждённый производителем',
                delivery_time_fast_pay_min DATE                     NULL COMMENT 'Минимальный срок доставки',
                delivery_time_fast_pay_max DATE                     NULL COMMENT 'Максимальный срок доставки',
                delivery_old_time_min      DATE                     NULL COMMENT 'Прошлый минимальный срок доставки',
                delivery_old_time_max      DATE                     NULL COMMENT 'Прошлый максимальный срок доставки',
                delivery_index             VARCHAR(20)              NULL,
                delivery_country           INT                      NULL,
                delivery_region            VARCHAR(50)              NULL,
                delivery_city              VARCHAR(200)             NULL,
                delivery_address           VARCHAR(300)             NULL,
                delivery_building          VARCHAR(200)             NULL,
                delivery_phone_code        VARCHAR(20)              NULL,
                delivery_phone             VARCHAR(20)              NULL,
                sex                        SMALLINT                 NULL COMMENT 'Пол клиента',
                client_name                VARCHAR(255)             NULL COMMENT 'Имя клиента',
                client_surname             VARCHAR(255)             NULL COMMENT 'Фамилия клиента',
                company_name               VARCHAR(255)             NULL COMMENT 'Название компании',
                pay_type                   SMALLINT                 NOT NULL COMMENT 'Выбранный тип оплаты',
                pay_date_execution         DATETIME                 NULL COMMENT 'Дата до которой действует текущая цена заказа',
                offset_date                DATETIME                 NULL COMMENT 'Дата сдвига предполагаемого расчета доставки',
                offset_reason              SMALLINT                 NULL COMMENT 'тип причина сдвига сроков 1 - каникулы на фабрике, 2 - фабрика уточняет сроки пр-ва, 3 - другое',
                proposed_date              DATETIME                 NULL COMMENT 'Предполагаемая дата поставки',
                ship_date                  DATETIME                 NULL COMMENT 'Предполагаемая дата отгрузки',
                tracking_number            VARCHAR(50)              NULL COMMENT 'Номер треккинга',
                manager_name               VARCHAR(20)              NULL COMMENT 'Имя менеджера сопровождающего заказ',
                manager_email              VARCHAR(30)              NULL COMMENT 'Email менеджера сопровождающего заказ',
                manager_phone              VARCHAR(20)              NULL COMMENT 'Телефон менеджера сопровождающего заказ',
                carrier_name               VARCHAR(50)              NULL COMMENT 'Название транспортной компании',
                carrier_contact_data       VARCHAR(255)             NULL COMMENT 'Контактные данные транспортной компании',
                locale                     VARCHAR(5)               NOT NULL COMMENT 'локаль из которой был оформлен заказ',
                cur_rate                   DOUBLE PRECISION DEFAULT 1 NULL COMMENT 'курс на момент оплаты',
                currency                   VARCHAR(3) DEFAULT 'EUR'  NOT NULL COMMENT 'валюта при которой был оформлен заказ',
                measure                    VARCHAR(3) DEFAULT 'm'    NOT NULL COMMENT 'ед. изм. в которой был оформлен заказ',
                name                       VARCHAR(200)             NOT NULL COMMENT 'Название заказа',
                description                VARCHAR(1000)            NULL COMMENT 'Дополнительная информация',
                create_date                DATETIME                 NOT NULL COMMENT 'Дата создания',
                update_date                DATETIME                 NULL COMMENT 'Дата изменения',
                warehouse_data             LONGTEXT                 NULL COMMENT 'Данные склада: адрес, название, часы работы',
                step                       SMALLINT   DEFAULT 1     NOT NULL COMMENT 'если true то заказ не будет сброшен в следствии изменений',
                address_equal              TINYINT(1) DEFAULT 1     NULL COMMENT 'Адреса плательщика и получателя совпадают (false - разные, true - одинаковые )',
                bank_transfer_requested    TINYINT(1)               NULL COMMENT 'Запрашивался ли счет на банковский перевод',
                accept_pay                 TINYINT(1)               NULL COMMENT 'Если true то заказ отправлен в работу',
                cancel_date                DATETIME                 NULL COMMENT 'Конечная дата согласования сроков поставки',
                weight_gross               DOUBLE PRECISION         NULL COMMENT 'Общий вес брутто заказа',
                product_review             TINYINT(1)               NULL COMMENT 'Оставлен отзыв по коллекциям в заказе',
                mirror                     SMALLINT                 NULL COMMENT 'Метка зеркала на котором создается заказ',
                process                    TINYINT(1)               NULL COMMENT 'метка массовой обработки',
                fact_date                  DATETIME                 NULL COMMENT 'Фактическая дата поставки',
                entrance_review            SMALLINT                 NULL COMMENT 'Фиксирует вход клиента на страницу отзыва и последующие клики',
                payment_euro               TINYINT(1) DEFAULT 0     NULL COMMENT 'Если true, то оплату посчитать в евро',
                spec_price                 TINYINT(1)               NULL COMMENT 'установлена спец цена по заказу',
                show_msg                   TINYINT(1)               NULL COMMENT 'Показывать спец. сообщение',
                delivery_price_euro        DOUBLE PRECISION         NULL COMMENT 'Стоимость доставки в евро',
                address_payer              INT                      NULL,
                sending_date               DATETIME                 NULL COMMENT 'Расчетная дата поставки',
                delivery_calculate_type    SMALLINT   DEFAULT 0     NULL COMMENT 'Тип расчета: 0 - ручной, 1 - автоматический',
                full_payment_date          DATE                     NULL COMMENT 'Дата полной оплаты заказа',
                bank_details               LONGTEXT                 NULL COMMENT 'Реквизиты банка для возврата средств',
                delivery_apartment_office  VARCHAR(30)              NULL COMMENT 'Квартира/офис',
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = 'Хранит информацию о заказах'
        SQL);

        $this->addSql('CREATE INDEX IDX_1 ON orders (delivery_country)');
        $this->addSql('CREATE INDEX IDX_2 ON orders (user_id)');
        $this->addSql('CREATE INDEX IDX_3 ON orders (create_date)');
        $this->addSql('CREATE INDEX IDX_4 ON orders (create_date, status)');
        $this->addSql('CREATE INDEX IDX_5 ON orders (hash)');

        $this->addSql(<<<'SQL'
            CREATE TABLE orders_article (
                id               INT AUTO_INCREMENT NOT NULL,
                orders_id        INT                      NULL,
                article_id       INT                      NULL COMMENT 'ID коллекции',
                amount           DOUBLE PRECISION         NOT NULL COMMENT 'количество артикулов в ед. измерения',
                price            DOUBLE PRECISION         NOT NULL COMMENT 'Цена на момент оплаты заказа',
                price_eur        DOUBLE PRECISION         NULL COMMENT 'Цена в Евро по заказу',
                currency         VARCHAR(3)               NULL COMMENT 'Валюта для которой установлена цена',
                measure          VARCHAR(2)               NULL COMMENT 'Ед. изм. для которой установлена цена',
                delivery_time_min DATE                    NULL COMMENT 'Минимальный срок доставки',
                delivery_time_max DATE                    NULL COMMENT 'Максимальный срок доставки',
                weight           DOUBLE PRECISION         NOT NULL COMMENT 'вес упаковки',
                multiple_pallet  SMALLINT                 NULL COMMENT 'Кратность палете, 1 - кратно упаковке, 2 - кратно палете, 3 - не меньше палеты',
                packaging_count  DOUBLE PRECISION         NOT NULL COMMENT 'Количество кратно которому можно добавлять товар в заказ',
                pallet           DOUBLE PRECISION         NOT NULL COMMENT 'количество в палете на момент заказа',
                packaging        DOUBLE PRECISION         NOT NULL COMMENT 'количество в упаковке',
                swimming_pool    TINYINT(1) DEFAULT 0     NOT NULL COMMENT 'Плитка специально для бассейна',
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = 'Хранит информацию об артикулах заказа'
        SQL);

        $this->addSql('CREATE INDEX IDX_318C0B7C7294869C ON orders_article (article_id)');
        $this->addSql('CREATE INDEX IDX_318C0B7C7FC358ED ON orders_article (orders_id)');
        $this->addSql('ALTER TABLE orders_article ADD CONSTRAINT FK_orders_article_orders FOREIGN KEY (orders_id) REFERENCES orders (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE orders_article DROP FOREIGN KEY FK_orders_article_orders');
        $this->addSql('DROP TABLE orders_article');
        $this->addSql('DROP TABLE orders');
    }
}
