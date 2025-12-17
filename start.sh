#!/bin/sh
set -e

# Cloud Run: si quedó un cache viejo en /tmp, Laravel lo vuelve a usar
rm -f /tmp/laravel-config.php /tmp/laravel-routes.php /tmp/laravel-services.php /tmp/laravel-packages.php 2>/dev/null || true
rm -f /app/bootstrap/cache/*.php 2>/dev/null || true





# Asegurar folders
mkdir -p storage/framework/{cache,sessions,views} bootstrap/cache
chmod -R 775 storage bootstrap/cache || true





# Validación mínima (sin imprimir la key)
if [ -z "${APP_KEY}" ]; then
  echo "ERROR: APP_KEY no está seteado en variables de entorno."
  exit 1


exec php -S 0.0.0.0:${PORT:-8080} -t public /app/server.php

exec php -S 0.0.0.0:${PORT:-8080} -t public server.php
