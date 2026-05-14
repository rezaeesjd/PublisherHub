#!/usr/bin/env bash
set -euo pipefail
BASE="${1:-http://127.0.0.1:8099}"

echo "== P0 checks against ${BASE} =="

echo "-- sitemap canonical --"
curl -sI "${BASE}/blog/sitemap.xml.php" | sed -n '1,8p'

echo "-- sitemap friendly endpoint --"
curl -sI "${BASE}/blog/sitemap.xml" | sed -n '1,8p'

echo "-- archive page=1 canonicalization --"
curl -sI "${BASE}/blog/?page=1" | sed -n '1,10p'

echo "-- archive indexability --"
curl -s "${BASE}/blog/" | rg -n "<meta name=\"robots\"|<link rel=\"canonical\"" -n
