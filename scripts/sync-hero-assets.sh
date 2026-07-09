#!/usr/bin/env bash
# Télécharge les médias des blocs hero vers public/assets/sections/hero/_shared/
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
DEST="$ROOT/public/assets/sections/hero/_shared"
CDN="https://deifkwefumgah.cloudfront.net/shadcnblocks"

mkdir -p \
  "$DEST/avatars" \
  "$DEST/patterns" \
  "$DEST/mockups" \
  "$DEST/logos" \
  "$DEST/avatars-webp" \
  "$DEST/backgrounds"

fetch() {
  local url="$1"
  local out="$2"
  if [[ -f "$out" && -s "$out" ]]; then
    echo "skip $out"
    return 0
  fi
  echo "get $out"
  curl -fsSL -o "$out" "$url"
}

for i in 1 2 3 4 5; do
  fetch "$CDN/image-set/modern/saas-hero/saas-hero-${i}-16x9.png" \
    "$DEST/saas-hero-${i}-16x9.png"
  fetch "$CDN/image-set/modern/saas-hero/saas-hero-${i}-16x9-dark.png" \
    "$DEST/saas-hero-${i}-16x9-dark.png"
done

for i in 1 2 3 4 5; do
  fetch "$CDN/image-set/modern/avatars/avatar${i}.jpg" \
    "$DEST/avatars/avatar${i}.jpg"
done

fetch "$CDN/block/patterns/square-alt-grid.svg" "$DEST/patterns/square-alt-grid.svg"
fetch "$CDN/block/patterns/noise.png" "$DEST/patterns/noise.png"
fetch "$CDN/block/block-1.svg" "$DEST/block-1.svg"
fetch "$CDN/block/placeholder-1.svg" "$DEST/placeholder-1.svg"
fetch "$CDN/block/placeholder-dark-7-tall.svg" "$DEST/placeholder-dark-7-tall.svg"
fetch "$CDN/block/mockups/phone-2.png" "$DEST/mockups/phone-2.png"

for id in 1 3 4 6; do
  fetch "$CDN/block/avatar-${id}.webp" "$DEST/avatars-webp/avatar-${id}.webp"
done

fetch "$CDN/block/logos/shadcn-ui-icon.svg" "$DEST/logos/ui-kit-icon.svg"
fetch "$CDN/block/logos/typescript-icon.svg" "$DEST/logos/typescript-icon.svg"
fetch "$CDN/block/logos/react-icon.svg" "$DEST/logos/react-icon.svg"
fetch "$CDN/block/logos/tailwind-icon.svg" "$DEST/logos/tailwind-icon.svg"

for i in $(seq 1 12); do
  fetch "$CDN/image-set/placeholder/logos/fictional-company-logo-${i}.svg" \
    "$DEST/logos/fictional-company-logo-${i}.svg"
done

fetch "$CDN/image-set/modern/fullscreen/pawel-czerwinski-IbHFznCKnqA-unsplash.jpg" \
  "$DEST/backgrounds/fullscreen-architecture.jpg"

echo "Assets hero synchronisés dans $DEST"
