<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/github.php';
require_once __DIR__ . '/content-loader.php';

wps_require_auth();

$settings = wps_load_settings();
$connection = wps_test_github_connection($settings);
$postsResult = wps_get_posts($settings);
$workflowCounts = ['Published' => 0, 'Needs Review' => 0, 'Revision Required' => 0, 'Blocked' => 0, 'Draft' => 0];
$workflowRows = [];
if ($postsResult['ok']) {
    foreach ($postsResult['posts'] as $post) {
        $status = wps_human_workflow_status($post);
        if (isset($workflowCounts[$status['label']])) {
            $workflowCounts[$status['label']]++;
        }

        $nextAction = 'Continue content generation';
        if ($status['label'] === 'Needs Review') {
            $nextAction = 'Run QA and complete human review';
        } elseif ($status['label'] === 'Revision Required') {
            $nextAction = 'Return to generate step and fix package';
        } elseif ($status['label'] === 'Published') {
            $nextAction = 'Monitor performance and refresh later';
        } elseif ($status['label'] === 'Blocked') {
            $nextAction = 'Resolve clarifications before continuing';
        }

        $workflowRows[] = [
            'title' => (string) ($post['title'] ?? 'Untitled'),
            'slug' => (string) ($post['slug'] ?? ''),
            'publish_status' => (string) ($post['publish_status'] ?? 'draft'),
            'qa_status' => (string) ($post['qa_status'] ?? 'pending'),
            'status_label' => $status['label'],
            'status_tone' => (string) ($status['tone'] ?? 'muted'),
            'status_reason' => wps_status_reason($post),
            'next_action' => $nextAction,
        ];
    }
}

function wps_status_reason(array $post): string
{
    $publish = (string) ($post['publish_status'] ?? 'draft');
    $qa = (string) ($post['qa_status'] ?? 'pending');
    $warnings = $post['meta']['warnings'] ?? [];

    if ($publish === 'published') {
        return 'Live verification is complete and this package is confirmed published.';
    }

    if ($qa === 'needs_clarification') {
        return 'Blocking clarifications are unresolved. Resolve them before final copy can proceed.';
    }

    if ($qa === 'needs_fix' || $publish === 'needs_fix') {
        return 'QA found blocking issues that must be fixed before review or publish.';
    }

    if ($publish === 'ready_for_review') {
        return 'Generated and internally complete, waiting for human review approval.';
    }

    if ($publish === 'ready_for_sync') {
        return 'Approved in repo; still waiting for sync/deployment to live environment.';
    }

    if ($publish === 'needs_live_verification') {
        return 'Synced/approved, but archive and single-post pages are not yet live-verified.';
    }

    if (!empty($warnings) && is_array($warnings)) {
        return 'Still draft with warnings: ' . (string) $warnings[0];
    }

    return 'Draft content package. Generation exists, but review/publish gate is not completed yet.';
}

wps_render_header($settings['archive_title']);
?>

<section class="hero panel">
    <p class="eyebrow">Milano Adventures Blog</p>
    <h1><?php echo wps_h($settings['archive_title']); ?></h1>
    <p><?php echo wps_h($settings['archive_description']); ?></p>
</section>

<section class="panel">
    <h2>Package workflow queue</h2>
    <p class="muted">This queue follows your approved loop: Generate → Review → Fix if needed → Approve for publish → Sync → Live verify.</p>
    <?php if (empty($workflowRows)): ?>
        <p class="muted">No content packages found yet. Run a generation command first.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table class="workflow-table">
                <thead>
                    <tr>
                        <th>Package</th>
                        <th>Status</th>
                        <th>Why this status</th>
                        <th>Next action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($workflowRows as $row): ?>
                        <tr>
                            <td>
                                <strong><a href="edit-post.php?slug=<?php echo rawurlencode($row['slug']); ?>"><?php echo wps_h($row['title']); ?></a></strong><br>
                                <small class="muted"><?php echo wps_h($row['slug']); ?></small>
                            </td>
                            <td><span class="qa-pill qa-pill-<?php echo wps_h($row['status_tone']); ?>"><?php echo wps_h($row['status_label']); ?></span></td>
                            <td>
                                <small><?php echo wps_h($row['status_reason']); ?></small><br>
                                <small class="muted">publish_status=<?php echo wps_h($row['publish_status']); ?> · qa_status=<?php echo wps_h($row['qa_status']); ?></small>
                            </td>
                            <td><?php echo wps_h($row['next_action']); ?><br><a href="edit-post.php?slug=<?php echo rawurlencode($row['slug']); ?>">Open package</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
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
                    <h3><a href="edit-post.php?slug=<?php echo rawurlencode((string) $item['name']); ?>"><?php echo wps_h(ucwords(str_replace('-', ' ', $item['name']))); ?></a></h3>
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
