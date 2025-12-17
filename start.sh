#!/bin/sh
set -eu

echo "== EnigmaCero start.sh =="

# 1) Limpiar caches que a veces quedan pegados (Cloud Run / tmp)
rm -f /tmp/laravel-config.php \
      /tmp/laravel-routes.php \
      /tmp/laravel-services.php \
      /tmp/laravel-packages.php 2>/dev/null || true

rm -f /app/bootstrap/cache/config.php \
      /app/bootstrap/cache/routes*.php \
      /app/bootstrap/cache/services.php \
      /app/bootstrap/cache/packages.php 2>/dev/null || true

# 2) Asegurar folders de Laravel
mkdir -p /app/storage/framework/cache \
         /app/storage/framework/sessions \
         /app/storage/framework/views \
         /app/bootstrap/cache

chmod -R 775 /app/storage /app/bootstrap/cache 2>/dev/null || true

# 3) Validación mínima (sin imprimir la key)
if [ -z "${APP_KEY:-}" ]; then
  echo "ERROR: APP_KEY no está seteado en variables de entorno."
  exit 1
fi

# 4) (Opcional) mostrar info útil sin secretos
echo "PORT=${PORT:-8080}"
echo "APP_ENV=${APP_ENV:-production}"

# 5) Arrancar servidor (Cloud Run escucha en $PORT)
exec php -S 0.0.0.0:${PORT:-8080} -t /app/public /app/server.php

