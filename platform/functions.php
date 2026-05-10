<?php
/**
 * Shared helpers for WebPublisherSystem.
 * Standalone, no WordPress, no password in this phase.
 */

const WPS_DATA_DIR = __DIR__ . '/data';
const WPS_SETTINGS_FILE = WPS_DATA_DIR . '/settings.json';

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

    // Preferred when the package is uploaded as /WebPublisherSystem/.
    $marker = '/WebPublisherSystem/';
    $pos = strpos($scriptName, $marker);
    if ($pos !== false) {
        return $origin . substr($scriptName, 0, $pos + strlen('/WebPublisherSystem'));
    }

    // Fallback for renamed installs: remove the current app subfolder from URL path.
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
    $separator = str_contains($assetUrl, '?') ? '&' : '?';

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

    // If user pasted /WebPublisherSystem/blogs2/, keep only the part inside WebPublisherSystem.
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

    if (str_contains($scriptName, '/platform/index.php')) {
        return 'dashboard';
    }

    if (str_contains($scriptName, '/platform/')) {
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
    $isAdminContext = $currentNavItem === 'settings';
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
