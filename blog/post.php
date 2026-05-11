<?php
require_once __DIR__ . '/../platform/content-loader.php';

$settings = wps_load_settings();
$slug = trim((string) ($_GET['slug'] ?? ''));
if ($slug === '') {
    http_response_code(404);
    echo 'Post not found.';
    exit;
}

$postResult = wps_find_post_by_slug($settings, $slug);
if (!$postResult['ok'] || !is_array($postResult['post'])) {
    http_response_code(404);
    echo 'Post not found.';
    exit;
}

$post = $postResult['post'];
$contentResult = wps_get_post_content($settings, $post);
if (!$contentResult['ok']) {
    http_response_code(500);
    echo wps_h($contentResult['error']);
    exit;
}

$title = (string) ($post['title'] ?? $slug);
$description = (string) ($post['meta_description'] ?? '');
$blogHtml = wps_markdown_to_html((string) $contentResult['blog']);
$faqMarkdown = trim((string) ($contentResult['faq'] ?? ''));
$faqHtml = $faqMarkdown !== '' ? wps_markdown_to_html($faqMarkdown) : '';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo wps_h($title); ?></title>
  <?php if ($description !== ''): ?><meta name="description" content="<?php echo wps_h($description); ?>"><?php endif; ?>
  <link rel="stylesheet" href="../platform/assets/theme.css">
</head>
<body>
  <main class="wrap" style="max-width: 880px; padding: 32px 16px; margin: 0 auto;">
    <p><a href="./">&larr; Back to archive</a></p>
    <article class="card" style="padding: 20px;">
      <?php echo $blogHtml; ?>
    </article>
    <?php if ($faqHtml !== ''): ?>
      <section class="card" style="padding: 20px; margin-top: 20px;">
        <?php echo $faqHtml; ?>
      </section>
    <?php endif; ?>
  </main>
</body>
</html>
