<?php
/**
 * Lightweight cache layer for the public blog surface.
 *
 *  - wps_archive_index_*  : pre-built JSON index of every publishable post so
 *                            blog/index.php doesn't scan every meta.json on
 *                            each request.
 *  - wps_cached_render_*  : on-disk caches of rendered blog-post.md and
 *                            faq.md HTML, invalidated by the markdown file's
 *                            mtime + a content-loader version stamp.
 */

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/content-loader.php';
require_once __DIR__ . '/post-overrides.php';

const WPS_ARCHIVE_INDEX_FILE   = WPS_DATA_DIR . '/archive-index.json';
const WPS_RENDER_CACHE_VERSION = 'r3';

/**
 * Returns the publishable archive index, building it from disk if the
 * cache is missing or stale relative to the newest tour meta.json mtime.
 */
function wps_archive_index(array $settings, bool $forceRebuild = false): array
{
    if (!$forceRebuild && is_file(WPS_ARCHIVE_INDEX_FILE)) {
        $cacheMtime = (int) @filemtime(WPS_ARCHIVE_INDEX_FILE);
        $newestSource = wps_archive_newest_source_mtime();
        if ($cacheMtime > 0 && $cacheMtime >= $newestSource) {
            $raw = @file_get_contents(WPS_ARCHIVE_INDEX_FILE);
            $decoded = json_decode((string) $raw, true);
            if (is_array($decoded) && isset($decoded['posts']) && is_array($decoded['posts'])) {
                return $decoded;
            }
        }
    }

    return wps_archive_index_rebuild($settings);
}

function wps_archive_newest_source_mtime(): int
{
    $base = realpath(WPS_LOCAL_CONTENT_DIR);
    if ($base === false || !is_dir($base)) {
        return 0;
    }
    $newest = (int) @filemtime($base);
    foreach (scandir($base) ?: [] as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }
        foreach (['meta.json', 'blog-post.md', 'faq.md'] as $f) {
            $p = $base . '/' . $entry . '/' . $f;
            if (is_file($p)) {
                $newest = max($newest, (int) @filemtime($p));
            }
        }
    }
    return $newest;
}

function wps_archive_index_rebuild(array $settings): array
{
    wps_ensure_data_dir();

    $postsResult = wps_get_posts($settings);
    // Public archive surface. We include ready_for_review here to preserve
    // the pre-cache behavior (operators rely on the public archive as a
    // visual preview). Hardening the gate is a separate workflow change.
    $publishable = ['ready_for_review', 'ready_for_sync', 'needs_live_verification', 'published'];
    $records = [];

    if ($postsResult['ok']) {
        foreach ($postsResult['posts'] as $post) {
            if (!in_array((string) ($post['publish_status'] ?? ''), $publishable, true)) {
                continue;
            }
            $applied = wps_apply_post_override($post);
            $publicSlug = (string) ($applied['public_slug'] ?? $applied['slug'] ?? '');
            if ($publicSlug === '') {
                continue;
            }
            $meta = is_array($applied['meta'] ?? null) ? $applied['meta'] : [];
            $records[] = [
                'public_slug'      => $publicSlug,
                'base_slug'        => (string) ($applied['slug'] ?? ''),
                'title'            => (string) ($applied['title'] ?? ''),
                'meta_description' => (string) ($applied['meta_description'] ?? ''),
                'primary_keyword'  => (string) ($applied['primary_keyword'] ?? ''),
                'funnel_stage'     => (string) ($applied['funnel_stage'] ?? ''),
                'publish_status'   => (string) ($applied['publish_status'] ?? ''),
                'qa_status'        => (string) ($applied['qa_status'] ?? ''),
                'published_date'   => (string) ($applied['published_date'] ?? ''),
                'last_qa_date'     => (string) ($meta['last_qa_date'] ?? ''),
                'hero_image'       => (string) ($meta['hero_image'] ?? ''),
                'cluster_parent'   => (string) ($meta['variant_of'] ?? $applied['slug'] ?? ''),
            ];
        }
    }

    usort($records, function (array $a, array $b): int {
        $da = $a['last_qa_date'] ?: $a['published_date'];
        $db = $b['last_qa_date'] ?: $b['published_date'];
        if ($da !== $db) {
            return strcmp($db, $da); // newest first
        }
        return strcmp($a['title'], $b['title']);
    });

    $payload = [
        'schema_version' => 1,
        'built_at'       => gmdate('c'),
        'post_count'     => count($records),
        'posts'          => $records,
    ];

    wps_atomic_write(
        WPS_ARCHIVE_INDEX_FILE,
        json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    );

    return $payload;
}

/**
 * Subset of the archive index suitable for indexing. Only posts whose
 * publish_status === 'published' are returned, so the sitemap and any
 * canonical-only surface never expose review-state copy to crawlers.
 */
function wps_published_records(array $records): array
{
    return array_values(array_filter($records, function ($r) {
        return is_array($r) && (string) ($r['publish_status'] ?? '') === 'published';
    }));
}

function wps_archive_index_invalidate(): void
{
    if (is_file(WPS_ARCHIVE_INDEX_FILE)) {
        @unlink(WPS_ARCHIVE_INDEX_FILE);
    }
}

function wps_archive_paginate(array $records, int $page, int $perPage): array
{
    $perPage = max(1, $perPage);
    $total   = count($records);
    $pages   = max(1, (int) ceil($total / $perPage));
    $page    = max(1, min($pages, $page));
    $offset  = ($page - 1) * $perPage;

    return [
        'page'       => $page,
        'pages'      => $pages,
        'per_page'   => $perPage,
        'total'      => $total,
        'records'    => array_slice($records, $offset, $perPage),
    ];
}

/**
 * Returns rendered HTML for a tour's blog-post.md (or faq.md), caching the
 * result next to the source markdown. The cache key encodes the source
 * mtime and a renderer version so edits + renderer upgrades both invalidate.
 */
function wps_cached_render_markdown(string $sourcePath): string
{
    if (!is_file($sourcePath)) {
        return '';
    }

    $mtime    = (int) @filemtime($sourcePath);
    $cacheKey = WPS_RENDER_CACHE_VERSION . '.' . $mtime;
    $cachePath = $sourcePath . '.cache.html';
    $stampPath = $sourcePath . '.cache.stamp';

    if (is_file($cachePath) && is_file($stampPath)) {
        $stamp = trim((string) @file_get_contents($stampPath));
        if ($stamp === $cacheKey) {
            return (string) @file_get_contents($cachePath);
        }
    }

    $markdown = (string) @file_get_contents($sourcePath);
    if (!function_exists('wps_render_markdown')) {
        require_once __DIR__ . '/markdown.php';
    }
    // Pass the source folder so relative image paths resolve on disk and
    // the renderer can attach width/height attributes (CLS prevention).
    $html = wps_render_markdown($markdown, dirname($sourcePath));

    // Best-effort: cache may fail to write on read-only hosts, that's fine.
    @file_put_contents($cachePath, $html);
    @file_put_contents($stampPath, $cacheKey);

    return $html;
}

/**
 * Parse faq.md into {question, answer_html} pairs split on H2 headings.
 * Falls back to a single answer block when no H2s are present.
 */
function wps_parse_faq_pairs(string $markdown): array
{
    $markdown = trim($markdown);
    if ($markdown === '') {
        return [];
    }

    if (!function_exists('wps_render_markdown')) {
        require_once __DIR__ . '/markdown.php';
    }

    // Strip the leading H1 (e.g. "# FAQ") so it doesn't end up inside the
    // first answer.
    $markdown = preg_replace('/^\s*#\s+[^\n]+\n/', '', $markdown, 1) ?? $markdown;

    $pairs = [];
    $lines = preg_split('/\r\n|\r|\n/', $markdown) ?: [];
    $currentQ = null;
    $buffer   = [];

    $flush = function () use (&$pairs, &$currentQ, &$buffer): void {
        if ($currentQ !== null) {
            $answerMd = trim(implode("\n", $buffer));
            $pairs[] = [
                'question'    => trim($currentQ),
                'answer_html' => $answerMd === '' ? '' : wps_render_markdown($answerMd),
                'answer_text' => $answerMd,
            ];
        }
        $currentQ = null;
        $buffer = [];
    };

    foreach ($lines as $line) {
        if (preg_match('/^\s*##\s+(.+?)\s*#*\s*$/', $line, $m)) {
            $flush();
            $currentQ = $m[1];
            continue;
        }
        if ($currentQ === null) {
            // Pre-Q content (rare): keep as a preamble pair with no question.
            $buffer[] = $line;
            continue;
        }
        $buffer[] = $line;
    }
    $flush();

    return $pairs;
}
