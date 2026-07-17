FROM php:8.4-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    unzip \
    wget \
    bash \
    openssl \
    libzip-dev \
    libpng-dev \
    jpeg-dev \
    freetype-dev \
    icu-dev \
    libxml2-dev \
    oniguruma-dev \
    mysql-client \
    $PHPIZE_DEPS

# Configure GD with JPEG, PNG, and Freetype support
RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg

# Install PHP extensions
RUN docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    zip \
    gd \
    intl \
    opcache \
    mbstring \
    xml

# Expose process env (Docker/Portainer) on $_ENV — app code reads $_ENV directly
RUN echo 'variables_order = "EGPCS"' > /usr/local/etc/php/conf.d/zz-env.ini

# Install Composer
COPY --from=composer/composer:latest /usr/bin/composer /usr/local/bin/composer

COPY docker/entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

WORKDIR /var/www/html

COPY composer.json composer.lock symfony.lock ./
RUN composer install --prefer-dist --no-scripts --no-progress --no-interaction

COPY . .

RUN mkdir -p var/cache var/log config/jwt && \
    chown -R www-data:www-data var config/jwt

EXPOSE 9000

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm"]
