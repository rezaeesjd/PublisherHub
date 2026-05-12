<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/content-loader.php';

wps_require_auth();

$registryResult = wps_load_cluster_registry();
$registry = $registryResult['registry'];
$clusters = $registry['clusters'] ?? [];

$totalClusters = count($clusters);
$totalAssets = 0;
$publishedAssets = 0;
$missingRequiredAssets = 0;

foreach ($clusters as $cluster) {
    $score = wps_cluster_completeness($cluster);
    $totalAssets += (int) $score['total'];
    $publishedAssets += (int) $score['published'];
    $missingRequiredAssets += count($score['missing_required']);
}

wps_render_header('Cluster Registry');
?>

<section class="hero panel">
    <p class="eyebrow">Content Funnel Registry</p>
    <h1>Cluster Registry</h1>
    <p>This dashboard reads the same machine-readable JSON registry used by AI generation and QA workflows. No separate dashboard-only state should exist.</p>
</section>

<section class="panel">
    <h2>Cluster snapshot</h2>
    <div class="status-grid">
        <div class="status-card">
            <strong>Total clusters</strong>
            <span><?php echo (int) $totalClusters; ?> cluster(s)</span>
        </div>
        <div class="status-card">
            <strong>Total assets</strong>
            <span><?php echo (int) $totalAssets; ?> tracked asset(s)</span>
        </div>
        <div class="status-card">
            <strong>Published assets</strong>
            <span><?php echo (int) $publishedAssets; ?> published</span>
        </div>
        <div class="status-card">
            <strong>Missing required assets</strong>
            <span><?php echo (int) $missingRequiredAssets; ?> missing</span>
        </div>
    </div>
</section>

<section class="panel">
    <h2>Active clusters</h2>
    <p class="muted">AI agents should read and update this same registry during generation, QA, and publishing workflows.</p>

    <?php if (!$registryResult['ok']): ?>
        <div class="alert alert-error">
            <?php echo wps_h($registryResult['error']); ?>
        </div>
    <?php elseif (empty($clusters)): ?>
        <p class="muted">No clusters registered yet. Generate the first package and update the registry.</p>
    <?php else: ?>
        <div class="post-grid">
            <?php foreach ($clusters as $cluster): ?>
                <?php
                $score = wps_cluster_completeness($cluster);
                $clusterStatus = wps_cluster_status_label($cluster);
                $clusterName = (string) ($cluster['title'] ?? $cluster['cluster_parent'] ?? 'Untitled Cluster');
                $parentSlug = (string) ($cluster['cluster_parent'] ?? '');
                $primarySlug = (string) ($cluster['primary_conversion_asset'] ?? $parentSlug);
                $nextGeneration = (string) ($cluster['next_recommended_generation'] ?? 'Review missing required assets');
                $clusterAssets = array_values(array_filter(($cluster['assets'] ?? []), 'is_array'));
                ?>
                <article class="post-card" id="cluster-<?php echo wps_h($parentSlug); ?>">
                    <p class="post-label">Cluster</p>
                    <h3><?php echo wps_h($clusterName); ?></h3>

                    <div class="post-meta">
                        <span><?php echo wps_h($clusterStatus); ?></span>
                        <span><?php echo (int) $score['created']; ?>/<?php echo (int) $score['total']; ?> assets</span>
                        <span><?php echo (int) $score['published']; ?> published</span>
                    </div>

                    <p><strong>Primary conversion asset:</strong><br>
                        <a href="edit-post.php?slug=<?php echo rawurlencode($primarySlug); ?>"><?php echo wps_h($primarySlug); ?></a>
                    </p>

                    <p><strong>Next recommended generation:</strong><br><?php echo wps_h($nextGeneration); ?></p>

                    <?php if (!empty($clusterAssets)): ?>
                        <p><strong>Cluster assets and relations:</strong></p>
                        <ul class="cluster-asset-list">
                            <?php foreach ($clusterAssets as $asset): ?>
                                <?php
                                $assetSlug = (string) ($asset['package_slug'] ?? '');
                                $assetTitle = (string) ($asset['title'] ?? $assetSlug);
                                $assetStatus = (string) ($asset['status'] ?? 'not_started');
                                $assetType = (string) ($asset['cluster_type'] ?? '');
                                $assetRole = (string) ($asset['cluster_role'] ?? '');
                                $assetTarget = (string) ($asset['next_step_link_target'] ?? '');
                                $assetIsPrimary = $assetSlug !== '' && $assetSlug === $primarySlug;
                                $isExistingPackage = $assetSlug !== ''
                                    && preg_match('/^[a-z0-9][a-z0-9-]*$/', $assetSlug)
                                    && is_dir(WPS_LOCAL_CONTENT_DIR . '/' . $assetSlug);
                                ?>
                                <li>
                                    <span class="qa-pill qa-pill-muted"><?php echo wps_h($assetType ?: 'asset'); ?></span>
                                    <strong>
                                        <?php if ($isExistingPackage): ?>
                                            <a href="edit-post.php?slug=<?php echo rawurlencode($assetSlug); ?>"><?php echo wps_h($assetTitle); ?></a>
                                        <?php else: ?>
                                            <?php echo wps_h($assetTitle); ?>
                                        <?php endif; ?>
                                    </strong>
                                    <?php if ($assetIsPrimary): ?> <em>· primary conversion</em><?php endif; ?>
                                    <br>
                                    <small class="muted">
                                        role=<?php echo wps_h($assetRole); ?> · status=<?php echo wps_h($assetStatus); ?>
                                        <?php if ($assetTarget !== ''): ?>
                                            · links to <?php echo wps_h($assetTarget); ?>
                                        <?php endif; ?>
                                        <?php if (!$isExistingPackage && $assetSlug !== ''): ?>
                                            · <em>not yet generated</em>
                                        <?php endif; ?>
                                    </small>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <?php if (!empty($score['missing_required'])): ?>
                        <p><strong>Missing required:</strong></p>
                        <ul>
                            <?php foreach ($score['missing_required'] as $missing): ?>
                                <li><?php echo wps_h($missing); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <div class="card-actions">
                        <span class="muted">parent: <?php echo wps_h($parentSlug); ?></span>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<section class="panel muted-panel">
    <h2>Source of truth</h2>
    <p>The dashboard and AI workflows must both use <code>content-system/clusters/cluster-registry.json</code> as the operational source of truth. Avoid maintaining duplicate state in markdown, dashboard-only storage, or separate AI memory.</p>
</section>

<?php wps_render_footer(); ?>
