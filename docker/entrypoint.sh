#!/bin/sh
set -e

mkdir -p /var/log/supervisor

# Use .env from the project (already copied in during build)

cd /var/www/html

# Generate app key if not set
php artisan key:generate --no-interaction --force

# Ensure SQLite database file exists
if [ ! -f /var/www/html/database/database.sqlite ]; then
    touch /var/www/html/database/database.sqlite
fi

# Run migrations
php artisan migrate --force --no-interaction

# Clear and cache config
php artisan config:clear
php artisan cache:clear

# Fix permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/database
chmod -R 775 /var/www/html/storage /var/www/html/database

# Start supervisord
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
