#!/usr/bin/env bash
# Audit cross-cluster internal links across every tour package.
#
# Scans each content-system/tours/<package>/blog-post.md and faq.md for
# `wps-cluster:<package_slug>` markdown links, resolves each through the
# cluster registry, and prints the linked/deferred decision per occurrence.
# Use this as a publish-prep gate and to populate the per-run cross-link
# report appended to qa-report.md (SYSQA-20260516-001).
set -euo pipefail

ROOT="$(cd "$(dirname "$0")" && pwd)"
TOURS_DIR="${ROOT}/content-system/tours"

if [[ ! -d "${TOURS_DIR}" ]]; then
  echo "tours dir not found: ${TOURS_DIR}" >&2
  exit 1
fi

WPS_ROOT="${ROOT}" php -d display_errors=1 -r '
$root = getenv("WPS_ROOT") ?: __DIR__;
require $root . "/platform/cluster-links.php";
$base = $root . "/content-system/tours";
$any = false;
foreach (scandir($base) ?: [] as $entry) {
    if ($entry === "." || $entry === "..") continue;
    $folder = $base . "/" . $entry;
    if (!is_dir($folder)) continue;
    $decisions = wps_audit_cluster_links_in_package($folder);
    if (empty($decisions)) continue;
    $any = true;
    echo "\n# {$entry}\n";
    echo wps_format_cluster_link_report($decisions);
}
if (!$any) {
    echo "No wps-cluster: links found across any tour package.\n";
}
'
