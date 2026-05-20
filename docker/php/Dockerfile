#############################################
# Stage 1: Install PHP dependencies via Composer
#############################################
FROM php:8.3-fpm

# Install system packages required
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    zip \
    default-mysql-client \
    libicu-dev \
    libzip-dev \
    libpng-dev \
    libxml2-dev \
    libxslt1-dev \
    libonig-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    pkg-config \
    libssl-dev \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install \
        intl \
        pdo \
        pdo_mysql \
        zip \
        gd \
        xsl \
        opcache \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Intelligent cache
COPY composer.json composer.lock ./

# Install dependencies PHP
RUN composer install \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

# Copy project
COPY . .

# Permissions
RUN mkdir -p var/cache var/log \
    && chown -R www-data:www-data var config public src

EXPOSE 9000

# default command
CMD ["php-fpm"]