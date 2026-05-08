<?php
const WPS_ASSET_BASE = '../platform';
const WPS_SETTINGS_URL = '../platform/settings.php';

require_once __DIR__ . '/../platform/auth.php';
require_once __DIR__ . '/../platform/content-loader.php';
require_once __DIR__ . '/../platform/post-overrides.php';

$adminSignedIn = wps_is_logged_in();
$settings = wps_load_settings();
wps_redirect_legacy_blog_path_if_needed($settings);
$slug = trim($_GET['slug'] ?? '');

$samplePost = [
    'slug' => 'sample-cinque-terre-tour-from-milan',
    'base_slug' => 'sample-cinque-terre-tour-from-milan',
    'public_slug' => 'sample-cinque-terre-tour-from-milan',
    'title' => 'Cinque Terre Tour from Milan: Easy Full-Day Coastal Guide',
    'meta_description' => 'Preview how a published Milano Adventures blog post will look with a real archive card, single post layout, CTA section, and FAQ.',
    'primary_keyword' => 'cinque terre tour from milan',
    'funnel_stage' => 'Sample',
    'product_reference_code' => 'Preview',
];

$sampleBlog = "# Cinque Terre Tour from Milan: Easy Full-Day Coastal Guide\n\nWant to see how your published blog posts will look before the GitHub content sync is fully active? This sample article shows the front-end single-post layout, including headings, paragraphs, CTA placement, and FAQ-style content.\n\n## Why this page exists\n\nThis is a demo post for the WebPublisherSystem front-end blog archive. When real generated content is available in GitHub, this sample will be replaced by your actual tour articles.\n\n## What the final blog posts will include\n\n- A clear SEO title and H1\n- A short hook paragraph\n- Practical booking-focused content\n- A mid-page direct booking CTA\n- A final strong CTA\n- FAQ content that helps travelers make a decision\n\n## Sample CTA\n\nReady to check the real tour details? Use the website booking link when it is connected in settings: `{{WebsiteLink}}`\n\n## What happens next\n\nAfter the content system is synced, each generated tour folder will become a real blog card in the archive. Clicking the card will open the full single post page, just like this one.";

$sampleFaq = "# FAQ\n\n## Is this a real published tour article?\nThis is a sample preview article. Real posts will come from the generated content folders in GitHub.\n\n## Will real posts replace this sample?\nYes. Once the archive detects generated posts, it will show the real posts instead of relying on this demo card.\n\n## Where are settings managed?\nArchive and GitHub settings are managed from the platform settings page, not from the public blog archive.";

$isSample = $slug === 'sample-cinque-terre-tour-from-milan';

if ($isSample) {
    $postResult = ['ok' => true, 'error' => '', 'post' => $samplePost];
    $post = $samplePost;
    $contentResult = [
        'ok' => true,
        'error' => '',
        'blog' => wps_replace_placeholders($sampleBlog, $settings),
        'faq' => wps_replace_placeholders($sampleFaq, $settings),
    ];
} else {
    $postResult = $slug ? wps_find_post_by_public_or_base_slug($settings, $slug) : ['ok' => false, 'error' => 'Missing post slug.', 'post' => null];
    $post = $postResult['post'] ?? null;

    $contentResult = $post ? wps_get_post_content($settings, $post) : ['ok' => false, 'error' => $postResult['error'] ?? 'Post not found.', 'blog' => '', 'faq' => ''];

    if ($post && $contentResult['ok']) {
        $override = wps_load_post_override((string) ($post['base_slug'] ?? $post['slug'] ?? ''));
        if (array_key_exists('blog_content', $override)) {
            $contentResult['blog'] = wps_replace_placeholders((string) $override['blog_content'], $settings);
        }
        if (array_key_exists('faq_content', $override)) {
            $contentResult['faq'] = wps_replace_placeholders((string) $override['faq_content'], $settings);
        }
    }
}

$pageTitle = $post['title'] ?? 'Blog Post';
$baseSlug = $post['base_slug'] ?? $post['slug'] ?? '';
$publicSlug = $post['public_slug'] ?? $post['slug'] ?? '';
wps_render_header($pageTitle);
?>

<?php if (!$postResult['ok'] || !$post || !$contentResult['ok']): ?>
    <section class="panel">
        <h1>Post not available</h1>
        <div class="alert alert-error">
            <?php echo wps_h($contentResult['error'] ?: ($postResult['error'] ?? 'Post not found.')); ?>
        </div>
        <a class="button-secondary" href="./">Back to Blog Archive</a>
    </section>
<?php else: ?>
    <article class="panel blog-post">
        <p class="eyebrow"><?php echo wps_h($post['primary_keyword'] ?: 'Travel guide'); ?></p>
        <h1><?php echo wps_h($post['title']); ?></h1>
        <?php if (!empty($post['meta_description'])): ?>
            <p class="lead"><?php echo wps_h($post['meta_description']); ?></p>
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

        <div class="content-body">
            <?php echo wps_markdown_to_html($contentResult['blog']); ?>
        </div>
    </article>

    <?php if (!empty(trim($contentResult['faq']))): ?>
        <section class="panel blog-faq">
            <div class="content-body">
                <?php echo wps_markdown_to_html($contentResult['faq']); ?>
            </div>
        </section>
    <?php endif; ?>

    <section class="panel muted-panel">
        <div class="actions">
            <?php if (!$isSample && $adminSignedIn): ?>
                <a class="button-secondary" href="../platform/edit-post.php?slug=<?php echo urlencode($baseSlug); ?>">Edit This Blog Post</a>
            <?php endif; ?>
            <a class="button-secondary" href="./">← Back to Blog Archive</a>
        </div>
    </section>
<?php endif; ?>

<?php wps_render_footer(); ?>
