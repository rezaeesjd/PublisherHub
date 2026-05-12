<?php
require_once __DIR__ . '/../platform/content-loader.php';
require_once __DIR__ . '/../platform/post-overrides.php';
require_once __DIR__ . '/../platform/cache.php';

$settings = wps_load_settings();
wps_enforce_https();

$slug = trim((string) ($_GET['slug'] ?? ''));
if ($slug === '') {
    http_response_code(404);
    echo 'Post not found.';
    exit;
}

$postResult = wps_find_post_by_public_or_base_slug($settings, $slug);
if (!$postResult['ok'] || !is_array($postResult['post'])) {
    http_response_code(404);
    echo 'Post not found.';
    exit;
}

$post = $postResult['post'];
$folderPath = (string) ($post['folder_path'] ?? '');
$meta = is_array($post['meta'] ?? null) ? $post['meta'] : [];

// Cached rendering of blog-post.md and faq.md (sibling .cache.html files
// invalidated by source mtime + renderer version).
$blogHtml = '';
$faqMarkdown = '';
if ($folderPath !== '') {
    $blogHtml = wps_replace_placeholders(
        wps_cached_render_markdown($folderPath . '/blog-post.md'),
        $settings
    );
    if (is_file($folderPath . '/faq.md')) {
        $faqMarkdown = wps_replace_placeholders(
            (string) @file_get_contents($folderPath . '/faq.md'),
            $settings
        );
    }
}

if ($blogHtml === '') {
    http_response_code(500);
    echo 'Post content unavailable.';
    exit;
}

$faqPairs = $faqMarkdown !== '' ? wps_parse_faq_pairs($faqMarkdown) : [];

$title = (string) ($post['title'] ?? $slug);
$description = (string) ($post['meta_description'] ?? '');
$publicSlug = (string) ($post['public_slug'] ?? $post['slug'] ?? $slug);
$archiveUrl = rtrim(wps_archive_url(), '/') . '/';
$canonical  = $archiveUrl . 'post.php?slug=' . rawurlencode($publicSlug);
$systemBase = rtrim(wps_system_url_base(), '/') . '/';
$siteName   = (string) ($settings['site_name'] ?? '');

$dateModified  = (string) ($meta['last_qa_date'] ?? $post['published_date'] ?? '');
$datePublished = (string) ($meta['first_published_at'] ?? $dateModified);
$heroImage     = (string) ($meta['hero_image'] ?? '');
if ($heroImage !== '' && !preg_match('#^https?://#i', $heroImage)) {
    $heroImage = $archiveUrl . 'post.php?slug=' . rawurlencode($publicSlug) . '#hero';
    // local hero images are not yet rendered to public URLs; suppress to avoid bad JSON-LD.
    $heroImage = '';
}

$cssVersion = @filemtime(__DIR__ . '/../platform/assets/theme.css') ?: time();
$themeCssUrl = rtrim(wps_system_url_base(), '/') . '/platform/assets/theme.css?v=' . rawurlencode((string) $cssVersion);

$archiveTitle = trim((string) ($settings['archive_title'] ?? 'Blog'));
$breadcrumb = [
    '@context'        => 'https://schema.org',
    '@type'           => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => $siteName !== '' ? $siteName : 'Home', 'item' => $systemBase],
        ['@type' => 'ListItem', 'position' => 2, 'name' => $archiveTitle !== '' ? $archiveTitle : 'Blog', 'item' => $archiveUrl],
        ['@type' => 'ListItem', 'position' => 3, 'name' => $title, 'item' => $canonical],
    ],
];

$organization = [
    '@type' => 'Organization',
    'name'  => $siteName,
    'url'   => $systemBase,
];
$orgLogo = trim((string) ($settings['organization_logo_url'] ?? ''));
if ($orgLogo !== '') {
    $organization['logo'] = $orgLogo;
}

$article = [
    '@context'      => 'https://schema.org',
    '@type'         => 'Article',
    'headline'      => $title,
    'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $canonical],
    'publisher'     => $organization,
];
if ($description !== '') {
    $article['description'] = $description;
}
if ($datePublished !== '') {
    $article['datePublished'] = $datePublished;
}
if ($dateModified !== '') {
    $article['dateModified'] = $dateModified;
}
if ($heroImage !== '') {
    $article['image'] = [$heroImage];
}

$faqLd = null;
if (!empty($faqPairs)) {
    $faqEntities = [];
    foreach ($faqPairs as $pair) {
        if (($pair['question'] ?? '') === '' || ($pair['answer_text'] ?? '') === '') {
            continue;
        }
        $faqEntities[] = [
            '@type'          => 'Question',
            'name'           => (string) $pair['question'],
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => (string) $pair['answer_text'],
            ],
        ];
    }
    if (!empty($faqEntities)) {
        $faqLd = [
            '@context'   => 'https://schema.org',
            '@type'      => 'FAQPage',
            'mainEntity' => $faqEntities,
        ];
    }
}

// TouristTrip schema is emitted whenever we have enough booking data to be
// useful. price_from is a free-text field like "EUR 157"; we parse a number
// + ISO code out of it. Bad parses fall back to omitting offers.
$touristTrip = null;
$priceFrom = trim((string) ($meta['price_from'] ?? ''));
$offer = null;
if ($priceFrom !== '' && preg_match('/^\s*([A-Z]{3})\s*([\d,.]+)/', $priceFrom, $m)) {
    $amount = (float) str_replace([',', ' '], ['', ''], $m[2]);
    if ($amount > 0) {
        $ctaLink = (string) ($meta['cta_primary_link'] ?? '');
        $offer = [
            '@type'         => 'Offer',
            'price'         => number_format($amount, 2, '.', ''),
            'priceCurrency' => $m[1],
            'availability'  => 'https://schema.org/InStock',
        ];
        if ($ctaLink !== '') {
            $offer['url'] = $ctaLink;
        }
    }
}

if (in_array((string) ($meta['funnel_stage'] ?? ''), ['BOFU', 'MOFU'], true) || $offer !== null) {
    $touristTrip = [
        '@context' => 'https://schema.org',
        '@type'    => 'TouristTrip',
        'name'     => (string) ($meta['canonical_tour_title'] ?? $title),
        'url'      => $canonical,
        'provider' => $organization,
    ];
    if ($description !== '') {
        $touristTrip['description'] = $description;
    }
    if (!empty($meta['duration_text'])) {
        $touristTrip['itinerary'] = ['@type' => 'ItemList', 'name' => 'Duration', 'description' => (string) $meta['duration_text']];
    }
    if (!empty($meta['meeting_point'])) {
        $touristTrip['touristType'] = $meta['meeting_point'];
    }
    if ($offer !== null) {
        $touristTrip['offers'] = $offer;
    }
}

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo wps_h($title); ?></title>
  <?php if ($description !== ''): ?><meta name="description" content="<?php echo wps_h($description); ?>"><?php endif; ?>
  <link rel="canonical" href="<?php echo wps_h($canonical); ?>">
  <meta property="og:type" content="article">
  <meta property="og:site_name" content="<?php echo wps_h($siteName); ?>">
  <meta property="og:title" content="<?php echo wps_h($title); ?>">
  <?php if ($description !== ''): ?><meta property="og:description" content="<?php echo wps_h($description); ?>"><?php endif; ?>
  <meta property="og:url" content="<?php echo wps_h($canonical); ?>">
  <?php if ($heroImage !== ''): ?><meta property="og:image" content="<?php echo wps_h($heroImage); ?>"><?php endif; ?>
  <?php if ($datePublished !== ''): ?><meta property="article:published_time" content="<?php echo wps_h($datePublished); ?>"><?php endif; ?>
  <?php if ($dateModified !== ''): ?><meta property="article:modified_time" content="<?php echo wps_h($dateModified); ?>"><?php endif; ?>
  <meta name="twitter:card" content="<?php echo $heroImage !== '' ? 'summary_large_image' : 'summary'; ?>">
  <meta name="twitter:title" content="<?php echo wps_h($title); ?>">
  <?php if ($description !== ''): ?><meta name="twitter:description" content="<?php echo wps_h($description); ?>"><?php endif; ?>
  <?php if ($heroImage !== ''): ?><meta name="twitter:image" content="<?php echo wps_h($heroImage); ?>"><?php endif; ?>
  <link rel="stylesheet" href="<?php echo wps_h($themeCssUrl); ?>">
  <script type="application/ld+json"><?php echo json_encode($breadcrumb, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?></script>
  <script type="application/ld+json"><?php echo json_encode($article, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?></script>
  <?php if ($faqLd !== null): ?>
  <script type="application/ld+json"><?php echo json_encode($faqLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?></script>
  <?php endif; ?>
  <?php if ($touristTrip !== null): ?>
  <script type="application/ld+json"><?php echo json_encode($touristTrip, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?></script>
  <?php endif; ?>
</head>
<body>
  <main class="wrap" style="max-width: 880px; padding: 32px 16px; margin: 0 auto;">
    <nav aria-label="Breadcrumb" class="muted" style="margin-bottom: 8px;">
      <a href="./">&larr; Back to <?php echo wps_h($archiveTitle !== '' ? $archiveTitle : 'archive'); ?></a>
    </nav>
    <?php if ($dateModified !== ''): ?>
      <p class="muted"><small>
        <?php if ($datePublished !== '' && $datePublished !== $dateModified): ?>
          Published <time datetime="<?php echo wps_h($datePublished); ?>"><?php echo wps_h($datePublished); ?></time>
          &middot;
        <?php endif; ?>
        Updated <time datetime="<?php echo wps_h($dateModified); ?>"><?php echo wps_h($dateModified); ?></time>
      </small></p>
    <?php endif; ?>
    <article class="card" style="padding: 20px;">
      <?php echo $blogHtml; ?>
    </article>
    <?php if (!empty($faqPairs)): ?>
      <section class="card faq-block" style="padding: 20px; margin-top: 20px;" aria-labelledby="faq-heading">
        <h2 id="faq-heading">Frequently asked questions</h2>
        <?php foreach ($faqPairs as $pair): ?>
          <?php if (($pair['question'] ?? '') === '') { continue; } ?>
          <details class="faq-item">
            <summary><?php echo wps_h((string) $pair['question']); ?></summary>
            <div class="faq-answer"><?php echo $pair['answer_html']; ?></div>
          </details>
        <?php endforeach; ?>
      </section>
    <?php endif; ?>
  </main>
</body>
</html>
