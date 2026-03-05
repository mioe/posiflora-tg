FROM php:8.4-cli-alpine

RUN apk add --no-cache postgresql-dev \
    && docker-php-ext-install pdo pdo_pgsql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts

COPY . .
RUN composer run-script post-install-cmd --no-interaction || true

EXPOSE 8080

CMD ["php", "-S", "0.0.0.0:8080", "-t", "public/"]
