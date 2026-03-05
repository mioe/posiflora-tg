# posiflora-tg

MVP интеграции Telegram-уведомлений для магазинов Posiflora.

## Стек

- **Backend**: PHP 8.4 / Symfony 8.0 / Doctrine ORM / PostgreSQL 18
- **Frontend**: Angular 21 / TypeScript
- **Инфраструктура**: Docker / docker-compose

---

## Быстрый старт (одна команда)

```bash
# Запустить всё: postgres + backend + frontend
docker compose up -d --build

# Применить миграции и загрузить тестовые данные
docker compose exec backend php bin/console doctrine:migrations:migrate --no-interaction
docker compose exec backend php bin/console doctrine:fixtures:load --no-interaction
```

| Сервис | URL |
|---|---|
| Frontend | http://localhost:4200 |
| Backend API | http://localhost:8080 |
| PostgreSQL | localhost:54322 |

Страница интеграции открывается по адресу `http://localhost:4200/shops/<shopId>/growth/telegram`.
ID магазина можно получить из вывода fixtures или запроса к БД.

> **Примечание**: `docker compose up` без seed'а покажет пустой список статусов. Обязательно запустите fixtures после первого старта.

### Переменные окружения (.env в корне)

```env
PG_USER=pg-user
PG_PASSWORD=pg-pass
PG_DB_DEV=dev-posiflora
PG_DB_TEST=test-posiflora
PG_PORT=54322
```

### Переменные окружения

| Переменная | По умолчанию | Описание |
|---|---|---|
| `PG_USER` | `pg-user` | Пользователь PostgreSQL |
| `PG_PASSWORD` | `pg-pass` | Пароль PostgreSQL |
| `PG_DB_DEV` | `dev-posiflora` | Имя dev-базы |
| `PG_PORT` | `54322` | Порт PostgreSQL на хосте |
| `TELEGRAM_MOCK` | `true` | `true` — mock-режим, `false` — реальный Telegram |

---

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
php -S localhost:8080 -t public/
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
# После seed'а посмотреть через psql или запрос к БД
```

---

## Тестовые данные (seed)

Фикстуры создают:
- **1 магазин** «Posiflora Demo»
- **TelegramIntegration** для этого магазина (mock token, enabled=true)
- **7 тестовых заказов** (A-1001 … A-1007)

```bash
php bin/console doctrine:fixtures:load --no-interaction
```

---

## Тесты

```bash
cd symfony-backend

# Создать тестовую БД и применить миграции
php bin/console doctrine:database:create --env=test --if-not-exists
php bin/console doctrine:migrations:migrate --env=test --no-interaction

# Запустить тесты
php bin/phpunit
```

Тесты находятся в `tests/Service/OrderServiceTest.php`:
1. **testCreateOrderWithEnabledIntegrationSendsMessageAndLogsSent** — при включённой интеграции TelegramClient вызывается и пишется лог SENT
2. **testIdempotencyPreventsDoubleSendAndNoDuplicateLog** — если лог уже есть, TelegramClient не вызывается и новый лог не создаётся
3. **testTelegramFailureLogsFailedButOrderIsStillCreated** — при ошибке Telegram заказ создаётся, лог FAILED

---

## API

### POST `/shops/{shopId}/telegram/connect`
Подключить / обновить Telegram-интеграцию.
```json
{ "botToken": "123456:TOKEN", "chatId": "987654321", "enabled": true }
```

### GET `/shops/{shopId}/telegram/status`
Статус интеграции (enabled, lastSentAt, sentCount/failedCount за 7 дней).

### POST `/shops/{shopId}/orders`
Создать заказ (эмуляция). Автоматически отправляет Telegram-уведомление если интеграция включена.
```json
{ "number": "A-1005", "total": 2490, "customerName": "Анна" }
```
Ответ включает поле `sendStatus`: `sent` / `failed` / `skipped`.

---

## Режим Telegram

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

---

## Допущения и упрощения

- Аутентификация не реализована (не в объёме MVP)
- `botToken` хранится в БД в открытом виде (в продакшне — шифровать через Symfony Secrets или env)
- Frontend использует хардкод `http://localhost:8080` как baseUrl — в продакшне вынести в environment
- Отправка Telegram синхронная; для продакшна стоит вынести в очередь (Symfony Messenger + RabbitMQ/Redis)
- CORS не настроен — для prod нужен `nelmio/cors-bundle`
- Тесты юнитные (моки), без реальной БД
