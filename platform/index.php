<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/github.php';

wps_require_auth();

$settings = wps_load_settings();
$connection = wps_test_github_connection($settings);

wps_render_header($settings['archive_title']);
?>

<section class="hero panel">
    <p class="eyebrow">Milano Adventures Blog</p>
    <h1><?php echo wps_h($settings['archive_title']); ?></h1>
    <p><?php echo wps_h($settings['archive_description']); ?></p>
</section>

<section class="panel">
    <h2>Archive setup status</h2>
    <p>This platform is installed and connected to the configured public GitHub repository path.</p>

    <div class="status-grid">
        <div class="status-card">
            <strong>Archive URL</strong>
            <span><?php echo wps_h($settings['archive_base_url'] ?: wps_current_url_base()); ?></span>
        </div>
        <div class="status-card">
            <strong>GitHub source</strong>
            <span><?php echo wps_h($settings['github_owner'] . '/' . $settings['github_repo']); ?></span>
        </div>
        <div class="status-card">
            <strong>Content path</strong>
            <span><?php echo wps_h($settings['github_content_path']); ?></span>
        </div>
    </div>

    <?php if ($connection['ok']): ?>
        <div class="alert alert-success">
            <?php echo wps_h($connection['message']); ?>
        </div>
    <?php else: ?>
        <div class="alert alert-error">
            <?php echo wps_h($connection['message']); ?>
            <br><a href="settings.php">Check settings</a>
        </div>
    <?php endif; ?>
</section>

<section class="panel">
    <h2>Detected content folders</h2>

    <?php if (!$connection['ok']): ?>
        <p>Content folders cannot be loaded until the GitHub connection works.</p>
    <?php elseif (empty($connection['items'])): ?>
        <p>No folders found in the configured GitHub content path yet.</p>
    <?php else: ?>
        <div class="post-grid">
            <?php foreach ($connection['items'] as $item): ?>
                <?php if (($item['type'] ?? '') !== 'dir') { continue; } ?>
                <article class="post-card">
                    <p class="post-label">GitHub folder</p>
                    <h3><?php echo wps_h(ucwords(str_replace('-', ' ', $item['name']))); ?></h3>
                    <p class="muted"><?php echo wps_h($item['path']); ?></p>
                    <span class="read-more">Publishing view will be added in the next phase →</span>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<section class="panel muted-panel">
    <h2>Next phase</h2>
    <p>The next step is adding a sync/publish feature that reads each folder's <code>meta.json</code>, <code>blog-post.md</code>, and <code>faq.md</code>, then creates public blog pages from them.</p>
    <a class="button-secondary" href="settings.php">Open Settings</a>
</section>

<?php wps_render_footer(); ?>
