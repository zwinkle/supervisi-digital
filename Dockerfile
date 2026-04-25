# ============================================================
# Stage 1: Composer dependencies
# ============================================================
FROM composer:2.7 AS composer-build

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

COPY . .
RUN composer dump-autoload --optimize --no-dev

# ============================================================
# Stage 2: Node 22 / Vite build
# ============================================================
FROM node:22-alpine AS node-build

WORKDIR /app

COPY package*.json ./
RUN npm ci --omit=dev

COPY . .
COPY --from=composer-build /app/vendor ./vendor

RUN npm run build

# ============================================================
# Stage 3: Production image (PHP 8.4)
# ============================================================
FROM php:8.4-fpm-alpine AS production

LABEL description="supervisi-digital | Laravel 12 | PHP 8.4 | Production"

# System dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    zip \
    unzip \
    icu-dev \
    oniguruma-dev \
    postgresql-dev \
    shadow \
    && rm -rf /var/cache/apk/*

# PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        pgsql \
        gd \
        zip \
        intl \
        mbstring \
        bcmath \
        opcache \
        pcntl \
        exif \
        sockets \
        sodium

COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/php/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/supervisor/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

WORKDIR /var/www/html

COPY --from=composer-build --chown=www-data:www-data /app .
COPY --from=node-build --chown=www-data:www-data /app/public/build ./public/build

# Permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache \
    && mkdir -p /var/log/nginx /var/log/supervisor \
    && chown -R www-data:www-data /var/log/nginx

# Expose port internal
EXPOSE 8080

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
