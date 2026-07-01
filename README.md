# API для работы с заказами

## Запуск проекта и инициализация
1. Запуск docker-контейнеров:
   ```bash
   make up
   ```
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