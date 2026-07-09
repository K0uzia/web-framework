FROM php:8.2-cli-bookworm

RUN apt-get update && apt-get install -y --no-install-recommends \
    libsqlite3-0 \
    sqlite3 \
    unzip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

COPY . .

RUN bash bin/sync-styles 2>/dev/null || true \
    && mkdir -p data public/uploads/site public/uploads/media public/uploads/fonts \
    && chmod +x scripts/docker-entrypoint.sh

ENV APP_ENV=prod \
    APP_HTTPS=1

EXPOSE 8080

ENTRYPOINT ["scripts/docker-entrypoint.sh"]
CMD ["php-server"]
