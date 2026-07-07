#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
VENV_DIR="$ROOT/tools/video-tools-venv"
ENV_HINT="$ROOT/config/video-tools.env"

echo "Installation des outils d'import video (yt-dlp, ffmpeg)..."

install_ffmpeg() {
  if command -v ffmpeg >/dev/null 2>&1; then
    return 0
  fi
  if command -v apt-get >/dev/null 2>&1; then
    sudo apt-get update
    sudo apt-get install -y ffmpeg
  elif command -v dnf >/dev/null 2>&1; then
    sudo dnf install -y ffmpeg
  else
    echo "Installez ffmpeg manuellement." >&2
    exit 1
  fi
}

install_ytdlp_apt() {
  if command -v yt-dlp >/dev/null 2>&1; then
    return 0
  fi
  if ! command -v apt-get >/dev/null 2>&1; then
    return 1
  fi
  if apt-cache show yt-dlp >/dev/null 2>&1; then
    sudo apt-get install -y yt-dlp
    command -v yt-dlp >/dev/null 2>&1
    return $?
  fi
  return 1
}

install_ytdlp_pipx() {
  if command -v yt-dlp >/dev/null 2>&1; then
    return 0
  fi
  if ! command -v pipx >/dev/null 2>&1; then
    if command -v apt-get >/dev/null 2>&1; then
      sudo apt-get install -y pipx
      pipx ensurepath >/dev/null 2>&1 || true
    else
      return 1
    fi
  fi
  pipx install yt-dlp || pipx upgrade yt-dlp
  command -v yt-dlp >/dev/null 2>&1
}

install_ytdlp_github() {
  local target="$ROOT/tools/bin/yt-dlp"
  mkdir -p "$ROOT/tools/bin"
  echo "-> Telechargement de yt-dlp (release GitHub) vers $target"
  if command -v curl >/dev/null 2>&1; then
    curl -fsSL "https://github.com/yt-dlp/yt-dlp/releases/latest/download/yt-dlp" -o "$target"
  elif command -v wget >/dev/null 2>&1; then
    wget -qO "$target" "https://github.com/yt-dlp/yt-dlp/releases/latest/download/yt-dlp"
  else
    return 1
  fi
  chmod +x "$target"
  [ -x "$target" ]
}

install_ytdlp_venv() {
  if [ ! -d "$VENV_DIR" ]; then
    echo "-> Installation de yt-dlp dans un venv local ($VENV_DIR)"
    if ! command -v python3 >/dev/null 2>&1; then
      if command -v apt-get >/dev/null 2>&1; then
        sudo apt-get install -y python3 python3-venv
      else
        echo "python3 introuvable." >&2
        exit 1
      fi
    fi
    python3 -m venv "$VENV_DIR"
  fi

  "$VENV_DIR/bin/pip" install --upgrade pip
  "$VENV_DIR/bin/pip" install --upgrade yt-dlp

  mkdir -p "$(dirname "$ENV_HINT")"
  cat > "$ENV_HINT" <<EOF
# Genere par scripts/install-video-tools.sh
# Sourcez ce fichier avant le worker : source config/video-tools.env
export VIDEO_IMPORT_YT_DLP_BIN="$VENV_DIR/bin/yt-dlp"
EOF

  echo "-> Binaire yt-dlp local : $VENV_DIR/bin/yt-dlp"
  echo "-> Puis : source $ENV_HINT"
  [ -x "$VENV_DIR/bin/yt-dlp" ]
}

write_env_hint() {
  local bin_path="$1"
  mkdir -p "$(dirname "$ENV_HINT")"
  cat > "$ENV_HINT" <<EOF
# Genere par scripts/install-video-tools.sh
export VIDEO_IMPORT_YT_DLP_BIN="$bin_path"
EOF
}

install_ffmpeg

if install_ytdlp_github; then
  echo "yt-dlp installe via release GitHub (tools/bin/yt-dlp)."
  write_env_hint "$ROOT/tools/bin/yt-dlp"
elif install_ytdlp_pipx; then
  echo "yt-dlp installe via pipx."
elif install_ytdlp_venv; then
  echo "yt-dlp installe via venv local."
else
  install_ytdlp_apt || true
fi

YTDLP_BIN="${VIDEO_IMPORT_YT_DLP_BIN:-}"
if [ -z "$YTDLP_BIN" ] && [ -x "$ROOT/tools/bin/yt-dlp" ]; then
  YTDLP_BIN="$ROOT/tools/bin/yt-dlp"
fi
if [ -z "$YTDLP_BIN" ] && [ -x "$VENV_DIR/bin/yt-dlp" ]; then
  YTDLP_BIN="$VENV_DIR/bin/yt-dlp"
fi
if [ -z "$YTDLP_BIN" ]; then
  YTDLP_BIN="$(command -v yt-dlp 2>/dev/null || true)"
fi

if [ -n "$YTDLP_BIN" ] && [ ! -f "$ENV_HINT" ]; then
  write_env_hint "$YTDLP_BIN"
fi

IMPORTS_DIR="${VIDEO_IMPORT_ROOT:-$ROOT/public/uploads/media/imports}"
mkdir -p "$IMPORTS_DIR"
chmod 775 "$IMPORTS_DIR" 2>/dev/null || true

echo ""
echo "Versions installees :"
ffmpeg -version | head -n 1 || true
if [ -n "$YTDLP_BIN" ]; then
  "$YTDLP_BIN" --version || true
else
  echo "yt-dlp introuvable." >&2
  exit 1
fi

echo ""
echo "Dossier imports : $IMPORTS_DIR"
if [ -f "$ENV_HINT" ]; then
  echo "Worker avec yt-dlp local :"
  echo "  source $ENV_HINT && php $ROOT/bin/video-import-worker.php"
else
  echo "Worker : php $ROOT/bin/video-import-worker.php"
fi
echo "Termine."
