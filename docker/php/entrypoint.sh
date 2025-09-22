#!/usr/bin/env sh
set -e

cd /var/www/html

if [ ! -d vendor ]; then
  composer install --prefer-dist --no-progress --no-interaction
fi

if [ ! -f .env ]; then
  cp .env.example .env || true
fi

# Ensure .env contains APP_KEY entry
if ! grep -q '^APP_KEY=' .env; then
  printf '\nAPP_KEY=\n' >> .env
fi

# Ensure app key exists (only if empty)
if [ -z "$(grep '^APP_KEY=' .env | cut -d'=' -f2)" ]; then
  php artisan key:generate --force --no-interaction || true
fi

# Sync DB envs into .env if present (so artisan reads the same)
if [ -n "$DB_CONNECTION" ]; then
  if grep -q '^DB_CONNECTION=' .env; then sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION='"$DB_CONNECTION"'/' .env; else printf 'DB_CONNECTION=%s\n' "$DB_CONNECTION" >> .env; fi
fi
if [ -n "$DB_DATABASE" ]; then
  if grep -q '^DB_DATABASE=' .env; then sed -i 's#^DB_DATABASE=.*#DB_DATABASE='"$DB_DATABASE"'#' .env; else printf 'DB_DATABASE=%s\n' "$DB_DATABASE" >> .env; fi
fi

# Ensure SQLite database file exists (use env effective path)
DB_PATH="$DB_DATABASE"
if [ -z "$DB_PATH" ]; then
  # Fallback to default relative path
  DB_PATH="database/database.sqlite"
fi
mkdir -p "$(dirname "$DB_PATH")" || true
touch "$DB_PATH" || true

# Clear cached config to apply env changes
php artisan config:clear || true

# Run migrations and seed on first boot cache marker
if [ ! -f storage/app/.bootstrapped ]; then
  php artisan migrate --force --no-interaction || true
  php artisan db:seed --force --no-interaction || true
  mkdir -p storage/app
  touch storage/app/.bootstrapped || true
fi

# Ensure storage and bootstrap cache are writable
chown -R www-data:www-data storage bootstrap/cache || true
chmod -R ug+rw storage bootstrap/cache || true

exec "$@"


