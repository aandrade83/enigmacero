#!/usr/bin/env sh
set -eu

# Si falta APP_KEY, mejor fallar explícito con mensaje claro
if [ -z "${APP_KEY:-}" ]; then
  echo "FATAL: APP_KEY no está definido en el runtime de Cloud Run" >&2
  exit 1
fi

# Esto evita que Laravel use valores viejos (APP_KEY/DB_*/etc)
rm -f bootstrap/cache/*.php || true

# Limpieza extra (no rompe si falla)
php artisan config:clear >/dev/null 2>&1 || true
php artisan route:clear  >/dev/null 2>&1 || true
php artisan view:clear   >/dev/null 2>&1 || true

exec php -S 0.0.0.0:${PORT:-8080} -t public server.php
