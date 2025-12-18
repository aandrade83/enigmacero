#!/bin/sh
set -eu

# 1) Limpiar caches que pueden “congelar” env vars del build
rm -f /tmp/laravel-*.php 2>/dev/null || true
rm -f /app/bootstrap/cache/config.php \
      /app/bootstrap/cache/routes.php \
      /app/bootstrap/cache/services.php \
      /app/bootstrap/cache/packages.php 2>/dev/null || true
rm -f /app/bootstrap/cache/*.php 2>/dev/null || true

# 2) Asegurar carpetas necesarias
mkdir -p /app/storage/framework/cache /app/storage/framework/sessions /app/storage/framework/views /app/bootstrap/cache
chmod -R 775 /app/storage /app/bootstrap/cache || true

# 3) Limpiar via artisan (no debe tumbar el arranque)
php /app/artisan config:clear >/dev/null 2>&1 || true
php /app/artisan cache:clear  >/dev/null 2>&1 || true
php /app/artisan route:clear  >/dev/null 2>&1 || true
php /app/artisan view:clear   >/dev/null 2>&1 || true

# 4) APP_KEY obligatorio
if [ -z "${APP_KEY:-}" ]; then
  echo "ERROR: APP_KEY no está seteado en variables de entorno." >&2
  exit 1
fi

# 5) Log mínimo (para ver si el proceso VE las env vars)
echo "BOOT: GOOGLE_CLOUD_PROJECT=${GOOGLE_CLOUD_PROJECT:-}" >&2
echo "BOOT: GCS_BUCKET=${GCS_BUCKET:-}" >&2
echo "BOOT: GCS_PATH_PREFIX=${GCS_PATH_PREFIX:-}" >&2

exec php -S 0.0.0.0:${PORT:-8080} -t public /app/server.php
