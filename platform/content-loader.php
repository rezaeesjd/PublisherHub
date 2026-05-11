<?php
require_once __DIR__ . '/functions.php';

const WPS_LOCAL_CONTENT_DIR = __DIR__ . '/../content-system/tours';

function wps_replace_placeholders(string $content, array $settings): string
{
    return str_replace(
        ['{{WebsiteLink}}', '{{TripAdvisorLink}}', '{{ViatorLink}}'],
        [$settings['website_link'] ?? '{{WebsiteLink}}', $settings['tripadvisor_link'] ?? '{{TripAdvisorLink}}', $settings['viator_link'] ?? '{{ViatorLink}}'],
        $content
    );
}

function wps_get_content_folders(array $settings): array
{
    $baseDir = realpath(WPS_LOCAL_CONTENT_DIR);

    if ($baseDir === false || !is_dir($baseDir)) {
        return [
            'ok' => false,
            'error' => 'Local content folder not found: WebPublisherSystem/content-system/tours. Upload or sync the content-system folder first.',
            'folders' => [],
        ];
    }

    $folders = [];
    $items = scandir($baseDir);

    if ($items === false) {
        return ['ok' => false, 'error' => 'Could not read local content folder.', 'folders' => []];
    }

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $fullPath = $baseDir . '/' . $item;
        if (!is_dir($fullPath)) {
            continue;
        }

        $folders[] = [
            'name' => $item,
            'path' => $fullPath,
            'relative_path' => 'content-system/tours/' . $item,
            'type' => 'dir',
        ];
    }

    return ['ok' => true, 'error' => '', 'folders' => $folders];
}

function wps_read_local_file(string $path): array
{
    $baseDir = realpath(WPS_LOCAL_CONTENT_DIR);
    $realPath = realpath($path);

    if ($baseDir === false || $realPath === false || !str_starts_with($realPath, $baseDir)) {
        return ['ok' => false, 'content' => '', 'error' => 'Unsafe or missing local file path.'];
    }

    if (!is_file($realPath)) {
        return ['ok' => false, 'content' => '', 'error' => 'Local file not found.'];
    }

    $content = file_get_contents($realPath);
    if ($content === false) {
        return ['ok' => false, 'content' => '', 'error' => 'Could not read local file.'];
    }

    return ['ok' => true, 'content' => $content, 'error' => ''];
}

function wps_get_posts(array $settings): array
{
    $foldersResult = wps_get_content_folders($settings);
    if (!$foldersResult['ok']) {
        return ['ok' => false, 'error' => $foldersResult['error'], 'posts' => []];
    }

    $posts = [];

    foreach ($foldersResult['folders'] as $folder) {
        $folderPath = $folder['path'] ?? '';
        $folderName = $folder['name'] ?? '';

        if (!$folderPath || !$folderName) {
            continue;
        }

        $metaFile = wps_read_local_file($folderPath . '/meta.json');
        if (!$metaFile['ok']) {
            continue;
        }

        $meta = json_decode($metaFile['content'], true);
        if (!is_array($meta)) {
            continue;
        }

        $slug = $meta['slug'] ?? $folderName;
        $publishedDate = '';
        if (!empty($meta['last_qa_date']) && is_string($meta['last_qa_date'])) {
            $publishedDate = $meta['last_qa_date'];
        } else {
            $blogFilePath = $folderPath . '/blog-post.md';
            if (is_file($blogFilePath)) {
                $modifiedAt = filemtime($blogFilePath);
                if ($modifiedAt !== false) {
                    $publishedDate = gmdate('Y-m-d', $modifiedAt);
                }
            }
        }

        $posts[] = [
            'folder_name' => $folderName,
            'folder_path' => $folderPath,
            'slug' => $slug,
            'title' => $meta['page_title'] ?? $meta['tour_title'] ?? ucwords(str_replace('-', ' ', $folderName)),
            'meta_description' => $meta['meta_description'] ?? '',
            'primary_keyword' => $meta['primary_keyword'] ?? '',
            'funnel_stage' => $meta['funnel_stage'] ?? '',
            'product_reference_code' => $meta['product_reference_code'] ?? '',
            'brand' => $meta['brand'] ?? ($settings['site_name'] ?? ''),
            'publish_status' => (string) ($meta['publish_status'] ?? 'draft'),
            'qa_status' => (string) ($meta['qa_status'] ?? 'pending'),
            'public_copy_state' => (string) ($meta['public_copy_state'] ?? 'not_started'),
            'published_date' => $publishedDate,
            'meta' => $meta,
        ];
    }

    usort($posts, fn($a, $b) => strcmp($a['title'], $b['title']));

    return ['ok' => true, 'error' => '', 'posts' => $posts];
}

function wps_human_workflow_status(array $post): array
{
    $publish = (string) ($post['publish_status'] ?? 'draft');
    $qa = (string) ($post['qa_status'] ?? 'pending');

    if ($publish === 'published') {
        return ['label' => 'Published', 'tone' => 'success'];
    }

    if ($qa === 'needs_clarification') {
        return ['label' => 'Blocked', 'tone' => 'danger'];
    }

    if ($qa === 'needs_fix' || $publish === 'needs_fix') {
        return ['label' => 'Revision Required', 'tone' => 'danger'];
    }

    if (in_array($publish, ['ready_for_review', 'ready_for_sync', 'needs_live_verification'], true)) {
        return ['label' => 'Needs Review', 'tone' => 'warning'];
    }

    return ['label' => 'Draft', 'tone' => 'muted'];
}

function wps_find_post_by_slug(array $settings, string $slug): array
{
    $postsResult = wps_get_posts($settings);
    if (!$postsResult['ok']) {
        return ['ok' => false, 'error' => $postsResult['error'], 'post' => null];
    }

    foreach ($postsResult['posts'] as $post) {
        if (($post['slug'] ?? '') === $slug || ($post['folder_name'] ?? '') === $slug) {
            return ['ok' => true, 'error' => '', 'post' => $post];
        }
    }

    return ['ok' => false, 'error' => 'Post not found.', 'post' => null];
}

function wps_get_post_content(array $settings, array $post): array
{
    $folderPath = $post['folder_path'] ?? '';
    if (!$folderPath) {
        return ['ok' => false, 'error' => 'Missing post folder path.', 'blog' => '', 'faq' => ''];
    }

    $blog = wps_read_local_file($folderPath . '/blog-post.md');
    $faq = wps_read_local_file($folderPath . '/faq.md');

    if (!$blog['ok']) {
        return ['ok' => false, 'error' => $blog['error'], 'blog' => '', 'faq' => ''];
    }

    return [
        'ok' => true,
        'error' => '',
        'blog' => wps_replace_placeholders($blog['content'], $settings),
        'faq' => $faq['ok'] ? wps_replace_placeholders($faq['content'], $settings) : '',
    ];
}

require_once __DIR__ . '/markdown.php';

function wps_markdown_to_html(string $markdown): string
{
    return wps_render_markdown($markdown);
}
