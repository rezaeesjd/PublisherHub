<?php
require_once __DIR__ . '/../platform/content-loader.php';
require_once __DIR__ . '/../platform/post-overrides.php';
require_once __DIR__ . '/../platform/cache.php';

$settings = wps_load_settings();
wps_enforce_https();

$index = wps_archive_index($settings);
$records = is_array($index['posts'] ?? null) ? $index['posts'] : [];

$perPage = max(5, min(100, (int) ($settings['archive_page_size'] ?? 20)));
$page = max(1, (int) ($_GET['page'] ?? 1));
$paged = wps_archive_paginate($records, $page, $perPage);

$archiveTitle = trim((string) ($settings['archive_title'] ?? 'Blog'));
$archiveDescription = trim((string) ($settings['archive_description'] ?? ''));
$siteName = trim((string) ($settings['site_name'] ?? 'Milano Adventures'));
$cssVersion = @filemtime(__DIR__ . '/../platform/assets/theme.css') ?: time();
$themeCssUrl = rtrim(wps_system_url_base(), '/') . '/platform/assets/theme.css?v=' . rawurlencode((string) $cssVersion);

$archiveUrl = rtrim(wps_archive_url(), '/') . '/';
$canonical = $paged['page'] === 1
    ? $archiveUrl
    : $archiveUrl . '?page=' . $paged['page'];
$pageTitle = $archiveTitle !== '' ? $archiveTitle : 'Blog';
if ($paged['page'] > 1) {
    $pageTitle .= ' — Page ' . $paged['page'];
}
$metaDescription = $archiveDescription;

// JSON-LD: ItemList + BreadcrumbList for the archive surface.
$itemListItems = [];
foreach ($paged['records'] as $i => $record) {
    $itemListItems[] = [
        '@type'    => 'ListItem',
        'position' => $i + 1 + ($paged['page'] - 1) * $paged['per_page'],
        'url'      => $archiveUrl . 'post.php?slug=' . rawurlencode((string) ($record['public_slug'] ?? '')),
        'name'     => (string) ($record['title'] ?? ''),
    ];
}
$jsonLdItemList = [
    '@context'        => 'https://schema.org',
    '@type'           => 'ItemList',
    'itemListElement' => $itemListItems,
    'name'            => $archiveTitle,
];
$jsonLdBreadcrumb = [
    '@context'        => 'https://schema.org',
    '@type'           => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => $siteName,     'item' => rtrim(wps_system_url_base(), '/') . '/'],
        ['@type' => 'ListItem', 'position' => 2, 'name' => $archiveTitle, 'item' => $archiveUrl],
    ],
];

$prevUrl = $paged['page'] > 1
    ? ($paged['page'] - 1 === 1 ? $archiveUrl : $archiveUrl . '?page=' . ($paged['page'] - 1))
    : '';
$nextUrl = $paged['page'] < $paged['pages']
    ? $archiveUrl . '?page=' . ($paged['page'] + 1)
    : '';

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo wps_h($pageTitle); ?></title>
  <?php if ($metaDescription !== ''): ?><meta name="description" content="<?php echo wps_h($metaDescription); ?>"><?php endif; ?>
  <link rel="canonical" href="<?php echo wps_h($canonical); ?>">
  <?php if ($prevUrl !== ''): ?><link rel="prev" href="<?php echo wps_h($prevUrl); ?>"><?php endif; ?>
  <?php if ($nextUrl !== ''): ?><link rel="next" href="<?php echo wps_h($nextUrl); ?>"><?php endif; ?>
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="<?php echo wps_h($siteName); ?>">
  <meta property="og:title" content="<?php echo wps_h($pageTitle); ?>">
  <?php if ($metaDescription !== ''): ?><meta property="og:description" content="<?php echo wps_h($metaDescription); ?>"><?php endif; ?>
  <meta property="og:url" content="<?php echo wps_h($canonical); ?>">
  <meta name="twitter:card" content="summary">
  <meta name="twitter:title" content="<?php echo wps_h($pageTitle); ?>">
  <?php if ($metaDescription !== ''): ?><meta name="twitter:description" content="<?php echo wps_h($metaDescription); ?>"><?php endif; ?>
  <link rel="stylesheet" href="<?php echo wps_h($themeCssUrl); ?>">
  <script type="application/ld+json"><?php echo json_encode($jsonLdBreadcrumb, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?></script>
  <?php if (!empty($itemListItems)): ?>
  <script type="application/ld+json"><?php echo json_encode($jsonLdItemList, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?></script>
  <?php endif; ?>
</head>
<body>
  <main class="wrap" style="max-width: 960px; padding: 32px 16px; margin: 0 auto;">
    <header style="margin-bottom: 24px;">
      <p class="eyebrow"><?php echo wps_h($siteName); ?></p>
      <h1><?php echo wps_h($archiveTitle); ?></h1>
      <?php if ($archiveDescription !== ''): ?>
        <p><?php echo wps_h($archiveDescription); ?></p>
      <?php endif; ?>
      <?php if ($paged['total'] > 0): ?>
        <p class="muted">
          <?php echo (int) $paged['total']; ?> post<?php echo $paged['total'] === 1 ? '' : 's'; ?>
          &middot; page <?php echo (int) $paged['page']; ?> of <?php echo (int) $paged['pages']; ?>
        </p>
      <?php endif; ?>
    </header>

    <?php if ($paged['total'] === 0): ?>
      <p>No posts are available yet.</p>
    <?php else: ?>
      <ul style="list-style: none; padding: 0; margin: 0; display: grid; gap: 16px;">
        <?php foreach ($paged['records'] as $record): ?>
          <?php $slug = (string) ($record['public_slug'] ?? ''); ?>
          <?php $date = (string) ($record['last_qa_date'] ?? $record['published_date'] ?? ''); ?>
          <li class="card" style="padding: 18px;">
            <h2 style="margin-top: 0;"><a href="post.php?slug=<?php echo rawurlencode($slug); ?>"><?php echo wps_h((string) ($record['title'] ?? $slug)); ?></a></h2>
            <?php if (!empty($record['meta_description'])): ?>
              <p><?php echo wps_h((string) $record['meta_description']); ?></p>
            <?php endif; ?>
            <?php if ($date !== ''): ?>
              <p class="muted"><small>Updated <time datetime="<?php echo wps_h($date); ?>"><?php echo wps_h($date); ?></time></small></p>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>

      <?php if ($paged['pages'] > 1): ?>
        <nav class="archive-pagination" aria-label="Pagination" style="display:flex; gap:12px; margin-top:24px; align-items:center; justify-content:space-between;">
          <div>
            <?php if ($prevUrl !== ''): ?>
              <a href="<?php echo wps_h($prevUrl); ?>" rel="prev">&larr; Newer</a>
            <?php endif; ?>
          </div>
          <div class="muted">Page <?php echo (int) $paged['page']; ?> / <?php echo (int) $paged['pages']; ?></div>
          <div>
            <?php if ($nextUrl !== ''): ?>
              <a href="<?php echo wps_h($nextUrl); ?>" rel="next">Older &rarr;</a>
            <?php endif; ?>
          </div>
        </nav>
      <?php endif; ?>
    <?php endif; ?>
  </main>
</body>
</html>
