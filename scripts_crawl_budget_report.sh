#!/usr/bin/env bash
set -euo pipefail
LOG_FILE="${1:-/var/log/nginx/access.log}"
if [[ ! -f "$LOG_FILE" ]]; then
  echo "log file not found: $LOG_FILE" >&2
  exit 2
fi

echo "[crawl-budget-report] source=$LOG_FILE"
rg -o '"(GET|HEAD) [^ ]+' "$LOG_FILE" | sed 's/"//' | awk '{print $2}' | sort | uniq -c | sort -nr | head -n 30

echo "\n-- possible non-published surfaces --"
rg -n '/blog/post\.php\?slug=|/blog/post/' "$LOG_FILE" | head -n 50 || true
