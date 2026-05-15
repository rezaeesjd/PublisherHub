<?php
const WPS_ASSET_BASE = '.';
const WPS_SETTINGS_URL = 'settings.php';

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/content-loader.php';
require_once __DIR__ . '/post-overrides.php';

wps_require_auth();

$settings = wps_load_settings();
$slug = trim($_GET['slug'] ?? $_POST['base_slug'] ?? '');
$error = '';
$success = '';

$postResult = $slug ? wps_find_post_by_public_or_base_slug($settings, $slug) : ['ok' => false, 'error' => 'Missing post slug.', 'post' => null];
$post = $postResult['post'] ?? null;

function wps_edit_get_file_value(array $post, string $fileName): string
{
    $folderPath = $post['folder_path'] ?? '';
    if (!$folderPath) {
        return '';
    }

    $file = wps_read_local_file($folderPath . '/' . $fileName);
    return $file['ok'] ? (string) $file['content'] : '';
}

$baseSlug = $post ? (string) ($post['base_slug'] ?? $post['slug'] ?? '') : '';
$publicSlug = $post ? (string) ($post['public_slug'] ?? $baseSlug) : '';
$override = $post ? wps_load_post_override($baseSlug) : [];
$editValues = [
    'public_slug' => $override['public_slug'] ?? $publicSlug,
    'title' => $post['title'] ?? '',
    'meta_description' => $post['meta_description'] ?? '',
    'primary_keyword' => $post['primary_keyword'] ?? '',
    'funnel_stage' => $post['funnel_stage'] ?? '',
    'product_reference_code' => $post['product_reference_code'] ?? '',
    'blog_content' => $override['blog_content'] ?? ($post ? wps_edit_get_file_value($post, 'blog-post.md') : ''),
    'faq_content' => $override['faq_content'] ?? ($post ? wps_edit_get_file_value($post, 'faq.md') : ''),
];

$sourceFactsContent = $post ? wps_edit_get_file_value($post, 'source-facts.md') : '';
$briefContent = $post ? wps_edit_get_file_value($post, 'brief.md') : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $post) {
    wps_csrf_validate_or_die();

    $submittedPublicSlug = wps_post_safe_slug((string) ($_POST['public_slug'] ?? ''));
    $saveData = [
        'public_slug' => $submittedPublicSlug !== '' ? $submittedPublicSlug : $baseSlug,
        'title' => trim((string) ($_POST['title'] ?? '')),
        'meta_description' => trim((string) ($_POST['meta_description'] ?? '')),
        'primary_keyword' => trim((string) ($_POST['primary_keyword'] ?? '')),
        'funnel_stage' => trim((string) ($_POST['funnel_stage'] ?? '')),
        'product_reference_code' => trim((string) ($_POST['product_reference_code'] ?? '')),
        'blog_content' => (string) ($_POST['blog_content'] ?? ''),
        'faq_content' => (string) ($_POST['faq_content'] ?? ''),
    ];

    if ($saveData['title'] === '') {
        $error = 'Title is required.';
    } elseif ($saveData['blog_content'] === '') {
        $error = 'Blog content is required.';
    } elseif ($submittedPublicSlug !== '' && wps_public_slug_in_use($settings, $submittedPublicSlug, $baseSlug)) {
        $error = 'That public slug is already used by another post. Choose a different slug.';
        $editValues = $saveData;
    } else {
        $saveResult = wps_save_post_override($baseSlug, $saveData);
        if ($saveResult['ok']) {
            $success = 'Blog post changes saved locally.';
            $post = wps_apply_post_override(array_merge($post, $saveData));
            $baseSlug = (string) ($post['base_slug'] ?? $baseSlug);
            $publicSlug = (string) ($post['public_slug'] ?? $saveData['public_slug']);
            $editValues = $saveData;
        } else {
            $error = $saveResult['error'] ?: 'Could not save this blog post. Make sure platform/data/ is writable.';
            $editValues = $saveData;
        }
    }
}

$clusterIndex = wps_index_tour_clusters();
$clusterEntry = $post ? ($clusterIndex['by_package_slug'][$baseSlug] ?? null) : null;
$cluster = is_array($clusterEntry) ? ($clusterEntry['cluster'] ?? null) : null;
$clusterAsset = is_array($clusterEntry) ? ($clusterEntry['asset'] ?? null) : null;
$clusterPrimarySlug = is_array($cluster) ? (string) ($cluster['primary_conversion_asset'] ?? $cluster['cluster_parent'] ?? '') : '';
$isClusterParent = $post && $cluster && $baseSlug === $clusterPrimarySlug;

$pageTitle = $post
    ? ($isClusterParent
        ? 'Tour: ' . ($cluster['title'] ?? $post['title'] ?? 'Tour')
        : 'Edit: ' . ($post['title'] ?? 'Blog Post'))
    : 'Edit Blog Post';
wps_render_header($pageTitle);
?>

<?php if (!$postResult['ok'] || !$post): ?>
    <section class="panel">
        <h1>Post not found</h1>
        <div class="alert alert-error"><?php echo wps_h($postResult['error'] ?? 'Post not found.'); ?></div>
        <a class="button-secondary" href="<?php echo wps_h(wps_archive_url()); ?>">Back to Blog Archive</a>
    </section>
<?php elseif ($isClusterParent): ?>
    <?php
        $clusterTitle = (string) ($cluster['title'] ?? $post['title'] ?? 'Tour');
        $parentSlug = (string) ($cluster['cluster_parent'] ?? '');
        $destination = (string) ($cluster['destination'] ?? '');
        $originCity = (string) ($cluster['origin_city'] ?? '');
        $viatorUrl = (string) ($cluster['viator_url'] ?? '');
        $tripadvisorUrl = (string) ($cluster['tripadvisor_url'] ?? '');
        $websiteUrl = (string) ($cluster['website_url'] ?? '');
        $nextGeneration = (string) ($cluster['next_recommended_generation'] ?? '');
        $score = wps_cluster_completeness($cluster);
        $clusterStatus = wps_cluster_status_label($cluster);
        $blogAssets = array_values(array_filter(
            ($cluster['assets'] ?? []),
            static function ($asset) use ($baseSlug): bool {
                return is_array($asset) && (string) ($asset['package_slug'] ?? '') !== $baseSlug;
            }
        ));
    ?>
    <section class="panel">
        <p class="eyebrow">Tour cluster</p>
        <h1><?php echo wps_h($clusterTitle); ?></h1>
        <p class="muted">This is the top-level tour. Source facts and the BOFU booking content live here; supporting blog posts in the cluster are listed below.</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo wps_h($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo wps_h($success); ?></div>
        <?php endif; ?>

        <div class="post-meta">
            <?php if ($parentSlug !== ''): ?><span>Cluster parent: <?php echo wps_h($parentSlug); ?></span><?php endif; ?>
            <?php if ($destination !== ''): ?><span>Destination: <?php echo wps_h($destination); ?></span><?php endif; ?>
            <?php if ($originCity !== ''): ?><span>From: <?php echo wps_h($originCity); ?></span><?php endif; ?>
            <span>Status: <?php echo wps_h($clusterStatus); ?></span>
            <span><?php echo (int) $score['created']; ?>/<?php echo (int) $score['total']; ?> assets generated</span>
        </div>

        <?php if ($viatorUrl !== '' || $tripadvisorUrl !== '' || $websiteUrl !== ''): ?>
            <p style="margin-top:0.75rem;">
                <?php if ($websiteUrl !== '' && strpos($websiteUrl, '{{') === false): ?>
                    <a class="button-secondary" href="<?php echo wps_h($websiteUrl); ?>" target="_blank" rel="noopener">Website booking</a>
                <?php endif; ?>
                <?php if ($viatorUrl !== ''): ?>
                    <a class="button-secondary" href="<?php echo wps_h($viatorUrl); ?>" target="_blank" rel="noopener">Viator listing</a>
                <?php endif; ?>
                <?php if ($tripadvisorUrl !== ''): ?>
                    <a class="button-secondary" href="<?php echo wps_h($tripadvisorUrl); ?>" target="_blank" rel="noopener">TripAdvisor listing</a>
                <?php endif; ?>
            </p>
        <?php endif; ?>

        <?php if ($nextGeneration !== ''): ?>
            <p><strong>Next recommended generation:</strong> <?php echo wps_h($nextGeneration); ?></p>
        <?php endif; ?>
    </section>

    <section class="panel">
        <h2>Blogs in this tour cluster</h2>
        <p class="muted">Each row below is a single blog asset under this tour. Open one to edit it as an individual blog.</p>
        <?php if (empty($blogAssets)): ?>
            <p class="muted">No supporting blog assets defined yet.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table class="workflow-table">
                    <thead>
                        <tr>
                            <th>Blog asset</th>
                            <th>Type / role</th>
                            <th>Status</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($blogAssets as $asset): ?>
                            <?php
                                $assetSlug = (string) ($asset['package_slug'] ?? '');
                                $assetTitle = (string) ($asset['title'] ?? ($assetSlug !== '' ? $assetSlug : 'Untitled content'));
                                $assetStatus = (string) ($asset['status'] ?? 'not_started');
                                $assetType = (string) ($asset['cluster_type'] ?? '');
                                $assetRole = (string) ($asset['cluster_role'] ?? '');
                                $assetRequired = !empty($asset['required']);
                                $assetNotes = trim((string) ($asset['notes'] ?? ''));
                                $packageExists = $assetSlug !== ''
                                    && preg_match('/^[a-z0-9][a-z0-9-]*$/', $assetSlug)
                                    && is_dir(WPS_LOCAL_CONTENT_DIR . '/' . $assetSlug);
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
                                <td><span class="qa-pill qa-pill-<?php echo wps_h(wps_asset_status_tone($assetStatus)); ?>"><?php echo wps_h($assetStatus); ?></span></td>
                                <td><small class="muted"><?php echo wps_h($assetNotes !== '' ? $assetNotes : '—'); ?></small></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <section class="panel">
        <h2>Original tour source content</h2>
        <p class="muted">Read-only canonical tour data from the generated package files. Single blogs in this cluster pull their facts from here.</p>

        <?php
            $meta = is_array($post['meta'] ?? null) ? $post['meta'] : [];
            $factToString = static function ($value): string {
                if (is_array($value)) {
                    return implode(', ', array_map('strval', $value));
                }
                return (string) $value;
            };
            $tourFacts = [
                'Price from' => (string) ($meta['price_from'] ?? ''),
                'Duration' => (string) ($meta['duration_text'] ?? ''),
                'Start time' => (string) ($meta['start_time'] ?? ''),
                'Operating days' => $factToString($meta['operating_days'] ?? ''),
                'Meeting point' => (string) ($meta['meeting_point'] ?? ''),
                'End point' => (string) ($meta['end_point'] ?? ''),
                'Languages' => $factToString($meta['languages'] ?? ''),
                'Max travelers' => isset($meta['max_travelers']) ? (string) $meta['max_travelers'] : '',
                'Max travelers per booking' => isset($meta['max_travelers_per_booking']) ? (string) $meta['max_travelers_per_booking'] : '',
                'Product reference code' => (string) ($meta['product_reference_code'] ?? ''),
            ];
            $tourFacts = array_filter($tourFacts, static fn($v) => trim((string) $v) !== '');
        ?>
        <?php if (!empty($tourFacts)): ?>
            <h3>Tour facts</h3>
            <p class="muted">Structured tour data from <code>meta.json</code>. Itinerary, inclusions and exclusions are in the source facts below.</p>
            <div class="table-wrap" style="margin-bottom:1rem;">
                <table class="workflow-table">
                    <tbody>
                        <?php foreach ($tourFacts as $label => $value): ?>
                            <tr>
                                <th style="width:16rem; text-align:left;"><?php echo wps_h($label); ?></th>
                                <td><?php echo wps_h((string) $value); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <label>
            Source facts (source-facts.md)
            <textarea rows="14" readonly><?php echo wps_h($sourceFactsContent); ?></textarea>
        </label>

        <label>
            Package brief (brief.md)
            <textarea rows="8" readonly><?php echo wps_h($briefContent); ?></textarea>
        </label>
    </section>

    <section class="panel">
        <h2>Main booking content (BOFU)</h2>
        <p class="muted">This is the cluster's primary booking-intent blog post. Edits are saved locally only and do not modify the generated markdown files in GitHub.</p>
        <form method="post" class="form edit-post-form">
            <?php echo wps_csrf_field(); ?>
            <input type="hidden" name="base_slug" value="<?php echo wps_h($baseSlug); ?>">

            <label>
                Public URL slug
                <input type="text" name="public_slug" value="<?php echo wps_h($editValues['public_slug']); ?>" pattern="[a-zA-Z0-9_-]+" required>
                <small>This changes the public single-post URL only. It does not rename any server folder or source content folder.</small>
            </label>

            <label>
                Page title
                <input type="text" name="title" value="<?php echo wps_h($editValues['title']); ?>" required>
            </label>

            <label>
                Meta description / archive excerpt
                <textarea name="meta_description" rows="3"><?php echo wps_h($editValues['meta_description']); ?></textarea>
            </label>

            <div class="grid-form compact-grid">
                <label>
                    Primary keyword
                    <input type="text" name="primary_keyword" value="<?php echo wps_h($editValues['primary_keyword']); ?>">
                </label>

                <label>
                    Funnel stage / label
                    <input type="text" name="funnel_stage" value="<?php echo wps_h($editValues['funnel_stage']); ?>">
                </label>

                <label>
                    Product reference code
                    <input type="text" name="product_reference_code" value="<?php echo wps_h($editValues['product_reference_code']); ?>">
                </label>
            </div>

            <label>
                Blog content markdown
                <textarea name="blog_content" rows="22" required><?php echo wps_h($editValues['blog_content']); ?></textarea>
            </label>

            <label>
                FAQ markdown
                <textarea name="faq_content" rows="12"><?php echo wps_h($editValues['faq_content']); ?></textarea>
            </label>

            <div class="actions">
                <button type="submit">Save Main Booking Post</button>
                <a class="button-secondary" href="../blog/post.php?slug=<?php echo urlencode($publicSlug); ?>">View Post</a>
                <a class="button-secondary" href="<?php echo wps_h(wps_asset_url('index.php')); ?>">Back to Dashboard</a>
            </div>
        </form>
    </section>
<?php else: ?>
    <?php $parentClusterLink = $cluster ? (string) ($cluster['primary_conversion_asset'] ?? $cluster['cluster_parent'] ?? '') : ''; ?>
    <section class="panel">
        <p class="eyebrow">Single blog in tour cluster<?php if ($cluster && !empty($cluster['title'])): ?> · <?php echo wps_h((string) $cluster['title']); ?><?php endif; ?></p>
        <h1>Edit Blog Post</h1>
        <p class="muted">This is a single blog under its parent tour cluster. Edits are saved locally only and do not change other posts or the original generated markdown files in GitHub.</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo wps_h($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo wps_h($success); ?></div>
        <?php endif; ?>

        <div class="post-meta">
            <?php if ($cluster && !empty($cluster['title'])): ?>
                <span>Tour cluster:
                    <?php if ($parentClusterLink !== ''): ?>
                        <a href="edit-post.php?slug=<?php echo rawurlencode($parentClusterLink); ?>"><?php echo wps_h((string) $cluster['title']); ?></a>
                    <?php else: ?>
                        <?php echo wps_h((string) $cluster['title']); ?>
                    <?php endif; ?>
                </span>
            <?php endif; ?>
            <?php if ($clusterAsset): ?>
                <span>Role: <?php echo wps_h((string) ($clusterAsset['cluster_type'] ?? '')); ?> / <?php echo wps_h((string) ($clusterAsset['cluster_role'] ?? '')); ?></span>
            <?php endif; ?>
            <span>Base slug: <?php echo wps_h($baseSlug); ?></span>
            <span>Public slug: <?php echo wps_h($publicSlug); ?></span>
            <?php if (!empty($post['has_local_edits']) || $success): ?>
                <span>Local edits active</span>
            <?php endif; ?>
        </div>
    </section>

    <section class="panel">
        <h2>Original tour source content</h2>
        <p class="muted">These fields are read-only and come from the generated package files used to build this blog.</p>

        <label>
            Source facts (source-facts.md)
            <textarea rows="14" readonly><?php echo wps_h($sourceFactsContent); ?></textarea>
        </label>

        <label>
            Package brief (brief.md)
            <textarea rows="8" readonly><?php echo wps_h($briefContent); ?></textarea>
        </label>
    </section>

    <section class="panel">
        <form method="post" class="form edit-post-form">
            <?php echo wps_csrf_field(); ?>
            <input type="hidden" name="base_slug" value="<?php echo wps_h($baseSlug); ?>">

            <label>
                Public URL slug
                <input type="text" name="public_slug" value="<?php echo wps_h($editValues['public_slug']); ?>" pattern="[a-zA-Z0-9_-]+" required>
                <small>This changes the public single-post URL only. It does not rename any server folder or source content folder.</small>
            </label>

            <label>
                Page title
                <input type="text" name="title" value="<?php echo wps_h($editValues['title']); ?>" required>
            </label>

            <label>
                Meta description / archive excerpt
                <textarea name="meta_description" rows="3"><?php echo wps_h($editValues['meta_description']); ?></textarea>
            </label>

            <div class="grid-form compact-grid">
                <label>
                    Primary keyword
                    <input type="text" name="primary_keyword" value="<?php echo wps_h($editValues['primary_keyword']); ?>">
                </label>

                <label>
                    Funnel stage / label
                    <input type="text" name="funnel_stage" value="<?php echo wps_h($editValues['funnel_stage']); ?>">
                </label>

                <label>
                    Product reference code
                    <input type="text" name="product_reference_code" value="<?php echo wps_h($editValues['product_reference_code']); ?>">
                </label>
            </div>

            <label>
                Blog content markdown
                <textarea name="blog_content" rows="22" required><?php echo wps_h($editValues['blog_content']); ?></textarea>
            </label>

            <label>
                FAQ markdown
                <textarea name="faq_content" rows="12"><?php echo wps_h($editValues['faq_content']); ?></textarea>
            </label>

            <div class="actions">
                <button type="submit">Save This Blog Post</button>
                <a class="button-secondary" href="../blog/post.php?slug=<?php echo urlencode($publicSlug); ?>">View Post</a>
                <?php if ($parentClusterLink !== ''): ?>
                    <a class="button-secondary" href="edit-post.php?slug=<?php echo rawurlencode($parentClusterLink); ?>">Back to Tour Cluster</a>
                <?php endif; ?>
                <a class="button-secondary" href="<?php echo wps_h(wps_archive_url()); ?>">Back to Archive</a>
            </div>
        </form>
    </section>
<?php endif; ?>

<?php wps_render_footer(); ?>
