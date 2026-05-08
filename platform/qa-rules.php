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
    $isPublished = in_array($publishStatus, ['published', 'ready_for_sync', 'needs_live_verification'], true);

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

        $h1Count = preg_match_all('/^#\s+/m', $blogContent);
        if ($h1Count === 0) {
            $findings[] = wps_qa_finding('fail', 'h1-missing', 'blog-post.md has no top-level H1.');
        } elseif ($h1Count > 1) {
            $findings[] = wps_qa_finding('warn', 'h1-multiple', "blog-post.md has {$h1Count} H1 lines; expected exactly one.");
        }

        $brandName = (string) ($meta['brand'] ?? '');
        if ($brandName !== '' && stripos($blogContent, $brandName) === false) {
            $findings[] = wps_qa_finding('warn', 'brand-missing', "blog-post.md does not mention the active brand '{$brandName}'.");
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

    return $reports;
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
        $severity = ($isPublished || $blocking) ? 'fail' : 'warn';
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

    $meta['qa_status'] = $statusMap[$report['overall']] ?? 'pending';
    $meta['last_qa_date'] = gmdate('Y-m-d');

    file_put_contents(
        $metaPath,
        json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n"
    );

    return $meta;
}
