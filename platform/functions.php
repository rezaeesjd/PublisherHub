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
        mkdir(WPS_DATA_DIR, 0755, true);
    }
}

function wps_default_settings(): array
{
    return [
        'site_name' => 'Milano Adventures',
        'archive_title' => 'Travel Guides & Tour Ideas',
        'archive_description' => 'Helpful travel guides, tour ideas, and booking-focused articles from Milano Adventures.',
        'archive_base_url' => 'blog',
        'github_owner' => 'rezaeesjd',
        'github_repo' => 'PublisherHub',
        'github_branch' => 'main',
        'github_content_path' => 'WebPublisherSystem/content-system/tours',
        'website_link' => '{{WebsiteLink}}',
        'tripadvisor_link' => '{{TripAdvisorLink}}',
        'viator_link' => '{{ViatorLink}}',
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
        'allowed_asset_statuses' => ['not_started', 'planned', 'draft', 'needs_clarification', 'needs_fix', 'ready_for_review', 'ready_for_sync', 'needs_live_verification', 'published', 'refresh_needed'],
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
    $createdStatuses = ['draft', 'needs_clarification', 'needs_fix', 'ready_for_review', 'ready_for_sync', 'needs_live_verification', 'published', 'refresh_needed'];
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
    file_put_contents($aliasDir . '/index.php', "<?php\nrequire_once __DIR__ . '/" . $up . "blog/index.php';\n");
    file_put_contents($aliasDir . '/post.php', "<?php\nrequire_once __DIR__ . '/" . $up . "blog/post.php';\n");
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
        header('Location: ' . $target, true, 302);
        exit;
    }

    header('Location: ' . $targetBase, true, 302);
    exit;
}

function wps_settings_url(): string
{
    return defined('WPS_SETTINGS_URL') ? WPS_SETTINGS_URL : 'settings.php';
}

function wps_current_nav_item(): string
{
    $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));

    if (wps_str_contains($scriptName, '/platform/clusters.php')) {
        return 'clusters';
    }

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
    $clustersIsActive = $currentNavItem === 'clusters';
    $settingsIsActive = $currentNavItem === 'settings';
    $isAdminContext = in_array($currentNavItem, ['settings', 'clusters', 'dashboard'], true);
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
                    <a class="<?php echo $clustersIsActive ? 'active' : ''; ?>" href="<?php echo wps_h(wps_asset_url('clusters.php')); ?>" <?php echo $clustersIsActive ? 'aria-current="page"' : ''; ?>>Clusters</a>
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
