# API для работы с заказами

## Запуск проекта и инициализация
1. Запуск docker-контейнеров:
   ```bash
   make up
   ```
1.2. Примечание по поводу запуска: у меня установлен docker-compose (в старом формате). Возможно, на вашей машине уже установлен docker compose. В этом случае вместо docker compose up -d нужно будет запустить docker-compose up -d

2. Установить зависимости:
   ```bash
   make install
   ```
3. Генерация тестовых фикстур (1000 заказов со случайными датами и позициями):
   ```bash
   make make-fixtures
   ```
4. Запуск тестов:
   ```bash
   make run-test
   ```
---

## Доступные API Эндпоинты

- /api-doc.html - REST Swagger UI ( по умолчанию http://localhost:8190/api-doc.html )
- /api/soap?wsdl - SOAP WSDL    


## Дамп
- https://github.com/tai88ua/test_order_core_api/blob/main/dump_update.sql - дамп базы

- Сделана нормализация таблиц orders и orders_article , а также изменен тип данных c double на decimal у тех полей которые используются в финансовых расчетах.
