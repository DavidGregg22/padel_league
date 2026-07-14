FROM php:8.2-fpm-alpine

# Install system dependencies (cached unless this block changes)
RUN apk add --no-cache \
    nginx \
    nodejs \
    npm \
    postgresql-dev \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    curl \
    supervisor \
    && docker-php-ext-install pdo pdo_pgsql gd zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# --- Layer: PHP deps (only rebuilds when composer.lock changes) ---
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# --- Layer: Node deps (only rebuilds when package-lock changes) ---
COPY package.json package-lock.json ./
RUN npm ci

# --- Layer: App code ---
COPY . .
RUN composer dump-autoload --optimize \
    && npm run build \
    && rm -rf node_modules

# Permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Config files
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80
ENTRYPOINT ["/entrypoint.sh"]
