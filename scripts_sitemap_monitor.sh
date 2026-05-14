#!/usr/bin/env bash
set -euo pipefail
BASE="${1:-http://127.0.0.1:8099}"
SITEMAP="${BASE}/blog/sitemap.xml"
PHP_VARIANT="${BASE}/blog/sitemap.xml.php"

code=$(curl -s -o /tmp/sitemap.xml -w "%{http_code}" "$SITEMAP")
if [[ "$code" != "200" ]]; then
  echo "ERROR: sitemap endpoint returned $code"
  exit 2
fi

redir=$(curl -sI "$PHP_VARIANT" | rg -i '^location:' || true)
if [[ -z "$redir" ]]; then
  echo "ERROR: sitemap.php did not redirect"
  exit 3
fi

count=$(rg -o '<url>' /tmp/sitemap.xml | wc -l | tr -d ' ')
if [[ "$count" -lt 1 ]]; then
  echo "ERROR: sitemap has no url entries"
  exit 4
fi

echo "OK: sitemap healthy, entries=$count"
