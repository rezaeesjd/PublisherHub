<?php
/**
 * Download a zip of platform/data/ — settings, post overrides, and
 * (excluding the auth file by default) anything else stored locally.
 * This is the manual backup path during Phase 1 before automated
 * snapshots exist.
 */

require_once __DIR__ . '/auth.php';

wps_require_auth();

if (!class_exists('ZipArchive')) {
    http_response_code(500);
    echo 'PHP ZipArchive is unavailable on this host. Install the zip extension or copy platform/data/ manually.';
    exit;
}

$dataDir = realpath(__DIR__ . '/data');
if ($dataDir === false || !is_dir($dataDir)) {
    http_response_code(500);
    echo 'platform/data/ not found.';
    exit;
}

$includeSecrets = isset($_GET['include_secrets']) && $_GET['include_secrets'] === '1';

$tmp = tempnam(sys_get_temp_dir(), 'wps-backup-');
if ($tmp === false) {
    http_response_code(500);
    echo 'Could not create temporary backup file.';
    exit;
}

$zip = new ZipArchive();
if ($zip->open($tmp, ZipArchive::OVERWRITE) !== true) {
    @unlink($tmp);
    http_response_code(500);
    echo 'Could not open backup archive for writing.';
    exit;
}

$secretFiles = [
    'auth.json',
    'auth-attempts.json',
];

$iter = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($dataDir, FilesystemIterator::SKIP_DOTS),
    RecursiveIteratorIterator::LEAVES_ONLY
);

foreach ($iter as $file) {
    if (!$file->isFile()) {
        continue;
    }

    $real = $file->getRealPath();
    if ($real === false) {
        continue;
    }

    $relative = ltrim(str_replace('\\', '/', substr($real, strlen($dataDir))), '/');
    $basename = basename($relative);

    if (!$includeSecrets && in_array($basename, $secretFiles, true)) {
        continue;
    }

    $zip->addFile($real, 'platform-data/' . $relative);
}

$zip->addFromString(
    'platform-data/BACKUP-README.txt',
    "WebPublisherSystem backup\n"
    . "Generated at: " . gmdate('c') . "\n"
    . "Includes auth.json: " . ($includeSecrets ? 'yes' : 'no') . "\n"
    . "Source dir: platform/data/\n"
);

$zip->close();

$filename = 'wps-backup-' . gmdate('Ymd-His') . '.zip';
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($tmp));
header('Cache-Control: no-store');

readfile($tmp);
@unlink($tmp);
