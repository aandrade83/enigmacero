#!/usr/bin/env sh
set -e

echo "== Boot: cleaning Laravel caches =="

rm -f /app/bootstrap/cache/config.php \
      /app/bootstrap/cache/routes*.php \
      /app/bootstrap/cache/services.php \
      /app/bootstrap/cache/packages.php || true

rm -rf /app/storage/framework/cache/* \
       /app/storage/framework/sessions/* \
       /app/storage/framework/views/* || true

# Debug rápido en logs (opcional pero útil)
php -r 'echo "APP_KEY=".(getenv("APP_KEY")? "SET":"MISSING").PHP_EOL;'

exec php -S 0.0.0.0:${PORT:-8080} -t public /app/server.php
