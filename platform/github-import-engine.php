<?php
/**
 * GitHub Import Addon — Engine v1.0.0
 *
 * Reusable, standalone engine for importing and syncing content from one or
 * more GitHub repositories into a local directory tree.
 *
 * Requirements: PHP 7.4+, a writable data directory.
 * Optional:     cURL (falls back to file_get_contents),
 *               ZipArchive (falls back to GitHub API tree walk).
 *
 * Standalone usage:
 *   define('GHIMPORT_DATA_DIR',   '/srv/mysite/data');
 *   define('GHIMPORT_LOCAL_ROOT', '/srv/mysite');
 *   require_once 'github-import-engine.php';
 *
 * Within PublisherHub — just include; all paths are set automatically.
 *
 * @license MIT
 * @link    https://github.com/rezaeesjd/publisherhub
 */

// ---------------------------------------------------------------------------
// Bootstrap constants (override before including this file)
// ---------------------------------------------------------------------------

if (!defined('GHIMPORT_DATA_DIR')) {
    define('GHIMPORT_DATA_DIR', __DIR__ . '/data');
}

if (!defined('GHIMPORT_LOCAL_ROOT')) {
    define('GHIMPORT_LOCAL_ROOT', dirname(__DIR__));
}

if (!defined('GHIMPORT_CONNECTIONS_FILE')) {
    define('GHIMPORT_CONNECTIONS_FILE', GHIMPORT_DATA_DIR . '/github-imports.json');
}

// Paths (relative to GHIMPORT_LOCAL_ROOT) that are never overwritten during sync.
if (!defined('GHIMPORT_PROTECTED_PATHS')) {
    define('GHIMPORT_PROTECTED_PATHS', ['platform/data']);
}

// ---------------------------------------------------------------------------
// Atomic file write (reuses WPS helper when embedded; self-contained otherwise)
// ---------------------------------------------------------------------------

function ghimport_atomic_write(string $path, string $contents): bool
{
    if (function_exists('wps_atomic_write')) {
        return wps_atomic_write($path, $contents);
    }

    $dir = dirname($path);
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        return false;
    }

    $tmp    = $dir . '/.' . basename($path) . '.' . bin2hex(random_bytes(6)) . '.tmp';
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

// ---------------------------------------------------------------------------
// Connection store
// ---------------------------------------------------------------------------

function ghimport_ensure_data_dir(): void
{
    if (!is_dir(GHIMPORT_DATA_DIR)) {
        mkdir(GHIMPORT_DATA_DIR, 0755, true);
    }
}

function ghimport_load_connections(): array
{
    ghimport_ensure_data_dir();

    if (!file_exists(GHIMPORT_CONNECTIONS_FILE)) {
        return [];
    }

    $json = file_get_contents(GHIMPORT_CONNECTIONS_FILE);
    $data = json_decode($json, true);

    return is_array($data) ? $data : [];
}

function ghimport_save_connections(array $connections): bool
{
    ghimport_ensure_data_dir();
    return ghimport_atomic_write(
        GHIMPORT_CONNECTIONS_FILE,
        json_encode(array_values($connections), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
    );
}

function ghimport_find_connection(array $connections, string $id): ?array
{
    foreach ($connections as $conn) {
        if (($conn['id'] ?? '') === $id) {
            return $conn;
        }
    }
    return null;
}

function ghimport_update_connection(array &$connections, array $updated): void
{
    foreach ($connections as $i => $conn) {
        if (($conn['id'] ?? '') === ($updated['id'] ?? '')) {
            $connections[$i] = $updated;
            return;
        }
    }
}


function ghimport_update_connection_status(string $id, string $status): bool
{
    if ($id === '') {
        return false;
    }

    $connections = ghimport_load_connections();
    $found = false;

    foreach ($connections as &$conn) {
        if (($conn['id'] ?? '') !== $id) {
            continue;
        }

        $conn['last_sync_status'] = $status;
        $conn['last_synced_at'] = gmdate('c');
        $found = true;
        break;
    }
    unset($conn);

    if (!$found) {
        return false;
    }

    return ghimport_save_connections($connections);
}

function ghimport_delete_connection(array &$connections, string $id): void
{
    $connections = array_values(array_filter($connections, fn($c) => ($c['id'] ?? '') !== $id));
}

function ghimport_new_id(): string
{
    return bin2hex(random_bytes(8));
}

// ---------------------------------------------------------------------------
// GitHub URL parser
// ---------------------------------------------------------------------------

/**
 * Parse a GitHub URL or shorthand into connection parameters.
 *
 * Accepted formats:
 *   https://github.com/owner/repo
 *   https://github.com/owner/repo/tree/main
 *   https://github.com/owner/repo/tree/main/path/to/content
 *   owner/repo
 *   owner/repo@branch
 *
 * Returns: ['ok'=>bool, 'owner'=>'', 'repo'=>'', 'branch'=>'main',
 *           'content_path'=>'', 'error'=>'']
 */
function ghimport_parse_github_url(string $input): array
{
    $result = [
        'ok'           => false,
        'owner'        => '',
        'repo'         => '',
        'branch'       => 'main',
        'content_path' => '',
        'error'        => '',
    ];

    $input = trim($input);

    if ($input === '') {
        $result['error'] = 'GitHub URL or owner/repo shorthand is required.';
        return $result;
    }

    // Shorthand: owner/repo  or  owner/repo@branch
    if (!str_contains($input, '://') && !str_starts_with($input, 'github.com')) {
        $atParts    = explode('@', $input, 2);
        $slashParts = array_values(array_filter(explode('/', trim($atParts[0], '/'))));

        if (count($slashParts) >= 2 && $slashParts[0] !== '' && $slashParts[1] !== '') {
            $result['owner']  = $slashParts[0];
            $result['repo']   = preg_replace('/\.git$/', '', $slashParts[1]);
            $result['branch'] = isset($atParts[1]) && $atParts[1] !== '' ? $atParts[1] : 'main';
            $result['ok']     = true;
            return $result;
        }

        $result['error'] = 'Invalid shorthand. Expected owner/repo or owner/repo@branch.';
        return $result;
    }

    // Prepend scheme if bare domain was passed
    if (str_starts_with($input, 'github.com')) {
        $input = 'https://' . $input;
    }

    $parsed = parse_url($input);
    if (!$parsed || ($parsed['host'] ?? '') !== 'github.com') {
        $result['error'] = 'Only github.com repositories are supported.';
        return $result;
    }

    // Decode percent-encoded segments before storing so they are not
    // double-encoded when rawurlencode() is applied later on API calls.
    // e.g. browser-copied "my%20folder" → stored as "my folder" → API "my%20folder" ✓
    $segments = array_values(
        array_filter(
            array_map('urldecode', explode('/', trim($parsed['path'] ?? '', '/'))),
            fn($s) => $s !== ''
        )
    );

    if (count($segments) < 2) {
        $result['error'] = 'Could not extract owner and repo from the URL.';
        return $result;
    }

    $result['owner'] = $segments[0];
    $result['repo']  = preg_replace('/\.git$/', '', $segments[1]);

    // /tree/{branch}[/{path...}]
    // Branch names may contain slashes. For tree URLs, try to resolve the
    // longest matching branch name using GitHub's branches API, then treat the
    // remaining segments as content_path.
    if (isset($segments[2]) && $segments[2] === 'tree' && isset($segments[3])) {
        $treeTail = array_slice($segments, 3);
        $resolved = ghimport_resolve_tree_branch_and_path($result['owner'], $result['repo'], $treeTail);

        $result['branch'] = $resolved['branch'];
        $result['content_path'] = $resolved['content_path'];
    }

    $result['ok'] = true;
    return $result;
}


/**
 * Resolve /tree URL tail into branch + path, including branch names with '/'.
 *
 * @param string $owner
 * @param string $repo
 * @param array<int,string> $treeTail Segments after /tree/
 * @return array{branch:string,content_path:string}
 */
function ghimport_resolve_tree_branch_and_path(string $owner, string $repo, array $treeTail): array
{
    $fallbackBranch = $treeTail[0] ?? 'main';
    $fallbackPath = count($treeTail) > 1 ? implode('/', array_slice($treeTail, 1)) : '';

    if (count($treeTail) <= 1) {
        return ['branch' => $fallbackBranch, 'content_path' => ''];
    }

    $branches = ghimport_fetch_repo_branch_names($owner, $repo);
    if (empty($branches)) {
        return ['branch' => $fallbackBranch, 'content_path' => $fallbackPath];
    }

    for ($i = count($treeTail); $i >= 1; $i--) {
        $candidate = implode('/', array_slice($treeTail, 0, $i));
        if (in_array($candidate, $branches, true)) {
            $path = $i < count($treeTail) ? implode('/', array_slice($treeTail, $i)) : '';
            return ['branch' => $candidate, 'content_path' => $path];
        }
    }

    return ['branch' => $fallbackBranch, 'content_path' => $fallbackPath];
}

/**
 * Fetch branch names for a repository (best-effort; returns [] on failure).
 *
 * @return array<int,string>
 */
function ghimport_fetch_repo_branch_names(string $owner, string $repo): array
{
    $url = 'https://api.github.com/repos/' . rawurlencode($owner) . '/' . rawurlencode($repo) . '/branches?per_page=100';
    $res = ghimport_http_get($url);

    if (!($res['ok'] ?? false) || ($res['status'] ?? 0) >= 400 || !is_array($res['body'] ?? null)) {
        return [];
    }

    $names = [];
    foreach ($res['body'] as $row) {
        if (is_array($row) && isset($row['name']) && is_string($row['name']) && $row['name'] !== '') {
            $names[] = $row['name'];
        }
    }

    return $names;
}

// ---------------------------------------------------------------------------
// HTTP client (cURL with file_get_contents fallback; optional Bearer token)
// ---------------------------------------------------------------------------

function ghimport_http_get(string $url, string $token = '', bool $binary = false): array
{
    $headers = ['User-Agent: GitHubImportAddon/1.0'];

    if ($binary) {
        $headers[] = 'Accept: application/octet-stream';
    } else {
        $headers[] = 'Accept: application/vnd.github+json';
    }

    if ($token !== '') {
        $headers[] = 'Authorization: Bearer ' . $token;
    }

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        $body    = curl_exec($ch);
        $code    = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($body === false || $curlErr !== '') {
            return ['ok' => false, 'status' => $code, 'body' => '', 'error' => $curlErr ?: 'cURL request failed.'];
        }
    } else {
        $ctx  = stream_context_create(['http' => [
            'method'        => 'GET',
            'header'        => implode("\r\n", $headers),
            'timeout'       => 60,
            'ignore_errors' => true,
        ]]);
        $body = @file_get_contents($url, false, $ctx);
        $code = 0;

        if (isset($http_response_header) && is_array($http_response_header)) {
            foreach ($http_response_header as $line) {
                if (preg_match('#HTTP/\S+\s+(\d{3})#', $line, $m)) {
                    $code = (int) $m[1];
                    break;
                }
            }
        }

        if ($body === false) {
            return ['ok' => false, 'status' => 0, 'body' => '', 'error' => 'HTTP request failed. Enable cURL for better reliability.'];
        }
    }

    if ($code < 200 || $code >= 300) {
        $msg = 'HTTP ' . $code;
        if (!$binary) {
            $decoded = json_decode($body, true);
            if (is_array($decoded) && isset($decoded['message'])) {
                $msg = $decoded['message'];
            }
        }
        return ['ok' => false, 'status' => $code, 'body' => $body, 'error' => $msg];
    }

    return ['ok' => true, 'status' => $code, 'body' => $body, 'error' => ''];
}

function ghimport_api_get(string $url, string $token = ''): array
{
    $result = ghimport_http_get($url, $token, false);
    if (!$result['ok']) {
        return ['ok' => false, 'error' => $result['error'], 'data' => null];
    }

    $data = json_decode($result['body'], true);
    return ['ok' => true, 'error' => '', 'data' => $data];
}

// ---------------------------------------------------------------------------
// Connection test
// ---------------------------------------------------------------------------

function ghimport_test_connection(array $conn): array
{
    $owner  = trim($conn['owner'] ?? '');
    $repo   = trim($conn['repo'] ?? '');
    $branch = trim($conn['branch'] ?? 'main');
    $path   = trim($conn['content_path'] ?? '', '/');
    $token  = $conn['token'] ?? '';

    if ($owner === '' || $repo === '') {
        return ['ok' => false, 'message' => 'Missing owner or repo.', 'items' => []];
    }

    $encodedPath = $path !== ''
        ? '/' . implode('/', array_map('rawurlencode', explode('/', $path)))
        : '';

    $url = 'https://api.github.com/repos/'
        . rawurlencode($owner) . '/' . rawurlencode($repo)
        . '/contents' . $encodedPath
        . '?ref=' . rawurlencode($branch);

    $result = ghimport_api_get($url, $token);

    if (!$result['ok']) {
        return ['ok' => false, 'message' => 'Connection failed: ' . $result['error'], 'items' => []];
    }

    $items = [];
    $data  = $result['data'];

    if (is_array($data)) {
        // Single file response
        if (isset($data['type'])) {
            $data = [$data];
        }
        foreach ($data as $item) {
            if (is_array($item) && isset($item['name'], $item['type'])) {
                $items[] = ['name' => $item['name'], 'type' => $item['type']];
            }
        }
    }

    $location = $path !== '' ? $path : 'repo root';
    return [
        'ok'      => true,
        'message' => 'Connection OK. Found ' . count($items) . ' item(s) at ' . $location . '.',
        'items'   => $items,
    ];
}

// ---------------------------------------------------------------------------
// Sync engine
// ---------------------------------------------------------------------------

function ghimport_is_protected(string $relativePath): bool
{
    $normalized = trim(str_replace('\\', '/', $relativePath), '/');
    $protected  = is_array(GHIMPORT_PROTECTED_PATHS) ? GHIMPORT_PROTECTED_PATHS : [];

    foreach ($protected as $prefix) {
        $prefix = trim((string) $prefix, '/');
        if ($normalized === $prefix || str_starts_with($normalized, $prefix . '/')) {
            return true;
        }
    }

    return false;
}

function ghimport_write_local(string $localRoot, string $relativePath, string $content, array &$results): void
{
    $relativePath = trim(str_replace('\\', '/', $relativePath), '/');

    if ($relativePath === '' || ghimport_is_protected($relativePath)) {
        $results[] = ['status' => 'skipped', 'path' => $relativePath ?: '(empty)', 'message' => 'Protected or invalid path skipped.'];
        return;
    }

    $targetPath  = $localRoot . '/' . $relativePath;
    $targetDir   = dirname($targetPath);
    $realRoot    = realpath($localRoot);

    if ($realRoot === false) {
        $results[] = ['status' => 'error', 'path' => $relativePath, 'message' => 'Could not resolve local root.'];
        return;
    }

    if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
        $results[] = ['status' => 'error', 'path' => $relativePath, 'message' => 'Could not create directory.'];
        return;
    }

    $realTargetDir = realpath($targetDir);
    if ($realTargetDir === false) {
        $results[] = ['status' => 'error', 'path' => $relativePath, 'message' => 'Could not resolve target directory.'];
        return;
    }

    $rootPrefix   = rtrim(str_replace('\\', '/', $realRoot), '/') . '/';
    $targetPrefix = rtrim(str_replace('\\', '/', $realTargetDir), '/') . '/';

    if (!str_starts_with($targetPrefix, $rootPrefix)) {
        $results[] = ['status' => 'error', 'path' => $relativePath, 'message' => 'Unsafe path blocked (directory traversal).'];
        return;
    }

    $existing = is_file($targetPath) ? file_get_contents($targetPath) : null;

    if ($existing === $content) {
        $results[] = ['status' => 'unchanged', 'path' => $relativePath, 'message' => 'Already up to date.'];
        return;
    }

    if (file_put_contents($targetPath, $content) === false) {
        $results[] = ['status' => 'error', 'path' => $relativePath, 'message' => 'Could not write file. Check permissions.'];
        return;
    }

    $results[] = [
        'status'  => $existing === null ? 'created' : 'updated',
        'path'    => $relativePath,
        'message' => 'Synced from GitHub.',
    ];
}

/**
 * Map a repo-relative file path to a local relative path.
 *
 * Files under $contentPath (in the repo) are placed under $localPath
 * (on the server), preserving the directory structure beneath $contentPath.
 * When $localPath is empty it mirrors $contentPath.
 *
 * Returns null when the file is outside $contentPath.
 */


function ghimport_collect_local_files(string $localRoot): array
{
    $files = [];
    if (!is_dir($localRoot)) {
        return $files;
    }

    $root = realpath($localRoot);
    if ($root === false) {
        return $files;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    $rootPrefix = rtrim(str_replace('\\', '/', $root), '/') . '/';

    foreach ($iterator as $fileInfo) {
        if (!$fileInfo->isFile()) {
            continue;
        }

        $absolute = str_replace('\\', '/', $fileInfo->getPathname());
        if (!str_starts_with($absolute, $rootPrefix)) {
            continue;
        }

        $relative = substr($absolute, strlen($rootPrefix));
        if ($relative === '' || ghimport_is_protected($relative)) {
            continue;
        }

        $files[$relative] = true;
    }

    return $files;
}


function ghimport_connection_target_prefix(array $conn): string
{
    $contentPath = trim(str_replace('\\', '/', $conn['content_path'] ?? ''), '/');
    $localPath = trim(str_replace('\\', '/', $conn['local_path'] ?? ''), '/');
    return $localPath !== '' ? $localPath : $contentPath;
}

function ghimport_connection_has_shared_target(array $conn): bool
{
    $connId = (string) ($conn['id'] ?? '');
    $targetPrefix = ghimport_connection_target_prefix($conn);

    foreach (ghimport_load_connections() as $other) {
        if (!($other['enabled'] ?? true)) {
            continue;
        }
        if ((string) ($other['id'] ?? '') === $connId) {
            continue;
        }
        if (ghimport_connection_target_prefix($other) === $targetPrefix) {
            return true;
        }
    }

    return false;
}

function ghimport_prune_deleted_files(string $localRoot, array $syncedPaths, array &$results, string $scopePrefix = ''): void
{
    $localFiles = ghimport_collect_local_files($localRoot);
    if (empty($localFiles)) {
        return;
    }

    foreach ($localFiles as $relativePath => $_) {
        if ($scopePrefix !== '') {
            $scope = rtrim($scopePrefix, '/') . '/';
            if ($relativePath !== $scopePrefix && !str_starts_with($relativePath, $scope)) {
                continue;
            }
        }

        if (isset($syncedPaths[$relativePath])) {
            continue;
        }

        $targetPath = rtrim($localRoot, '/\\') . '/' . $relativePath;
        if (!is_file($targetPath)) {
            continue;
        }

        if (@unlink($targetPath)) {
            $results[] = ['status' => 'deleted', 'path' => $relativePath, 'message' => 'Removed because it no longer exists in GitHub source.'];
        } else {
            $results[] = ['status' => 'error', 'path' => $relativePath, 'message' => 'Failed to remove stale local file.'];
        }
    }
}

function ghimport_map_path(string $repoFilePath, string $contentPath, string $localPath): ?string
{
    $repoFilePath = trim(str_replace('\\', '/', $repoFilePath), '/');
    $contentPath  = trim(str_replace('\\', '/', $contentPath), '/');
    $localPath    = trim(str_replace('\\', '/', $localPath), '/');

    if ($contentPath === '') {
        return $localPath !== '' ? $localPath . '/' . $repoFilePath : $repoFilePath;
    }

    $prefix = $contentPath . '/';

    if ($repoFilePath === $contentPath) {
        $rel = '';
    } elseif (str_starts_with($repoFilePath, $prefix)) {
        $rel = substr($repoFilePath, strlen($prefix));
    } else {
        return null;
    }

    $base = $localPath !== '' ? $localPath : $contentPath;

    return $rel !== '' ? $base . '/' . $rel : $base;
}

function ghimport_sync_via_zip(array $conn, string $localRoot, array &$results): bool
{
    if (!class_exists('ZipArchive')) {
        $results[] = ['status' => 'skipped', 'path' => 'zip', 'message' => 'ZipArchive unavailable; using API fallback.'];
        return false;
    }

    $owner       = $conn['owner'];
    $repo        = $conn['repo'];
    $branch      = $conn['branch'] ?? 'main';
    $contentPath = trim($conn['content_path'] ?? '', '/');
    $localPath   = trim($conn['local_path'] ?? '', '/');
    $token       = $conn['token'] ?? '';

    $zipUrl = 'https://codeload.github.com/'
        . rawurlencode($owner) . '/' . rawurlencode($repo)
        . '/zip/refs/heads/' . rawurlencode($branch);

    $dl = ghimport_http_get($zipUrl, $token, true);

    if (!$dl['ok']) {
        $results[] = ['status' => 'error', 'path' => 'zip download', 'message' => $dl['error']];
        return false;
    }

    $zipPath = tempnam(sys_get_temp_dir(), 'ghimport-');
    if ($zipPath === false || file_put_contents($zipPath, $dl['body']) === false) {
        $results[] = ['status' => 'error', 'path' => 'zip', 'message' => 'Could not write temporary zip file.'];
        return false;
    }

    $zip = new ZipArchive();
    if ($zip->open($zipPath) !== true) {
        @unlink($zipPath);
        $results[] = ['status' => 'error', 'path' => 'zip', 'message' => 'Could not open downloaded zip archive.'];
        return false;
    }

    // GitHub archives the repo as "{repo}-{branch}/" at the zip root.
    // Try to detect the actual prefix from the first entry.
    $zipRootPrefix = '';
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $entry = str_replace('\\', '/', $zip->getNameIndex($i));
        $slashPos = strpos($entry, '/');
        if ($slashPos !== false) {
            $zipRootPrefix = substr($entry, 0, $slashPos + 1);
            break;
        }
    }

    $synced = 0;

    for ($i = 0; $i < $zip->numFiles; $i++) {
        $entry = str_replace('\\', '/', $zip->getNameIndex($i));

        if (str_ends_with($entry, '/')) {
            continue;
        }

        // Strip the zip root folder
        $repoRelPath = $zipRootPrefix !== '' && str_starts_with($entry, $zipRootPrefix)
            ? substr($entry, strlen($zipRootPrefix))
            : $entry;

        $localRelPath = ghimport_map_path($repoRelPath, $contentPath, $localPath);
        if ($localRelPath === null) {
            continue;
        }

        $content = $zip->getFromIndex($i);
        if ($content === false) {
            $results[] = ['status' => 'error', 'path' => $repoRelPath, 'message' => 'Could not read from zip.'];
            continue;
        }

        ghimport_write_local($localRoot, $localRelPath, $content, $results);
        $synced++;
    }

    $zip->close();
    @unlink($zipPath);

    if ($synced === 0) {
        $results[] = ['status' => 'error', 'path' => 'zip', 'message' => 'No matching files found in zip. Check the content path setting.'];
        return false;
    }

    return true;
}

function ghimport_sync_via_api(array $conn, string $localRoot, array &$results, string $apiPath = ''): void
{
    $owner       = $conn['owner'];
    $repo        = $conn['repo'];
    $branch      = $conn['branch'] ?? 'main';
    $contentPath = trim($conn['content_path'] ?? '', '/');
    $localPath   = trim($conn['local_path'] ?? '', '/');
    $token       = $conn['token'] ?? '';

    // On the first call, start at content_path
    if ($apiPath === '') {
        $apiPath = $contentPath;
    }

    $encodedPath = $apiPath !== ''
        ? '/' . implode('/', array_map('rawurlencode', explode('/', $apiPath)))
        : '';

    $url = 'https://api.github.com/repos/'
        . rawurlencode($owner) . '/' . rawurlencode($repo)
        . '/contents' . $encodedPath
        . '?ref=' . rawurlencode($branch);

    $response = ghimport_api_get($url, $token);

    if (!$response['ok']) {
        $results[] = ['status' => 'error', 'path' => $apiPath ?: '/', 'message' => $response['error']];
        return;
    }

    $items = $response['data'];

    // Single file response
    if (is_array($items) && isset($items['type'])) {
        $items = [$items];
    }

    if (!is_array($items)) {
        $results[] = ['status' => 'error', 'path' => $apiPath ?: '/', 'message' => 'Unexpected GitHub API response.'];
        return;
    }

    foreach ($items as $item) {
        if (!is_array($item) || empty($item['path']) || empty($item['type'])) {
            continue;
        }

        $repoRelPath = $item['path'];

        if ($item['type'] === 'dir') {
            ghimport_sync_via_api($conn, $localRoot, $results, $repoRelPath);
            continue;
        }

        if ($item['type'] !== 'file') {
            $results[] = ['status' => 'skipped', 'path' => $repoRelPath, 'message' => 'Unknown type: ' . $item['type']];
            continue;
        }

        $localRelPath = ghimport_map_path($repoRelPath, $contentPath, $localPath);
        if ($localRelPath === null) {
            continue;
        }

        $downloadUrl = $item['download_url'] ?? '';
        if ($downloadUrl === '') {
            $results[] = ['status' => 'error', 'path' => $repoRelPath, 'message' => 'No download URL returned by API.'];
            continue;
        }

        $dl = ghimport_http_get($downloadUrl, $token, true);
        if (!$dl['ok']) {
            $results[] = ['status' => 'error', 'path' => $repoRelPath, 'message' => $dl['error']];
            continue;
        }

        ghimport_write_local($localRoot, $localRelPath, $dl['body'], $results);
    }
}

/**
 * Sync a single connection. Tries ZIP download first; falls back to API walk.
 * Returns an array of result items.
 */
function ghimport_sync_connection(array $conn, string $localRoot = GHIMPORT_LOCAL_ROOT): array
{
    $results = [];

    $zipOk = ghimport_sync_via_zip($conn, $localRoot, $results);
    if (!$zipOk) {
        ghimport_sync_via_api($conn, $localRoot, $results);
    }

    $syncedPaths = [];
    foreach ($results as $item) {
        if (in_array($item['status'], ['created', 'updated', 'unchanged'], true)) {
            $syncedPaths[$item['path']] = true;
        }
    }

    $hasErrors = false;
    foreach ($results as $item) {
        if (($item['status'] ?? '') === 'error') {
            $hasErrors = true;
            break;
        }
    }

    $shouldPrune = !array_key_exists('prune_deleted', $conn) || (bool) $conn['prune_deleted'];
    if ($shouldPrune && !$hasErrors) {
        if (ghimport_connection_has_shared_target($conn)) {
            $results[] = ['status' => 'skipped', 'path' => 'prune', 'message' => 'Prune skipped because another enabled connection shares this target path.'];
        } else {
            ghimport_prune_deleted_files($localRoot, $syncedPaths, $results, ghimport_connection_target_prefix($conn));
        }
    } elseif ($shouldPrune && $hasErrors) {
        $results[] = ['status' => 'skipped', 'path' => 'prune', 'message' => 'Prune skipped because sync reported errors.'];
    }

    return $results;
}

/**
 * Summarise a results array into counts and an overall status string.
 *
 * Returns: ['status'=>'ok'|'partial'|'error', 'created'=>n, 'updated'=>n,
 *           'unchanged'=>n, 'skipped'=>n, 'error'=>n]
 */
function ghimport_results_summary(array $results): array
{
    $counts = ['created' => 0, 'updated' => 0, 'unchanged' => 0, 'deleted' => 0, 'skipped' => 0, 'error' => 0];

    foreach ($results as $r) {
        $s = $r['status'] ?? 'error';
        if (array_key_exists($s, $counts)) {
            $counts[$s]++;
        }
    }

    $written = $counts['created'] + $counts['updated'];
    $status  = 'ok';

    if ($counts['error'] > 0 && $written === 0) {
        $status = 'error';
    } elseif ($counts['error'] > 0) {
        $status = 'partial';
    }

    return array_merge($counts, ['status' => $status]);
}
