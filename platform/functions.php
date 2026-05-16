<?php
/**
 * Shared helpers for WebPublisherSystem.
 * Standalone, no WordPress, no password in this phase.
 */

const WPS_DATA_DIR = __DIR__ . '/data';
const WPS_SETTINGS_FILE = WPS_DATA_DIR . '/settings.json';
const WPS_CLUSTER_REGISTRY_FILE = __DIR__ . '/../content-system/clusters/cluster-registry.json';

function wps_ensure_data_dir(): void
{
    if (!is_dir(WPS_DATA_DIR)) {
        mkdir(WPS_DATA_DIR, 0700, true);
    }
    wps_ensure_data_dir_guard();
}

/**
 * Drop a deny-all .htaccess + index stub inside platform/data so even a
 * misconfigured docroot will not expose auth.json / github-imports.json /
 * .secret-key. Safe to call on every request — writes are skipped when the
 * files already exist.
 */
function wps_ensure_data_dir_guard(): void
{
    $htaccess = WPS_DATA_DIR . '/.htaccess';
    if (!is_file($htaccess)) {
        @file_put_contents(
            $htaccess,
            "# WebPublisherSystem: deny all access to this folder.\n"
            . "Require all denied\n"
            . "<IfModule !mod_authz_core.c>\n"
            . "    Order allow,deny\n"
            . "    Deny from all\n"
            . "</IfModule>\n"
        );
        @chmod($htaccess, 0644);
    }

    $indexStub = WPS_DATA_DIR . '/index.html';
    if (!is_file($indexStub)) {
        @file_put_contents($indexStub, '');
        @chmod($indexStub, 0644);
    }

    // Best-effort tighten permissions on sensitive files if they exist.
    foreach (['auth.json', 'github-imports.json', '.secret-key', 'auth-attempts.json'] as $sensitive) {
        $path = WPS_DATA_DIR . '/' . $sensitive;
        if (is_file($path)) {
            @chmod($path, 0600);
        }
    }
}

/**
 * Returns true when the current request is over HTTPS. Honors common
 * reverse-proxy headers in addition to $_SERVER['HTTPS'].
 */
function wps_request_is_https(): bool
{
    if (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') {
        return true;
    }
    if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string) $_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') {
        return true;
    }
    if (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443) {
        return true;
    }
    return false;
}

/**
 * Force-redirect plain HTTP requests to HTTPS when the operator has opted
 * in via settings. When the request is already HTTPS, set HSTS so future
 * loads come back over TLS.
 */
function wps_enforce_https(): void
{
    $settings = wps_load_settings();
    if (empty($settings['force_https'])) {
        return;
    }

    if (!wps_request_is_https()) {
        $host = (string) ($_SERVER['HTTP_HOST'] ?? '');
        $uri  = (string) ($_SERVER['REQUEST_URI'] ?? '/');
        if ($host !== '' && PHP_SAPI !== 'cli') {
            header('Location: https://' . $host . $uri, true, 301);
            exit;
        }
        return;
    }

    if (PHP_SAPI !== 'cli' && !headers_sent()) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

/**
 * Emit baseline security + indexing headers for public blog responses.
 * Safe to call multiple times — bails if headers already sent.
 */
function wps_emit_public_headers(): void
{
    if (PHP_SAPI === 'cli' || headers_sent()) {
        return;
    }
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('X-Frame-Options: SAMEORIGIN');
    header('Permissions-Policy: interest-cohort=()');
    // CSP for the public blog surface. The pages legitimately rely on inline
    // critical CSS, the inline gtag bootstrap, and a stylesheet onload
    // handler, so style/script keep 'unsafe-inline'; everything else is
    // locked down (default-src 'self', restricted base-uri/form-action,
    // frame-ancestors). GA endpoints are explicitly allowlisted.
    header(
        "Content-Security-Policy: default-src 'self'; "
        . "base-uri 'self'; "
        . "form-action 'self'; "
        . "frame-ancestors 'self'; "
        . "object-src 'none'; "
        . "img-src 'self' https: data:; "
        . "font-src 'self'; "
        . "style-src 'self' 'unsafe-inline'; "
        . "script-src 'self' 'unsafe-inline' https://www.googletagmanager.com https://www.google-analytics.com; "
        . "connect-src 'self' https://www.googletagmanager.com https://www.google-analytics.com https://*.google-analytics.com"
    );
}

/**
 * Emit X-Robots-Tag: noindex,nofollow when a post is not in 'published'
 * status. Drafts and review-state copies remain accessible by direct URL
 * (preview behavior) but stay out of Google's index.
 */
function wps_emit_noindex_if_unpublished(string $publishStatus): void
{
    if (PHP_SAPI === 'cli' || headers_sent()) {
        return;
    }
    if ($publishStatus !== 'published') {
        header('X-Robots-Tag: noindex, nofollow, noarchive');
    }
}

/**
 * Resolve the email allowed to sign in as admin. Reads from settings;
 * falls back to the legacy install email so existing deployments do not
 * lose access during the migration.
 */
function wps_admin_email(): string
{
    $settings = wps_load_settings();
    $email = strtolower(trim((string) ($settings['admin_email'] ?? '')));
    if ($email !== '') {
        return $email;
    }
    if (defined('WPS_LEGACY_ADMIN_EMAIL') && WPS_LEGACY_ADMIN_EMAIL !== '') {
        return strtolower(trim((string) WPS_LEGACY_ADMIN_EMAIL));
    }
    // No admin email configured — first-run setup will write one.
    return '';
}

function wps_default_settings(): array
{
    return [
        'site_name' => 'Milano Adventures',
        'archive_title' => 'Travel Guides & Tour Ideas',
        'archive_description' => 'Helpful travel guides, tour ideas, and booking-focused articles from Milano Adventures.',
        'archive_base_url' => 'blog',
        // Absolute URL of the real site homepage, used for the breadcrumb
        // "Home" link and WebSite JSON-LD. Empty falls back to the derived
        // system base, which may point at a subdirectory rather than root.
        'site_home_url' => '',
        'github_owner' => 'rezaeesjd',
        'github_repo' => 'PublisherHub',
        'github_branch' => 'main',
        'github_content_path' => 'WebPublisherSystem/content-system/tours',
        'website_link' => '{{WebsiteLink}}',
        'tripadvisor_link' => '{{TripAdvisorLink}}',
        'viator_link' => '{{ViatorLink}}',
        // Site-wide secondary review/profile links shown in the per-post
        // booking CTA card. Keys are visible labels, values are absolute
        // URLs. Per-post meta.cta_secondary_links override entries by key.
        'cta_secondary_links' => [],
        'admin_email' => '',
        'force_https' => false,
        'archive_page_size' => 20,
        'organization_logo_url' => '',
        'archive_og_image_url' => '',
        'default_author_name' => '',
        'default_author_url' => '',
        'ga4_measurement_id' => '',
        'google_site_verification' => '',
        'bing_site_verification' => '',
        'twitter_handle' => '',
        // Whether clean URL rewrites (/blog/post/<slug>/) are active. Set
        // to false on hosts where .htaccess / mod_rewrite isn't honored
        // (AllowOverride None, plain nginx without a rewrite block, etc.)
        // so link generation falls back to /blog/post.php?slug=<slug>.
        'clean_urls_enabled' => true,
        'updated_at' => gmdate('c'),
    ];
}

function wps_load_settings(): array
{
    wps_ensure_data_dir();

    $settings = wps_default_settings();

    if (!file_exists(WPS_SETTINGS_FILE)) {
        wps_ensure_archive_alias($settings);
        return $settings;
    }

    $json = file_get_contents(WPS_SETTINGS_FILE);
    $data = json_decode($json, true);

    if (!is_array($data)) {
        wps_ensure_archive_alias($settings);
        return $settings;
    }

    $settings = array_merge($settings, $data);
    wps_ensure_archive_alias($settings);

    return $settings;
}

function wps_save_settings(array $settings): bool
{
    wps_ensure_data_dir();
    $settings['updated_at'] = gmdate('c');

    return wps_atomic_write(
        WPS_SETTINGS_FILE,
        json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
    );
}

function wps_default_cluster_registry(): array
{
    return [
        'schema_version' => '1.0',
        'last_updated' => gmdate('Y-m-d'),
        'source_of_truth' => true,
        'description' => 'Machine-readable cluster registry used by both AI agents and the WebPublisherSystem dashboard. Do not maintain separate cluster status sources.',
        'default_required_assets' => [
            ['required' => true, 'cluster_type' => 'BOFU', 'cluster_role' => 'main-booking-post', 'purpose' => 'Main commercial booking-intent asset', 'recommended_linking_priority' => 'link-to-booking'],
            ['required' => true, 'cluster_type' => 'MOFU', 'cluster_role' => 'comparison-post', 'purpose' => 'Compare guided tour vs DIY, transport, or alternatives', 'recommended_linking_priority' => 'link-to-bofu'],
            ['required' => false, 'cluster_type' => 'MOFU', 'cluster_role' => 'comparison-post', 'purpose' => 'Compare this tour with another destination or tour option', 'recommended_linking_priority' => 'link-to-bofu'],
            ['required' => true, 'cluster_type' => 'TOFU', 'cluster_role' => 'destination-guide', 'purpose' => 'Broad discovery guide to attract early-stage travelers', 'recommended_linking_priority' => 'link-to-mofu'],
            ['required' => false, 'cluster_type' => 'TOFU', 'cluster_role' => 'itinerary-guide', 'purpose' => 'Practical itinerary, timing, seasonal, or route guide', 'recommended_linking_priority' => 'link-to-mofu'],
            ['required' => true, 'cluster_type' => 'FAQ', 'cluster_role' => 'faq-support-post', 'purpose' => 'Remove booking doubts and link back to BOFU', 'recommended_linking_priority' => 'link-to-bofu'],
        ],
        'allowed_asset_statuses' => ['not_started', 'planned', 'draft', 'needs_clarification', 'needs_fix', 'ready_for_review', 'published', 'refresh_needed'],
        'clusters' => [],
    ];
}

function wps_load_cluster_registry(): array
{
    if (!is_file(WPS_CLUSTER_REGISTRY_FILE)) {
        return ['ok' => true, 'registry' => wps_default_cluster_registry(), 'error' => ''];
    }

    $json = file_get_contents(WPS_CLUSTER_REGISTRY_FILE);
    $data = json_decode((string) $json, true);

    if (!is_array($data)) {
        return ['ok' => false, 'registry' => wps_default_cluster_registry(), 'error' => 'Cluster registry JSON is invalid.'];
    }

    $data = array_merge(wps_default_cluster_registry(), $data);
    if (!isset($data['clusters']) || !is_array($data['clusters'])) {
        $data['clusters'] = [];
    }

    return ['ok' => true, 'registry' => $data, 'error' => ''];
}

function wps_save_cluster_registry(array $registry): bool
{
    $registry['last_updated'] = gmdate('Y-m-d');
    $registry['source_of_truth'] = true;

    return wps_atomic_write(
        WPS_CLUSTER_REGISTRY_FILE,
        json_encode($registry, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
    );
}

function wps_cluster_asset_status_counts(array $cluster): array
{
    $counts = [];
    foreach (($cluster['assets'] ?? []) as $asset) {
        if (!is_array($asset)) {
            continue;
        }
        $status = (string) ($asset['status'] ?? 'not_started');
        $counts[$status] = ($counts[$status] ?? 0) + 1;
    }
    return $counts;
}

function wps_cluster_completeness(array $cluster): array
{
    $assets = array_values(array_filter(($cluster['assets'] ?? []), 'is_array'));
    $total = count($assets);
    $createdStatuses = ['draft', 'needs_clarification', 'needs_fix', 'ready_for_review', 'published', 'refresh_needed'];
    $created = 0;
    $published = 0;
    $missingRequired = [];

    foreach ($assets as $asset) {
        $status = (string) ($asset['status'] ?? 'not_started');
        if (in_array($status, $createdStatuses, true)) {
            $created++;
        }
        if ($status === 'published') {
            $published++;
        }
        if (!empty($asset['required']) && in_array($status, ['not_started', 'planned'], true)) {
            $missingRequired[] = (string) (($asset['cluster_type'] ?? '') . ' / ' . ($asset['cluster_role'] ?? ''));
        }
    }

    return ['created' => $created, 'published' => $published, 'total' => $total, 'missing_required' => $missingRequired];
}

function wps_cluster_status_label(array $cluster): string
{
    $explicit = trim((string) ($cluster['cluster_status'] ?? ''));
    if ($explicit !== '') {
        return $explicit;
    }

    $score = wps_cluster_completeness($cluster);
    if ($score['total'] === 0 || $score['created'] === 0) {
        return 'not-started';
    }
    if (!empty($score['missing_required'])) {
        return 'early';
    }
    if ($score['created'] >= 6) {
        return 'complete';
    }
    if ($score['created'] >= 4) {
        return 'minimally-complete';
    }
    return 'early';
}

/**
 * Build a lookup of which cluster each tour package belongs to. Returns:
 *   ['by_package_slug' => [slug => ['cluster' => $cluster, 'asset' => $asset]],
 *    'by_parent'       => [parent_slug => $cluster]]
 *
 * The cluster registry is the source of truth — this just indexes it for
 * fast O(1) lookup from the dashboard rows. Tour-side `cluster_parent` in
 * meta.json is treated as a hint; the registry's package_slug membership wins.
 */
function wps_index_tour_clusters(): array
{
    $result = wps_load_cluster_registry();
    $clusters = $result['registry']['clusters'] ?? [];
    $byPackage = [];
    $byParent = [];

    foreach ($clusters as $cluster) {
        if (!is_array($cluster)) {
            continue;
        }
        $parent = (string) ($cluster['cluster_parent'] ?? '');
        if ($parent !== '') {
            $byParent[$parent] = $cluster;
        }
        foreach (($cluster['assets'] ?? []) as $asset) {
            if (!is_array($asset)) {
                continue;
            }
            $slug = trim((string) ($asset['package_slug'] ?? ''));
            if ($slug === '') {
                continue;
            }
            $byPackage[$slug] = ['cluster' => $cluster, 'asset' => $asset];
        }
    }

    return ['by_package_slug' => $byPackage, 'by_parent' => $byParent];
}

/**
 * Source content is a static reference (the cluster's source-facts data),
 * NOT a publishable blog asset. The BOFU "main-booking-post" is a real
 * publishable blog asset on par with MOFU / TOFU / FAQ. Earlier revisions
 * mistakenly equated the cluster's primary_conversion_asset (the BOFU
 * package) with source content and suppressed it from the public archive;
 * see structures/cluster-metadata-standard.md for the corrected rule.
 *
 * Kept as a no-op so callers don't have to change shape, but no package
 * slug ever qualifies: source content lives inside the BOFU package as
 * source-facts.md and is never a public URL.
 */
function wps_is_source_content_package(string $baseSlug): bool
{
    unset($baseSlug);
    return false;
}

/**
 * True when a slug looks like a retired "-vN" variant clone (e.g.
 * "bernina-...-from-milan-v2"). The -vN variant mechanism was retired:
 * re-running generation now creates a distinct, typed cluster blog asset
 * with its own keyword-meaningful slug, never a numbered clone. Any
 * package still matching this pattern is a stale clone that must be
 * deleted, not linked into a cluster.
 */
function wps_is_retired_variant_slug(string $slug): bool
{
    return preg_match('/-v[0-9]+$/', trim($slug)) === 1;
}

/**
 * Write file contents atomically with an exclusive lock. Writes to a
 * temp sibling first, fsyncs, then renames into place. Prevents
 * concurrent writers from corrupting the JSON store.
 */
function wps_atomic_write(string $path, string $contents): bool
{
    $dir = dirname($path);
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        return false;
    }

    $tmp = $dir . '/.' . basename($path) . '.' . bin2hex(random_bytes(6)) . '.tmp';
    $handle = @fopen($tmp, 'wb');
    if ($handle === false) {
        return false;
    }

    if (!flock($handle, LOCK_EX)) {
        fclose($handle);
        @unlink($tmp);
        return false;
    }

    $written = fwrite($handle, $contents);
    fflush($handle);
    flock($handle, LOCK_UN);
    fclose($handle);

    if ($written === false || $written !== strlen($contents)) {
        @unlink($tmp);
        return false;
    }

    if (!@rename($tmp, $path)) {
        @unlink($tmp);
        return false;
    }

    @chmod($path, 0644);
    return true;
}


function wps_str_contains(string $haystack, string $needle): bool
{
    return $needle === '' || strpos($haystack, $needle) !== false;
}

function wps_str_starts_with(string $haystack, string $needle): bool
{
    return $needle === '' || strncmp($haystack, $needle, strlen($needle)) === 0;
}

function wps_h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function wps_url_origin(): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    return $scheme . '://' . $host;
}

function wps_current_url_base(): string
{
    $scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    return wps_url_origin() . ($scriptDir ? $scriptDir : '');
}

function wps_system_url_base(): string
{
    $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $origin = wps_url_origin();

    $marker = '/WebPublisherSystem/';
    $pos = strpos($scriptName, $marker);
    if ($pos !== false) {
        return $origin . substr($scriptName, 0, $pos + strlen('/WebPublisherSystem'));
    }

    $dir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
    $segments = array_values(array_filter(explode('/', trim($dir, '/')), fn($part) => $part !== ''));
    $last = end($segments);
    if (in_array($last, ['platform', 'blog', 'blogs'], true)) {
        array_pop($segments);
    }

    return $origin . ($segments ? '/' . implode('/', $segments) : '');
}

/**
 * Absolute URL of the real site homepage, used for the breadcrumb "Home"
 * crumb and WebSite JSON-LD. Prefers the operator-configured site_home_url;
 * falls back to the derived system base (which may be a subdirectory).
 */
function wps_site_home_url(): string
{
    $settings = wps_load_settings();
    $configured = trim((string) ($settings['site_home_url'] ?? ''));
    if ($configured !== '' && preg_match('#^https?://#i', $configured)) {
        return rtrim($configured, '/') . '/';
    }
    return rtrim(wps_system_url_base(), '/') . '/';
}

function wps_asset_url(string $path): string
{
    $base = defined('WPS_ASSET_BASE') ? trim((string) WPS_ASSET_BASE) : '';
    $cleanPath = ltrim($path, '/');
    $assetUrl = $base === '' ? $cleanPath : rtrim($base, '/') . '/' . $cleanPath;

    $localPath = __DIR__ . '/' . $cleanPath;
    $version = is_file($localPath) ? (string) filemtime($localPath) : (string) time();
    $separator = wps_str_contains($assetUrl, '?') ? '&' : '?';

    return $assetUrl . $separator . 'v=' . rawurlencode($version);
}

function wps_sanitize_archive_slug(string $slug): string
{
    $parts = array_filter(explode('/', trim($slug, '/')), fn($part) => $part !== '');
    $safe = [];

    foreach ($parts as $part) {
        if ($part === '.' || $part === '..') {
            return '';
        }

        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $part)) {
            return '';
        }

        $safe[] = $part;
    }

    return implode('/', $safe);
}

function wps_archive_slug_from_setting(array $settings): string
{
    $value = trim((string) ($settings['archive_base_url'] ?? ''));
    if ($value === '') {
        return 'blog';
    }

    if (preg_match('#^https?://#i', $value)) {
        $path = (string) parse_url($value, PHP_URL_PATH);
    } else {
        $path = $value;
    }

    $path = trim($path, '/');
    $parts = array_values(array_filter(explode('/', $path), fn($part) => $part !== ''));
    $systemIndex = array_search('WebPublisherSystem', $parts, true);
    if ($systemIndex !== false) {
        $parts = array_slice($parts, $systemIndex + 1);
        $path = implode('/', $parts);
    }

    $slug = wps_sanitize_archive_slug($path);
    return $slug !== '' ? $slug : 'blog';
}

function wps_archive_url(): string
{
    if (defined('WPS_ARCHIVE_URL')) {
        return WPS_ARCHIVE_URL;
    }

    $settings = wps_load_settings();
    $slug = wps_archive_slug_from_setting($settings);

    return rtrim(wps_system_url_base(), '/') . '/' . trim($slug, '/') . '/';
}

function wps_ensure_archive_alias(array $settings): void
{
    $slug = wps_sanitize_archive_slug(wps_archive_slug_from_setting($settings));
    $root = realpath(__DIR__ . '/..');
    if ($root === false || $slug === '' || $slug === 'blog') {
        return;
    }

    $aliasDir = $root . '/' . $slug;
    if (!is_dir($aliasDir) && !mkdir($aliasDir, 0755, true) && !is_dir($aliasDir)) {
        return;
    }

    $realAliasDir = realpath($aliasDir);
    $rootPrefix = rtrim(str_replace('\\', '/', $root), '/') . '/';
    $aliasPrefix = $realAliasDir ? rtrim(str_replace('\\', '/', $realAliasDir), '/') . '/' : '';
    if ($aliasPrefix === '' || !str_starts_with($aliasPrefix, $rootPrefix)) {
        return;
    }

    $depth = count(array_filter(explode('/', trim($slug, '/')), fn($part) => $part !== ''));
    $up = str_repeat('../', max(1, $depth));

    file_put_contents($aliasDir . '/.wps-archive-alias', "managed-by=WebPublisherSystem\n");
    // Mirror every public blog entry point at the alias so robots.txt,
    // llms.txt, the XML sitemap and the RSS feed resolve under the custom
    // archive slug just as they do under /blog/.
    foreach (['index.php', 'post.php', 'sitemap.xml.php', 'robots.txt.php', 'llms.txt.php', 'feed.xml.php'] as $stub) {
        file_put_contents($aliasDir . '/' . $stub, "<?php\nrequire_once __DIR__ . '/" . $up . "blog/" . $stub . "';\n");
    }

    // Mirror the blog rewrite behavior so clean URLs like
    // /<archive-slug>/post/<public-slug>/ resolve for custom archive aliases.
    $aliasHtaccess = <<<HTACCESS
<IfModule mod_rewrite.c>
    RewriteEngine On

    RewriteRule ^robots\.txt$ robots.txt.php [L]
    RewriteRule ^llms\.txt$ llms.txt.php [L]
    RewriteRule ^sitemap\.xml$ sitemap.xml.php [L]
    RewriteRule ^feed\.xml$ feed.xml.php [L]
    RewriteRule ^post/([A-Za-z0-9_-]+)/?$ post.php?slug=$1 [QSA,L]
    RewriteRule ^page/([0-9]+)/?$ index.php?page=$1 [QSA,L]
</IfModule>
HTACCESS;
    file_put_contents($aliasDir . '/.htaccess', $aliasHtaccess . "\n");
}

function wps_redirect_legacy_blog_path_if_needed(array $settings): void
{
    $slug = wps_sanitize_archive_slug(wps_archive_slug_from_setting($settings));
    if ($slug === '' || $slug === 'blog') {
        return;
    }

    $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $scriptDir = trim(str_replace('\\', '/', dirname($scriptName)), '/');
    if ($scriptDir === '' || $scriptDir === '.') {
        return;
    }

    $scriptSegments = explode('/', $scriptDir);
    $currentArchivePath = end($scriptSegments);
    if ($currentArchivePath === basename($slug)) {
        return;
    }

    $targetBase = wps_archive_url();
    $isPost = basename($scriptName) === 'post.php';
    if ($isPost) {
        $slugParam = isset($_GET['slug']) ? (string) $_GET['slug'] : '';
        $target = rtrim($targetBase, '/') . '/post.php' . ($slugParam !== '' ? '?slug=' . rawurlencode($slugParam) : '');
        header('Location: ' . $target, true, 301);
        exit;
    }

    header('Location: ' . $targetBase, true, 301);
    exit;
}

function wps_settings_url(): string
{
    return defined('WPS_SETTINGS_URL') ? WPS_SETTINGS_URL : 'settings.php';
}

function wps_current_nav_item(): string
{
    $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));

    if (wps_str_contains($scriptName, '/platform/index.php')) {
        return 'dashboard';
    }

    if (wps_str_contains($scriptName, '/platform/')) {
        return 'settings';
    }

    return 'archive';
}

function wps_render_header(string $title): void
{
    $settings = wps_load_settings();
    $currentNavItem = wps_current_nav_item();
    $archiveIsActive = $currentNavItem === 'archive';
    $dashboardIsActive = $currentNavItem === 'dashboard';
    $settingsIsActive = $currentNavItem === 'settings';
    $isAdminContext = in_array($currentNavItem, ['settings', 'dashboard'], true);
    $signedIn = function_exists('wps_is_logged_in') && wps_is_logged_in();
    $logoutUrl = defined('WPS_ASSET_BASE') && WPS_ASSET_BASE === '.' ? 'logout.php' : '../platform/logout.php';
    ?>
    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo wps_h($title); ?> | <?php echo wps_h($settings['site_name']); ?></title>
        <link rel="stylesheet" href="<?php echo wps_h(wps_asset_url('assets/style.css')); ?>">
    </head>
    <body>
    <header class="site-header">
        <div class="container header-inner">
            <a class="brand" href="<?php echo wps_h(wps_archive_url()); ?>"><?php echo wps_h($settings['site_name']); ?></a>
            <nav>
                <a class="<?php echo $archiveIsActive ? 'active' : ''; ?>" href="<?php echo wps_h(wps_archive_url()); ?>" <?php echo $archiveIsActive ? 'aria-current="page"' : ''; ?>>Archive</a>
                <?php if ($isAdminContext || $signedIn): ?>
                    <a class="<?php echo $dashboardIsActive ? 'active' : ''; ?>" href="<?php echo wps_h(wps_asset_url('index.php')); ?>" <?php echo $dashboardIsActive ? 'aria-current="page"' : ''; ?>>Dashboard</a>
                    <a class="<?php echo $settingsIsActive ? 'active' : ''; ?>" href="<?php echo wps_h(wps_settings_url()); ?>" <?php echo $settingsIsActive ? 'aria-current="page"' : ''; ?>>Settings</a>
                <?php endif; ?>
                <?php if ($signedIn): ?>
                    <a class="nav-signout" href="<?php echo wps_h($logoutUrl); ?>">Sign out</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    <main class="container">
    <?php
}

/**
 * Render the public URL for a post. Emits the canonical
 * /blog/post/<slug>/ form when clean URLs are active (the default — the
 * shipped .htaccess provides the rewrite), and falls back to the
 * /blog/post.php?slug=<slug> form when settings.clean_urls_enabled is
 * false (i.e. the host doesn't honor .htaccess / mod_rewrite is off).
 */
function wps_public_post_url(string $publicSlug): string
{
    $archiveUrl = rtrim(wps_archive_url(), '/') . '/';
    $publicSlug = trim($publicSlug);
    if ($publicSlug === '') {
        return $archiveUrl;
    }
    $settings = wps_load_settings();
    $clean = !array_key_exists('clean_urls_enabled', $settings) || !empty($settings['clean_urls_enabled']);
    return $clean
        ? $archiveUrl . 'post/' . rawurlencode($publicSlug)
        : $archiveUrl . 'post.php?slug=' . rawurlencode($publicSlug);
}

/**
 * Render an archive-pagination URL. Page 1 is always the bare archive URL
 * (canonical). Higher pages use /page/<n>/ under clean URLs and ?page=<n>
 * otherwise. See wps_public_post_url() for the fallback rationale.
 */
function wps_archive_page_url(int $page): string
{
    $archiveUrl = rtrim(wps_archive_url(), '/') . '/';
    if ($page <= 1) {
        return $archiveUrl;
    }
    $settings = wps_load_settings();
    $clean = !array_key_exists('clean_urls_enabled', $settings) || !empty($settings['clean_urls_enabled']);
    return $clean
        ? $archiveUrl . 'page/' . $page
        : $archiveUrl . '?page=' . $page;
}

/**
 * Resolve a meta.hero_image value to a public URL.
 *  - http(s) URLs pass through unchanged.
 *  - Relative paths (e.g. "images/hero.jpg") resolve against the tour
 *    folder name under the content-system tours path.
 * Returns '' when the value is empty or cannot be resolved on disk.
 */
function wps_resolve_hero_image_url(string $heroValue, string $tourFolderName): string
{
    $heroValue = trim($heroValue);
    if ($heroValue === '') {
        return '';
    }
    if (preg_match('#^https?://#i', $heroValue)) {
        return $heroValue;
    }
    if ($tourFolderName === '' || !preg_match('/^[a-zA-Z0-9_-]+$/', $tourFolderName)) {
        return '';
    }
    $rel = ltrim($heroValue, '/');
    $diskPath = __DIR__ . '/../content-system/tours/' . $tourFolderName . '/' . $rel;
    if (!is_file($diskPath)) {
        return '';
    }
    $base = rtrim(wps_system_url_base(), '/') . '/';
    return $base . 'content-system/tours/' . rawurlencode($tourFolderName) . '/' . str_replace('%2F', '/', rawurlencode($rel));
}

/**
 * Resolve a meta.hero_image to its public URL plus measured pixel
 * dimensions. Dimensions are filled only when the image resolves to a
 * local file we can read with getimagesize(); remote URLs return null
 * width/height so callers omit the og:image:width/height meta tags rather
 * than emit guessed values.
 *
 * @return array{url:string,width:?int,height:?int}
 */
function wps_resolve_hero_image(string $heroValue, string $tourFolderName): array
{
    $url = wps_resolve_hero_image_url($heroValue, $tourFolderName);
    $out = ['url' => $url, 'width' => null, 'height' => null];
    if ($url === '') {
        return $out;
    }
    $heroValue = trim($heroValue);
    if ($heroValue === '' || preg_match('#^https?://#i', $heroValue)) {
        return $out;
    }
    if ($tourFolderName === '' || !preg_match('/^[a-zA-Z0-9_-]+$/', $tourFolderName)) {
        return $out;
    }
    $rel = ltrim($heroValue, '/');
    $diskPath = __DIR__ . '/../content-system/tours/' . $tourFolderName . '/' . $rel;
    $size = @getimagesize($diskPath);
    if (is_array($size) && !empty($size[0]) && !empty($size[1])) {
        $out['width'] = (int) $size[0];
        $out['height'] = (int) $size[1];
    }
    return $out;
}

/**
 * Guarantee a rendered post body has exactly one <h1> for a clean document
 * outline. With no <h1>, a fallback built from the post title is prepended.
 * With several, the extras are demoted to <h2> so a single top-level
 * heading remains.
 */
function wps_enforce_single_h1(string $html, string $fallbackTitle): string
{
    $count = (int) preg_match_all('/<h1\b/i', $html);
    if ($count === 0) {
        $fallbackTitle = trim($fallbackTitle);
        return $fallbackTitle === ''
            ? $html
            : '<h1>' . wps_h($fallbackTitle) . "</h1>\n" . $html;
    }
    if ($count === 1) {
        return $html;
    }
    $openSeen = 0;
    return preg_replace_callback('/<(\/?)h1(\b[^>]*)>/i', function ($m) use (&$openSeen) {
        $isClosing = $m[1] === '/';
        if (!$isClosing) {
            $openSeen++;
        }
        // Keep the first <h1>...</h1> pair; demote every later one to <h2>.
        if ($openSeen <= 1) {
            return $m[0];
        }
        return '<' . $m[1] . 'h2' . $m[2] . '>';
    }, $html) ?? $html;
}

/**
 * Compute reading time (whole minutes, min 1) and word count from HTML.
 * Strips tags first so navigation and inline code don't inflate the count.
 */
function wps_reading_time(string $html, int $wordsPerMinute = 220): array
{
    $words = str_word_count(strip_tags($html));
    $minutes = max(1, (int) ceil($words / max(1, $wordsPerMinute)));
    return ['words' => $words, 'minutes' => $minutes];
}

/**
 * Trim and clamp a description to ~160 chars on a word boundary.
 */
function wps_trim_description(string $text, int $max = 158): string
{
    $text = trim(preg_replace('/\s+/', ' ', $text) ?? '');
    if ($text === '' || mb_strlen($text) <= $max) {
        return $text;
    }
    $cut = mb_substr($text, 0, $max);
    $space = mb_strrpos($cut, ' ');
    if ($space !== false && $space > $max - 30) {
        $cut = mb_substr($cut, 0, $space);
    }
    return rtrim($cut, " ,.;:") . '…';
}

function wps_human_date(string $date): string
{
    $date = trim($date);
    if ($date === '') {
        return '';
    }
    $ts = strtotime($date);
    if ($ts === false) {
        return $date;
    }
    return gmdate('F j, Y', $ts);
}

function wps_human_publish_status(string $status): string
{
    return match ($status) {
        'ready_for_review' => 'Needs review',
        'published' => 'Published',
        default => ucwords(str_replace('_', ' ', $status ?: 'preview')),
    };
}

/**
 * Best-effort word count of the rendered body, used as a fallback when
 * the meta.description is missing.
 */
function wps_auto_meta_description(string $html, int $max = 158): string
{
    if ($html === '') {
        return '';
    }
    // Drop nav links, FAQ headings, etc. by keeping only paragraph text.
    if (preg_match_all('#<p[^>]*>(.+?)</p>#is', $html, $matches) && !empty($matches[1])) {
        $text = strip_tags(implode(' ', $matches[1]));
    } else {
        $text = strip_tags($html);
    }
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return wps_trim_description($text, $max);
}

/**
 * Append UTM parameters to known booking-channel destination URLs.
 * Leaves placeholders and non-http URLs alone. Idempotent: skips URLs
 * that already include utm_source.
 */
function wps_append_utm(string $url, string $campaign, string $source = 'blog', string $medium = 'cta'): string
{
    if ($url === '' || !preg_match('#^https?://#i', $url)) {
        return $url;
    }
    if (stripos($url, 'utm_source=') !== false) {
        return $url;
    }
    $campaign = $campaign === '' ? 'wps' : preg_replace('/[^A-Za-z0-9_-]+/', '-', $campaign);
    $separator = (strpos($url, '?') === false) ? '?' : '&';
    return $url
        . $separator
        . 'utm_source=' . rawurlencode($source)
        . '&utm_medium=' . rawurlencode($medium)
        . '&utm_campaign=' . rawurlencode($campaign);
}

/**
 * Return a list of unique host names that should be preconnected for
 * a given rendered HTML blob. Used by post.php to emit <link rel="preconnect">
 * hints for booking CTAs (Viator, TripAdvisor, GetYourGuide, etc.).
 */
function wps_preconnect_hosts_from_html(string $html): array
{
    if ($html === '' || !preg_match_all('#href="https?://([^/"]+)/?[^"]*"#i', $html, $matches)) {
        return [];
    }
    $allow = ['viator.com', 'www.viator.com', 'tripadvisor.com', 'www.tripadvisor.com', 'getyourguide.com', 'www.getyourguide.com', 'booking.com', 'www.booking.com'];
    $out = [];
    foreach ($matches[1] as $host) {
        $host = strtolower($host);
        foreach ($allow as $candidate) {
            if ($host === $candidate || str_ends_with($host, '.' . $candidate)) {
                $out[$host] = true;
                break;
            }
        }
    }
    return array_keys($out);
}

/**
 * Render <link rel="preconnect"> tags for analytics + booking CTAs.
 * Always emits googletagmanager + google-analytics when GA4 is enabled.
 */
function wps_render_preconnect(array $settings, string $html = ''): string
{
    $hosts = [];
    if (!empty($settings['ga4_measurement_id'])) {
        $hosts['www.googletagmanager.com'] = true;
        $hosts['www.google-analytics.com'] = true;
    }
    foreach (wps_preconnect_hosts_from_html($html) as $host) {
        $hosts[$host] = true;
    }
    if (empty($hosts)) {
        return '';
    }
    $out = '';
    foreach (array_keys($hosts) as $host) {
        $safe = wps_h($host);
        $out .= '<link rel="preconnect" href="https://' . $safe . '" crossorigin>'
              . '<link rel="dns-prefetch" href="https://' . $safe . '">';
    }
    return $out;
}

/**
 * GA4 + Google/Bing Search Console verification tags. Output is empty
 * when no GA4 id and no verification tokens are configured.
 *
 * @param string $where 'head' for verification meta tags, 'body' for the
 *                       deferred gtag.js loader.
 */
function wps_render_analytics(array $settings, string $where = 'body'): string
{
    if ($where === 'head') {
        $out = '';
        $g = trim((string) ($settings['google_site_verification'] ?? ''));
        if ($g !== '') {
            $out .= '<meta name="google-site-verification" content="' . wps_h($g) . '">';
        }
        $b = trim((string) ($settings['bing_site_verification'] ?? ''));
        if ($b !== '') {
            $out .= '<meta name="msvalidate.01" content="' . wps_h($b) . '">';
        }
        return $out;
    }

    $id = trim((string) ($settings['ga4_measurement_id'] ?? ''));
    if ($id === '' || !preg_match('/^G-[A-Z0-9]{4,}$/i', $id)) {
        return '';
    }
    $safeId = wps_h($id);
    return <<<HTML
<script async src="https://www.googletagmanager.com/gtag/js?id={$safeId}"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','{$safeId}',{'anonymize_ip':true});</script>
HTML;
}

/**
 * Critical CSS for above-the-fold paint. Tiny subset that mirrors theme.css
 * for the article + archive header. Loaded inline; the full stylesheet is
 * fetched non-blocking via media-swap trick.
 */
function wps_critical_css(): string
{
    return '*,*::before,*::after{box-sizing:border-box}'
         . 'html{-webkit-font-smoothing:antialiased}'
         . 'body{margin:0;font-family:system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;background:#f2f5f9;color:#0e1c2e;line-height:1.65;font-size:16px}'
         . 'a{color:#1a6bc4;text-decoration:none}'
         . '.wrap{max-width:880px;padding:32px 16px;margin:0 auto}'
         . '.card{background:#fff;border:1px solid #d5dde8;border-radius:12px;box-shadow:0 1px 3px rgba(14,28,46,.07)}'
         . 'h1{font-size:30px;line-height:1.2;letter-spacing:-.02em;font-weight:800;margin-top:0}'
         . 'h2{font-size:22px;font-weight:700;letter-spacing:-.01em}'
         . '.muted{color:#536070}'
         . 'img{max-width:100%;height:auto}'
         . '.skip-link{position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden}'
         . '.skip-link:focus{position:fixed;left:8px;top:8px;width:auto;height:auto;padding:8px 12px;background:#1a6bc4;color:#fff;border-radius:6px;z-index:9999}';
}

/**
 * Build a markup string that loads a stylesheet without blocking the
 * first paint. Falls back to a synchronous <link> inside <noscript> so
 * non-JS clients still get the design.
 */
function wps_render_deferred_stylesheet(string $href): string
{
    $safe = wps_h($href);
    return '<link rel="preload" href="' . $safe . '" as="style">'
         . '<link rel="stylesheet" href="' . $safe . '" media="print" onload="this.media=\'all\';this.onload=null">'
         . '<noscript><link rel="stylesheet" href="' . $safe . '"></noscript>';
}

/**
 * Pick up to $limit related posts for the given post record. Scores by
 * shared primary_keyword (3 pts), shared funnel_stage (1 pt), and shared
 * cluster_parent (2 pts). Filters out the current post.
 */
function wps_related_posts(array $current, array $allRecords, int $limit = 4): array
{
    $currentSlug = (string) ($current['public_slug'] ?? $current['slug'] ?? '');
    $currentKw   = strtolower(trim((string) ($current['primary_keyword'] ?? '')));
    $currentFun  = (string) ($current['funnel_stage'] ?? '');
    $currentParent = (string) ($current['cluster_parent'] ?? $current['variant_of'] ?? '');

    $scored = [];
    foreach ($allRecords as $rec) {
        $slug = (string) ($rec['public_slug'] ?? '');
        if ($slug === '' || $slug === $currentSlug) {
            continue;
        }
        $score = 0;
        $kw = strtolower(trim((string) ($rec['primary_keyword'] ?? '')));
        if ($kw !== '' && $currentKw !== '' && $kw === $currentKw) {
            $score += 3;
        } elseif ($kw !== '' && $currentKw !== '' && (str_contains($kw, $currentKw) || str_contains($currentKw, $kw))) {
            $score += 1;
        }
        if ($currentFun !== '' && ($rec['funnel_stage'] ?? '') === $currentFun) {
            $score += 1;
        }
        $parent = (string) ($rec['cluster_parent'] ?? '');
        if ($currentParent !== '' && $parent !== '' && $parent === $currentParent) {
            $score += 2;
        }
        if ($score > 0) {
            $scored[] = ['score' => $score, 'record' => $rec];
        }
    }

    usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);
    return array_slice(array_map(fn($s) => $s['record'], $scored), 0, $limit);
}

/**
 * Find the cluster registry entry that owns this tour package, if any.
 * Returns the cluster array (with full assets list) or null when the
 * package is not registered. Used to inherit booking links and surface
 * sibling assets in the per-post UI.
 */
function wps_cluster_for_post(array $post): ?array
{
    $baseSlug = trim((string) ($post['base_slug'] ?? $post['slug'] ?? ''));
    if ($baseSlug === '') {
        return null;
    }
    $index = wps_index_tour_clusters();
    $entry = $index['by_package_slug'][$baseSlug] ?? null;
    if (!is_array($entry) || !is_array($entry['cluster'] ?? null)) {
        return null;
    }
    return $entry['cluster'];
}

/**
 * Resolve a single booking link with a per-post -> cluster -> site fallback
 * chain. Placeholders ({{...}}) and non-http values are treated as "no link"
 * so we never render dummy CTAs. Real URLs are tagged with the UTM medium
 * passed in.
 *
 * @internal exposed for wps_resolve_post_booking_links().
 */
function wps_pick_booking_link(array $sources, string $campaign, string $medium): array
{
    foreach ($sources as $source => $candidate) {
        $url = trim((string) $candidate);
        if ($url === '' || strpos($url, '{{') !== false) {
            continue;
        }
        if (!preg_match('#^https?://#i', $url)) {
            continue;
        }
        if (function_exists('wps_append_utm') && $campaign !== '') {
            $url = wps_append_utm($url, $campaign, 'blog', $medium);
        }
        return ['url' => $url, 'source' => (string) $source];
    }
    return ['url' => '', 'source' => ''];
}

/**
 * Resolve every external review/booking destination available for a blog
 * post. Resolution order for each channel is: per-post meta -> cluster
 * registry -> site settings. Secondary links are merged across site
 * defaults and per-post overrides keyed by case-insensitive label.
 *
 * @return array{
 *   viator: array{url:string,source:string},
 *   tripadvisor: array{url:string,source:string},
 *   website: array{url:string,source:string},
 *   secondary: list<array{label:string,url:string,source:string}>,
 *   cluster: array<string,mixed>
 * }
 */
function wps_resolve_post_booking_links(array $post, array $settings, string $utmCampaign): array
{
    $meta    = is_array($post['meta'] ?? null) ? $post['meta'] : [];
    $cluster = wps_cluster_for_post($post) ?? [];

    $viator = wps_pick_booking_link([
        'post'    => (string) ($meta['viator_link'] ?? ''),
        'cluster' => (string) ($cluster['viator_url'] ?? ''),
        'site'    => (string) ($settings['viator_link'] ?? ''),
    ], $utmCampaign, 'cta-viator');

    $tripadvisor = wps_pick_booking_link([
        'post'    => (string) ($meta['tripadvisor_link'] ?? ''),
        'cluster' => (string) ($cluster['tripadvisor_url'] ?? ''),
        'site'    => (string) ($settings['tripadvisor_link'] ?? ''),
    ], $utmCampaign, 'cta-tripadvisor');

    $website = wps_pick_booking_link([
        'post'    => (string) ($meta['website_link'] ?? ''),
        'cluster' => (string) ($cluster['website_url'] ?? ''),
        'site'    => (string) ($settings['website_link'] ?? ''),
    ], $utmCampaign, 'cta-website');

    $secondaryByKey = [];
    $merge = static function ($links, string $source) use (&$secondaryByKey): void {
        if (!is_array($links)) {
            return;
        }
        foreach ($links as $label => $url) {
            $url = trim((string) $url);
            $label = trim((string) $label);
            if ($label === '' || $url === '' || strpos($url, '{{') !== false) {
                continue;
            }
            if (!preg_match('#^https?://#i', $url)) {
                continue;
            }
            $secondaryByKey[strtolower($label)] = ['label' => $label, 'url' => $url, 'source' => $source];
        }
    };
    $merge($settings['cta_secondary_links'] ?? null, 'site');
    $merge($meta['cta_secondary_links'] ?? null, 'post');

    $secondary = [];
    foreach ($secondaryByKey as $entry) {
        if (function_exists('wps_append_utm') && $utmCampaign !== '') {
            $entry['url'] = wps_append_utm($entry['url'], $utmCampaign, 'blog', 'cta-secondary');
        }
        $secondary[] = $entry;
    }

    return [
        'viator'      => $viator,
        'tripadvisor' => $tripadvisor,
        'website'     => $website,
        'secondary'   => $secondary,
        'cluster'     => $cluster,
    ];
}

/**
 * Filter archive records down to cluster siblings (other package_slugs
 * registered in the same cluster registry entry as the current post).
 * Returns at most $limit records, ordered as they appear in the archive
 * index (newest first).
 */
function wps_cluster_sibling_records(array $post, array $allRecords, int $limit = 4): array
{
    $cluster = wps_cluster_for_post($post);
    if (!$cluster) {
        return [];
    }
    $baseSlug = (string) ($post['base_slug'] ?? $post['slug'] ?? '');
    $siblings = [];
    foreach (($cluster['assets'] ?? []) as $asset) {
        if (!is_array($asset)) {
            continue;
        }
        $slug = trim((string) ($asset['package_slug'] ?? ''));
        if ($slug === '' || $slug === $baseSlug) {
            continue;
        }
        $siblings[$slug] = true;
    }
    if (!$siblings) {
        return [];
    }
    $out = [];
    foreach ($allRecords as $rec) {
        if (!is_array($rec)) {
            continue;
        }
        $recBase = (string) ($rec['base_slug'] ?? '');
        if ($recBase !== '' && isset($siblings[$recBase])) {
            $out[] = $rec;
            if (count($out) >= $limit) {
                break;
            }
        }
    }
    return $out;
}

function wps_render_footer(): void
{
    $settings = wps_load_settings();
    $year = date('Y');
    ?>
    </main>
    <footer class="site-footer">
        <div class="container">
            <p><?php echo wps_h($settings['site_name']); ?> &middot; PublisherHub &middot; <?php echo wps_h($year); ?></p>
        </div>
    </footer>
    </body>
    </html>
    <?php
}
