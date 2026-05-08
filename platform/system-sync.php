<?php
const WPS_ASSET_BASE = '.';
const WPS_SETTINGS_URL = 'settings.php';

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/github.php';

wps_require_auth();

$settings = wps_load_settings();
$repoRootPath = 'WebPublisherSystem';
$localRoot = realpath(__DIR__ . '/..');
$results = [];
$error = '';
$success = '';

function wps_sync_should_skip_local(string $relativePath): bool
{
    $normalized = trim(str_replace('\\', '/', $relativePath), '/');

    $skipPrefixes = [
        'platform/data',
    ];

    foreach ($skipPrefixes as $prefix) {
        if ($normalized === $prefix || str_starts_with($normalized, $prefix . '/')) {
            return true;
        }
    }

    return false;
}

function wps_sync_should_skip_repo(string $repoPath): bool
{
    $normalized = trim(str_replace('\\', '/', $repoPath), '/');

    $skipPrefixes = [
        'WebPublisherSystem/platform/data',
    ];

    foreach ($skipPrefixes as $prefix) {
        if ($normalized === $prefix || str_starts_with($normalized, $prefix . '/')) {
            return true;
        }
    }

    return false;
}

function wps_sync_relative_path(string $repoPath): string
{
    $prefix = 'WebPublisherSystem/';
    if (str_starts_with($repoPath, $prefix)) {
        return substr($repoPath, strlen($prefix));
    }

    return $repoPath;
}

function wps_sync_download_binary(string $url): array
{
    $headers = [
        'User-Agent: WebPublisherSystem',
        'Accept: application/octet-stream',
    ];

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        $body = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($body === false || $error) {
            return ['ok' => false, 'body' => '', 'error' => $error ?: 'Download failed.', 'status' => $httpCode];
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            return ['ok' => false, 'body' => '', 'error' => 'Download returned HTTP ' . $httpCode, 'status' => $httpCode];
        }

        return ['ok' => true, 'body' => $body, 'error' => '', 'status' => $httpCode];
    }

    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => implode("\r\n", $headers),
            'timeout' => 60,
        ],
    ]);

    $body = @file_get_contents($url, false, $context);
    if ($body === false) {
        return ['ok' => false, 'body' => '', 'error' => 'Download failed. cURL is unavailable and file_get_contents could not fetch the URL.', 'status' => 0];
    }

    return ['ok' => true, 'body' => $body, 'error' => '', 'status' => 200];
}

function wps_sync_download_raw(string $url): array
{
    $result = wps_sync_download_binary($url);
    return [
        'ok' => $result['ok'],
        'body' => $result['body'],
        'error' => $result['error'],
        'status' => $result['status'],
    ];
}

function wps_sync_write_local_file(string $localRoot, string $relativePath, string $content, array &$results): void
{
    $relativePath = trim(str_replace('\\', '/', $relativePath), '/');

    if ($relativePath === '' || wps_sync_should_skip_local($relativePath)) {
        $results[] = ['status' => 'skipped', 'path' => $relativePath ?: '(empty)', 'message' => 'Runtime or invalid path skipped.'];
        return;
    }

    $targetPath = $localRoot . '/' . $relativePath;
    $targetDir = dirname($targetPath);
    $realLocalRoot = realpath($localRoot);

    if ($realLocalRoot === false) {
        $results[] = ['status' => 'error', 'path' => $relativePath, 'message' => 'Could not resolve local root.'];
        return;
    }

    if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
        $results[] = ['status' => 'error', 'path' => $relativePath, 'message' => 'Could not create local directory.'];
        return;
    }

    $realTargetDir = realpath($targetDir);
    if ($realTargetDir === false) {
        $results[] = ['status' => 'error', 'path' => $relativePath, 'message' => 'Could not resolve target directory.'];
        return;
    }

    $rootPrefix = rtrim(str_replace('\\', '/', $realLocalRoot), '/') . '/';
    $targetPrefix = rtrim(str_replace('\\', '/', $realTargetDir), '/') . '/';
    if (!str_starts_with($targetPrefix, $rootPrefix)) {
        $results[] = ['status' => 'error', 'path' => $relativePath, 'message' => 'Unsafe local path blocked.'];
        return;
    }

    $existing = file_exists($targetPath) ? file_get_contents($targetPath) : null;
    if ($existing === $content) {
        $results[] = ['status' => 'unchanged', 'path' => $relativePath, 'message' => 'Already up to date.'];
        return;
    }

    if (file_put_contents($targetPath, $content) === false) {
        $results[] = ['status' => 'error', 'path' => $relativePath, 'message' => 'Could not write local file. Check permissions.'];
        return;
    }

    $results[] = ['status' => $existing === null ? 'created' : 'updated', 'path' => $relativePath, 'message' => 'Synced from GitHub.'];
}

function wps_sync_via_zip(array $settings, string $localRoot, array &$results): bool
{
    if (!class_exists('ZipArchive')) {
        $results[] = ['status' => 'error', 'path' => 'zip', 'message' => 'PHP ZipArchive is unavailable. Falling back to API sync.'];
        return false;
    }

    $owner = trim((string) ($settings['github_owner'] ?? ''));
    $repo = trim((string) ($settings['github_repo'] ?? ''));
    $branch = trim((string) ($settings['github_branch'] ?? 'main'));

    if ($owner === '' || $repo === '' || $branch === '') {
        $results[] = ['status' => 'error', 'path' => 'zip', 'message' => 'Missing GitHub owner/repo/branch settings.'];
        return false;
    }

    $zipUrl = 'https://codeload.github.com/' . rawurlencode($owner) . '/' . rawurlencode($repo) . '/zip/refs/heads/' . rawurlencode($branch);
    $download = wps_sync_download_binary($zipUrl);

    if (!$download['ok']) {
        $results[] = ['status' => 'error', 'path' => 'zip download', 'message' => $download['error']];
        return false;
    }

    $zipPath = tempnam(sys_get_temp_dir(), 'wps-sync-');
    if ($zipPath === false || file_put_contents($zipPath, $download['body']) === false) {
        $results[] = ['status' => 'error', 'path' => 'zip', 'message' => 'Could not write temporary zip file.'];
        return false;
    }

    $zip = new ZipArchive();
    if ($zip->open($zipPath) !== true) {
        @unlink($zipPath);
        $results[] = ['status' => 'error', 'path' => 'zip', 'message' => 'Could not open downloaded zip file.'];
        return false;
    }

    $synced = 0;
    $sourcePrefixMarker = '/WebPublisherSystem/';

    for ($i = 0; $i < $zip->numFiles; $i++) {
        $entryName = str_replace('\\', '/', $zip->getNameIndex($i));

        if (str_ends_with($entryName, '/')) {
            continue;
        }

        $pos = strpos($entryName, $sourcePrefixMarker);
        if ($pos === false) {
            continue;
        }

        $relativePath = substr($entryName, $pos + strlen($sourcePrefixMarker));
        if ($relativePath === '' || wps_sync_should_skip_local($relativePath)) {
            continue;
        }

        $content = $zip->getFromIndex($i);
        if ($content === false) {
            $results[] = ['status' => 'error', 'path' => $relativePath, 'message' => 'Could not read file from zip.'];
            continue;
        }

        wps_sync_write_local_file($localRoot, $relativePath, $content, $results);
        $synced++;
    }

    $zip->close();
    @unlink($zipPath);

    if ($synced === 0) {
        $results[] = ['status' => 'error', 'path' => 'zip', 'message' => 'No WebPublisherSystem files found in the downloaded zip.'];
        return false;
    }

    $results[] = ['status' => 'updated', 'path' => 'WebPublisherSystem/*', 'message' => 'Zip sync completed without deleting local custom folders.'];
    return true;
}

/**
 * After sync, do a HEAD on every public post URL to confirm it's reachable.
 * Reports any 404/non-2xx as an error so the operator notices broken posts
 * before declaring sync successful.
 */
function wps_sync_live_verify(array $settings, array &$results): void
{
    if (!function_exists('wps_get_posts')) {
        require_once __DIR__ . '/content-loader.php';
        require_once __DIR__ . '/post-overrides.php';
    }

    $postsResult = wps_get_posts($settings);
    if (!$postsResult['ok']) {
        $results[] = ['status' => 'skipped', 'path' => 'live-verify', 'message' => 'No posts to verify: ' . $postsResult['error']];
        return;
    }

    $base = rtrim(wps_archive_url(), '/');
    $checked = 0;
    foreach ($postsResult['posts'] as $post) {
        $applied = wps_apply_post_override($post);
        $publicSlug = (string) ($applied['public_slug'] ?? $applied['base_slug'] ?? '');
        if ($publicSlug === '') {
            continue;
        }

        $url = $base . '/post.php?slug=' . rawurlencode($publicSlug);
        $status = wps_sync_head_request_status($url);
        $checked++;

        if ($status >= 200 && $status < 400) {
            $results[] = ['status' => 'unchanged', 'path' => $publicSlug, 'message' => 'Live verify OK (HTTP ' . $status . ').'];
        } elseif ($status === 0) {
            $results[] = ['status' => 'skipped', 'path' => $publicSlug, 'message' => 'Live verify could not reach the URL from this host.'];
        } else {
            $results[] = ['status' => 'error', 'path' => $publicSlug, 'message' => 'Live verify returned HTTP ' . $status . ' for ' . $url];
        }
    }

    if ($checked === 0) {
        $results[] = ['status' => 'skipped', 'path' => 'live-verify', 'message' => 'No published posts were available to verify.'];
    }
}

function wps_sync_head_request_status(string $url): int
{
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_NOBODY => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => 'WebPublisherSystem/live-verify',
        ]);
        curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $status;
    }

    $context = stream_context_create([
        'http' => [
            'method' => 'HEAD',
            'timeout' => 10,
            'header' => "User-Agent: WebPublisherSystem/live-verify\r\n",
            'ignore_errors' => true,
        ],
    ]);
    @file_get_contents($url, false, $context);

    $http_response_header = $http_response_header ?? [];
    foreach ($http_response_header as $line) {
        if (preg_match('#HTTP/\S+\s+(\d{3})#', $line, $m)) {
            return (int) $m[1];
        }
    }

    return 0;
}

function wps_sync_recreate_archive_alias(array $settings, array &$results): void
{
    $slug = wps_archive_slug_from_setting($settings);
    wps_ensure_archive_alias($settings);

    if ($slug === 'blog') {
        $results[] = ['status' => 'unchanged', 'path' => 'blog/', 'message' => 'Default archive path is part of the system package.'];
        return;
    }

    $root = realpath(__DIR__ . '/..');
    $aliasIndex = $root ? $root . '/' . $slug . '/index.php' : '';
    $aliasPost = $root ? $root . '/' . $slug . '/post.php' : '';

    if ($aliasIndex && is_file($aliasIndex) && is_file($aliasPost)) {
        $results[] = ['status' => 'updated', 'path' => $slug . '/', 'message' => 'Saved archive alias recreated after sync.'];
    } else {
        $results[] = ['status' => 'error', 'path' => $slug . '/', 'message' => 'Could not recreate saved archive alias. Check folder permissions.'];
    }
}

function wps_sync_path(array $settings, string $repoPath, string $localRoot, array &$results): void
{
    if (wps_sync_should_skip_repo($repoPath)) {
        $results[] = ['status' => 'skipped', 'path' => $repoPath, 'message' => 'Runtime data path skipped.'];
        return;
    }

    $url = wps_github_api_url($settings, $repoPath);
    $response = wps_github_fetch_json($url);

    if (!$response['ok']) {
        $results[] = ['status' => 'error', 'path' => $repoPath, 'message' => $response['error']];
        return;
    }

    $items = $response['data'];
    if (isset($items['type'])) {
        $items = [$items];
    }

    if (!is_array($items)) {
        $results[] = ['status' => 'error', 'path' => $repoPath, 'message' => 'Unexpected GitHub response.'];
        return;
    }

    foreach ($items as $item) {
        if (!is_array($item) || empty($item['path']) || empty($item['type'])) {
            continue;
        }

        $path = $item['path'];

        if (wps_sync_should_skip_repo($path)) {
            $results[] = ['status' => 'skipped', 'path' => $path, 'message' => 'Runtime data path skipped.'];
            continue;
        }

        if ($item['type'] === 'dir') {
            wps_sync_path($settings, $path, $localRoot, $results);
            continue;
        }

        if ($item['type'] !== 'file') {
            $results[] = ['status' => 'skipped', 'path' => $path, 'message' => 'Unsupported item type: ' . $item['type']];
            continue;
        }

        $relativePath = wps_sync_relative_path($path);
        $downloadUrl = $item['download_url'] ?? '';
        if (!$downloadUrl) {
            $results[] = ['status' => 'error', 'path' => $path, 'message' => 'Missing GitHub download URL.'];
            continue;
        }

        $download = wps_sync_download_raw($downloadUrl);
        if (!$download['ok']) {
            $results[] = ['status' => 'error', 'path' => $path, 'message' => $download['error']];
            continue;
        }

        wps_sync_write_local_file($localRoot, $relativePath, $download['body'], $results);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    wps_csrf_validate_or_die();

    if (!$localRoot) {
        $error = 'Could not resolve local WebPublisherSystem root folder.';
    } else {
        $zipSynced = wps_sync_via_zip($settings, $localRoot, $results);
        if (!$zipSynced) {
            wps_sync_path($settings, $repoRootPath, $localRoot, $results);
        }
        $settings = wps_load_settings();
        wps_sync_recreate_archive_alias($settings, $results);
        wps_sync_live_verify($settings, $results);
        $errors = array_filter($results, fn($item) => $item['status'] === 'error');
        $success = empty($errors)
            ? 'System sync completed successfully.'
            : 'System sync completed with ' . count($errors) . ' error(s).';
    }
}

wps_render_header('System Sync');
?>

<section class="panel">
    <h1>Sync WebPublisherSystem from GitHub</h1>
    <p class="muted">This updates files from the public GitHub repository using a safe zip-based sync when available, and falls back to GitHub API sync if needed. Runtime settings in <code>platform/data/</code> are always skipped so your saved settings are not overwritten. After sync, the saved archive slug is recreated automatically.</p>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo wps_h($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert <?php echo str_contains($success, 'error') ? 'alert-error' : 'alert-success'; ?>"><?php echo wps_h($success); ?></div>
    <?php endif; ?>

    <form method="post" class="actions">
        <?php echo wps_csrf_field(); ?>
        <button type="submit">Sync All System Files from GitHub</button>
        <a class="button-secondary" href="settings.php">Back to Settings</a>
        <a class="button-secondary" href="<?php echo wps_h(wps_archive_url()); ?>">View Blog Archive</a>
    </form>
</section>

<?php if ($results): ?>
    <section class="panel">
        <h2>Sync Results</h2>
        <div class="result-box">
            <ul>
                <?php foreach ($results as $item): ?>
                    <li>
                        <strong><?php echo wps_h(strtoupper($item['status'])); ?></strong>
                        — <?php echo wps_h($item['path']); ?>
                        <br><small><?php echo wps_h($item['message']); ?></small>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>
<?php endif; ?>

<?php wps_render_footer(); ?>
