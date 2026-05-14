<?php
/**
 * RSS 2.0 feed for the public blog. Sourced from the same published archive
 * index used by blog/index.php and the XML sitemap, so it rebuilds
 * automatically when posts are synced or QA stamps roll over.
 */

require_once __DIR__ . '/../platform/content-loader.php';
require_once __DIR__ . '/../platform/post-overrides.php';
require_once __DIR__ . '/../platform/cache.php';

$settings = wps_load_settings();
wps_enforce_https();
wps_emit_public_headers();

$index = wps_archive_index($settings);
$allRecords = is_array($index['posts'] ?? null) ? $index['posts'] : [];

// Crawlers and feed readers only ever see published content — the same
// gate the XML sitemap applies.
$records = wps_published_records($allRecords);

$archiveUrl = rtrim(wps_archive_url(), '/') . '/';
$feedUrl = $archiveUrl . 'feed.xml';
$siteName = trim((string) ($settings['site_name'] ?? 'Blog'));
$archiveTitle = trim((string) ($settings['archive_title'] ?? 'Blog'));
$archiveDescription = trim((string) ($settings['archive_description'] ?? ''));
$feedTitle = $archiveTitle !== '' ? $archiveTitle : ($siteName !== '' ? $siteName : 'Blog');

$rfc822 = static function (string $date): string {
    $ts = $date !== '' ? strtotime($date) : false;
    return gmdate('D, d M Y H:i:s', $ts !== false ? $ts : time()) . ' GMT';
};

$buildDate = '';
foreach ($records as $r) {
    $d = (string) ($r['last_qa_date'] ?? $r['published_date'] ?? '');
    if ($d !== '' && $d > $buildDate) {
        $buildDate = $d;
    }
}

header('Content-Type: application/rss+xml; charset=utf-8');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">' . "\n";
echo "  <channel>\n";
echo '    <title>' . htmlspecialchars($feedTitle, ENT_QUOTES, 'UTF-8') . "</title>\n";
echo '    <link>' . htmlspecialchars($archiveUrl, ENT_QUOTES, 'UTF-8') . "</link>\n";
echo '    <atom:link href="' . htmlspecialchars($feedUrl, ENT_QUOTES, 'UTF-8') . '" rel="self" type="application/rss+xml" />' . "\n";
if ($archiveDescription !== '') {
    echo '    <description>' . htmlspecialchars($archiveDescription, ENT_QUOTES, 'UTF-8') . "</description>\n";
}
echo "    <language>en</language>\n";
echo '    <lastBuildDate>' . htmlspecialchars($rfc822($buildDate), ENT_QUOTES, 'UTF-8') . "</lastBuildDate>\n";

foreach ($records as $record) {
    $slug = (string) ($record['public_slug'] ?? '');
    if ($slug === '') {
        continue;
    }
    $url = wps_public_post_url($slug);
    $title = (string) ($record['title'] ?? $slug);
    $desc = trim((string) ($record['meta_description'] ?? ''));
    $date = (string) ($record['last_qa_date'] ?? $record['published_date'] ?? '');

    echo "    <item>\n";
    echo '      <title>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . "</title>\n";
    echo '      <link>' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . "</link>\n";
    echo '      <guid isPermaLink="true">' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . "</guid>\n";
    if ($desc !== '') {
        echo '      <description>' . htmlspecialchars($desc, ENT_QUOTES, 'UTF-8') . "</description>\n";
    }
    if ($date !== '') {
        echo '      <pubDate>' . htmlspecialchars($rfc822($date), ENT_QUOTES, 'UTF-8') . "</pubDate>\n";
    }
    echo "    </item>\n";
}

echo "  </channel>\n";
echo "</rss>\n";
