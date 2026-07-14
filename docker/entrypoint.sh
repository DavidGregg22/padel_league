#!/bin/sh
set -e

mkdir -p /var/log/supervisor

cd /var/www/html

# Generate app key if not set
php artisan key:generate --no-interaction --force

# Run migrations (works with any DB driver from .env)
# First ensure migrations table exists and mark pre-existing migrations
php artisan migrate:install --no-interaction 2>/dev/null || true
php artisan tinker --execute="
\$existing = DB::table('migrations')->pluck('migration')->toArray();
\$old = [
    '0001_01_01_000000_create_users_table',
    '0001_01_01_000001_create_cache_table',
    '0001_01_01_000002_create_jobs_table',
    '2026_07_05_210757_add_is_admin_to_users_table',
    '2026_07_05_210757_create_seasons_table',
    '2026_07_05_210758_create_double_pairs_table',
    '2026_07_05_210758_create_doubles_matches_table',
    '2026_07_05_210758_create_singles_matches_table',
    '2026_07_08_210658_add_sets_to_matches_tables',
];
foreach (\$old as \$m) {
    if (!in_array(\$m, \$existing)) {
        DB::table('migrations')->insert(['migration' => \$m, 'batch' => 1]);
    }
}
" 2>/dev/null || true
php artisan migrate --force --no-interaction || echo "Migration warning — continuing..."

# Clear and cache config
php artisan config:clear
php artisan cache:clear

# Fix permissions
chown -R www-data:www-data /var/www/html/storage
chmod -R 775 /var/www/html/storage

# Start supervisord
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
