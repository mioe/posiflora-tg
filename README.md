# posiflora-tg

MVP интеграции Telegram-уведомлений для магазинов Posiflora.

## Стек

- **Backend**: PHP 8.4 / Symfony 8.0 / Doctrine ORM / PostgreSQL 18
- **Frontend**: Angular 21 / TypeScript
- **Инфраструктура**: Docker / docker-compose


## Инструменты разработки

- **[Zed](https://zed.dev/)** — редактор кода
- **[Claude Code](https://claude.ai/code)** — AI-агент для разработки
- **[Bruno](https://www.usebruno.com/)** — клиент для тестирования API
- **[DBeaver](https://dbeaver.io/)** — просмотр и управление БД
- **[OrbStack](https://orbstack.dev/)** — запуск Docker-контейнеров

> **Совместимость**: проверено на macOS / Linux. На Windows возможен баг с инициализацией PostgreSQL — образ использует `.sh`-скрипт для создания нескольких баз данных (`.snippets/postgres/init-databases`), который может не выполниться корректно в Docker Desktop на Windows из-за проблем с переносами строк (CRLF) или правами на файл.


## Быстрый старт (одна команда)

```bash
# Запустить всё: postgres + backend + frontend
docker compose up -d --build

# Применить миграции и загрузить тестовые данные
docker compose exec backend php bin/console doctrine:migrations:migrate --no-interaction
docker compose exec backend php bin/console doctrine:fixtures:load --no-interaction
```

| Сервис      | URL                   |
| ----------- | --------------------- |
| Frontend    | http://localhost:4200 |
| Backend API | http://localhost:8080 |
| PostgreSQL  | localhost:54322       |

Страница интеграции открывается по адресу `http://localhost:4200/shops/<shopId>/growth/telegram`.
ID магазина можно получить из вывода fixtures или запроса к БД.

> **Примечание**: `docker compose up` без seed'а покажет пустой список статусов. Обязательно запустите fixtures после первого старта.

```bash
curl -s -X POST http://localhost:8080/api/shops/019cbd9e-63ee-7893-8f4c-04a5b40be6ae/orders \
  -H "Content-Type: application/json" \
  -d '{"number":"TEST-001","total":999,"customerName":"Тест"}'
```

### Переменные окружения

| Переменная      | По умолчанию    | Описание                                         |
| --------------- | --------------- | ------------------------------------------------ |
| `PG_USER`       | `pg-user`       | Пользователь PostgreSQL                          |
| `PG_PASSWORD`   | `pg-pass`       | Пароль PostgreSQL                                |
| `PG_DB_DEV`     | `dev-posiflora` | Имя dev-базы                                     |
| `PG_PORT`       | `54322`         | Порт PostgreSQL на хосте                         |
| `TELEGRAM_MOCK` | `true`          | `true` — mock-режим, `false` — реальный Telegram |


## Запуск без Docker

### Backend

```bash
cd symfony-backend

# Установить зависимости
composer install

# Настроить DATABASE_URL в .env
# По умолчанию: postgresql://pg-user:pg-pass@localhost:54322/dev-posiflora

# Применить миграции
php bin/console doctrine:migrations:migrate --no-interaction

# Загрузить тестовые данные
php bin/console doctrine:fixtures:load --no-interaction

# Запустить сервер
php -S localhost:8080 -t public/ public/index.php
```

### Frontend

```bash
cd angular-frontend

npm install
npm start
# http://localhost:4200
```

Открыть страницу: `http://localhost:4200/shops/<shopId>/growth/telegram`

ID тестового магазина можно получить через API:

```bash
curl -s http://localhost:8080/api/shops | jq '.[0].id'
```


## Тестовые данные (seed)

Фикстуры создают:

- **1 магазин** «Posiflora Demo»
- **TelegramIntegration** для этого магазина (mock token, enabled=true)
- **7 тестовых заказов** (A-1001 … A-1007)

```bash
php bin/console doctrine:fixtures:load --no-interaction
```


## Тесты

Тесты юнитные — используют моки, реальная БД не нужна.

### Через Docker

```bash
docker compose exec backend php bin/phpunit
```

### Локально

```bash
cd symfony-backend
php bin/phpunit
```

Тесты находятся в `tests/Service/OrderServiceTest.php`:

1. **testCreateOrderWithEnabledIntegrationSendsMessageAndLogsSent** — при включённой интеграции TelegramClient вызывается и пишется лог SENT
2. **testIdempotencyPreventsDoubleSendAndNoDuplicateLog** — если лог уже есть, TelegramClient не вызывается и новый лог не создаётся
3. **testTelegramFailureLogsFailedButOrderIsStillCreated** — при ошибке Telegram заказ создаётся, лог FAILED


## API

### POST `/api/shops/{shopId}/telegram/connect`

Подключить / обновить Telegram-интеграцию.

```json
{ "botToken": "123456:TOKEN", "chatId": "987654321", "enabled": true }
```

### GET `/api/shops/{shopId}/telegram/status`

Статус интеграции (enabled, lastSentAt, sentCount/failedCount за 7 дней).

### POST `/api/shops/{shopId}/orders`

Создать заказ (эмуляция). Автоматически отправляет Telegram-уведомление если интеграция включена.

```json
{ "number": "A-1005", "total": 2490, "customerName": "Анна" }
```

Ответ включает поле `sendStatus`: `sent` / `failed` / `skipped`.


## Режим работы Telegram

### Mock (по умолчанию)

```env
TELEGRAM_MOCK=true
```

Сообщения логируются в Symfony logger, реальных HTTP-запросов нет.

### Реальный Telegram

```env
TELEGRAM_MOCK=false
```

Используйте настоящий `botToken` (от @BotFather) и `chatId`.


## Тестирование API через Bruno

В директории `.bruno/posiflora-tg/` находится коллекция запросов для [Bruno](https://www.usebruno.com/) — open-source альтернативы Postman/Insomnia.

Чтобы открыть коллекцию:

1. Установите Bruno
2. `Open Collection` → выберите папку `.bruno/posiflora-tg`
3. Настройте переменные `host` и `shopId` в разделе **Vars** коллекции

Доступные запросы:

- `POST app_order_create` — создать заказ и получить `sendStatus`

![Bruno — POST /orders](.docs/screenshots/bruno-order-create.png)


## Скриншоты

### Frontend — страница Telegram-интеграции

![Telegram-интеграция](.docs/screenshots/frontend-telegram-page.png)

### Реальные уведомления в Telegram

![Уведомления в боте](.docs/screenshots/telegram-bot-messages.png)


## Допущения и упрощения

- Аутентификация не реализована (не в объёме MVP)
- `botToken` хранится в БД в открытом виде (в продакшне — шифровать через Symfony Secrets или env)
- Frontend использует Angular proxy для проксирования `/api` на backend — в продакшне вынести `baseUrl` в environment
- Отправка Telegram синхронная; для продакшна стоит вынести в очередь (Symfony Messenger + RabbitMQ/Redis)
- CORS не настроен — для prod нужен `nelmio/cors-bundle`
- Тесты юнитные (моки), без реальной БД
