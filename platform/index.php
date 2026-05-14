<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/github.php';
require_once __DIR__ . '/content-loader.php';

wps_require_auth();

$settings = wps_load_settings();
$connection = wps_test_github_connection($settings);
$postsResult = wps_get_posts($settings);
$registryResult = wps_load_cluster_registry();
$clusters = array_values(array_filter(($registryResult['registry']['clusters'] ?? []), 'is_array'));

$postsBySlug = [];
if ($postsResult['ok']) {
    foreach ($postsResult['posts'] as $post) {
        $slug = (string) ($post['slug'] ?? '');
        if ($slug !== '') {
            $postsBySlug[$slug] = $post;
        }
    }
}

$workflowCounts = ['Published' => 0, 'Ready for Review' => 0, 'Revision Required' => 0, 'Blocked' => 0, 'Draft' => 0];
$assignedSlugs = [];
$totalClusters = count($clusters);
$totalAssets = 0;
$publishedAssets = 0;
$missingRequiredAssets = 0;
foreach ($clusters as $cluster) {
    $score = wps_cluster_completeness($cluster);
    $totalAssets += (int) $score['total'];
    $publishedAssets += (int) $score['published'];
    $missingRequiredAssets += count($score['missing_required']);
    foreach (($cluster['assets'] ?? []) as $asset) {
        if (!is_array($asset)) {
            continue;
        }
        $slug = trim((string) ($asset['package_slug'] ?? ''));
        if ($slug !== '') {
            $assignedSlugs[$slug] = true;
        }
    }
}
if ($postsResult['ok']) {
    foreach ($postsResult['posts'] as $post) {
        $status = wps_human_workflow_status($post);
        if (isset($workflowCounts[$status['label']])) {
            $workflowCounts[$status['label']]++;
        }
    }
}

function wps_asset_next_action(string $status): string
{
    switch ($status) {
        case 'published':
            return 'Live — monitor and refresh later.';

        case 'ready_for_review':
            return 'Run QA and complete human review.';
        case 'needs_fix':
            return 'Return to generation and fix the package.';
        case 'needs_clarification':
            return 'Resolve blocking clarifications before continuing.';
        case 'draft':
            return 'Continue drafting the package.';
        case 'refresh_needed':
            return 'Refresh stale content.';
        case 'planned':
            return 'Not generated yet — run generation.';
        case 'not_started':
        default:
            return 'Not started — kick off generation.';
    }
}

function wps_asset_status_tone(string $status): string
{
    switch ($status) {
        case 'published':
            return 'success';
        case 'ready_for_review':
        case 'draft':
            return 'warning';
        case 'needs_clarification':
        case 'needs_fix':
            return 'danger';
        case 'planned':
        case 'not_started':
        default:
            return 'muted';
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
    <p class="muted">Combined view of packages on disk and cluster registry coverage. The cluster registry (<code>content-system/clusters/cluster-registry.json</code>) is the source of truth.</p>
    <div class="status-grid">
        <?php foreach ($workflowCounts as $label => $count): ?>
            <div class="status-card">
                <strong><?php echo wps_h($label); ?></strong>
                <span><?php echo (int) $count; ?> package(s)</span>
            </div>
        <?php endforeach; ?>
        <div class="status-card">
            <strong>Tours (clusters)</strong>
            <span><?php echo (int) $totalClusters; ?> tour cluster(s)</span>
        </div>
        <div class="status-card">
            <strong>Tracked assets</strong>
            <span><?php echo (int) $publishedAssets; ?> published / <?php echo (int) $totalAssets; ?> total</span>
        </div>
        <div class="status-card">
            <strong>Missing required</strong>
            <span><?php echo (int) $missingRequiredAssets; ?> required asset(s) missing</span>
        </div>
    </div>
    <div class="actions">
        <a class="button-secondary" href="qa.php">Open QA Report</a>
        <a class="button-secondary" href="../blog/">Open Blog Archive</a>
        <a class="button-secondary" href="preview-archive.php">Open Preview Archive</a>
    </div>
</section>

<section class="panel">
    <h2>Tours, source packages, and content assets</h2>
    <p class="muted">Each cluster shows one source tour package (kept for dashboard/content-generation data) and separate blog assets (TOFU/MOFU/FAQ, etc.) generated from that source.</p>

    <?php if (!($registryResult['ok'] ?? false)): ?>
        <div class="alert alert-error">
            <?php echo wps_h((string) ($registryResult['error'] ?? 'Cluster registry could not be loaded.')); ?>
        </div>
    <?php elseif (empty($clusters)): ?>
        <p class="muted">No tour clusters registered yet. Add the first cluster to <code>content-system/clusters/cluster-registry.json</code>.</p>
    <?php else: ?>
        <?php foreach ($clusters as $cluster): ?>
            <?php
                $score = wps_cluster_completeness($cluster);
                $clusterStatus = wps_cluster_status_label($cluster);
                $clusterName = (string) ($cluster['title'] ?? $cluster['cluster_parent'] ?? 'Untitled Tour');
                $parentSlug = (string) ($cluster['cluster_parent'] ?? '');
                $primarySlug = (string) ($cluster['primary_conversion_asset'] ?? $parentSlug);
                $destination = (string) ($cluster['destination'] ?? '');
                $originCity = (string) ($cluster['origin_city'] ?? '');
                $viatorUrl = (string) ($cluster['viator_url'] ?? '');
                $nextGeneration = (string) ($cluster['next_recommended_generation'] ?? 'Review missing required assets');
                $clusterAssets = array_values(array_filter(($cluster['assets'] ?? []), 'is_array'));
                $sourcePost = $postsBySlug[$primarySlug] ?? null;
                $blogAssets = array_values(array_filter($clusterAssets, static function (array $asset) use ($primarySlug): bool {
                    return (string) ($asset['package_slug'] ?? '') !== $primarySlug;
                }));
            ?>
            <article class="panel cluster-panel" id="cluster-<?php echo wps_h($parentSlug); ?>" style="border:1px solid var(--border); margin-bottom:1.25rem;">
                <header style="display:flex; flex-wrap:wrap; justify-content:space-between; gap:0.75rem; align-items:baseline;">
                    <div>
                        <p class="eyebrow">Tour cluster</p>
                        <h3 style="margin:0;"><?php echo wps_h($clusterName); ?></h3>
                        <p class="muted" style="margin:0.25rem 0 0;">
                            <?php if ($destination !== ''): ?>Destination: <?php echo wps_h($destination); ?><?php endif; ?>
                            <?php if ($originCity !== ''): ?> · From: <?php echo wps_h($originCity); ?><?php endif; ?>
                            <?php if ($parentSlug !== ''): ?> · parent: <code><?php echo wps_h($parentSlug); ?></code><?php endif; ?>
                        </p>
                    </div>
                    <div style="text-align:right;">
                        <span class="qa-pill qa-pill-muted"><?php echo wps_h($clusterStatus); ?></span><br>
                        <small class="muted">
                            <?php echo (int) $score['created']; ?>/<?php echo (int) $score['total']; ?> assets generated
                            · <?php echo (int) $score['published']; ?> published
                        </small>
                    </div>
                </header>

                <p style="margin:0.75rem 0;">
                    <strong>Source tour package:</strong>
                    <?php if (isset($postsBySlug[$primarySlug])): ?>
                        <a href="edit-post.php?slug=<?php echo rawurlencode($primarySlug); ?>"><?php echo wps_h($postsBySlug[$primarySlug]['title'] ?? $primarySlug); ?></a>
                    <?php else: ?>
                        <?php echo wps_h($primarySlug); ?>
                    <?php endif; ?>
                    <?php if ($viatorUrl !== ''): ?>
                        · <a href="<?php echo wps_h($viatorUrl); ?>" target="_blank" rel="noopener">Viator listing</a>
                    <?php endif; ?>
                </p>

                <p style="margin:0 0 0.75rem;"><strong>Next recommended generation:</strong> <?php echo wps_h($nextGeneration); ?></p>

                <div class="table-wrap" style="margin-bottom:0.75rem;">
                    <table class="workflow-table">
                        <thead>
                            <tr>
                                <th>Source package</th>
                                <th>Package role</th>
                                <th>Status</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <?php if ($sourcePost): ?>
                                        <strong><a href="edit-post.php?slug=<?php echo rawurlencode($primarySlug); ?>"><?php echo wps_h($sourcePost['title'] ?? $primarySlug); ?></a></strong>
                                    <?php else: ?>
                                        <strong><?php echo wps_h($primarySlug !== '' ? $primarySlug : 'Unlinked source package'); ?></strong>
                                    <?php endif; ?>
                                    <?php if ($primarySlug !== ''): ?>
                                        <br><small class="muted"><?php echo wps_h($primarySlug); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="qa-pill qa-pill-muted">source_content</span><br>
                                    <small class="muted">Canonical tour data (not a blog asset)</small>
                                </td>
                                <td>
                                    <?php
                                        $sourceStatus = $sourcePost ? (string) ($sourcePost['publish_status'] ?? 'draft') : 'not_started';
                                        $sourceTone = wps_asset_status_tone($sourceStatus);
                                    ?>
                                    <span class="qa-pill qa-pill-<?php echo wps_h($sourceTone); ?>"><?php echo wps_h(wps_human_publish_status($sourceStatus)); ?></span>
                                </td>
                                <td><small class="muted">Used for dashboard facts and future cluster blog generation.</small></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <?php if (!empty($blogAssets)): ?>
                    <div class="table-wrap">
                        <table class="workflow-table">
                            <thead>
                                <tr>
                                    <th>Blog content asset</th>
                                    <th>Type / role</th>
                                    <th>Status</th>
                                    <th>What's needed next</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($blogAssets as $asset): ?>
                                    <?php
                                        $assetSlug = (string) ($asset['package_slug'] ?? '');
                                        $assetTitle = (string) ($asset['title'] ?? $assetSlug ?: 'Untitled content');
                                        $assetStatus = (string) ($asset['status'] ?? 'not_started');
                                        $assetType = (string) ($asset['cluster_type'] ?? '');
                                        $assetRole = (string) ($asset['cluster_role'] ?? '');
                                        $assetRequired = !empty($asset['required']);
                                        $assetNotes = trim((string) ($asset['notes'] ?? ''));
                                        $packageExists = $assetSlug !== ''
                                            && preg_match('/^[a-z0-9][a-z0-9-]*$/', $assetSlug)
                                            && is_dir(WPS_LOCAL_CONTENT_DIR . '/' . $assetSlug);
                                        $tone = wps_asset_status_tone($assetStatus);
                                        $nextAction = wps_asset_next_action($assetStatus);
                                    ?>
                                    <tr>
                                        <td>
                                            <strong>
                                                <?php if ($packageExists): ?>
                                                    <a href="edit-post.php?slug=<?php echo rawurlencode($assetSlug); ?>"><?php echo wps_h($assetTitle); ?></a>
                                                <?php else: ?>
                                                    <?php echo wps_h($assetTitle); ?>
                                                <?php endif; ?>
                                            </strong>
                                            <?php if ($assetRequired): ?> <small class="muted">· required</small><?php endif; ?>
                                            <?php if ($assetSlug !== ''): ?>
                                                <br><small class="muted"><?php echo wps_h($assetSlug); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="qa-pill qa-pill-muted"><?php echo wps_h($assetType ?: 'asset'); ?></span><br>
                                            <small class="muted"><?php echo wps_h($assetRole ?: '—'); ?></small>
                                        </td>
                                        <td><span class="qa-pill qa-pill-<?php echo wps_h($tone); ?>"><?php echo wps_h(wps_human_publish_status($assetStatus)); ?></span></td>
                                        <td>
                                            <?php echo wps_h($nextAction); ?>
                                            <?php if ($assetNotes !== ''): ?>
                                                <br><small class="muted"><?php echo wps_h($assetNotes); ?></small>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="muted">No assets defined for this tour yet.</p>
                <?php endif; ?>

                <?php if (!empty($score['missing_required'])): ?>
                    <p style="margin-top:0.75rem;"><strong>Missing required:</strong>
                        <?php echo wps_h(implode(', ', $score['missing_required'])); ?>
                    </p>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</section>

<?php
    $unclusteredPosts = [];
    $retiredVariantCount = 0;
    if ($postsResult['ok']) {
        foreach ($postsResult['posts'] as $post) {
            $slug = (string) ($post['slug'] ?? '');
            $folder = (string) ($post['folder_name'] ?? '');
            $assigned = isset($assignedSlugs[$slug]) || isset($assignedSlugs[$folder]);
            if (!$assigned) {
                $post['is_retired_variant'] = wps_is_retired_variant_slug($slug)
                    || wps_is_retired_variant_slug($folder);
                if ($post['is_retired_variant']) {
                    $retiredVariantCount++;
                }
                $unclusteredPosts[] = $post;
            }
        }
    }
?>

<?php if (!empty($unclusteredPosts)): ?>
<section class="panel">
    <h2>Packages not linked to any tour</h2>
    <p class="muted">These packages exist on disk but aren't referenced in the cluster registry. Either link them to a tour cluster or remove them.</p>
    <?php if ($retiredVariantCount > 0): ?>
        <div class="alert alert-error">
            <strong><?php echo (int) $retiredVariantCount; ?> retired <code>-vN</code> variant clone(s) found.</strong>
            The <code>-vN</code> variant mechanism was retired — re-running generation now creates a
            distinct, typed cluster asset, not a numbered clone. Delete these package directories from
            <code>content-system/tours/</code>.
        </div>
    <?php endif; ?>
    <div class="table-wrap">
        <table class="workflow-table">
            <thead>
                <tr>
                    <th>Package</th>
                    <th>Status</th>
                    <th>Why this status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($unclusteredPosts as $post): ?>
                    <?php
                        $status = wps_human_workflow_status($post);
                        $slug = (string) ($post['slug'] ?? '');
                        $isRetiredVariant = !empty($post['is_retired_variant']);
                    ?>
                    <tr>
                        <td>
                            <strong><a href="edit-post.php?slug=<?php echo rawurlencode($slug); ?>"><?php echo wps_h($post['title'] ?? 'Untitled'); ?></a></strong><br>
                            <small class="muted"><?php echo wps_h($slug); ?></small>
                        </td>
                        <td>
                            <?php if ($isRetiredVariant): ?>
                                <span class="qa-pill qa-pill-danger">Retired variant clone</span>
                            <?php else: ?>
                                <span class="qa-pill qa-pill-<?php echo wps_h($status['tone'] ?? 'muted'); ?>"><?php echo wps_h($status['label']); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($isRetiredVariant): ?>
                                <small class="muted"><strong>Hard error:</strong> retired <code>-vN</code> variant clone. Delete this package — do not link it to a cluster.</small>
                            <?php else: ?>
                                <small class="muted">publish_status=<?php echo wps_h((string) $post['publish_status']); ?> · qa_status=<?php echo wps_h((string) $post['qa_status']); ?></small>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php endif; ?>

<section class="panel">
    <h2>Archive setup status</h2>
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

<?php wps_render_footer(); ?>
