<?php
require_once __DIR__ . '/../platform/content-loader.php';

$settings = wps_load_settings();
wps_enforce_https();

$archiveUrl = rtrim(wps_archive_url(), '/') . '/';
$sitemapUrl = $archiveUrl . 'sitemap.xml';

header('Content-Type: text/plain; charset=utf-8');

echo "User-agent: *\n";
echo "Allow: /\n";
echo "Disallow: /platform/\n";
echo "Disallow: /WebPublisherSystem/platform/\n";
echo "Disallow: /content-system/\n";
echo "Disallow: /WebPublisherSystem/content-system/\n";
echo "\n";
echo "Sitemap: {$sitemapUrl}\n";
