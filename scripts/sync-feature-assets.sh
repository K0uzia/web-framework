#!/usr/bin/env bash
# Télécharge les médias des blocs features vers public/assets/sections/features/_shared/
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
DEST="$ROOT/public/assets/sections/features/_shared"
CDN="https://deifkwefumgah.cloudfront.net/shadcnblocks"

mkdir -p "$DEST/lummi" "$DEST/images"

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

fetch "$CDN/image-set/modern/saas-details/saas-detail-1-1x1.png" "$DEST/saas-detail-1-1x1.png"

for i in $(seq 1 6); do
  fetch "$CDN/image-set/modern/saas-details/saas-card-detail-${i}-4x3.svg" \
    "$DEST/saas-card-detail-${i}-4x3.svg"
done

for i in $(seq 1 4); do
  fetch "$CDN/block/placeholder-${i}.svg" "$DEST/placeholder-${i}.svg"
done

fetch "$CDN/block/lummi/bw12.jpeg" "$DEST/lummi/bw12.jpeg"
fetch "$CDN/block/lummi/bw15.jpeg" "$DEST/lummi/bw15.jpeg"
fetch "$CDN/block/lummi/bw20.jpeg" "$DEST/lummi/bw20.jpeg"
fetch "$CDN/block/lummi/bw21.jpeg" "$DEST/lummi/bw21.jpeg"
fetch "$CDN/image-set/placeholder/images/1-1x1.jpg" "$DEST/images/1-1x1.jpg"

echo "Assets features synchronisés dans $DEST"
