FROM php:8.3.36-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zlib1g-dev \
    default-libmysqlclient-dev \
    pkg-config \
    libreadline-dev \
    && docker-php-ext-configure zip \
    && docker-php-ext-install -j$(nproc) zip pdo_mysql intl opcache \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

# Install Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# No COPY of source code here since it's mounted via docker-compose volume
# Dependencies will be installed via `make vendor` (composer install inside container)
