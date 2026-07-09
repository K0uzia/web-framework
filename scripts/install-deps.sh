#!/usr/bin/env bash
# Installe les dépendances système et dev pour CapsulePHP (extensions PHP, SQLite, Composer).
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"

log() { printf '· %s\n' "$*"; }
ok() { printf '✅ %s\n' "$*"; }
warn() { printf '⚠️  %s\n' "$*"; }
err() { printf '❌ %s\n' "$*" >&2; }
die() {
    err "$*"
    exit 1
}

run_as_root() {
    if [[ "$(id -u)" -eq 0 ]]; then
        "$@"
    elif command -v sudo >/dev/null 2>&1; then
        sudo "$@"
    else
        die "Droits root requis. Relancez avec sudo ou en root."
    fi
}

php_version() {
    if ! command -v php >/dev/null 2>&1; then
        echo ""
        return 1
    fi
    php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;'
}

detect_pkg() {
    if command -v apt-get >/dev/null 2>&1; then
        echo "apt"
    elif command -v dnf >/dev/null 2>&1; then
        echo "dnf"
    else
        echo "unknown"
    fi
}

apt_pkg_available() {
    local pkg="$1"
    apt-cache show "$pkg" >/dev/null 2>&1
}

install_apt_php_ext() {
    local suffix="$1"
    local ver candidates=() pkg

    ver="$(php_version 2>/dev/null || true)"
    if [[ -n "$ver" ]]; then
        candidates+=("php${ver}-${suffix}")
    fi
    candidates+=("php-${suffix}")

    for pkg in "${candidates[@]}"; do
        if apt_pkg_available "$pkg"; then
            log "Installation de $pkg"
            run_as_root apt-get install -y "$pkg"
            return 0
        fi
    done

    for pkg in "${candidates[@]}"; do
        log "Tentative d'installation de $pkg"
        if run_as_root apt-get install -y "$pkg"; then
            return 0
        fi
    done

    warn "Extension PHP non installée : $suffix (paquets essayés : ${candidates[*]})"
    return 1
}

install_apt_packages() {
    log "Mise à jour des index apt..."
    run_as_root apt-get update -qq

    run_as_root apt-get install -y sqlite3 composer || true

    local ext
    for ext in cli sqlite3 mysql intl mbstring xml curl zip; do
        install_apt_php_ext "$ext" || true
    done
}

install_dnf_packages() {
    local pkgs=("$@")
    log "Installation dnf : ${pkgs[*]}"
    run_as_root dnf install -y "${pkgs[@]}"
}

install_system_deps() {
    local pm
    pm="$(detect_pkg)"

    case "$pm" in
        apt)
            install_apt_packages
            ;;
        dnf)
            install_dnf_packages \
                sqlite \
                php-cli \
                php-pdo \
                php-sqlite3 \
                php-mysqlnd \
                php-intl \
                php-mbstring \
                php-xml \
                php-curl \
                php-zip \
                composer
            ;;
        *)
            die "Gestionnaire de paquets non supporté.

Installez manuellement :
  - PHP 8.2+ avec extensions : pdo_sqlite, sqlite3, intl, mbstring, xml, dom, xmlwriter, json, tokenizer
  - SQLite 3 (CLI)
  - Composer (dev/tests)

Debian/Ubuntu / Linux Mint :
  sudo apt update
  sudo apt install sqlite3 php-cli php-sqlite3 php-mysql php-intl php-mbstring php-xml composer

Fedora/RHEL :
  sudo dnf install sqlite php-cli php-pdo php-sqlite3 php-mysqlnd php-intl php-mbstring php-xml composer"
            ;;
    esac

    ok "Paquets système installés."
}

install_composer_if_missing() {
    if command -v composer >/dev/null 2>&1; then
        return 0
    fi

    warn "Composer introuvable via le gestionnaire de paquets, installation locale..."
    need php
    local installer="$ROOT/tools/composer-setup.php"
    mkdir -p "$ROOT/tools/bin"
    php -r "copy('https://getcomposer.org/installer', '$installer');"
    php "$installer" --install-dir="$ROOT/tools/bin" --filename=composer
    rm -f "$installer"
    export PATH="$ROOT/tools/bin:$PATH"
    ok "Composer installé dans tools/bin/composer"
}

composer_cmd() {
    if command -v composer >/dev/null 2>&1; then
        echo "composer"
    elif [[ -x "$ROOT/tools/bin/composer" ]]; then
        echo "$ROOT/tools/bin/composer"
    else
        echo ""
    fi
}

install_composer_vendor() {
    local composer_bin
    composer_bin="$(composer_cmd)"
    [[ -n "$composer_bin" ]] || return 0

    if [[ ! -f "$ROOT/composer.json" ]]; then
        return 0
    fi

    log "Installation des dépendances Composer (dev)..."
    (cd "$ROOT" && "$composer_bin" install --no-interaction --prefer-dist --optimize-autoloader)
    ok "Vendor Composer prêt."
}

php_has_extension() {
    local ext="$1"
    php -m | grep -qi "^${ext}$"
}

verify_extensions() {
    local missing=0
    local ext

    local required=(
        pdo
        pdo_sqlite
        sqlite3
        intl
        json
        tokenizer
    )
    local dev=(
        mbstring
        dom
        xml
        xmlwriter
    )

    for ext in "${required[@]}"; do
        if php_has_extension "$ext"; then
            ok "Extension PHP : $ext"
        else
            err "Extension PHP manquante (requise) : $ext"
            missing=1
        fi
    done

    for ext in "${dev[@]}"; do
        if php_has_extension "$ext"; then
            ok "Extension PHP : $ext"
        else
            warn "Extension PHP manquante (tests/QA) : $ext"
            missing=1
        fi
    done

    if [[ "$missing" -ne 0 ]]; then
        echo
        warn "Certaines extensions sont absentes."
        local ver hint=""
        ver="$(php_version 2>/dev/null || true)"
        if [[ -n "$ver" ]]; then
            hint="php-mbstring php-xml (ou php${ver}-mbstring php${ver}-xml)"
        else
            hint="php-mbstring php-xml"
        fi
        warn "Linux Mint / Ubuntu : sudo apt update && sudo apt install $hint"
        warn "Ou relancez : make deps"
        return 1
    fi

    return 0
}

need() { command -v "$1" >/dev/null 2>&1 || die "Manque binaire : $1"; }

main() {
    log "CapsulePHP : installation des dépendances"
    echo

    install_system_deps

    if ! command -v php >/dev/null 2>&1; then
        die "PHP introuvable après installation. Vérifiez votre PATH."
    fi

    install_composer_if_missing
    install_composer_vendor

    echo
    php -v
    echo
    verify_extensions || true

    echo
    ok "Terminé. Prochaine étape : make init && make dev"
}

main "$@"
