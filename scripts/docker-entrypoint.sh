#!/usr/bin/env bash
set -euo pipefail

cd /app

if [[ ! -f data/database.sqlite ]]; then
  bash setup-local.sh init
fi

if [[ ! -d public/uploads/site ]]; then
  mkdir -p public/uploads/site public/uploads/media public/uploads/fonts
fi

if [[ -d /app/data/uploads && ! -L public/uploads ]]; then
  rm -rf public/uploads
  ln -sfn /app/data/uploads public/uploads
  mkdir -p /app/data/uploads/site /app/data/uploads/media /app/data/uploads/fonts
fi

if [[ "${1:-}" == "php-server" ]]; then
  PORT="${PORT:-8080}"
  echo "→ CapsulePHP sur 0.0.0.0:${PORT}"
  exec php -S "0.0.0.0:${PORT}" -t public public/index.php
fi

exec "$@"
