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

$pageTitle = $post ? 'Edit: ' . ($post['title'] ?? 'Blog Post') : 'Edit Blog Post';
wps_render_header($pageTitle);
?>

<?php if (!$postResult['ok'] || !$post): ?>
    <section class="panel">
        <h1>Post not found</h1>
        <div class="alert alert-error"><?php echo wps_h($postResult['error'] ?? 'Post not found.'); ?></div>
        <a class="button-secondary" href="<?php echo wps_h(wps_archive_url()); ?>">Back to Blog Archive</a>
    </section>
<?php else: ?>
    <section class="panel">
        <p class="eyebrow">Single blog editor</p>
        <h1>Edit Blog Post</h1>
        <p class="muted">These edits are saved locally only for this blog post. They do not change other posts and do not modify the original generated markdown files in GitHub.</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo wps_h($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo wps_h($success); ?></div>
        <?php endif; ?>

        <div class="post-meta">
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
                <a class="button-secondary" href="<?php echo wps_h(wps_archive_url()); ?>">Back to Archive</a>
            </div>
        </form>
    </section>
<?php endif; ?>

<?php wps_render_footer(); ?>
