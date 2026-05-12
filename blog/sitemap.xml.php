<?php
/**
 * XML sitemap for the public blog. Sourced from the same archive index
 * cache used by blog/index.php, so rebuilds happen automatically when new
 * posts are synced or QA stamps roll over.
 */

require_once __DIR__ . '/../platform/content-loader.php';
require_once __DIR__ . '/../platform/post-overrides.php';
require_once __DIR__ . '/../platform/cache.php';

$settings = wps_load_settings();
wps_enforce_https();

$index = wps_archive_index($settings);
$records = is_array($index['posts'] ?? null) ? $index['posts'] : [];

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
    $url = $archiveUrl . 'post.php?slug=' . rawurlencode($slug);
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
