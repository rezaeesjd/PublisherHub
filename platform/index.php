<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/github.php';
require_once __DIR__ . '/content-loader.php';

wps_require_auth();

$settings = wps_load_settings();
$connection = wps_test_github_connection($settings);
$postsResult = wps_get_posts($settings);
$clusterIndex = wps_index_tour_clusters();
$workflowCounts = ['Published' => 0, 'Needs Review' => 0, 'Revision Required' => 0, 'Blocked' => 0, 'Draft' => 0];
$workflowRows = [];
$workflowFolderMap = [];
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

        $slug = (string) ($post['slug'] ?? '');
        $clusterMembership = $clusterIndex['by_package_slug'][$slug] ?? null;
        $clusterTitle = '';
        $clusterParent = '';
        $clusterRole = '';
        $clusterIsParent = false;
        if (is_array($clusterMembership)) {
            $clusterTitle = (string) ($clusterMembership['cluster']['title'] ?? '');
            $clusterParent = (string) ($clusterMembership['cluster']['cluster_parent'] ?? '');
            $clusterRole = (string) ($clusterMembership['asset']['cluster_role'] ?? '');
            $clusterIsParent = ((string) ($clusterMembership['cluster']['primary_conversion_asset'] ?? '')) === $slug;
        }

        $workflowFolderMap[$post['folder_name'] ?? $slug] = true;

        $workflowRows[] = [
            'title' => (string) ($post['title'] ?? 'Untitled'),
            'slug' => $slug,
            'publish_status' => (string) ($post['publish_status'] ?? 'draft'),
            'qa_status' => (string) ($post['qa_status'] ?? 'pending'),
            'status_label' => $status['label'],
            'status_tone' => (string) ($status['tone'] ?? 'muted'),
            'status_reason' => wps_status_reason($post),
            'next_action' => $nextAction,
            'cluster_title' => $clusterTitle,
            'cluster_parent' => $clusterParent,
            'cluster_role' => $clusterRole,
            'cluster_is_parent' => $clusterIsParent,
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
                        <th>Cluster</th>
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
                            <td>
                                <?php if ($row['cluster_title'] !== ''): ?>
                                    <a href="clusters.php#cluster-<?php echo wps_h(rawurlencode($row['cluster_parent'])); ?>"><?php echo wps_h($row['cluster_title']); ?></a><br>
                                    <small class="muted"><?php echo wps_h($row['cluster_role']); ?><?php echo $row['cluster_is_parent'] ? ' · cluster parent' : ''; ?></small>
                                <?php else: ?>
                                    <small class="muted">Not yet linked to a cluster</small>
                                <?php endif; ?>
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
    <h2>Content clusters</h2>

    <?php
        $clusterRegistryResult = wps_load_cluster_registry();
        $dashboardClusters = $clusterRegistryResult['registry']['clusters'] ?? [];
    ?>

    <?php if (empty($dashboardClusters)): ?>
        <p class="muted">No clusters are registered yet.</p>
    <?php else: ?>
        <div class="post-grid">
            <?php foreach ($dashboardClusters as $cluster): ?>
                <?php
                    $clusterName = (string) ($cluster['title'] ?? $cluster['cluster_parent'] ?? 'Untitled Cluster');
                    $clusterParent = (string) ($cluster['cluster_parent'] ?? '');
                    $clusterAssets = array_values(array_filter(($cluster['assets'] ?? []), 'is_array'));
                    $generatedAssets = array_values(array_filter($clusterAssets, static function (array $asset): bool {
                        return !in_array((string) ($asset['status'] ?? ''), ['planned', 'not_started'], true);
                    }));
                ?>
                <article class="post-card" id="cluster-<?php echo wps_h($clusterParent); ?>">
                    <p class="post-label">Cluster</p>
                    <h3><?php echo wps_h($clusterName); ?></h3>
                    <p class="muted">Original cluster title: <?php echo wps_h($clusterName); ?></p>
                    <p class="muted"><?php echo count($generatedAssets); ?> generated / <?php echo count($clusterAssets); ?> total contents</p>

                    <details>
                        <summary class="button-secondary" style="display:inline-block; cursor:pointer; margin-top:0.5rem;">Show contents</summary>
                        <div style="margin-top:0.75rem;">
                            <?php if (empty($clusterAssets)): ?>
                                <p class="muted">No content assets defined in this cluster yet.</p>
                            <?php else: ?>
                                <ul class="cluster-asset-list">
                                    <?php foreach ($clusterAssets as $asset): ?>
                                        <?php
                                            $assetTitle = (string) ($asset['title'] ?? $asset['package_slug'] ?? 'Untitled content');
                                            $assetSlug = (string) ($asset['package_slug'] ?? '');
                                            $assetType = (string) ($asset['cluster_type'] ?? 'N/A');
                                            $assetRole = (string) ($asset['cluster_role'] ?? 'N/A');
                                            $assetStatus = (string) ($asset['status'] ?? 'unknown');
                                            $isGenerated = !in_array($assetStatus, ['planned', 'not_started'], true);
                                        ?>
                                        <li>
                                            <strong><?php echo wps_h($assetTitle); ?></strong><br>
                                            <small class="muted">
                                                <?php echo $isGenerated ? 'Generated content' : 'Original/planned content'; ?>
                                                · Type: <?php echo wps_h($assetType); ?>
                                                · Role: <?php echo wps_h($assetRole); ?>
                                                · Status: <?php echo wps_h($assetStatus); ?>
                                            </small>
                                            <?php if ($assetSlug !== ''): ?>
                                                <br><a href="edit-post.php?slug=<?php echo rawurlencode($assetSlug); ?>">Open package</a>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </details>
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
