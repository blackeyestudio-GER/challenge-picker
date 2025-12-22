
FROM php:8.4-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    unzip \
    wget \
    bash \
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

# Install Composer
COPY --from=composer/composer:latest /usr/bin/composer /usr/local/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files
COPY composer.json composer.lock symfony.lock ./

# Install dependencies
RUN composer install --prefer-dist --no-scripts --no-progress --no-interaction

# Copy existing application code
COPY . .

# Create var directory with proper permissions
RUN mkdir -p var/cache var/log && \
    chown -R www-data:www-data var

# Expose port for PHP-FPM
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]
