<?php
const WPS_ASSET_BASE   = '.';
const WPS_SETTINGS_URL = 'settings.php';

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/secrets.php';
require_once __DIR__ . '/cache.php';
require_once __DIR__ . '/github-import-engine.php';

wps_require_auth();

$localRoot   = realpath(__DIR__ . '/..');
$connections = ghimport_load_connections();

// Migrate any plaintext tokens to encrypted form on first read.
$tokenMigrationNeeded = false;
foreach ($connections as $i => $c) {
    $stored = (string) ($c['token'] ?? '');
    if ($stored !== '' && !wps_secret_is_encrypted($stored)) {
        $connections[$i]['token'] = wps_secret_encrypt($stored);
        $tokenMigrationNeeded = true;
    }
}
if ($tokenMigrationNeeded) {
    ghimport_save_connections($connections);
    wps_secret_harden_path(GHIMPORT_CONNECTIONS_FILE);
}

$error       = '';
$success     = '';
$testResult  = null;
$syncResults = [];
$activeConnId = '';

// ---------------------------------------------------------------------------
// POST handler
// ---------------------------------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    wps_csrf_validate_or_die();

    $action  = trim($_POST['action'] ?? '');
    $connId  = trim($_POST['conn_id'] ?? '');

    // -- Add connection ------------------------------------------------------
    if ($action === 'add') {
        $rawUrl      = trim($_POST['github_url'] ?? '');
        $label       = trim($_POST['label'] ?? '');
        $branchOvr   = trim($_POST['branch'] ?? '');
        $contentPath = trim($_POST['content_path'] ?? '');
        $localPath   = trim($_POST['local_path'] ?? '');
        $token       = trim($_POST['token'] ?? '');

        $parsed = ghimport_parse_github_url($rawUrl);

        if (!$parsed['ok']) {
            $error = $parsed['error'];
        } else {
            $conn = [
                'id'               => ghimport_new_id(),
                'label'            => $label !== '' ? $label : $parsed['owner'] . '/' . $parsed['repo'],
                'github_url'       => 'https://github.com/' . $parsed['owner'] . '/' . $parsed['repo'],
                'owner'            => $parsed['owner'],
                'repo'             => $parsed['repo'],
                'branch'           => $branchOvr !== '' ? $branchOvr : $parsed['branch'],
                'content_path'     => $contentPath !== '' ? $contentPath : $parsed['content_path'],
                'local_path'       => $localPath,
                'token'            => wps_secret_encrypt($token),
                'enabled'          => true,
                'added_at'         => gmdate('c'),
                'last_synced_at'   => null,
                'last_sync_status' => null,
                'last_sync_count'  => 0,
                'last_sync_errors' => 0,
            ];

            $connections[] = $conn;

            if (ghimport_save_connections($connections)) {
                $success = 'Connection "' . wps_h($conn['label']) . '" added successfully.';
            } else {
                array_pop($connections);
                $error = 'Could not save connection. Make sure platform/data/ is writable.';
            }
        }
    }

    // -- Delete connection ---------------------------------------------------
    elseif ($action === 'delete' && $connId !== '') {
        $target = ghimport_find_connection($connections, $connId);
        if ($target) {
            ghimport_delete_connection($connections, $connId);
            if (ghimport_save_connections($connections)) {
                $success = 'Connection removed.';
            } else {
                $connections = ghimport_load_connections();
                $error = 'Could not save. Check file permissions.';
            }
        }
    }

    // -- Toggle enabled/disabled --------------------------------------------
    elseif ($action === 'toggle' && $connId !== '') {
        foreach ($connections as $i => $c) {
            if (($c['id'] ?? '') === $connId) {
                $connections[$i]['enabled'] = !($connections[$i]['enabled'] ?? true);
                break;
            }
        }
        ghimport_save_connections($connections);
    }

    // -- Test connection -----------------------------------------------------
    elseif ($action === 'test' && $connId !== '') {
        $target = ghimport_find_connection($connections, $connId);
        if ($target) {
            $testResult = ghimport_test_connection($target);
            $testResult['conn_label'] = $target['label'] ?? $connId;
            $activeConnId = $connId;
        } else {
            $error = 'Connection not found.';
        }
    }

    // -- Sync one connection -------------------------------------------------
    elseif ($action === 'sync_one' && $connId !== '') {
        if (!$localRoot) {
            $error = 'Could not resolve local root directory.';
        } else {
            $target = ghimport_find_connection($connections, $connId);
            if ($target) {
                $syncResults  = ghimport_sync_connection($target, $localRoot);
                $summary      = ghimport_results_summary($syncResults);
                $activeConnId = $connId;

                foreach ($connections as $i => $c) {
                    if (($c['id'] ?? '') === $connId) {
                        $connections[$i]['last_synced_at']   = gmdate('c');
                        $connections[$i]['last_sync_status'] = $summary['status'];
                        $connections[$i]['last_sync_count']  = $summary['created'] + $summary['updated'];
                        $connections[$i]['last_sync_errors'] = $summary['error'];
                        break;
                    }
                }
                ghimport_save_connections($connections);
                wps_archive_index_invalidate();

                $written = $summary['created'] + $summary['updated'];
                if ($summary['status'] === 'ok') {
                    $success = 'Sync complete — ' . $written . ' file(s) written.';
                } elseif ($summary['status'] === 'partial') {
                    $success = 'Sync complete with ' . $summary['error'] . ' error(s). ' . $written . ' file(s) written.';
                } else {
                    $error = 'Sync failed with ' . $summary['error'] . ' error(s). No files were written.';
                }
            } else {
                $error = 'Connection not found.';
            }
        }
    }

    // -- Sync all enabled connections ----------------------------------------
    elseif ($action === 'sync_all') {
        if (!$localRoot) {
            $error = 'Could not resolve local root directory.';
        } else {
            $totalWritten = 0;
            $totalErrors  = 0;
            $ranCount     = 0;

            foreach ($connections as $i => $conn) {
                if (!($conn['enabled'] ?? true)) {
                    continue;
                }
                $results     = ghimport_sync_connection($conn, $localRoot);
                $summary     = ghimport_results_summary($results);
                $syncResults = array_merge($syncResults, $results);

                $connections[$i]['last_synced_at']   = gmdate('c');
                $connections[$i]['last_sync_status'] = $summary['status'];
                $connections[$i]['last_sync_count']  = $summary['created'] + $summary['updated'];
                $connections[$i]['last_sync_errors'] = $summary['error'];

                $totalWritten += $summary['created'] + $summary['updated'];
                $totalErrors  += $summary['error'];
                $ranCount++;
            }

            ghimport_save_connections($connections);
            wps_archive_index_invalidate();

            if ($ranCount === 0) {
                $success = 'No enabled connections to sync.';
            } elseif ($totalErrors === 0) {
                $success = 'All ' . $ranCount . ' connection(s) synced — ' . $totalWritten . ' file(s) written.';
            } else {
                $success = $totalWritten . ' file(s) written across ' . $ranCount . ' connection(s); '
                    . $totalErrors . ' error(s) occurred.';
            }
        }
    }
}

$enabledConnections = count(array_filter($connections, fn($c) => $c['enabled'] ?? true));
$totalConnections = count($connections);
$lastSyncedAt = null;
foreach ($connections as $connection) {
    if (!empty($connection['last_synced_at']) && ($lastSyncedAt === null || $connection['last_synced_at'] > $lastSyncedAt)) {
        $lastSyncedAt = $connection['last_synced_at'];
    }
}

$latestEnabledConnectionId = null;
$latestEnabledConnectionSync = null;
foreach ($connections as $connection) {
    if (!(bool) ($connection['enabled'] ?? true)) {
        continue;
    }

    $syncAt = $connection['last_synced_at'] ?? null;
    if ($syncAt && ($latestEnabledConnectionSync === null || $syncAt > $latestEnabledConnectionSync)) {
        $latestEnabledConnectionSync = $syncAt;
        $latestEnabledConnectionId = $connection['id'] ?? null;
    }
}

wps_render_header('GitHub Import');
?>

<section class="panel hero">
    <p class="eyebrow">Addon</p>
    <h1>GitHub Import</h1>
    <p class="lead">Connect one or more GitHub repositories and sync their content into this installation. Each connection is independent — configure its branch, content path, and local target separately.</p>

    <?php if (!empty($connections) || !empty($syncResults)): ?>
    <div class="actions" style="margin-top:20px;">
        <a class="button-secondary" href="settings.php">Back to Settings</a>
    </div>
    <?php else: ?>
    <div class="actions" style="margin-top:20px;">
        <a class="button-secondary" href="settings.php">Back to Settings</a>
    </div>
    <?php endif; ?>
</section>


<?php if ($error): ?>
    <div class="alert alert-error"><?php echo wps_h($error); ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo wps_h($success); ?></div>
<?php endif; ?>

<?php // -- Test result -------------------------------------------------------
if ($testResult !== null): ?>
<section class="panel">
    <h2>Test Result — <?php echo wps_h($testResult['conn_label']); ?></h2>
    <?php if ($testResult['ok']): ?>
        <div class="alert alert-success"><?php echo wps_h($testResult['message']); ?></div>
        <?php if (!empty($testResult['items'])): ?>
        <ul class="ghimport-item-list">
            <?php foreach ($testResult['items'] as $item): ?>
                <li>
                    <span class="ghimport-type-badge <?php echo $item['type'] === 'dir' ? 'type-dir' : 'type-file'; ?>">
                        <?php echo wps_h($item['type']); ?>
                    </span>
                    <?php echo wps_h($item['name']); ?>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    <?php else: ?>
        <div class="alert alert-error"><?php echo wps_h($testResult['message']); ?></div>
    <?php endif; ?>
</section>
<?php endif; ?>

<?php // -- Sync results -------------------------------------------------------
if (!empty($syncResults)): ?>
<section class="panel">
    <h2>Sync Results</h2>
    <?php
    $summary = ghimport_results_summary($syncResults);
    $written = $summary['created'] + $summary['updated'];
    ?>
    <div class="status-grid ghimport-summary-grid">
        <div class="status-card">
            <strong>Written</strong>
            <span><?php echo $written; ?> file(s)</span>
        </div>
        <div class="status-card">
            <strong>Unchanged</strong>
            <span><?php echo $summary['unchanged']; ?> file(s)</span>
        </div>
        <?php if ($summary['deleted'] > 0): ?>
        <div class="status-card">
            <strong>Deleted</strong>
            <span><?php echo $summary['deleted']; ?> file(s)</span>
        </div>
        <?php endif; ?>
        <div class="status-card">
            <strong>Errors</strong>
            <span><?php echo $summary['error']; ?> error(s)</span>
        </div>
    </div>
    <div class="result-box" style="margin-top:16px;">
        <ul class="ghimport-result-list">
            <?php foreach ($syncResults as $item): ?>
            <li class="ghimport-result-item ghimport-status-<?php echo wps_h($item['status']); ?>">
                <span class="ghimport-result-status"><?php echo wps_h(strtoupper($item['status'])); ?></span>
                <span class="ghimport-result-path"><?php echo wps_h($item['path']); ?></span>
                <span class="ghimport-result-msg"><?php echo wps_h($item['message']); ?></span>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
<?php endif; ?>

<?php // -- Connection list ---------------------------------------------------
if (!empty($connections)): ?>
<section class="panel">
    <h2>Connections</h2>
    <div class="ghimport-conn-list">
        <?php foreach ($connections as $conn):
            $connId   = $conn['id'] ?? '';
            $enabled  = $conn['enabled'] ?? true;
            $lastSync = $conn['last_synced_at'] ?? null;
            $syncStat = $conn['last_sync_status'] ?? null;
            $label    = $conn['label'] ?? ($conn['owner'] . '/' . $conn['repo']);

            $badgeClass = 'badge-neutral';
            $badgeText  = 'Never synced';
            if ($syncStat === 'ok')      { $badgeClass = 'badge-ok';      $badgeText = 'OK'; }
            if ($syncStat === 'partial') { $badgeClass = 'badge-warning';  $badgeText = 'Partial'; }
            if ($syncStat === 'error')   { $badgeClass = 'badge-error';    $badgeText = 'Error'; }
            if (!$enabled)               { $badgeClass = 'badge-disabled'; $badgeText = 'Disabled'; }
        ?>
        <div class="ghimport-conn-card <?php echo !$enabled ? 'conn-disabled' : ''; ?>">
            <div class="ghimport-conn-header">
                <div class="ghimport-conn-title">
                    <h3><?php echo wps_h($label); ?></h3>
                    <?php if ($enabled && $latestEnabledConnectionId !== null && $connId === $latestEnabledConnectionId): ?>
                        <span class="ghimport-badge badge-ok">Active sync connection</span>
                    <?php endif; ?>
                    <span class="ghimport-badge <?php echo $badgeClass; ?>"><?php echo wps_h($badgeText); ?></span>
                </div>
            </div>

            <dl class="ghimport-conn-meta">
                <dt>Repository</dt>
                <dd>
                    <a href="<?php echo wps_h($conn['github_url'] ?? '#'); ?>" target="_blank" rel="noopener noreferrer">
                        <?php echo wps_h(($conn['owner'] ?? '') . '/' . ($conn['repo'] ?? '')); ?>
                    </a>
                    &nbsp;&middot;&nbsp; branch: <code><?php echo wps_h($conn['branch'] ?? 'main'); ?></code>
                </dd>

                <?php if (!empty($conn['content_path'])): ?>
                <dt>Content path</dt>
                <dd><code><?php echo wps_h($conn['content_path']); ?></code></dd>
                <?php endif; ?>

                <dt>Local target</dt>
                <dd>
                    <?php if (!empty($conn['local_path'])): ?>
                        <code><?php echo wps_h($conn['local_path']); ?></code>
                    <?php elseif (!empty($conn['content_path'])): ?>
                        <code><?php echo wps_h($conn['content_path']); ?></code>
                        <span class="muted"> (mirrored from repo)</span>
                    <?php else: ?>
                        <span class="muted">Repo root</span>
                    <?php endif; ?>
                </dd>

                <dt>Last synced</dt>
                <dd>
                    <?php if ($lastSync): ?>
                        <?php echo wps_h(date('Y-m-d H:i', strtotime($lastSync))); ?> UTC
                        <?php if ($enabled && $latestEnabledConnectionId !== null && $connId === $latestEnabledConnectionId): ?>
                            &nbsp;&middot;&nbsp;<strong>latest active sync</strong>
                        <?php endif; ?>
                        <?php if ($conn['last_sync_count'] > 0 || $conn['last_sync_errors'] > 0): ?>
                            &nbsp;&middot;&nbsp;
                            <?php echo (int) $conn['last_sync_count']; ?> written,
                            <?php echo (int) $conn['last_sync_errors']; ?> errors
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="muted">Never</span>
                    <?php endif; ?>
                </dd>

                <?php if (!empty($conn['token'])): ?>
                <dt>Token</dt>
                <dd><span class="muted">Configured (hidden)</span></dd>
                <?php endif; ?>
            </dl>

            <div class="ghimport-conn-actions">
                <form method="post" style="display:contents;">
                    <?php echo wps_csrf_field(); ?>
                    <input type="hidden" name="conn_id" value="<?php echo wps_h($connId); ?>">
                    <input type="hidden" name="action" value="test">
                    <button type="submit" class="button-secondary">Test</button>
                </form>

                <?php if ($enabled): ?>
                <form method="post" style="display:contents;">
                    <?php echo wps_csrf_field(); ?>
                    <input type="hidden" name="conn_id" value="<?php echo wps_h($connId); ?>">
                    <input type="hidden" name="action" value="sync_one">
                    <button type="submit">Sync Now</button>
                </form>
                <?php endif; ?>

                <form method="post" style="display:contents;">
                    <?php echo wps_csrf_field(); ?>
                    <input type="hidden" name="conn_id" value="<?php echo wps_h($connId); ?>">
                    <input type="hidden" name="action" value="toggle">
                    <button type="submit" class="button-secondary">
                        <?php echo $enabled ? 'Disable' : 'Enable'; ?>
                    </button>
                </form>

                <form method="post" style="display:contents;" onsubmit="return confirm('Remove this connection? Files already synced are not deleted.');">
                    <?php echo wps_csrf_field(); ?>
                    <input type="hidden" name="conn_id" value="<?php echo wps_h($connId); ?>">
                    <input type="hidden" name="action" value="delete">
                    <button type="submit" class="button-secondary btn-danger">Remove</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php else: ?>
<section class="panel muted-panel">
    <h2>No connections yet</h2>
    <p class="muted">Add a GitHub repository below to start importing content.</p>
</section>
<?php endif; ?>

<?php // -- Add connection form -----------------------------------------------
?>
<section class="panel">
    <h2>Add Connection</h2>
    <p class="muted">Paste any GitHub URL — a repo link, a branch URL, or a deep link to a specific folder. Advanced options let you override the branch, filter by a content path, and control where files land locally.</p>

    <form method="post" class="form grid-form" style="margin-top:20px;">
        <?php echo wps_csrf_field(); ?>
        <input type="hidden" name="action" value="add">

        <label class="full">
            GitHub URL <span class="muted" style="font-weight:400;">(required)</span>
            <input
                type="text"
                name="github_url"
                placeholder="https://github.com/owner/repo  or  owner/repo  or  tree URL with path"
                required
                autocomplete="off"
                spellcheck="false"
            >
            <small>Accepted: full URL, <code>owner/repo</code>, <code>owner/repo@branch</code>, or a tree URL like <code>github.com/owner/repo/tree/main/my-folder</code>.</small>
        </label>

        <label>
            Label <span class="muted" style="font-weight:400;">(optional)</span>
            <input type="text" name="label" placeholder="My Content Repo" autocomplete="off">
            <small>Defaults to owner/repo if left blank.</small>
        </label>

        <label>
            Branch override <span class="muted" style="font-weight:400;">(optional)</span>
            <input type="text" name="branch" placeholder="main" autocomplete="off" spellcheck="false">
            <small>Auto-detected from URL (including most slash-containing branch names from tree links). Use override only when you want to force a different branch.</small>
        </label>

        <label>
            Content path in repo <span class="muted" style="font-weight:400;">(optional)</span>
            <input type="text" name="content_path" placeholder="content-system/tours" autocomplete="off" spellcheck="false">
            <small>Folder inside the repo to sync from. Blank syncs the entire repo.</small>
        </label>

        <label>
            Local target path <span class="muted" style="font-weight:400;">(optional)</span>
            <input type="text" name="local_path" placeholder="content-system/tours" autocomplete="off" spellcheck="false">
            <small>Where to write files locally (relative to system root). Blank mirrors the content path.</small>
        </label>

        <label class="full">
            Access token <span class="muted" style="font-weight:400;">(optional)</span>
            <input type="password" name="token" placeholder="ghp_…" autocomplete="new-password" spellcheck="false">
            <small>Required for private repos. Also increases API rate limits for public repos. Encrypted with AES-256-GCM at rest in <code>platform/data/github-imports.json</code>; the encryption key lives in <code>platform/data/.secret-key</code> (excluded from sync, never committed).</small>
        </label>

        <div class="full actions">
            <button type="submit">Add Connection</button>
        </div>
    </form>
</section>

<section class="panel muted-panel">
    <h2>About this Addon</h2>
    <p class="muted">The GitHub Import engine (<code>github-import-engine.php</code>) is a self-contained, dependency-free PHP module. You can download it from the repository and drop it into any PHP 7.4+ project — no framework required.</p>
    <ul class="muted" style="margin:12px 0 0 20px; font-size:14px; line-height:1.8;">
        <li>Supports ZIP-based sync (fast) with API fallback for any host</li>
        <li>Multiple repos, each with independent branch, path, and token config</li>
        <li>Never overwrites <code>platform/data/</code> or other protected paths</li>
        <li>Directory traversal blocked at every write operation</li>
        <li>Private repos supported via personal access tokens (stored locally only)</li>
    </ul>
</section>

<?php wps_render_footer(); ?>
