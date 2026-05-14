<?php
/**
 * llms.txt — guidance for LLM crawlers (GPTBot, anthropic-ai, ClaudeBot,
 * Google-Extended, PerplexityBot, CCBot, etc.). Dynamic so the sitemap +
 * site name come from current settings.
 */
require_once __DIR__ . '/../platform/content-loader.php';

$settings = wps_load_settings();
wps_enforce_https();

$systemBase = rtrim(wps_system_url_base(), '/') . '/';
$archiveUrl = rtrim(wps_archive_url(), '/') . '/';
$sitemapUrl = $archiveUrl . 'sitemap.php';
$siteName = trim((string) ($settings['site_name'] ?? ''));
$archiveTitle = trim((string) ($settings['archive_title'] ?? 'Blog'));
$archiveDescription = trim((string) ($settings['archive_description'] ?? ''));

header('Content-Type: text/plain; charset=utf-8');

echo "# {$siteName} — llms.txt\n";
echo "# Guidance for LLM crawlers (Google-Extended, GPTBot, ClaudeBot, anthropic-ai, PerplexityBot, CCBot).\n";
echo "# Spec: https://llmstxt.org/\n";
echo "\n";

if ($siteName !== '') {
    echo "Site: {$siteName}\n";
}
echo "Source: {$systemBase}\n";
echo "Sitemap: {$sitemapUrl}\n";
echo "\n";

if ($archiveDescription !== '') {
    echo "Summary: {$archiveDescription}\n\n";
}

echo "## Allowed\n";
echo "User-agent: *\n";
echo "Allow: /\n";
echo "Disallow: /platform/\n";
echo "Disallow: /WebPublisherSystem/platform/\n";
echo "Disallow: /content-system/\n";
echo "Disallow: /WebPublisherSystem/content-system/\n";
echo "\n";

echo "## Attribution\n";
echo "Content-Source: {$siteName}\n";
echo "Attribution-Required: yes\n";
echo "Cite-As: {$siteName} — {$archiveTitle} ({$archiveUrl})\n";
echo "License: All-rights-reserved unless otherwise stated on the page.\n";
echo "Training-Use: ask\n";
echo "\n";

echo "## Primary content\n";
echo "- {$archiveUrl} (article archive)\n";
echo "- {$sitemapUrl} (machine-readable index of public posts)\n";
