<?php
require_once __DIR__ . '/../platform/content-loader.php';

$settings = wps_load_settings();
$postsResult = wps_get_posts($settings);
$posts = $postsResult['ok'] ? $postsResult['posts'] : [];

$archiveTitle = trim((string) ($settings['archive_title'] ?? 'Blog'));
$archiveDescription = trim((string) ($settings['archive_description'] ?? ''));
$siteName = trim((string) ($settings['site_name'] ?? 'Milano Adventures'));

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo wps_h($archiveTitle !== '' ? $archiveTitle : 'Blog'); ?></title>
  <link rel="stylesheet" href="../platform/assets/theme.css">
</head>
<body>
  <main class="wrap" style="max-width: 960px; padding: 32px 16px; margin: 0 auto;">
    <header style="margin-bottom: 24px;">
      <p class="eyebrow"><?php echo wps_h($siteName); ?></p>
      <h1><?php echo wps_h($archiveTitle); ?></h1>
      <?php if ($archiveDescription !== ''): ?>
        <p><?php echo wps_h($archiveDescription); ?></p>
      <?php endif; ?>
    </header>

    <?php if (!$postsResult['ok']): ?>
      <p><?php echo wps_h($postsResult['error']); ?></p>
    <?php elseif (count($posts) === 0): ?>
      <p>No posts are available yet.</p>
    <?php else: ?>
      <ul style="list-style: none; padding: 0; margin: 0; display: grid; gap: 16px;">
        <?php foreach ($posts as $post): ?>
          <?php $slug = (string) ($post['slug'] ?? ''); ?>
          <li class="card" style="padding: 18px;">
            <h2 style="margin-top: 0;"><a href="post.php?slug=<?php echo rawurlencode($slug); ?>"><?php echo wps_h((string) ($post['title'] ?? $slug)); ?></a></h2>
            <?php if (!empty($post['meta_description'])): ?>
              <p><?php echo wps_h((string) $post['meta_description']); ?></p>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </main>
</body>
</html>
