#!/usr/bin/env bash
set -euo pipefail
BASE="${1:-http://127.0.0.1:8099}"
shift || true
URLS=("${BASE}/blog/" "${BASE}/blog/?page=1" "${BASE}/blog/sitemap.xml.php" "${BASE}/blog/sitemap.xml")

echo "[canonical-chain-audit] base=${BASE}"
for u in "${URLS[@]}"; do
  echo "\n== ${u} =="
  curl -sI "$u" | sed -n '1,12p'
  echo "-- redirect chain --"
  curl -sIL "$u" | rg -n 'HTTP/|Location:'
done
