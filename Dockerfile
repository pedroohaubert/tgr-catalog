FROM php:8.4-cli-alpine

RUN apk add --no-cache \
    git \
    unzip \
    tzdata \
    oniguruma-dev \
    icu-data-full \
    icu-dev \
    bash \
    libpng-dev \
    libjpeg-turbo-dev \
    libzip-dev \
    sqlite-dev

RUN docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo \
        pdo_mysql \
        pdo_sqlite \
        gd \
        zip \
        intl

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENV PHP_OPCACHE_VALIDATE_TIMESTAMPS=1 \
    PHP_MEMORY_LIMIT=512M \
    PHP_MAX_EXECUTION_TIME=120

EXPOSE 8000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["sh", "-lc", "php artisan serve --host=0.0.0.0 --port=8000"]


