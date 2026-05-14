#!/usr/bin/env bash
set -euo pipefail
BASE="${1:-http://127.0.0.1:8099}"
PUBLISHED_SLUG="${2:-}"
UNPUBLISHED_SLUG="${3:-}"

echo "[P2 monitor] BASE=${BASE}"

check() {
  local name="$1"; shift
  echo "-- ${name}"
  "$@"
}

check "sitemap endpoint" curl -sI "${BASE}/blog/sitemap.php" | sed -n '1,8p'
check "sitemap implementation endpoint" curl -sI "${BASE}/blog/sitemap.xml.php" | sed -n '1,8p'
check "archive canonical" curl -sI "${BASE}/blog/?page=1" | sed -n '1,10p'

if [[ -n "${PUBLISHED_SLUG}" ]]; then
  check "published post robots" curl -s "${BASE}/blog/post.php?slug=${PUBLISHED_SLUG}" | rg -n '<meta name="robots"'
fi

if [[ -n "${UNPUBLISHED_SLUG}" ]]; then
  echo "-- unpublished post expected 404"
  code=$(curl -s -o /tmp/p2_unpublished.out -w "%{http_code}" "${BASE}/blog/post.php?slug=${UNPUBLISHED_SLUG}")
  echo "status=${code}"
fi
