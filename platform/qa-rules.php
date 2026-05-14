<?php
/**
 * QA rules for tour content packages. Pure functions over a tour folder
 * + meta. No I/O other than reading on-disk files so the CLI and the
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

const WPS_RECOMMENDED_TOUR_FILES = [
    'CHANGELOG.md',
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

const WPS_META_SCHEMA_PATH = __DIR__ . '/../content-system/meta.schema.json';

function wps_qa_run_for_tour(string $tourDir): array
{
    $findings = [];

    foreach (WPS_REQUIRED_TOUR_FILES as $required) {
        $path = $tourDir . '/' . $required;
        if (!is_file($path)) {
            $findings[] = wps_qa_finding('fail', 'missing-file', "Required file missing: {$required}");
        }
    }

    foreach (WPS_RECOMMENDED_TOUR_FILES as $recommended) {
        $path = $tourDir . '/' . $recommended;
        if (!is_file($path)) {
            $findings[] = wps_qa_finding('warn', 'missing-recommended-file', "Recommended file missing: {$recommended}");
        }
    }

    $metaPath = $tourDir . '/meta.json';
    $meta = is_file($metaPath) ? json_decode((string) file_get_contents($metaPath), true) : null;

    if (!is_array($meta)) {
        $findings[] = wps_qa_finding('fail', 'meta-invalid', 'meta.json is missing or not valid JSON.');
        $meta = [];
    }

    foreach (wps_meta_schema_findings($meta) as $f) {
        $findings[] = $f;
    }

    $publishStatus = (string) ($meta['publish_status'] ?? 'draft');
    $isPublished = ($publishStatus === 'published');

    foreach (wps_clarifications_findings($meta, $isPublished) as $f) {
        $findings[] = $f;
    }

    $blogPath = $tourDir . '/blog-post.md';
    $blogContent = is_file($blogPath) ? (string) file_get_contents($blogPath) : '';

    if ($blogContent !== '') {
        foreach (WPS_FORBIDDEN_ADMIN_LABELS as $pattern) {
            if (preg_match($pattern, $blogContent, $m)) {
                $findings[] = wps_qa_finding('fail', 'admin-label-leak', 'Admin label found in public blog-post.md: ' . trim($m[0]));
            }
        }

        $h1Count = preg_match_all('/^#\s+(.+?)\s*#*\s*$/m', $blogContent, $h1Matches);
        if ($h1Count === 0) {
            $findings[] = wps_qa_finding('fail', 'h1-missing', 'blog-post.md has no top-level H1.');
        } elseif ($h1Count > 1) {
            $findings[] = wps_qa_finding('warn', 'h1-multiple', "blog-post.md has {$h1Count} H1 lines; expected exactly one.");
        }

        // H1 ↔ page_title parity. Google de-duplicates conflicting signals
        // so a major mismatch dilutes the topical signal. Warn when normalized
        // forms diverge more than the 60% similarity threshold.
        $pageTitle = trim((string) ($meta['page_title'] ?? ''));
        if ($h1Count >= 1 && $pageTitle !== '' && isset($h1Matches[1][0])) {
            $firstH1 = trim($h1Matches[1][0]);
            $normalize = function (string $s): string {
                $s = mb_strtolower($s);
                $s = preg_replace('/[^a-z0-9\s]+/u', ' ', $s) ?? $s;
                return trim(preg_replace('/\s+/u', ' ', $s) ?? $s);
            };
            $a = $normalize($firstH1);
            $b = $normalize($pageTitle);
            similar_text($a, $b, $percent);
            if ($percent < 60) {
                $findings[] = wps_qa_finding(
                    'warn',
                    'title-h1-mismatch',
                    "blog-post.md H1 '{$firstH1}' diverges from meta.page_title '{$pageTitle}' (similarity " . round($percent) . '%). Align them for a consistent ranking signal.'
                );
            }
        }

        // Title length. Google currently shows up to ~60 chars before
        // truncating; titles > 65 chars almost always get rewritten.
        if ($pageTitle !== '' && mb_strlen($pageTitle) > 65) {
            $findings[] = wps_qa_finding(
                'warn',
                'title-too-long',
                'meta.page_title is ' . mb_strlen($pageTitle) . ' characters (target: ≤ 60). Google will truncate.'
            );
        }
        if ($pageTitle !== '' && mb_strlen($pageTitle) < 25) {
            $findings[] = wps_qa_finding(
                'warn',
                'title-too-short',
                'meta.page_title is ' . mb_strlen($pageTitle) . ' characters; usually too short to communicate intent.'
            );
        }

        $brandName = (string) ($meta['brand'] ?? '');
        if ($brandName !== '' && stripos($blogContent, $brandName) === false) {
            $findings[] = wps_qa_finding('warn', 'brand-missing', "blog-post.md does not mention the active brand '{$brandName}'.");
        }

        // Broken images: ![alt](path) where path is relative + missing on disk.
        if (preg_match_all('/!\[([^\]]*)\]\(([^)\s]+)/', $blogContent, $imgMatches, PREG_SET_ORDER)) {
            foreach ($imgMatches as $im) {
                $alt = trim((string) $im[1]);
                $src = trim((string) $im[2]);
                if ($alt === '') {
                    $findings[] = wps_qa_finding(
                        'warn',
                        'image-alt-missing',
                        "Image without alt text: {$src}. Alt text is required for accessibility and image SEO."
                    );
                }
                if ($src !== '' && !preg_match('#^(https?:)?//#i', $src) && !str_starts_with($src, 'data:')) {
                    $resolved = $tourDir . '/' . ltrim($src, '/');
                    if (!is_file($resolved)) {
                        $findings[] = wps_qa_finding(
                            'warn',
                            'image-missing',
                            "Image referenced but missing on disk: {$src}"
                        );
                    }
                }
            }
        }

        // Broken internal anchors / generic anchor text. Detect ambiguous
        // anchor text — "click here", "read more", "here", "book now"
        // (when not pointing at a known booking domain).
        if (preg_match_all('/\[([^\]]+)\]\(([^)\s]+)\)/', $blogContent, $linkMatches, PREG_SET_ORDER)) {
            $generic = ['click here', 'read more', 'here', 'this link', 'learn more', 'more'];
            foreach ($linkMatches as $lm) {
                $label = trim(strip_tags($lm[1]));
                $href  = trim($lm[2]);
                $labelLower = strtolower($label);
                if (in_array($labelLower, $generic, true)) {
                    $findings[] = wps_qa_finding(
                        'warn',
                        'link-generic-anchor',
                        "Generic anchor text '{$label}' → {$href}. Use descriptive anchor text that includes the destination topic."
                    );
                }
                // Internal links that look like raw filesystem references.
                if (preg_match('/\.md(?:[?#].*)?$/i', $href)) {
                    $findings[] = wps_qa_finding(
                        'warn',
                        'link-raw-markdown',
                        "Link points to a raw markdown file: {$href}. Convert to a public URL or relative slug."
                    );
                }
            }
        }
    }

    $placeholderTargets = [
        'blog-post.md' => 'public-facing',
        'faq.md' => 'public-facing',
        'internal-links.md' => 'internal',
        'brief.md' => 'internal',
        'automation-notes.md' => 'internal',
    ];

    foreach ($placeholderTargets as $file => $kind) {
        $path = $tourDir . '/' . $file;
        if (!is_file($path)) {
            continue;
        }
        $content = (string) file_get_contents($path);
        if (preg_match(WPS_PLACEHOLDER_PATTERN, $content, $m)) {
            $findings[] = wps_qa_finding(
                $isPublished && $kind === 'public-facing' ? 'fail' : 'warn',
                'placeholder-link',
                "{$file} still contains placeholder " . $m[0] . '.'
            );
        }
    }

    $sourcePath = $tourDir . '/source-facts.md';
    if (is_file($sourcePath)) {
        $sourceContent = (string) file_get_contents($sourcePath);
        if (stripos($sourceContent, 'missing input') === false && stripos($sourceContent, 'human review') === false) {
            $findings[] = wps_qa_finding('warn', 'source-facts-incomplete', 'source-facts.md does not flag any missing inputs or human-review items.');
        }
    }

    if (!empty($meta['hero_image'])) {
        $hero = (string) $meta['hero_image'];
        if (!preg_match('#^https?://#', $hero)) {
            $resolved = $tourDir . '/' . ltrim($hero, '/');
            if (!is_file($resolved)) {
                $findings[] = wps_qa_finding('warn', 'hero-image-missing', "meta.hero_image '{$hero}' does not exist on disk.");
            }
        }
    } else {
        if (is_dir($tourDir . '/images')) {
            $findings[] = wps_qa_finding('warn', 'hero-image-not-set', 'images/ folder exists but meta.hero_image is not set.');
        }
    }

    if ($publishStatus === 'published') {
        foreach ($findings as $i => $f) {
            if ($f['severity'] === 'warn' && in_array($f['code'], ['placeholder-link', 'source-facts-incomplete'], true)) {
                $findings[$i]['severity'] = 'fail';
                $findings[$i]['message'] .= ' (escalated: publish_status=published)';
            }
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
        'meta' => $meta,
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

    // Cross-package checks. These attach findings to individual reports
    // so the existing per-tour UI doesn't need a separate code path.
    wps_qa_apply_cross_package_findings($reports);

    return $reports;
}

/**
 * Stale freshness threshold for published content, in days. Travel content
 * decays fast (prices, hours, seasonal info), so anything older than this
 * is flagged for refresh on the dashboard and downgraded to qa_status=stale.
 */
const WPS_QA_FRESHNESS_THRESHOLD_DAYS = 90;

function wps_qa_apply_cross_package_findings(array &$reports): void
{
    if (empty($reports)) {
        return;
    }

    // -- S7: keyword cannibalization across the same cluster ----------------
    // Two published/ready posts that share the same primary_keyword and live
    // in the same cluster (variant_of chain) will compete in SERPs.
    $byKeywordCluster = [];
    foreach ($reports as $i => $report) {
        $meta = $report['meta'] ?? [];
        if (!is_array($meta)) {
            continue;
        }
        $keyword = strtolower(trim((string) ($meta['primary_keyword'] ?? '')));
        $cluster = strtolower(trim((string) ($meta['variant_of'] ?? $meta['slug'] ?? $report['tour'] ?? '')));
        $status  = (string) ($meta['publish_status'] ?? 'draft');
        $isLive  = in_array($status, ['ready_for_review', 'published', 'needs_live_verification', 'published'], true);

        if ($keyword === '' || !$isLive) {
            continue;
        }
        $key = $cluster . '||' . $keyword;
        $byKeywordCluster[$key][] = $i;
    }

    foreach ($byKeywordCluster as $key => $indexes) {
        if (count($indexes) < 2) {
            continue;
        }
        [, $keyword] = explode('||', $key, 2);
        $siblings = [];
        foreach ($indexes as $i) {
            $siblings[] = (string) ($reports[$i]['tour'] ?? '');
        }
        $sibList = implode(', ', array_values(array_filter($siblings)));
        foreach ($indexes as $i) {
            $self = (string) ($reports[$i]['tour'] ?? '');
            $others = implode(', ', array_values(array_filter($siblings, fn($s) => $s !== $self)));
            $reports[$i]['findings'][] = wps_qa_finding(
                'warn',
                'keyword-cannibalization',
                "primary_keyword '{$keyword}' also targeted by sibling(s) in cluster: {$others}. These posts will compete in SERPs — diversify modifiers or merge."
            );
            if ($reports[$i]['overall'] === 'pass') {
                $reports[$i]['overall'] = 'warning';
            }
        }
        unset($sibList);
    }

    // -- S11: freshness pass for published packages -------------------------
    $today = new DateTimeImmutable('today', new DateTimeZone('UTC'));
    foreach ($reports as $i => $report) {
        $meta = $report['meta'] ?? [];
        if (!is_array($meta)) {
            continue;
        }
        if ((string) ($meta['publish_status'] ?? '') !== 'published') {
            continue;
        }
        // Freshness anchors on last_content_refresh_at (set when copy is
        // actually rewritten) and falls back to first_published_at, so a
        // passive QA stamp does not reset the staleness clock.
        $anchor = (string) ($meta['last_content_refresh_at']
            ?? $meta['first_published_at']
            ?? $meta['last_qa_date']
            ?? '');
        if ($anchor === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $anchor)) {
            $reports[$i]['findings'][] = wps_qa_finding(
                'warn',
                'freshness-unknown',
                'Published package has no first_published_at / last_content_refresh_at; cannot evaluate freshness.'
            );
            if ($reports[$i]['overall'] === 'pass') {
                $reports[$i]['overall'] = 'warning';
            }
            continue;
        }
        $lastDate = DateTimeImmutable::createFromFormat('!Y-m-d', $anchor, new DateTimeZone('UTC'));
        if ($lastDate === false) {
            continue;
        }
        $ageDays = (int) $today->diff($lastDate)->days;
        if ($ageDays > WPS_QA_FRESHNESS_THRESHOLD_DAYS) {
            $reports[$i]['findings'][] = wps_qa_finding(
                'warn',
                'content-stale',
                "Published content is {$ageDays} days old (threshold: " . WPS_QA_FRESHNESS_THRESHOLD_DAYS . " days). Refresh prices, hours, seasonal claims and re-run QA."
            );
            if ($reports[$i]['overall'] === 'pass') {
                $reports[$i]['overall'] = 'warning';
            }
        }
    }
}

/**
 * Lightweight schema validator. Supports the subset of JSON Schema
 * actually used in content-system/meta.schema.json: required, type,
 * enum, pattern, minLength, maxLength, minimum, items.type,
 * additionalProperties (object value types).
 */
function wps_meta_schema_findings(array $meta): array
{
    $findings = [];
    if (!is_file(WPS_META_SCHEMA_PATH)) {
        return $findings;
    }

    $schema = json_decode((string) file_get_contents(WPS_META_SCHEMA_PATH), true);
    if (!is_array($schema)) {
        return [wps_qa_finding('warn', 'schema-load', 'meta.schema.json could not be parsed.')];
    }

    foreach ($schema['required'] ?? [] as $field) {
        if (!array_key_exists($field, $meta) || $meta[$field] === '' || $meta[$field] === null) {
            $findings[] = wps_qa_finding('fail', 'meta-field', "meta.{$field} is required.");
        }
    }

    foreach ($schema['properties'] ?? [] as $field => $rules) {
        if (!array_key_exists($field, $meta) || $meta[$field] === null) {
            continue;
        }
        $value = $meta[$field];

        if (isset($rules['enum']) && !in_array($value, $rules['enum'], true)) {
            $findings[] = wps_qa_finding('fail', 'meta-enum', "meta.{$field} value " . wps_qa_format_value($value) . ' is not in enum.');
        }

        if (isset($rules['pattern']) && is_string($value) && !preg_match('/' . str_replace('/', '\\/', $rules['pattern']) . '/', $value)) {
            $findings[] = wps_qa_finding('fail', 'meta-pattern', "meta.{$field} '{$value}' does not match pattern.");
        }

        if (isset($rules['minLength']) && is_string($value) && mb_strlen($value) < $rules['minLength']) {
            $findings[] = wps_qa_finding('warn', 'meta-min-length', "meta.{$field} is shorter than {$rules['minLength']} characters.");
        }

        if (isset($rules['maxLength']) && is_string($value) && mb_strlen($value) > $rules['maxLength']) {
            $findings[] = wps_qa_finding('warn', 'meta-max-length', "meta.{$field} is longer than {$rules['maxLength']} characters.");
        }

        if (isset($rules['minimum']) && is_numeric($value) && $value < $rules['minimum']) {
            $findings[] = wps_qa_finding('fail', 'meta-minimum', "meta.{$field} is below minimum {$rules['minimum']}.");
        }

        if (isset($rules['type']) && !wps_qa_type_matches($value, $rules['type'])) {
            $findings[] = wps_qa_finding('fail', 'meta-type', "meta.{$field} has wrong type.");
        }
    }

    return $findings;
}

function wps_qa_type_matches($value, $type): bool
{
    $types = is_array($type) ? $type : [$type];

    foreach ($types as $t) {
        switch ($t) {
            case 'string':
                if (is_string($value)) return true;
                break;
            case 'number':
                if (is_int($value) || is_float($value)) return true;
                break;
            case 'integer':
                if (is_int($value)) return true;
                break;
            case 'boolean':
                if (is_bool($value)) return true;
                break;
            case 'array':
                if (is_array($value) && array_keys($value) === range(0, count($value) - 1)) return true;
                if ($value === []) return true;
                break;
            case 'object':
                if (is_array($value) && (count($value) === 0 || array_keys($value) !== range(0, count($value) - 1))) return true;
                break;
            case 'null':
                if ($value === null) return true;
                break;
        }
    }

    return false;
}

function wps_qa_format_value($value): string
{
    if (is_string($value)) return "'{$value}'";
    if (is_bool($value)) return $value ? 'true' : 'false';
    if (is_array($value)) return json_encode($value);
    return (string) $value;
}

function wps_clarifications_findings(array $meta, bool $isPublished): array
{
    $findings = [];
    $clarifications = $meta['clarifications_needed'] ?? null;

    if (!is_array($clarifications) || $clarifications === []) {
        return $findings;
    }

    foreach ($clarifications as $i => $item) {
        if (!is_array($item)) {
            continue;
        }
        $field = (string) ($item['field'] ?? "clarification[{$i}]");
        $blocking = $item['blocking'] ?? true;
        $severity = $blocking ? 'fail' : 'warn';
        $question = (string) ($item['question'] ?? 'unresolved clarification');
        $findings[] = wps_qa_finding($severity, 'clarification-pending', "Pending WPS:CLARIFY for meta.{$field}: {$question}");
    }

    return $findings;
}

/**
 * Stamp last_qa_date and qa_status into meta.json based on a report.
 * Returns the updated meta array, or null when meta.json is missing.
 */
function wps_qa_stamp_meta(string $tourDir, array $report): ?array
{
    $metaPath = $tourDir . '/meta.json';
    if (!is_file($metaPath)) {
        return null;
    }

    $meta = json_decode((string) file_get_contents($metaPath), true);
    if (!is_array($meta)) {
        return null;
    }

    $statusMap = [
        'pass' => 'passing',
        'warning' => 'warning',
        'fail' => 'needs_fix',
    ];

    $resolvedStatus = $statusMap[$report['overall']] ?? 'pending';

    // If freshness was the only thing keeping the report at 'warning',
    // surface that as qa_status='stale' so the dashboard can show a
    // distinct refresh CTA instead of a generic warning.
    if ($report['overall'] === 'warning') {
        $codes = array_column($report['findings'] ?? [], 'code');
        if (in_array('content-stale', $codes, true)) {
            $resolvedStatus = 'stale';
        }
    }

    $meta['qa_status'] = $resolvedStatus;
    $meta['last_qa_date'] = gmdate('Y-m-d');

    file_put_contents(
        $metaPath,
        json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n"
    );

    if (function_exists('wps_archive_index_invalidate')) {
        wps_archive_index_invalidate();
    }

    return $meta;
}
