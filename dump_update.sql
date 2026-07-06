-- 1. Создаем справочник пользователей/менеджеров (заглушка для связи)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) COMMENT 'Пользователи и менеджеры системы';

-- 2. Выделяем адреса в отдельную сущность
CREATE TABLE addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    country_id INT NOT NULL,
    zip_code VARCHAR(20) NULL,
    region VARCHAR(50) NULL,
    city VARCHAR(200) NULL,
    street_address VARCHAR(300) NULL,
    building VARCHAR(200) NULL,
    apartment_office VARCHAR(30) NULL,
    INDEX IDX_COUNTRY (country_id)
) COMMENT 'Справочник адресов';

-- 3. Выделяем транспортные компании
CREATE TABLE carriers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    contact_data VARCHAR(255) NULL
) COMMENT 'Транспортные компании';

-- 4. Главная таблица заказов (очищенная от мусора)
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hash CHAR(32) NOT NULL COMMENT 'MD5 hash заказа',
    token CHAR(64) NOT NULL COMMENT 'уникальный хеш пользователя (SHA256)',
    number VARCHAR(10) NULL COMMENT 'Номер заказа',
    status VARCHAR(30) DEFAULT 'NEW' NOT NULL COMMENT 'Строковый статус (для Enum в Symfony)',
    
    -- Связи с сущностями
    user_id INT NULL,
    manager_id INT NULL COMMENT 'Менеджер сопровождающий заказ',
    carrier_id INT NULL COMMENT 'Транспортная компания',
    delivery_address_id INT NULL,
    billing_address_id INT NULL,

    -- Контакты для гостевых заказов
    guest_email VARCHAR(100) NULL COMMENT 'E-mail (если нет user_id)',
    guest_name VARCHAR(255) NULL,
    guest_surname VARCHAR(255) NULL,
    delivery_phone_code VARCHAR(20) NULL,
    delivery_phone VARCHAR(20) NULL,
    
    -- Налоги и компания
    company_name VARCHAR(255) NULL,
    vat_type TINYINT DEFAULT 0 NOT NULL COMMENT '0 - физ.лицо, 1 - НДС плательщик',
    vat_number VARCHAR(100) NULL,
    tax_number VARCHAR(50) NULL,
    
    -- Финансы (исторические слепки на момент заказа)
    discount SMALLINT NULL,
    delivery_price DECIMAL(10,2) NULL,
    delivery_price_euro DECIMAL(10,2) NULL,
    pay_type TINYINT NOT NULL,
    payment_euro TINYINT(1) DEFAULT 0 NULL,
    cur_rate DECIMAL(10,2) DEFAULT 1 NOT NULL COMMENT 'Курс на момент оплаты',
    currency VARCHAR(3) DEFAULT 'EUR' NOT NULL,
    measure VARCHAR(3) DEFAULT 'm' NOT NULL,
    
    -- Логистика и склад
    delivery_type TINYINT DEFAULT 0 NULL COMMENT '0 - адрес, 1 - склад',
    tracking_number VARCHAR(50) NULL,
    warehouse_data JSON NULL COMMENT 'Данные склада (JSON: адрес, часы работы)',
    weight_gross DOUBLE NULL,
    
    -- Даты и метки
    locale VARCHAR(5) NOT NULL,
    step SMALLINT DEFAULT 1 NOT NULL,
    name VARCHAR(200) NOT NULL,
    description TEXT NULL,
    bank_details JSON NULL COMMENT 'Реквизиты банка для возврата (JSON)',
    
    create_date DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
    update_date DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    ship_date DATETIME NULL COMMENT 'Предполагаемая дата отгрузки',
    fact_date DATETIME NULL COMMENT 'Фактическая дата поставки',
    full_payment_date DATE NULL,

    -- Внешние ключи (Foreign Keys)
    CONSTRAINT FK_ORDERS_USER FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT FK_ORDERS_MANAGER FOREIGN KEY (manager_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT FK_ORDERS_CARRIER FOREIGN KEY (carrier_id) REFERENCES carriers(id) ON DELETE SET NULL,
    CONSTRAINT FK_ORDERS_DELIVERY_ADDR FOREIGN KEY (delivery_address_id) REFERENCES addresses(id) ON DELETE SET NULL,
    CONSTRAINT FK_ORDERS_BILLING_ADDR FOREIGN KEY (billing_address_id) REFERENCES addresses(id) ON DELETE SET NULL,
    
    INDEX IDX_ORDERS_HASH (hash),
    INDEX IDX_ORDERS_CREATE_DATE (create_date)
) COMMENT 'Основная таблица заказов';

-- 5. История изменения сроков поставки (вместо 10 колонок в orders)
CREATE TABLE order_delivery_schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    time_min DATE NULL,
    time_max DATE NULL,
    is_confirmed_by_factory TINYINT(1) DEFAULT 0 NOT NULL,
    offset_reason TINYINT NULL COMMENT '1 - каникулы, 2 - уточнение, 3 - другое',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
    
    CONSTRAINT FK_SCHEDULE_ORDER FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) COMMENT 'Лог изменения сроков доставки по заказу';

-- 6. Позиции заказа (артикулы)
CREATE TABLE orders_article (
    id INT AUTO_INCREMENT PRIMARY KEY,
    orders_id INT NOT NULL,
    article_id INT NOT NULL COMMENT 'ID из каталога',
    
    -- Финансовые снимки
    amount DECIMAL(10,2) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    price_eur DECIMAL(10,2) NULL,
    currency VARCHAR(3) NULL,
    measure VARCHAR(2) NULL,
    
    -- Логистика единицы товара
    weight DOUBLE NOT NULL,
    pallet DOUBLE NOT NULL,
    packaging DOUBLE NOT NULL,
    packaging_count DOUBLE NOT NULL,
    multiple_pallet TINYINT NULL,
    delivery_time_min DATE NULL,
    delivery_time_max DATE NULL,
    
    CONSTRAINT FK_ARTICLE_ORDER FOREIGN KEY (orders_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX IDX_ARTICLE_ID (article_id)
) COMMENT 'Позиции заказа (исторический снимок)';