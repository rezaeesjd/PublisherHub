<?php
/**
 * XML sitemap for the public blog. Sourced from the same archive index
 * cache used by blog/index.php, so rebuilds happen automatically when new
 * posts are synced or QA stamps roll over.
 */

require_once __DIR__ . '/../platform/content-loader.php';
require_once __DIR__ . '/../platform/post-overrides.php';
require_once __DIR__ . '/../platform/cache.php';

// Canonicalize direct requests to the friendly /blog/sitemap.xml endpoint.
// This file is still included by sitemap.xml as the implementation target.
if (PHP_SAPI !== 'cli' && !headers_sent()) {
    $requestUri = str_replace('\\', '/', (string) ($_SERVER['REQUEST_URI'] ?? ''));
    if (preg_match('#/sitemap\.xml\.php(?:$|\?)#', $requestUri) === 1) {
        $canonical = rtrim(wps_archive_url(), '/') . '/sitemap.xml';
        header('Location: ' . $canonical, true, 301);
        exit;
    }
}

$settings = wps_load_settings();
wps_enforce_https();
wps_emit_public_headers();

$index = wps_archive_index($settings);
$allRecords = is_array($index['posts'] ?? null) ? $index['posts'] : [];

// Crawlers must only see content gated to publish_status === 'published'.
// Review-state copy is intentionally excluded — accessible via direct URL
// (with X-Robots-Tag: noindex) but never advertised in the sitemap.
$records = wps_published_records($allRecords);

$archiveUrl = rtrim(wps_archive_url(), '/') . '/';

header('Content-Type: application/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

// Archive index
echo "  <url>\n";
echo '    <loc>' . htmlspecialchars($archiveUrl, ENT_QUOTES, 'UTF-8') . "</loc>\n";
echo "    <changefreq>daily</changefreq>\n";
echo "    <priority>0.6</priority>\n";
echo "  </url>\n";

foreach ($records as $record) {
    $slug = (string) ($record['public_slug'] ?? '');
    if ($slug === '') {
        continue;
    }
    $url = wps_public_post_url($slug);
    $lastmod = (string) ($record['last_qa_date'] ?? $record['published_date'] ?? '');
    $funnel = (string) ($record['funnel_stage'] ?? '');
    $priority = $funnel === 'BOFU' ? '0.9' : ($funnel === 'MOFU' ? '0.8' : '0.7');

    echo "  <url>\n";
    echo '    <loc>' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . "</loc>\n";
    if ($lastmod !== '') {
        echo '    <lastmod>' . htmlspecialchars($lastmod, ENT_QUOTES, 'UTF-8') . "</lastmod>\n";
    }
    echo "    <changefreq>weekly</changefreq>\n";
    echo "    <priority>{$priority}</priority>\n";
    echo "  </url>\n";
}

echo "</urlset>\n";
