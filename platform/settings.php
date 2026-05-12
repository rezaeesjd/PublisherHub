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

    $rawAdminEmail = strtolower(trim((string) ($_POST['admin_email'] ?? '')));
    if ($rawAdminEmail !== '' && filter_var($rawAdminEmail, FILTER_VALIDATE_EMAIL)) {
        $settings['admin_email'] = $rawAdminEmail;
    }

    $settings['force_https'] = !empty($_POST['force_https']);

    $pageSize = (int) ($_POST['archive_page_size'] ?? $settings['archive_page_size'] ?? 20);
    $settings['archive_page_size'] = max(5, min(100, $pageSize));

    $rawLogo = trim((string) ($_POST['organization_logo_url'] ?? ''));
    if ($rawLogo === '' || filter_var($rawLogo, FILTER_VALIDATE_URL)) {
        $settings['organization_logo_url'] = $rawLogo;
    }

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
    <p>Use these tools to manage imports, run quality checks, and back up local edits.</p>
    <div class="actions">
        <a class="button-secondary" href="backup.php">Download Backup</a>
        <a class="button-secondary" href="qa.php">Run QA</a>
        <a class="button-secondary" href="github-import.php">Manage GitHub Import</a>
    </div>
    <p class="muted">Signed in as <strong><?php echo wps_h(wps_current_admin_email()); ?></strong>. <a href="logout.php">Sign out</a>.</p>
</section>

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

        <label class="full">
            Admin email (used to sign in)
            <input type="email" name="admin_email" value="<?php echo wps_h((string) ($settings['admin_email'] ?? '')); ?>" autocomplete="off">
            <small>The single email permitted to sign in. Leave blank to keep the current value.</small>
        </label>

        <label class="full">
            Organization logo URL (used in JSON-LD)
            <input type="url" name="organization_logo_url" value="<?php echo wps_h((string) ($settings['organization_logo_url'] ?? '')); ?>" placeholder="https://example.com/logo.png" autocomplete="off">
        </label>

        <label>
            Archive page size
            <input type="number" name="archive_page_size" min="5" max="100" value="<?php echo (int) ($settings['archive_page_size'] ?? 20); ?>">
            <small>How many posts to show per archive page.</small>
        </label>

        <label class="full checkbox-row">
            <input type="checkbox" name="force_https" value="1" <?php echo !empty($settings['force_https']) ? 'checked' : ''; ?>>
            Force HTTPS (301-redirect HTTP requests and emit HSTS)
            <small>Disable on localhost or pre-TLS staging hosts.</small>
        </label>

        <div class="full actions">
            <button type="submit">Save Settings</button>
        </div>
    </form>
</section>

<?php wps_render_footer(); ?>
