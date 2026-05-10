<?php
const WPS_ASSET_BASE = '../platform';
const WPS_SETTINGS_URL = '../platform/settings.php';

require_once __DIR__ . '/../platform/auth.php';
require_once __DIR__ . '/../platform/content-loader.php';
require_once __DIR__ . '/../platform/post-overrides.php';

$adminSignedIn = wps_is_logged_in();
$settings = wps_load_settings();
wps_redirect_legacy_blog_path_if_needed($settings);
$postsResult = wps_get_posts($settings);
if ($postsResult['ok'] && !empty($postsResult['posts'])) {
    $postsResult['posts'] = array_map('wps_apply_post_override', $postsResult['posts']);
}

wps_render_header($settings['archive_title']);
?>

<section class="hero panel">
    <p class="eyebrow">Milano Adventures Blog</p>
    <h1><?php echo wps_h($settings['archive_title']); ?></h1>
    <p><?php echo wps_h($settings['archive_description']); ?></p>
</section>

<section class="panel">
    <h2>Latest Travel Guides</h2>

    <?php if (!$postsResult['ok']): ?>
        <div class="alert alert-error">
            <?php echo wps_h($postsResult['error']); ?>
            <br><a href="../platform/settings.php">Check settings</a>
        </div>
    <?php elseif (empty($postsResult['posts'])): ?>
        <p class="muted">No published tour posts are available yet.</p>
    <?php else: ?>
        <div class="post-grid">
            <?php foreach ($postsResult['posts'] as $post): ?>
                <?php
                    $publicSlug = $post['public_slug'] ?? $post['slug'];
                    $baseSlug = $post['base_slug'] ?? $post['slug'];
                ?>
                <article class="post-card">
                    <p class="post-label"><?php echo wps_h($post['primary_keyword'] ?: 'Travel guide'); ?></p>
                    <h3>
                        <a href="post.php?slug=<?php echo urlencode($publicSlug); ?>">
                            <?php echo wps_h($post['title']); ?>
                        </a>
                    </h3>
                    <?php if (!empty($post['meta_description'])): ?>
                        <p><?php echo wps_h($post['meta_description']); ?></p>
                    <?php endif; ?>
                    <div class="post-meta">
                        <?php if (!empty($post['funnel_stage'])): ?>
                            <span><?php echo wps_h($post['funnel_stage']); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($post['product_reference_code'])): ?>
                            <span>Ref <?php echo wps_h($post['product_reference_code']); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($post['has_local_edits'])): ?>
                            <span>Edited</span>
                        <?php endif; ?>
                    </div>
                    <div class="card-actions">
                        <a class="read-more" href="post.php?slug=<?php echo urlencode($publicSlug); ?>">Read guide →</a>
                        <?php if ($adminSignedIn): ?>
                            <a class="edit-link" href="../platform/edit-post.php?slug=<?php echo urlencode($baseSlug); ?>">Edit</a>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php wps_render_footer(); ?>
