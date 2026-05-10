<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/github.php';
require_once __DIR__ . '/content-loader.php';

wps_require_auth();

$settings = wps_load_settings();
$connection = wps_test_github_connection($settings);
$postsResult = wps_get_posts($settings);
$workflowCounts = ['Published' => 0, 'Needs Review' => 0, 'Revision Required' => 0, 'Blocked' => 0, 'Draft' => 0];
if ($postsResult['ok']) {
    foreach ($postsResult['posts'] as $post) {
        $status = wps_human_workflow_status($post);
        if (isset($workflowCounts[$status['label']])) {
            $workflowCounts[$status['label']]++;
        }
    }
}

wps_render_header($settings['archive_title']);
?>

<section class="hero panel">
    <p class="eyebrow">Milano Adventures Blog</p>
    <h1><?php echo wps_h($settings['archive_title']); ?></h1>
    <p><?php echo wps_h($settings['archive_description']); ?></p>
</section>

<section class="panel">
    <h2>Workflow snapshot</h2>
    <p class="muted">This is the operational status view for the generate → review → publish loop.</p>
    <div class="status-grid">
        <?php foreach ($workflowCounts as $label => $count): ?>
            <div class="status-card">
                <strong><?php echo wps_h($label); ?></strong>
                <span><?php echo (int) $count; ?> package(s)</span>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="actions">
        <a class="button-secondary" href="qa.php">Open QA Report</a>
        <a class="button-secondary" href="../blog/">Open Blog Archive</a>
    </div>
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
                    <span class="read-more">Use QA Report and Blog Editor to review this package →</span>
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
