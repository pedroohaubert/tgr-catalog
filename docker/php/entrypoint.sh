#!/usr/bin/env sh
set -e

cd /var/www/html

if [ ! -d vendor ]; then
  composer install --prefer-dist --no-progress --no-interaction
fi

if [ ! -f .env ]; then
  cp .env.example .env || true
fi

if ! grep -q '^APP_KEY=' .env; then
  printf '\nAPP_KEY=\n' >> .env
fi

if [ -z "$(grep '^APP_KEY=' .env | cut -d'=' -f2)" ]; then
  php artisan key:generate --force --no-interaction || true
fi

if [ -n "$DB_CONNECTION" ]; then
  if grep -q '^DB_CONNECTION=' .env; then sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION='"$DB_CONNECTION"'/' .env; else printf 'DB_CONNECTION=%s\n' "$DB_CONNECTION" >> .env; fi
fi

# Database path is already correct in .env.example, no need to modify

if [ -n "$DB_DATABASE" ]; then
  if grep -q '^DB_DATABASE=' .env; then sed -i 's#^DB_DATABASE=.*#DB_DATABASE='"$DB_DATABASE"'#' .env; else printf 'DB_DATABASE=%s\n' "$DB_DATABASE" >> .env; fi
fi

DB_PATH="$DB_DATABASE"
if [ -z "$DB_PATH" ]; then
  DB_PATH="database/database.sqlite"
fi
mkdir -p "$(dirname "$DB_PATH")" || true
touch "$DB_PATH" || true

php artisan config:clear || true

# Initialize database if not already done
if [ ! -f storage/app/.bootstrapped ] || [ ! -s "$DB_PATH" ]; then
  echo "Initializing database..."
  php artisan migrate --force --no-interaction || true
  php artisan db:seed --force --no-interaction || true
  mkdir -p storage/app
  touch storage/app/.bootstrapped || true
  echo "Database initialized successfully!"
fi

chown -R www-data:www-data storage bootstrap/cache || true
chmod -R ug+rw storage bootstrap/cache || true

exec "$@"


