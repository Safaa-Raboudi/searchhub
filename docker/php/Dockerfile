FROM php:8.0-fpm

RUN apt-get update && apt-get install -y --no-install-recommends \
    libpq-dev \
    libicu-dev \
    libzip-dev \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_pgsql pgsql intl zip opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# This base image only has one shared conf.d (no separate cli/fpm scan
# dirs), so this applies to both SAPIs. The growing codebase's static
# analysis (composer, PHPStan, PHPUnit) needs more than the default
# 128M; 512M is still a reasonable request-memory bound for php-fpm too.
RUN echo "memory_limit = 512M" > /usr/local/etc/php/conf.d/memory-limit.ini

WORKDIR /var/www/html
