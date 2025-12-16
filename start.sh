#!/usr/bin/env sh
set -e

echo "==> Boot: cleaning Laravel caches..."
rm -f bootstrap/cache/*.php || true
rm -rf storage/framework/cache/data/* || true
rm -rf storage/framework/sessions/* || true
rm -rf storage/framework/views/* || true

# No imprimimos la key (seguridad), solo confirmamos si existe
if [ -z "${APP_KEY:-}" ]; then
  echo "==> ERROR: APP_KEY is EMPTY in runtime env"
else
  echo "==> OK: APP_KEY is present in runtime env"
fi

echo "==> Starting PHP server on ${PORT:-8080}..."
exec php -S 0.0.0.0:${PORT:-8080} -t public server.php
