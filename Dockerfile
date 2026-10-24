FROM php:8.3.36-fpm

# Build arguments for host user matching
ARG HOST_UID=1000
ARG HOST_GID=1000

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

# Create user matching host user to avoid permission issues
RUN groupadd -g $HOST_GID josinator && \
    useradd -u $HOST_UID -g $HOST_GID -s /bin/sh -m josinator && \
    mkdir -p /app && \
    chown -R $HOST_UID:$HOST_GID /app

# Install Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Run as non-root user matching host user
USER josinator

# No COPY of source code here since it's mounted via docker-compose volume
# Dependencies will be installed via `make vendor` (composer install inside container)
