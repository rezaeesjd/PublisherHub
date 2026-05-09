<?php
require_once __DIR__ . '/auth.php';

wps_require_auth();

$settings = wps_load_settings();
$error = '';
$success = '';

$archiveSlug = wps_archive_slug_from_setting($settings);
$archivePrefix = rtrim(wps_system_url_base(), '/') . '/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    wps_csrf_validate_or_die();
    $settings['site_name'] = trim($_POST['site_name'] ?? $settings['site_name']);
    $settings['archive_title'] = trim($_POST['archive_title'] ?? $settings['archive_title']);
    $settings['archive_description'] = trim($_POST['archive_description'] ?? $settings['archive_description']);
    $rawArchiveSlug = trim((string) ($_POST['archive_slug'] ?? $archiveSlug));
    $cleanArchiveSlug = wps_sanitize_archive_slug($rawArchiveSlug);
    $settings['archive_base_url'] = $cleanArchiveSlug === '' ? 'blog' : $cleanArchiveSlug;

    if (wps_save_settings($settings)) {
        wps_ensure_archive_alias($settings);
        $success = 'Settings saved.';
    } else {
        $error = 'Could not save settings. Make sure the platform/data folder is writable.';
    }

    $settings = wps_load_settings();
    $archiveSlug = wps_archive_slug_from_setting($settings);
    $archivePrefix = rtrim(wps_system_url_base(), '/') . '/';
}

wps_render_header('Settings');
?>

<section class="panel">
    <h1>WebPublisherSystem Settings</h1>
    <p class="muted">Configure the public blog archive. Content updates and generated blog files are managed through System Sync and the per-post editor.</p>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo wps_h($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo wps_h($success); ?></div>
    <?php endif; ?>
</section>

<section class="panel">
    <h2>Operations</h2>
    <p>System Sync and GitHub Import work together here. Sync uses active GitHub Import connection(s), refreshes repository files, preserves protected runtime data like <code>platform/data/</code>, and removes stale files deleted from connected repositories. You can also back up local edits and run QA.</p>
    <div class="actions">
        <a class="button-secondary" href="system-sync.php">System Sync</a>
        <a class="button-secondary" href="backup.php">Download Backup</a>
        <a class="button-secondary" href="qa.php">Run QA</a>
    </div>
    <p class="muted">Signed in as <strong><?php echo wps_h(wps_current_admin_email()); ?></strong>. <a href="logout.php">Sign out</a>.</p>
</section>

<?php
// GitHub Import addon — show a summary card if the engine is available.
$ghimportEngineAvailable = file_exists(__DIR__ . '/github-import-engine.php');
if ($ghimportEngineAvailable) {
    if (!function_exists('ghimport_load_connections')) {
        require_once __DIR__ . '/github-import-engine.php';
    }
    $ghConnections   = ghimport_load_connections();
    $ghEnabledCount  = count(array_filter($ghConnections, fn($c) => $c['enabled'] ?? true));
    $ghTotalCount    = count($ghConnections);
    $ghLastSynced    = null;
    foreach ($ghConnections as $c) {
        if (!empty($c['last_synced_at'])) {
            if ($ghLastSynced === null || $c['last_synced_at'] > $ghLastSynced) {
                $ghLastSynced = $c['last_synced_at'];
            }
        }
    }
}
?>

<?php if ($ghimportEngineAvailable): ?>
<section class="panel">
    <h2>GitHub Import</h2>
    <p>Import and sync content from one or more GitHub repositories. Each connection has its own branch, path, and optional access token.</p>

    <div class="status-grid" style="margin:16px 0;">
        <div class="status-card">
            <strong>Connections</strong>
            <span>
                <?php echo $ghTotalCount; ?> configured
                <?php if ($ghTotalCount > 0): ?>
                    &middot; <?php echo $ghEnabledCount; ?> enabled
                <?php endif; ?>
            </span>
        </div>
        <div class="status-card">
            <strong>Last Sync</strong>
            <span>
                <?php if ($ghLastSynced): ?>
                    <?php echo wps_h(date('Y-m-d H:i', strtotime($ghLastSynced))); ?> UTC
                <?php else: ?>
                    Never
                <?php endif; ?>
            </span>
        </div>
    </div>

    <div class="actions">
        <a class="button-secondary" href="github-import.php">Manage GitHub Import</a>
    </div>
</section>
<?php endif; ?>

<section class="panel">
    <h2>Configuration</h2>
    <form method="post" class="form grid-form">
        <?php echo wps_csrf_field(); ?>
        <label>
            Site name
            <input type="text" name="site_name" value="<?php echo wps_h($settings['site_name']); ?>" required>
        </label>

        <label>
            Archive title
            <input type="text" name="archive_title" value="<?php echo wps_h($settings['archive_title']); ?>" required>
        </label>

        <label class="full">
            Archive description
            <textarea name="archive_description" rows="3"><?php echo wps_h($settings['archive_description']); ?></textarea>
        </label>

        <div class="full field-block archive-slug-field">
            <label for="archive_slug">Archive slug</label>
            <div class="url-slug-row">
                <span class="url-slug-prefix" title="<?php echo wps_h($archivePrefix); ?>"><?php echo wps_h($archivePrefix); ?></span>
                <input id="archive_slug" type="text" name="archive_slug" value="<?php echo wps_h($archiveSlug); ?>" placeholder="blog" pattern="[a-zA-Z0-9_\-/]*">
                <span class="url-slug-suffix">/</span>
            </div>
            <small>Enter only the slug, for example <code>blog</code>, <code>blogs2</code>, or <code>travel-guides</code>. It will be created inside WebPublisherSystem.</small>
            <div class="archive-url-actions">
                <a class="button-secondary" href="<?php echo wps_h(wps_archive_url()); ?>" target="_blank" rel="noopener">Open Blog Archive in New Tab</a>
            </div>
        </div>

        <div class="full actions">
            <button type="submit">Save Settings</button>
        </div>
    </form>
</section>

<?php wps_render_footer(); ?>
