<?php
require_once __DIR__ . '/../platform/content-loader.php';
require_once __DIR__ . '/../platform/post-overrides.php';
require_once __DIR__ . '/../platform/cache.php';
require_once __DIR__ . '/../platform/auth.php';

$settings = wps_load_settings();
wps_enforce_https();
wps_emit_public_headers();

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
$folderName = (string) ($post['folder_name'] ?? '');
$meta = is_array($post['meta'] ?? null) ? $post['meta'] : [];

// Source-content packages hold canonical tour data for the dashboard and
// content generation; they are not public blog assets and have no public
// page. They remain visible to operators in the dashboard.
if (wps_is_source_content_package((string) ($post['base_slug'] ?? $post['slug'] ?? ''))) {
    http_response_code(404);
    echo 'Post not found.';
    exit;
}

$publicSlug = (string) ($post['public_slug'] ?? $post['slug'] ?? $slug);

// G2A.10: 301 any non-canonical slug (a legacy slug or the internal base
// slug) to the current public URL so each post is reachable at exactly one
// indexable URL.
if ($publicSlug !== '' && $slug !== $publicSlug && PHP_SAPI !== 'cli' && !headers_sent()) {
    header('Location: ' . wps_public_post_url($publicSlug), true, 301);
    exit;
}

// G1.2: noindex non-published content even when accessed via direct URL.
$publishStatus = (string) ($post['publish_status'] ?? 'draft');
wps_emit_noindex_if_unpublished($publishStatus);

// Public readers should only access published posts. Unpublished content
// remains available only to authenticated operators for preview/debug.
if ($publishStatus !== 'published' && !wps_is_logged_in()) {
    http_response_code(404);
    echo 'Post not found.';
    exit;
}

// G2A.9: tag CTA destinations with utm_campaign=<public_slug> so booking
// clicks attribute back to the right article in GA4.
$utmCampaign = $publicSlug !== '' ? $publicSlug : (string) ($post['slug'] ?? 'post');

// Cached rendering of blog-post.md and faq.md (sibling .cache.html files
// invalidated by source mtime + renderer version).
$blogHtml = '';
$faqMarkdown = '';
if ($folderPath !== '') {
    $blogHtml = wps_replace_placeholders(
        wps_cached_render_markdown($folderPath . '/blog-post.md'),
        $settings,
        $utmCampaign
    );
    if (is_file($folderPath . '/faq.md')) {
        $faqMarkdown = wps_replace_placeholders(
            (string) @file_get_contents($folderPath . '/faq.md'),
            $settings,
            $utmCampaign
        );
    }
}

if ($blogHtml === '') {
    http_response_code(500);
    echo 'Post content unavailable.';
    exit;
}

$faqPairs = $faqMarkdown !== '' ? wps_parse_faq_pairs($faqMarkdown) : [];

// FAQ-stage posts already render the full Q&A in their body; the standalone
// FAQ accordion would duplicate it on the same URL. Other funnel stages keep
// the accordion as a supplementary block. FAQPage JSON-LD is emitted either
// way so FAQ posts still get structured data.
$isFaqPost = strtoupper(trim((string) ($post['funnel_stage'] ?? ''))) === 'FAQ';

$title = (string) ($post['title'] ?? $slug);

// Guarantee the rendered body has exactly one <h1> for a clean document
// outline regardless of how the source markdown was authored.
$blogHtml = wps_enforce_single_h1($blogHtml, $title);

// G1.3: fall back to an auto-generated description when meta has none,
// trimmed on a word boundary at ~158 chars.
$description = trim((string) ($post['meta_description'] ?? ''));
if ($description === '') {
    $description = wps_auto_meta_description($blogHtml);
} else {
    $description = wps_trim_description($description, 200);
}

$archiveUrl = rtrim(wps_archive_url(), '/') . '/';
$canonical  = wps_public_post_url($publicSlug);
$systemBase = wps_site_home_url();
$feedUrl    = $archiveUrl . 'feed.xml';
$siteName   = (string) ($settings['site_name'] ?? '');
$twitterHandle = trim((string) ($settings['twitter_handle'] ?? ''));
$articleSection = trim((string) ($meta['destination'] ?? ''));

$dateModified  = (string) ($meta['last_qa_date'] ?? $post['published_date'] ?? '');
$datePublished = (string) ($meta['first_published_at'] ?? $dateModified);

// G1.11: resolve local hero images to real public URLs (and measured
// dimensions when the file is local) so og:image, twitter:image and
// Article.image fire with accurate metadata.
$hero       = wps_resolve_hero_image((string) ($meta['hero_image'] ?? ''), $folderName);
$heroImage  = (string) $hero['url'];
$heroWidth  = $hero['width'];
$heroHeight = $hero['height'];

$cssVersion = @filemtime(__DIR__ . '/../platform/assets/theme.css') ?: time();
$themeCssUrl = rtrim(wps_system_url_base(), '/') . '/platform/assets/theme.css?v=' . rawurlencode((string) $cssVersion);

$archiveTitle = trim((string) ($settings['archive_title'] ?? 'Blog'));

// G1.7: E-E-A-T author. Read from meta.json (per-post) and fall back to
// the site-wide default. Empty author means we omit the byline entirely
// rather than emit weak signals.
$authorName = trim((string) ($meta['author'] ?? $meta['author_name'] ?? $settings['default_author_name'] ?? ''));
$authorUrl  = trim((string) ($meta['author_url'] ?? $settings['default_author_url'] ?? ''));

// G2A.7: reading time + word count for engagement + SEO depth signal.
$reading = wps_reading_time($blogHtml);

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
    '@context'         => 'https://schema.org',
    '@type'            => 'Article',
    'headline'         => $title,
    'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $canonical],
    'publisher'        => $organization,
    'wordCount'        => $reading['words'],
    'inLanguage'       => 'en',
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
if ($authorName !== '') {
    $authorNode = ['@type' => 'Person', 'name' => $authorName];
    if ($authorUrl !== '') {
        $authorNode['url'] = $authorUrl;
    }
    $article['author'] = $authorNode;
}
if (!empty($post['primary_keyword'])) {
    $article['about'] = (string) $post['primary_keyword'];
}
if (!empty($meta['destination'])) {
    $article['contentLocation'] = ['@type' => 'Place', 'name' => (string) $meta['destination']];
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
        if (function_exists('wps_append_utm') && $ctaLink !== '') {
            $ctaLink = wps_append_utm($ctaLink, $utmCampaign, 'blog', 'cta-schema');
        }
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

// G2A.5: related posts from the same cluster / keyword. Cluster siblings
// (resolved through the registry) are surfaced first; remaining slots are
// filled by keyword/funnel/variant similarity.
$archiveIndex = wps_archive_index($settings);
$archiveRecords = is_array($archiveIndex['posts'] ?? null) ? $archiveIndex['posts'] : [];

// Public readers can only open published posts; keep related links aligned
// so "From this tour cluster" never points to 404 pages for guests.
if (!wps_is_logged_in()) {
    $archiveRecords = wps_published_records($archiveRecords);
}

$relatedLimit = 4;
$siblingRecords = wps_cluster_sibling_records($post, $archiveRecords, $relatedLimit);
$seenSlugs = [];
foreach ($siblingRecords as $rec) {
    $seenSlugs[(string) ($rec['public_slug'] ?? '')] = true;
}
$seenSlugs[$publicSlug] = true;

$keywordRelated = wps_related_posts(
    [
        'public_slug'     => $publicSlug,
        'primary_keyword' => (string) ($post['primary_keyword'] ?? ''),
        'funnel_stage'    => (string) ($post['funnel_stage'] ?? ''),
        'variant_of'      => (string) ($meta['variant_of'] ?? ''),
    ],
    $archiveRecords,
    $relatedLimit * 2
);

$relatedRecords = $siblingRecords;
foreach ($keywordRelated as $rec) {
    if (count($relatedRecords) >= $relatedLimit) {
        break;
    }
    $slug = (string) ($rec['public_slug'] ?? '');
    if ($slug === '' || isset($seenSlugs[$slug])) {
        continue;
    }
    $seenSlugs[$slug] = true;
    $relatedRecords[] = $rec;
}

$clusterRelated = array_slice($siblingRecords, 0, $relatedLimit);
$topicRelated = [];
foreach ($relatedRecords as $rec) {
    $s = (string) ($rec['public_slug'] ?? '');
    if ($s === '' || isset($seenSlugs[$s]) && in_array($rec, $clusterRelated, true)) {
        continue;
    }
    if (!in_array($rec, $clusterRelated, true)) {
        $topicRelated[] = $rec;
    }
}

// G2A.10: external booking + review-platform CTAs resolved with a
// per-post -> cluster -> site fallback chain. Empty when no real links
// (only placeholders) are available, in which case the card is skipped.
$bookingLinks = wps_resolve_post_booking_links($post, $settings, $utmCampaign);
$hasPrimaryCta = $bookingLinks['viator']['url'] !== ''
    || $bookingLinks['tripadvisor']['url'] !== ''
    || $bookingLinks['website']['url'] !== '';
$hasSecondaryCta = !empty($bookingLinks['secondary']);
$showBookingCta = $hasPrimaryCta || $hasSecondaryCta;

// G2A.6: preconnect to CTA destinations we know we'll link to + analytics.
$preconnect = wps_render_preconnect($settings, $blogHtml);

$analyticsHead = wps_render_analytics($settings, 'head');
$analyticsBody = wps_render_analytics($settings, 'body');

$freshnessDays = null;
if ($dateModified !== '') {
    $freshnessTs = strtotime($dateModified);
    if ($freshnessTs !== false) {
        $freshnessDays = max(0, (int) floor((time() - $freshnessTs) / 86400));
    }
}

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo wps_h($siteName !== '' ? $title . ' | ' . $siteName : $title); ?></title>
  <?php if ($description !== ''): ?><meta name="description" content="<?php echo wps_h($description); ?>"><?php endif; ?>
  <meta name="robots" content="<?php echo $publishStatus === 'published' ? 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1' : 'noindex, nofollow'; ?>">
  <meta name="referrer" content="strict-origin-when-cross-origin">
  <link rel="canonical" href="<?php echo wps_h($canonical); ?>">
  <link rel="alternate" type="application/rss+xml" title="<?php echo wps_h($archiveTitle !== '' ? $archiveTitle : 'Blog'); ?>" href="<?php echo wps_h($feedUrl); ?>">
  <?php echo $analyticsHead; ?>
  <?php echo $preconnect; ?>
  <meta property="og:type" content="article">
  <meta property="og:site_name" content="<?php echo wps_h($siteName); ?>">
  <meta property="og:title" content="<?php echo wps_h($title); ?>">
  <?php if ($description !== ''): ?><meta property="og:description" content="<?php echo wps_h($description); ?>"><?php endif; ?>
  <meta property="og:url" content="<?php echo wps_h($canonical); ?>">
  <?php if ($heroImage !== ''): ?>
  <meta property="og:image" content="<?php echo wps_h($heroImage); ?>">
  <?php if ($heroWidth && $heroHeight): ?>
  <meta property="og:image:width" content="<?php echo (int) $heroWidth; ?>">
  <meta property="og:image:height" content="<?php echo (int) $heroHeight; ?>">
  <?php endif; ?>
  <?php endif; ?>
  <?php if ($datePublished !== ''): ?><meta property="article:published_time" content="<?php echo wps_h($datePublished); ?>"><?php endif; ?>
  <?php if ($dateModified !== ''): ?><meta property="article:modified_time" content="<?php echo wps_h($dateModified); ?>"><?php endif; ?>
  <?php if ($authorName !== ''): ?><meta property="article:author" content="<?php echo wps_h($authorName); ?>"><?php endif; ?>
  <?php if ($articleSection !== ''): ?><meta property="article:section" content="<?php echo wps_h($articleSection); ?>"><?php endif; ?>
  <meta name="twitter:card" content="<?php echo $heroImage !== '' ? 'summary_large_image' : 'summary'; ?>">
  <?php if ($twitterHandle !== ''): ?><meta name="twitter:site" content="@<?php echo wps_h($twitterHandle); ?>"><?php endif; ?>
  <meta name="twitter:title" content="<?php echo wps_h($title); ?>">
  <?php if ($description !== ''): ?><meta name="twitter:description" content="<?php echo wps_h($description); ?>"><?php endif; ?>
  <?php if ($heroImage !== ''): ?><meta name="twitter:image" content="<?php echo wps_h($heroImage); ?>"><?php endif; ?>
  <style><?php echo wps_critical_css(); ?></style>
  <?php echo wps_render_deferred_stylesheet($themeCssUrl); ?>
</head>
<body>
  <a class="skip-link" href="#main-content">Skip to main content</a>
  <main id="main-content" class="wrap" style="max-width: 880px; padding: 32px 16px; margin: 0 auto;">
    <nav aria-label="Breadcrumb" class="muted breadcrumb-inline" style="margin-bottom: 8px;">
      <a href="<?php echo wps_h($systemBase); ?>">Home</a> &rsaquo;
      <a href="<?php echo wps_h($archiveUrl); ?>"><?php echo wps_h($archiveTitle !== '' ? $archiveTitle : 'Archive'); ?></a> &rsaquo;
      <span aria-current="page"><?php echo wps_h($title); ?></span>
      <span class="sr-only">Current page</span>
    </nav>
    <?php if ($authorName !== '' || $dateModified !== '' || $reading['minutes'] > 0): ?>
      <p class="post-byline">
        <?php if ($authorName !== ''): ?>
          <span class="byline-author">By <?php if ($authorUrl !== ''): ?><a href="<?php echo wps_h($authorUrl); ?>" rel="author"><?php echo wps_h($authorName); ?></a><?php else: ?><?php echo wps_h($authorName); ?><?php endif; ?></span>
        <?php endif; ?>
        <?php if ($dateModified !== ''): ?>
          <span>
            <?php if ($datePublished !== '' && $datePublished !== $dateModified): ?>
              Published <time datetime="<?php echo wps_h($datePublished); ?>"><?php echo wps_h(wps_human_date($datePublished)); ?></time> &middot;
            <?php endif; ?>
            Updated <time datetime="<?php echo wps_h($dateModified); ?>"><?php echo wps_h(wps_human_date($dateModified)); ?></time>
          </span>
        <?php endif; ?>
        <span><?php echo (int) $reading['minutes']; ?> min read</span>
      </p>
    <?php endif; ?>
    <?php if ($description !== ''): ?>
      <aside class="post-summary" role="doc-abstract" aria-label="Article summary">
        <strong>Summary</strong>
        <p><?php echo wps_h($description); ?></p>
      </aside>
    <?php endif; ?>
    <?php // Internal review cadence ("Refresh recommended", "Reviewed N days
          // ago") is an operator signal, not public copy — readers already
          // get an "Updated" date in the byline above. Show only to operators. ?>
    <?php if ($dateModified !== '' && wps_is_logged_in()): ?>
      <p class="muted" style="margin: 10px 0 14px;">
        Last reviewed on <time datetime="<?php echo wps_h($dateModified); ?>"><?php echo wps_h(wps_human_date($dateModified)); ?></time>.
        <?php if ($freshnessDays !== null): ?>
          <?php if ($freshnessDays > 180): ?>
            <strong>Refresh recommended (<?php echo (int) $freshnessDays; ?> days since review).</strong>
          <?php else: ?>
            <span>Reviewed <?php echo (int) $freshnessDays; ?> day(s) ago.</span>
          <?php endif; ?>
        <?php endif; ?>
      </p>
    <?php endif; ?>
    <article class="card content-body" style="padding: 20px;">
      <?php echo $blogHtml; ?>
    </article>
    <?php if ($showBookingCta): ?>
      <?php
        $websiteLabel = $siteName !== '' ? 'Book direct on ' . $siteName : 'Book direct on our website';
        // rel="sponsored" on third-party booking destinations follows
        // Google's guidance for affiliate-style outbound links.
        $primaryRel = 'noopener sponsored';
        $ownRel = 'noopener';
      ?>
      <section class="card book-cta" aria-labelledby="book-cta-heading">
        <h2 id="book-cta-heading">Book this tour or check our reviews</h2>
        <p class="muted">Reserve your spot and read recent traveler reviews on the platforms below.</p>
        <ul class="book-cta-actions">
          <?php if ($bookingLinks['viator']['url'] !== ''): ?>
            <li>
              <a class="cta-button-booking" href="<?php echo wps_h($bookingLinks['viator']['url']); ?>" target="_blank" rel="<?php echo $primaryRel; ?>">Book on Viator</a>
              <span class="cta-sub">Live availability &amp; traveler reviews</span>
            </li>
          <?php endif; ?>
          <?php if ($bookingLinks['tripadvisor']['url'] !== ''): ?>
            <li>
              <a class="cta-button-secondary" href="<?php echo wps_h($bookingLinks['tripadvisor']['url']); ?>" target="_blank" rel="<?php echo $primaryRel; ?>">View on TripAdvisor</a>
              <span class="cta-sub">Ratings &amp; recent reviews</span>
            </li>
          <?php endif; ?>
          <?php if ($bookingLinks['website']['url'] !== ''): ?>
            <li>
              <a class="cta-button-secondary" href="<?php echo wps_h($bookingLinks['website']['url']); ?>" target="_blank" rel="<?php echo $ownRel; ?>"><?php echo wps_h($websiteLabel); ?></a>
              <span class="cta-sub">Book direct with the operator</span>
            </li>
          <?php endif; ?>
        </ul>
        <?php if ($hasSecondaryCta): ?>
          <div class="trust-row">
            <p class="muted trust-label">Also find us on</p>
            <ul class="trust-links">
              <?php foreach ($bookingLinks['secondary'] as $entry): ?>
                <li><a href="<?php echo wps_h($entry['url']); ?>" target="_blank" rel="noopener"><?php echo wps_h($entry['label']); ?></a></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>
      </section>
    <?php endif; ?>
    <?php if (!empty($faqPairs) && !$isFaqPost): ?>
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
    <?php if (!empty($clusterRelated)): ?>
      <aside class="card related-posts" aria-labelledby="related-heading">
        <h2 id="related-heading">From this tour cluster</h2>
        <ul>
          <?php foreach ($clusterRelated as $rec): ?>
            <?php $relSlug = (string) ($rec['public_slug'] ?? ''); if ($relSlug === '') continue; ?>
            <li>
              <a href="<?php echo wps_h(wps_public_post_url($relSlug)); ?>"><?php echo wps_h((string) ($rec['title'] ?? $relSlug)); ?></a>
              <?php if (!empty($rec['meta_description'])): ?>
                <p><?php echo wps_h(wps_trim_description((string) $rec['meta_description'], 110)); ?></p>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      </aside>
    <?php endif; ?>
    <?php if (!empty($topicRelated)): ?>
      <aside class="card related-posts" aria-labelledby="related-topic-heading">
        <h2 id="related-topic-heading">More guides on this topic</h2>
        <ul>
          <?php foreach ($topicRelated as $rec): ?>
            <?php $relSlug = (string) ($rec['public_slug'] ?? ''); if ($relSlug === '') continue; ?>
            <li>
              <a href="<?php echo wps_h(wps_public_post_url($relSlug)); ?>"><?php echo wps_h((string) ($rec['title'] ?? $relSlug)); ?></a>
              <?php if (!empty($rec['meta_description'])): ?>
                <p><?php echo wps_h(wps_trim_description((string) $rec['meta_description'], 110)); ?></p>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      </aside>
    <?php elseif (empty($clusterRelated)): ?>
      <aside class="card related-posts" aria-labelledby="related-empty-heading">
        <h2 id="related-empty-heading">More guides coming soon</h2>
        <p class="muted">We’re expanding this cluster with more destination-specific guides and FAQs.</p>
      </aside>
    <?php endif; ?>
  </main>

  <?php // G1.10: all JSON-LD emitted after main so it never blocks LCP. ?>
  <script type="application/ld+json"><?php echo json_encode($breadcrumb, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?></script>
  <script type="application/ld+json"><?php echo json_encode($article, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?></script>
  <?php if ($faqLd !== null): ?>
  <script type="application/ld+json"><?php echo json_encode($faqLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?></script>
  <?php endif; ?>
  <?php if ($touristTrip !== null): ?>
  <script type="application/ld+json"><?php echo json_encode($touristTrip, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?></script>
  <?php endif; ?>
  <?php echo $analyticsBody; ?>
</body>
</html>
