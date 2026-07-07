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

exec "$@"
