FROM php:8.4-cli

RUN apt-get update \
    && apt-get install -y git unzip libpq-dev libzip-dev \
    && docker-php-ext-install pcntl pdo_pgsql zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html/backend
