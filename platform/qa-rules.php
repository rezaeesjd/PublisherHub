<?php
/**
 * Phase 1 QA rules. Pure functions over a tour folder + meta. No I/O
 * other than reading the on-disk markdown so the CLI and a future
 * in-app QA gate can share the implementation.
 *
 * Each rule returns ['severity' => 'pass'|'warn'|'fail', 'message' => string].
 */

require_once __DIR__ . '/functions.php';

const WPS_REQUIRED_TOUR_FILES = [
    'source-facts.md',
    'brief.md',
    'keywords.md',
    'blog-post.md',
    'faq.md',
    'meta.json',
    'internal-links.md',
    'automation-notes.md',
    'qa-report.md',
];

const WPS_FORBIDDEN_ADMIN_LABELS = [
    '/^\s*#{1,6}\s*Page\s+Title\s*$/im',
    '/^\s*#{1,6}\s*URL\s+Slug\s*$/im',
    '/^\s*#{1,6}\s*Meta\s+Description\s*$/im',
    '/^\s*#{1,6}\s*H1\s*$/im',
    '/^\s*#{1,6}\s*Hook\s+paragraph\s*$/im',
    '/^\s*#{1,6}\s*Main\s+value\s+section\s*$/im',
    '/^\s*#{1,6}\s*Internal\s+linking\s+suggestions?\s*$/im',
    '/^\s*#{1,6}\s*Funnel\s+Stage\s*$/im',
    '/^\s*#{1,6}\s*Primary\s+Keyword\s*$/im',
];

const WPS_PLACEHOLDER_PATTERN = '/\{\{[A-Za-z0-9_]+\}\}/';

function wps_qa_run_for_tour(string $tourDir): array
{
    $findings = [];

    foreach (WPS_REQUIRED_TOUR_FILES as $required) {
        $path = $tourDir . '/' . $required;
        if (!is_file($path)) {
            $findings[] = wps_qa_finding('fail', 'missing-file', "Required file missing: {$required}");
        }
    }

    $metaPath = $tourDir . '/meta.json';
    $meta = is_file($metaPath) ? json_decode((string) file_get_contents($metaPath), true) : null;

    if (!is_array($meta)) {
        $findings[] = wps_qa_finding('fail', 'meta-invalid', 'meta.json is missing or not valid JSON.');
        $meta = [];
    }

    foreach (['brand', 'page_title', 'slug', 'meta_description', 'primary_keyword', 'funnel_stage'] as $field) {
        if (empty($meta[$field])) {
            $findings[] = wps_qa_finding('fail', 'meta-field', "meta.{$field} is required.");
        }
    }

    $slug = (string) ($meta['slug'] ?? '');
    if ($slug !== '' && !preg_match('/^[a-z0-9][a-z0-9-]*[a-z0-9]$/', $slug)) {
        $findings[] = wps_qa_finding('fail', 'meta-slug-format', "meta.slug '{$slug}' is not a clean lowercase, hyphen-separated slug.");
    }

    $publishStatus = (string) ($meta['publish_status'] ?? 'draft');
    $allowedStatuses = ['draft', 'ready_for_review', 'needs_fix', 'ready_for_sync', 'needs_live_verification', 'published', 'approved', 'archived'];
    if (!in_array($publishStatus, $allowedStatuses, true)) {
        $findings[] = wps_qa_finding('fail', 'publish-status', "meta.publish_status '{$publishStatus}' is not in the allowed set.");
    }

    $blogPath = $tourDir . '/blog-post.md';
    $blogContent = is_file($blogPath) ? (string) file_get_contents($blogPath) : '';

    if ($blogContent !== '') {
        foreach (WPS_FORBIDDEN_ADMIN_LABELS as $pattern) {
            if (preg_match($pattern, $blogContent, $m)) {
                $findings[] = wps_qa_finding('fail', 'admin-label-leak', 'Admin label found in public blog-post.md: ' . trim($m[0]));
            }
        }

        $h1Count = preg_match_all('/^#\s+/m', $blogContent);
        if ($h1Count === 0) {
            $findings[] = wps_qa_finding('fail', 'h1-missing', 'blog-post.md has no top-level H1.');
        } elseif ($h1Count > 1) {
            $findings[] = wps_qa_finding('warn', 'h1-multiple', "blog-post.md has {$h1Count} H1 lines; expected exactly one.");
        }

        if (preg_match(WPS_PLACEHOLDER_PATTERN, $blogContent, $m)) {
            $findings[] = wps_qa_finding('warn', 'placeholder-link', 'Public blog-post.md still contains placeholder ' . $m[0] . ' — not publish-ready.');
        }

        $brandName = (string) ($meta['brand'] ?? '');
        if ($brandName !== '' && stripos($blogContent, $brandName) === false) {
            $findings[] = wps_qa_finding('warn', 'brand-missing', "blog-post.md does not mention the active brand '{$brandName}'.");
        }
    }

    $faqPath = $tourDir . '/faq.md';
    if (is_file($faqPath) && preg_match(WPS_PLACEHOLDER_PATTERN, (string) file_get_contents($faqPath), $m)) {
        $findings[] = wps_qa_finding('warn', 'placeholder-link', 'faq.md still contains placeholder ' . $m[0] . '.');
    }

    $sourcePath = $tourDir . '/source-facts.md';
    if (is_file($sourcePath)) {
        $sourceContent = (string) file_get_contents($sourcePath);
        if (stripos($sourceContent, 'missing input') === false && stripos($sourceContent, 'human review') === false) {
            $findings[] = wps_qa_finding('warn', 'source-facts-incomplete', 'source-facts.md does not flag any missing inputs or human-review items.');
        }
    }

    $overall = 'pass';
    foreach ($findings as $f) {
        if ($f['severity'] === 'fail') {
            $overall = 'fail';
            break;
        }
        if ($f['severity'] === 'warn') {
            $overall = 'warning';
        }
    }

    return [
        'tour' => basename($tourDir),
        'overall' => $overall,
        'findings' => $findings,
    ];
}

function wps_qa_finding(string $severity, string $code, string $message): array
{
    return ['severity' => $severity, 'code' => $code, 'message' => $message];
}

function wps_qa_run_all(string $toursRoot): array
{
    $reports = [];
    if (!is_dir($toursRoot)) {
        return $reports;
    }

    foreach (scandir($toursRoot) ?: [] as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }
        $path = $toursRoot . '/' . $entry;
        if (!is_dir($path)) {
            continue;
        }
        $reports[] = wps_qa_run_for_tour($path);
    }

    return $reports;
}
