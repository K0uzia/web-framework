#!/usr/bin/env bash
# Build statique CapsulePHP pour Netlify (PHP + Composer sur l'image de build).
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

ensure_php() {
    if command -v php >/dev/null 2>&1; then
        return 0
    fi
    if command -v apt-get >/dev/null 2>&1; then
        echo "→ Installation PHP (image de build Netlify)…"
        export DEBIAN_FRONTEND=noninteractive
        apt-get update -qq
        apt-get install -y -qq php-cli php-sqlite3 php-mbstring php-xml unzip curl ca-certificates
        return 0
    fi
    echo "❌ PHP introuvable sur l'image de build. Vérifiez scripts/netlify-build.sh ou installez php-cli localement." >&2
    exit 1
}

ensure_composer() {
    if command -v composer >/dev/null 2>&1; then
        return 0
    fi
    echo "→ Installation Composer…"
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
}

ensure_php
ensure_composer

php -v
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

bash setup-local.sh init
bash bin/sync-styles

export NETLIFY="${NETLIFY:-true}"
export APP_ENV="${APP_ENV:-prod}"
export APP_HTTPS="${APP_HTTPS:-1}"
export APP_URL="${APP_URL:-${DEPLOY_PRIME_URL:-${URL:-https://example.netlify.app}}}"
export APP_BASE_PATH="${APP_BASE_PATH:-}"

echo "→ Export statique (APP_URL=${APP_URL}, APP_BASE_PATH=${APP_BASE_PATH:-/})"
php scripts/export-static.php dist

echo "✅ Build Netlify terminé : dist/"
