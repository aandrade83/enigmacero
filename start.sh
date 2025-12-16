#!/bin/sh
set -e

# Limpia caches pegados (especialmente si Cloud Run setea /tmp cache paths)
rm -f /tmp/laravel-*.php 2>/dev/null || true
rm -f bootstrap/cache/*.php 2>/dev/null || true

# Asegurar folders
mkdir -p storage/framework/{cache,sessions,views} bootstrap/cache
chmod -R 775 storage bootstrap/cache || true

# Validación mínima (sin imprimir la key)
if [ -z "${APP_KEY}" ]; then
  echo "ERROR: APP_KEY no está seteado en variables de entorno."
  exit 1
fi

exec php -S 0.0.0.0:${PORT:-8080} -t public server.php
