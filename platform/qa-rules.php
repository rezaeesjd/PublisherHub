<?php
/**
 * QA rules for tour content packages. Pure functions over a tour folder
 * + meta. No I/O other than reading on-disk files so the CLI and the
 * in-app QA gate can share the implementation.
 *
 * Each rule returns ['severity' => 'pass'|'warn'|'fail', 'message' => string].
 */

require_once __DIR__ . '/functions.php';

const WPS_REQUIRED_TOUR_FILES = [
    'source-facts.md',
    'brief.md',
    'keywords.md',
    'blog-post.md',
    'faq.md',
    'meta.json',
    'internal-links.md',
    'automation-notes.md',
    'qa-report.md',
];

const WPS_RECOMMENDED_TOUR_FILES = [
    'CHANGELOG.md',
];

const WPS_FORBIDDEN_ADMIN_LABELS = [
    '/^\s*#{1,6}\s*Page\s+Title\s*$/im',
    '/^\s*#{1,6}\s*URL\s+Slug\s*$/im',
    '/^\s*#{1,6}\s*Meta\s+Description\s*$/im',
    '/^\s*#{1,6}\s*H1\s*$/im',
    '/^\s*#{1,6}\s*Hook\s+paragraph\s*$/im',
    '/^\s*#{1,6}\s*Main\s+value\s+section\s*$/im',
    '/^\s*#{1,6}\s*Internal\s+linking\s+suggestions?\s*$/im',
    '/^\s*#{1,6}\s*Funnel\s+Stage\s*$/im',
    '/^\s*#{1,6}\s*Primary\s+Keyword\s*$/im',
];

const WPS_PLACEHOLDER_PATTERN = '/\{\{[A-Za-z0-9_]+\}\}/';

const WPS_META_SCHEMA_PATH = __DIR__ . '/../content-system/meta.schema.json';

function wps_qa_run_for_tour(string $tourDir): array
{
    $findings = [];

    foreach (WPS_REQUIRED_TOUR_FILES as $required) {
        $path = $tourDir . '/' . $required;
        if (!is_file($path)) {
            $findings[] = wps_qa_finding('fail', 'missing-file', "Required file missing: {$required}");
        }
    }

    foreach (WPS_RECOMMENDED_TOUR_FILES as $recommended) {
        $path = $tourDir . '/' . $recommended;
        if (!is_file($path)) {
            $findings[] = wps_qa_finding('warn', 'missing-recommended-file', "Recommended file missing: {$recommended}");
        }
    }

    $metaPath = $tourDir . '/meta.json';
    $meta = is_file($metaPath) ? json_decode((string) file_get_contents($metaPath), true) : null;

    if (!is_array($meta)) {
        $findings[] = wps_qa_finding('fail', 'meta-invalid', 'meta.json is missing or not valid JSON.');
        $meta = [];
    }

    $tourSlug = basename($tourDir);
    if (wps_is_retired_variant_slug($tourSlug) || wps_is_retired_variant_slug((string) ($meta['slug'] ?? ''))) {
        $findings[] = wps_qa_finding(
            'fail',
            'retired-variant-clone',
            "Package '{$tourSlug}' is a retired -vN variant clone. The -vN variant mechanism was retired; delete this package and, if a new angle is needed, generate a distinct typed cluster asset with its own slug."
        );
    }

    foreach (wps_meta_schema_findings($meta) as $f) {
        $findings[] = $f;
    }

    $publishStatus = (string) ($meta['publish_status'] ?? 'draft');
    $isPublished = ($publishStatus === 'published');

    foreach (wps_clarifications_findings($meta, $isPublished) as $f) {
        $findings[] = $f;
    }

    $blogPath = $tourDir . '/blog-post.md';
    $blogContent = is_file($blogPath) ? (string) file_get_contents($blogPath) : '';

    if ($blogContent !== '') {
        foreach (WPS_FORBIDDEN_ADMIN_LABELS as $pattern) {
            if (preg_match($pattern, $blogContent, $m)) {
                $findings[] = wps_qa_finding('fail', 'admin-label-leak', 'Admin label found in public blog-post.md: ' . trim($m[0]));
            }
        }

        $h1Count = preg_match_all('/^#\s+(.+?)\s*#*\s*$/m', $blogContent, $h1Matches);
        if ($h1Count === 0) {
            $findings[] = wps_qa_finding('fail', 'h1-missing', 'blog-post.md has no top-level H1.');
        } elseif ($h1Count > 1) {
            $findings[] = wps_qa_finding('warn', 'h1-multiple', "blog-post.md has {$h1Count} H1 lines; expected exactly one.");
        }

        // H1 ↔ page_title parity. Google de-duplicates conflicting signals
        // so a major mismatch dilutes the topical signal. Warn when normalized
        // forms diverge more than the 60% similarity threshold.
        $pageTitle = trim((string) ($meta['page_title'] ?? ''));
        if ($h1Count >= 1 && $pageTitle !== '' && isset($h1Matches[1][0])) {
            $firstH1 = trim($h1Matches[1][0]);
            $normalize = function (string $s): string {
                $s = mb_strtolower($s);
                $s = preg_replace('/[^a-z0-9\s]+/u', ' ', $s) ?? $s;
                return trim(preg_replace('/\s+/u', ' ', $s) ?? $s);
            };
            $a = $normalize($firstH1);
            $b = $normalize($pageTitle);
            similar_text($a, $b, $percent);
            if ($percent < 60) {
                $findings[] = wps_qa_finding(
                    'warn',
                    'title-h1-mismatch',
                    "blog-post.md H1 '{$firstH1}' diverges from meta.page_title '{$pageTitle}' (similarity " . round($percent) . '%). Align them for a consistent ranking signal.'
                );
            }
        }

        // Title length. SEO target is 50–60 chars; Google truncates ~60 and
        // almost always rewrites > 65.
        if ($pageTitle !== '' && mb_strlen($pageTitle) > 60) {
            $findings[] = wps_qa_finding(
                'warn',
                'title-too-long',
                'meta.page_title is ' . mb_strlen($pageTitle) . ' characters (target: 50–60). Google will truncate.'
            );
        }
        if ($pageTitle !== '' && mb_strlen($pageTitle) < 50) {
            $findings[] = wps_qa_finding(
                'warn',
                'title-too-short',
                'meta.page_title is ' . mb_strlen($pageTitle) . ' characters (target: 50–60); usually too short to communicate intent.'
            );
        }

        // Meta description length. SEO target is 140–160 chars.
        $metaDescription = trim((string) ($meta['meta_description'] ?? ''));
        if ($metaDescription !== '' && mb_strlen($metaDescription) > 160) {
            $findings[] = wps_qa_finding(
                'warn',
                'meta-description-too-long',
                'meta.meta_description is ' . mb_strlen($metaDescription) . ' characters (target: 140–160). Google will truncate.'
            );
        }
        if ($metaDescription !== '' && mb_strlen($metaDescription) < 140) {
            $findings[] = wps_qa_finding(
                'warn',
                'meta-description-too-short',
                'meta.meta_description is ' . mb_strlen($metaDescription) . ' characters (target: 140–160); usually too short to earn the click.'
            );
        }

        // ── On-page SEO enforcement (Group 2a) ─────────────────────────
        // Normalize lowercases and collapses punctuation/whitespace so
        // "Full-Day Tour" and "full day tour" compare equal.
        $normalizeSeoText = function (string $s): string {
            $s = mb_strtolower($s);
            $s = preg_replace('/[^a-z0-9\s]+/u', ' ', $s) ?? $s;
            return trim(preg_replace('/\s+/u', ' ', $s) ?? $s);
        };

        $primaryKeyword = trim((string) ($meta['primary_keyword'] ?? ''));
        $pkNorm = $primaryKeyword !== '' ? $normalizeSeoText($primaryKeyword) : '';
        $blogNorm = $normalizeSeoText($blogContent);
        $blogWords = $blogNorm === '' ? [] : explode(' ', $blogNorm);
        $wordCount = count($blogWords);

        if ($pkNorm !== '') {
            // 8. primary-keyword-in-title-prefix — keyword should sit in
            //    the front half of the title tag.
            if ($pageTitle !== '') {
                $titleNorm = $normalizeSeoText($pageTitle);
                $titlePos  = $titleNorm === '' ? false : mb_strpos($titleNorm, $pkNorm);
                if ($titlePos === false) {
                    $findings[] = wps_qa_finding(
                        'warn',
                        'primary-keyword-title-missing',
                        "meta.page_title does not contain primary_keyword '{$primaryKeyword}'."
                    );
                } elseif ($titlePos > (int) (mb_strlen($titleNorm) / 2)) {
                    $findings[] = wps_qa_finding(
                        'warn',
                        'primary-keyword-title-prefix',
                        "primary_keyword '{$primaryKeyword}' appears late in meta.page_title; move it toward the start for a stronger title-tag signal."
                    );
                }
            }

            // 9. primary-keyword-in-h1
            if ($h1Count >= 1 && isset($h1Matches[1][0])) {
                $h1Norm = $normalizeSeoText((string) $h1Matches[1][0]);
                if ($h1Norm === '' || mb_strpos($h1Norm, $pkNorm) === false) {
                    $findings[] = wps_qa_finding(
                        'warn',
                        'primary-keyword-h1-missing',
                        "blog-post.md H1 does not contain primary_keyword '{$primaryKeyword}'."
                    );
                }
            }

            // 10. primary-keyword-in-first-100-words
            if ($wordCount > 0) {
                $first100 = implode(' ', array_slice($blogWords, 0, 100));
                if (mb_strpos($first100, $pkNorm) === false) {
                    $findings[] = wps_qa_finding(
                        'warn',
                        'primary-keyword-first-100-words',
                        "primary_keyword '{$primaryKeyword}' does not appear in the first 100 words of blog-post.md."
                    );
                }
            }

            // 11. primary-keyword-in-at-least-one-h2 — also fires when
            //     the post has no H2 at all, since the target is "≥ 1 H2
            //     containing the primary keyword" (zero H2s fails that).
            preg_match_all('/^##\s+(.+?)\s*#*\s*$/m', $blogContent, $h2Matches);
            $h2Hit = false;
            foreach ($h2Matches[1] ?? [] as $h2) {
                if (mb_strpos($normalizeSeoText($h2), $pkNorm) !== false) {
                    $h2Hit = true;
                    break;
                }
            }
            if (!$h2Hit) {
                $h2Count = isset($h2Matches[1]) ? count($h2Matches[1]) : 0;
                $detail = $h2Count === 0
                    ? 'blog-post.md has no H2 headings at all.'
                    : "No H2 in blog-post.md contains primary_keyword '{$primaryKeyword}'.";
                $findings[] = wps_qa_finding(
                    'warn',
                    'primary-keyword-h2-missing',
                    $detail . ' Add an H2 that carries the primary keyword to reinforce topical relevance.'
                );
            }

            // 12. primary-keyword-in-conclusion — look at the last 200
            //     normalized words as a proxy for the conclusion section.
            if ($wordCount > 0) {
                $tail = implode(' ', array_slice($blogWords, -min(200, $wordCount)));
                if (mb_strpos($tail, $pkNorm) === false) {
                    $findings[] = wps_qa_finding(
                        'warn',
                        'primary-keyword-conclusion-missing',
                        "primary_keyword '{$primaryKeyword}' is absent from the last 200 words of blog-post.md."
                    );
                }
            }
        }

        // 13. keywords-coverage — long-tail terms from keywords.md
        //     should actually appear in the published post.
        $keywordsPath = $tourDir . '/keywords.md';
        if (is_file($keywordsPath)) {
            $longTail = wps_qa_keywords_section_items(
                (string) file_get_contents($keywordsPath),
                'long-tail'
            );
            if (!empty($longTail) && $blogNorm !== '') {
                $missing = [];
                foreach ($longTail as $kw) {
                    $kwNorm = $normalizeSeoText($kw);
                    if ($kwNorm === '') {
                        continue;
                    }
                    if (mb_strpos($blogNorm, $kwNorm) === false) {
                        $missing[] = $kw;
                    }
                }
                $total = count($longTail);
                $miss  = count($missing);
                if ($total > 0 && $miss > (int) ($total / 2)) {
                    $sample = array_slice($missing, 0, 3);
                    $findings[] = wps_qa_finding(
                        'warn',
                        'keywords-coverage-low',
                        'blog-post.md covers only ' . ($total - $miss) . "/{$total} long-tail keywords from keywords.md (e.g. missing: " . implode('; ', $sample) . ').'
                    );
                }
            }
        }

        // 14. word-count-500-900 — AGENTS.md §523 scopes the target to
        //     "a final main post", so only enforce on public_copy_state=final.
        //     Holding-notice (≤150 words) and not_started/provisional stubs
        //     have their own word-count expectations elsewhere.
        $publicCopyState = (string) ($meta['public_copy_state'] ?? '');
        if ($publicCopyState === 'final'
            && $wordCount > 0
            && ($wordCount < 500 || $wordCount > 900)
        ) {
            $findings[] = wps_qa_finding(
                'warn',
                'word-count-out-of-range',
                "blog-post.md is {$wordCount} words (AGENTS.md §523 target: 500–900)."
            );
        }

        $brandName = (string) ($meta['brand'] ?? '');
        if ($brandName !== '' && stripos($blogContent, $brandName) === false) {
            $findings[] = wps_qa_finding('warn', 'brand-missing', "blog-post.md does not mention the active brand '{$brandName}'.");
        }

        // Broken images: ![alt](path) where path is relative + missing on disk.
        if (preg_match_all('/!\[([^\]]*)\]\(([^)\s]+)/', $blogContent, $imgMatches, PREG_SET_ORDER)) {
            foreach ($imgMatches as $im) {
                $alt = trim((string) $im[1]);
                $src = trim((string) $im[2]);
                if ($alt === '') {
                    $findings[] = wps_qa_finding(
                        'warn',
                        'image-alt-missing',
                        "Image without alt text: {$src}. Alt text is required for accessibility and image SEO."
                    );
                }
                if ($src !== '' && !preg_match('#^(https?:)?//#i', $src) && !str_starts_with($src, 'data:')) {
                    $resolved = $tourDir . '/' . ltrim($src, '/');
                    if (!is_file($resolved)) {
                        $findings[] = wps_qa_finding(
                            'warn',
                            'image-missing',
                            "Image referenced but missing on disk: {$src}"
                        );
                    }
                }
            }
        }

        // Broken internal anchors / generic anchor text. Detect ambiguous
        // anchor text — "click here", "read more", "here", "book now"
        // (when not pointing at a known booking domain).
        if (preg_match_all('/\[([^\]]+)\]\(([^)\s]+)\)/', $blogContent, $linkMatches, PREG_SET_ORDER)) {
            $generic = ['click here', 'read more', 'here', 'this link', 'learn more', 'more'];
            foreach ($linkMatches as $lm) {
                $label = trim(strip_tags($lm[1]));
                $href  = trim($lm[2]);
                $labelLower = strtolower($label);
                if (in_array($labelLower, $generic, true)) {
                    $findings[] = wps_qa_finding(
                        'warn',
                        'link-generic-anchor',
                        "Generic anchor text '{$label}' → {$href}. Use descriptive anchor text that includes the destination topic."
                    );
                }
                // Internal links that look like raw filesystem references.
                if (preg_match('/\.md(?:[?#].*)?$/i', $href)) {
                    $findings[] = wps_qa_finding(
                        'warn',
                        'link-raw-markdown',
                        "Link points to a raw markdown file: {$href}. Convert to a public URL or relative slug."
                    );
                }
            }
        }
    }

    $placeholderTargets = [
        'blog-post.md' => 'public-facing',
        'faq.md' => 'public-facing',
        'internal-links.md' => 'internal',
        'brief.md' => 'internal',
        'automation-notes.md' => 'internal',
    ];

    foreach ($placeholderTargets as $file => $kind) {
        $path = $tourDir . '/' . $file;
        if (!is_file($path)) {
            continue;
        }
        $content = (string) file_get_contents($path);
        if (preg_match(WPS_PLACEHOLDER_PATTERN, $content, $m)) {
            $findings[] = wps_qa_finding(
                $isPublished && $kind === 'public-facing' ? 'fail' : 'warn',
                'placeholder-link',
                "{$file} still contains placeholder " . $m[0] . '.'
            );
        }
    }

    $sourcePath = $tourDir . '/source-facts.md';
    if (is_file($sourcePath)) {
        $sourceContent = (string) file_get_contents($sourcePath);
        if (stripos($sourceContent, 'missing input') === false && stripos($sourceContent, 'human review') === false) {
            $findings[] = wps_qa_finding('warn', 'source-facts-incomplete', 'source-facts.md does not flag any missing inputs or human-review items.');
        }
    }

    // 15. slug-length + stop-word check on the public-facing slug.
    //     Long, stop-word-laden slugs dilute the URL ranking signal and
    //     show up truncated in SERPs.
    $publicSlug = trim((string) ($meta['public_slug'] ?? $meta['slug'] ?? ''));
    if ($publicSlug !== '') {
        if (mb_strlen($publicSlug) > 50) {
            $findings[] = wps_qa_finding(
                'warn',
                'slug-too-long',
                "public_slug '{$publicSlug}' is " . mb_strlen($publicSlug) . ' characters (target: ≤ 50). Trim filler tokens for cleaner SERP URLs.'
            );
        }
        $slugStopWords = ['a', 'an', 'the', 'and', 'or', 'of', 'for', 'to', 'in', 'on', 'at', 'with', 'by'];
        $slugTokens = array_values(array_filter(explode('-', mb_strtolower($publicSlug)), fn($t) => $t !== ''));
        $slugStopHits = array_values(array_unique(array_intersect($slugTokens, $slugStopWords)));
        if (!empty($slugStopHits)) {
            $findings[] = wps_qa_finding(
                'warn',
                'slug-stop-words',
                "public_slug '{$publicSlug}' includes stop word(s): " . implode(', ', $slugStopHits) . '. Drop them — they add length without ranking value.'
            );
        }
    }

    // ── Structural & technical SEO (Group 2b) ──────────────────────────
    $publicCopyState = (string) ($meta['public_copy_state'] ?? '');
    $isFinal = ($publicCopyState === 'final');

    // 16. canonical-url — validate the optional override field.
    //     blog/post.php derives the rendered rel=canonical from
    //     wps_public_post_url($publicSlug), so meta.canonical_url is
    //     not required. When it *is* set, it must be a well-formed
    //     absolute URL whose final path segment matches public_slug,
    //     otherwise it disagrees with what the renderer emits.
    $canonicalUrl = trim((string) ($meta['canonical_url'] ?? ''));
    if ($canonicalUrl === '') {
        // Nothing to check; renderer fills this in from public_slug.
    } elseif (!preg_match('#^https?://[^\s]+$#', $canonicalUrl)) {
        $findings[] = wps_qa_finding(
            'fail',
            'canonical-url-malformed',
            "meta.canonical_url '{$canonicalUrl}' is not an absolute https URL."
        );
    } elseif ($publicSlug !== '') {
        $canonicalPath = (string) parse_url($canonicalUrl, PHP_URL_PATH);
        $canonicalLastSegment = trim($canonicalPath, '/');
        if ($canonicalLastSegment !== '' && strpos($canonicalLastSegment, '/') !== false) {
            $parts = explode('/', $canonicalLastSegment);
            $canonicalLastSegment = end($parts);
        }
        if ($canonicalLastSegment !== '' && $canonicalLastSegment !== $publicSlug) {
            $findings[] = wps_qa_finding(
                'warn',
                'canonical-url-slug-mismatch',
                "meta.canonical_url ends with '{$canonicalLastSegment}' but public_slug is '{$publicSlug}'. The canonical path must match the published slug."
            );
        }
    }

    // 17. JSON-LD readiness — surface whether the data needed to emit
    //     FAQPage and TouristTrip/Product schemas is actually present.
    $faqPath = $tourDir . '/faq.md';
    if (is_file($faqPath)) {
        $faqContent = (string) file_get_contents($faqPath);
        $qaPairs = preg_match_all('/^##\s+.+\?\s*$/m', $faqContent);
        if ($qaPairs < 3 && $isFinal) {
            $findings[] = wps_qa_finding(
                'warn',
                'faq-jsonld-insufficient',
                "faq.md has only {$qaPairs} question heading(s); FAQPage JSON-LD needs ≥ 3 Q&A pairs to be worth emitting."
            );
        }
    }

    // TouristTrip / Product JSON-LD needs core commercial fields.
    // Note: hero_image is intentionally excluded — AGENTS.md §849 makes
    // image assets optional in this phase, so a missing hero is not a
    // JSON-LD blocker. The existing `image` slot is filled by the
    // renderer's fallback when meta.hero_image is unset.
    $productFields = [
        'canonical_tour_title' => 'name',
        'meta_description'    => 'description',
        'price_from'          => 'offers.price',
    ];
    $missingProduct = [];
    foreach ($productFields as $metaKey => $jsonLdKey) {
        if (trim((string) ($meta[$metaKey] ?? '')) === '') {
            $missingProduct[] = "{$jsonLdKey} (meta.{$metaKey})";
        }
    }
    if (!empty($missingProduct) && $isFinal) {
        $findings[] = wps_qa_finding(
            'warn',
            'product-jsonld-incomplete',
            'TouristTrip/Product JSON-LD cannot be fully emitted — missing: ' . implode('; ', $missingProduct) . '.'
        );
    }

    // 18. Internal-link SEO checks on internal-links.md.
    $internalLinksPath = $tourDir . '/internal-links.md';
    if (is_file($internalLinksPath)) {
        $ilContent = (string) file_get_contents($internalLinksPath);
        $ilLower   = mb_strtolower($ilContent);

        // 18a. Hub link — a BOFU page should link from its booking hub
        //      (best-day-trips-from-milan etc.). Soft signal: the word
        //      "hub" or a "Best ..." anchor appears.
        $funnel = (string) ($meta['funnel_stage'] ?? '');
        if ($funnel === 'BOFU' && $isFinal) {
            if (strpos($ilLower, 'hub') === false && strpos($ilLower, 'best ') === false) {
                $findings[] = wps_qa_finding(
                    'warn',
                    'internal-links-hub-missing',
                    'internal-links.md for this BOFU package mentions no hub link (no "hub" or "Best …" anchor). Add the inbound link from the booking hub.'
                );
            }
        }

        // 18b. Cross-funnel coverage — internal-links.md should mention
        //      at least two funnel stages so the cluster cross-links.
        $stages = [];
        foreach (['BOFU', 'MOFU', 'TOFU', 'FAQ'] as $stage) {
            if (strpos($ilLower, mb_strtolower($stage)) !== false) {
                $stages[] = $stage;
            }
        }
        if (count($stages) < 2 && $isFinal) {
            $findings[] = wps_qa_finding(
                'warn',
                'internal-links-no-cross-funnel',
                'internal-links.md references fewer than 2 funnel stages (BOFU/MOFU/TOFU/FAQ); cross-funnel cross-linking is missing.'
            );
        }

        // 18c. Anchor-text variety — duplicate anchor text dilutes
        //      relevance signals. Pull "quoted" and **bolded** anchors.
        $anchors = [];
        if (preg_match_all('/"([^"\n]{3,})"/', $ilContent, $am)) {
            foreach ($am[1] as $a) $anchors[] = trim($a);
        }
        if (preg_match_all('/\*\*([^*\n]{3,})\*\*/', $ilContent, $am)) {
            foreach ($am[1] as $a) $anchors[] = trim($a);
        }
        $normAnchors = array_map(fn($a) => mb_strtolower(trim($a)), $anchors);
        $dupes = array_keys(array_filter(array_count_values($normAnchors), fn($n) => $n > 1));
        if (!empty($dupes)) {
            $findings[] = wps_qa_finding(
                'warn',
                'internal-links-anchor-duplicate',
                'internal-links.md reuses identical anchor text: ' . implode('; ', array_slice($dupes, 0, 3)) . '. Vary anchors so each link carries a distinct topical signal.'
            );
        }
    }

    // 19. H2/H3 hierarchy + subhead duplication.
    if ($blogContent !== '') {
        $allHeads = [];
        if (preg_match_all('/^(#{1,6})\s+(.+?)\s*#*\s*$/m', $blogContent, $hm, PREG_SET_ORDER)) {
            foreach ($hm as $h) {
                $allHeads[] = ['level' => strlen($h[1]), 'text' => trim($h[2])];
            }
        }

        // H3 before any H2 = broken hierarchy.
        $seenH2 = false;
        $brokenH3 = null;
        foreach ($allHeads as $h) {
            if ($h['level'] === 2) { $seenH2 = true; }
            elseif ($h['level'] === 3 && !$seenH2) { $brokenH3 = $h['text']; break; }
        }
        if ($brokenH3 !== null) {
            $findings[] = wps_qa_finding(
                'warn',
                'subhead-hierarchy-broken',
                "blog-post.md uses an H3 ('{$brokenH3}') before any H2. Promote it or add an H2 parent."
            );
        }

        // Duplicate H2/H3 headings (normalized).
        $subheadNorm = [];
        foreach ($allHeads as $h) {
            if ($h['level'] === 2 || $h['level'] === 3) {
                $subheadNorm[] = preg_replace('/\s+/u', ' ', mb_strtolower($h['text']));
            }
        }
        $headDupes = array_keys(array_filter(array_count_values($subheadNorm), fn($n) => $n > 1));
        if (!empty($headDupes)) {
            $findings[] = wps_qa_finding(
                'warn',
                'subhead-duplicate',
                'blog-post.md has duplicate H2/H3 subhead(s): ' . implode('; ', array_slice($headDupes, 0, 3)) . '.'
            );
        }
    }

    // 20. Hero image: alt text + descriptive filename when one is set.
    //     Per AGENTS.md §839/§849 hero images are optional in this
    //     phase — a missing meta.hero_image is fine as long as no
    //     images/ folder exists (the older hero-image-not-set rule
    //     below already covers the images/-exists-but-unset case).
    if (!empty($meta['hero_image'])) {
        $hero = (string) $meta['hero_image'];
        $heroBase = basename(parse_url($hero, PHP_URL_PATH) ?: $hero);
        $heroSlug = preg_replace('/\.[^.]+$/', '', $heroBase) ?? $heroBase;
        $heroSlugNorm = preg_replace('/[^a-z0-9]+/i', ' ', mb_strtolower($heroSlug));
        $heroSlugNorm = trim(preg_replace('/\s+/u', ' ', $heroSlugNorm ?? '') ?? '');
        $genericHeroNames = ['hero', 'image', 'img', 'photo', 'cover', 'banner', 'default', 'placeholder'];
        if ($heroSlugNorm !== '' && in_array($heroSlugNorm, $genericHeroNames, true)) {
            $findings[] = wps_qa_finding(
                'warn',
                'hero-image-filename-generic',
                "meta.hero_image filename '{$heroBase}' is generic. Use a keyword-bearing filename (e.g. include the primary keyword)."
            );
        }
        // Hero alt text — only checkable when the hero is rendered inline
        // in blog-post.md via the same path.
        if ($blogContent !== '' && !preg_match('#^https?://#i', $hero)) {
            $heroRef = preg_quote(ltrim($hero, '/'), '#');
            if (preg_match('#!\[([^\]]*)\]\([^)\s]*' . $heroRef . '#', $blogContent, $am)) {
                if (trim((string) $am[1]) === '') {
                    $findings[] = wps_qa_finding(
                        'warn',
                        'hero-image-alt-missing',
                        'Hero image is referenced in blog-post.md without alt text.'
                    );
                } elseif ($pkNorm !== '' && mb_strpos(mb_strtolower($am[1]), $pkNorm) === false) {
                    $findings[] = wps_qa_finding(
                        'warn',
                        'hero-image-alt-keyword-missing',
                        "Hero image alt text '{$am[1]}' does not include the primary keyword."
                    );
                }
            }
        }
    }

    if (!empty($meta['hero_image'])) {
        $hero = (string) $meta['hero_image'];
        if (!preg_match('#^https?://#', $hero)) {
            $resolved = $tourDir . '/' . ltrim($hero, '/');
            if (!is_file($resolved)) {
                $findings[] = wps_qa_finding('warn', 'hero-image-missing', "meta.hero_image '{$hero}' does not exist on disk.");
            }
        }
    } else {
        if (is_dir($tourDir . '/images')) {
            $findings[] = wps_qa_finding('warn', 'hero-image-not-set', 'images/ folder exists but meta.hero_image is not set.');
        }
    }

    if ($publishStatus === 'published') {
        foreach ($findings as $i => $f) {
            if ($f['severity'] === 'warn' && in_array($f['code'], ['placeholder-link', 'source-facts-incomplete'], true)) {
                $findings[$i]['severity'] = 'fail';
                $findings[$i]['message'] .= ' (escalated: publish_status=published)';
            }
        }
    }

    $overall = 'pass';
    foreach ($findings as $f) {
        if ($f['severity'] === 'fail') {
            $overall = 'fail';
            break;
        }
        if ($f['severity'] === 'warn') {
            $overall = 'warning';
        }
    }

    return [
        'tour' => basename($tourDir),
        'overall' => $overall,
        'findings' => $findings,
        'meta' => $meta,
    ];
}

function wps_qa_finding(string $severity, string $code, string $message): array
{
    return ['severity' => $severity, 'code' => $code, 'message' => $message];
}

function wps_qa_run_all(string $toursRoot): array
{
    $reports = [];
    if (!is_dir($toursRoot)) {
        return $reports;
    }

    foreach (scandir($toursRoot) ?: [] as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }
        $path = $toursRoot . '/' . $entry;
        if (!is_dir($path)) {
            continue;
        }
        $reports[] = wps_qa_run_for_tour($path);
    }

    // Cross-package checks. These attach findings to individual reports
    // so the existing per-tour UI doesn't need a separate code path.
    wps_qa_apply_cross_package_findings($reports);

    return $reports;
}

/**
 * Stale freshness threshold for published content, in days. Travel content
 * decays fast (prices, hours, seasonal info), so anything older than this
 * is flagged for refresh on the dashboard and downgraded to qa_status=stale.
 */
const WPS_QA_FRESHNESS_THRESHOLD_DAYS = 90;

function wps_qa_apply_cross_package_findings(array &$reports): void
{
    if (empty($reports)) {
        return;
    }

    // -- S7: keyword cannibalization across the same cluster ----------------
    // Two published/ready posts that share the same primary_keyword and live
    // in the same cluster (variant_of chain) will compete in SERPs.
    $byKeywordCluster = [];
    foreach ($reports as $i => $report) {
        $meta = $report['meta'] ?? [];
        if (!is_array($meta)) {
            continue;
        }
        $keyword = strtolower(trim((string) ($meta['primary_keyword'] ?? '')));
        $cluster = strtolower(trim((string) ($meta['variant_of'] ?? $meta['slug'] ?? $report['tour'] ?? '')));
        $status  = (string) ($meta['publish_status'] ?? 'draft');
        $isLive  = in_array($status, ['ready_for_review', 'published', 'published', 'published'], true);

        if ($keyword === '' || !$isLive) {
            continue;
        }
        $key = $cluster . '||' . $keyword;
        $byKeywordCluster[$key][] = $i;
    }

    foreach ($byKeywordCluster as $key => $indexes) {
        if (count($indexes) < 2) {
            continue;
        }
        [, $keyword] = explode('||', $key, 2);
        $siblings = [];
        foreach ($indexes as $i) {
            $siblings[] = (string) ($reports[$i]['tour'] ?? '');
        }
        $sibList = implode(', ', array_values(array_filter($siblings)));
        foreach ($indexes as $i) {
            $self = (string) ($reports[$i]['tour'] ?? '');
            $others = implode(', ', array_values(array_filter($siblings, fn($s) => $s !== $self)));
            $reports[$i]['findings'][] = wps_qa_finding(
                'warn',
                'keyword-cannibalization',
                "primary_keyword '{$keyword}' also targeted by sibling(s) in cluster: {$others}. These posts will compete in SERPs — diversify modifiers or merge."
            );
            if ($reports[$i]['overall'] === 'pass') {
                $reports[$i]['overall'] = 'warning';
            }
        }
        unset($sibList);
    }

    // -- S7b: page_title near-duplicate cannibalization across cluster ------
    // Two siblings in the same cluster_parent with normalized page_titles
    // ≥ 75% similar will compete on title-tag signal even if their
    // primary_keyword differs.
    //
    // Cluster membership is resolved from the registry first (the rest of
    // the platform treats the cluster registry as source of truth — see
    // wps_index_tour_clusters() in functions.php). The tour-side
    // `meta.cluster_parent` hint is used only as a fallback so the BOFU
    // primary asset (which does not carry its own cluster_parent) is still
    // compared against its siblings.
    $clusterIndex = function_exists('wps_index_tour_clusters') ? wps_index_tour_clusters() : ['by_package_slug' => []];
    $byPackageSlug = $clusterIndex['by_package_slug'] ?? [];

    $byCluster = [];
    foreach ($reports as $i => $report) {
        $meta = $report['meta'] ?? [];
        if (!is_array($meta)) {
            continue;
        }
        $tour = (string) ($report['tour'] ?? '');
        $registryCluster = '';
        if ($tour !== '' && isset($byPackageSlug[$tour]['cluster']['cluster_parent'])) {
            $registryCluster = strtolower(trim((string) $byPackageSlug[$tour]['cluster']['cluster_parent']));
        }
        $hintCluster = strtolower(trim((string) ($meta['cluster_parent'] ?? '')));
        $cluster = $registryCluster !== '' ? $registryCluster : $hintCluster;
        $status  = (string) ($meta['publish_status'] ?? 'draft');
        $isLive  = in_array($status, ['ready_for_review', 'published'], true);
        $title   = trim((string) ($meta['page_title'] ?? ''));
        if ($cluster === '' || $title === '' || !$isLive) {
            continue;
        }
        $byCluster[$cluster][] = ['i' => $i, 'title' => $title];
    }
    $normalize = function (string $s): string {
        $s = mb_strtolower($s);
        $s = preg_replace('/[^a-z0-9\s]+/u', ' ', $s) ?? $s;
        return trim(preg_replace('/\s+/u', ' ', $s) ?? $s);
    };
    foreach ($byCluster as $clusterSlug => $items) {
        if (count($items) < 2) {
            continue;
        }
        for ($a = 0; $a < count($items); $a++) {
            for ($b = $a + 1; $b < count($items); $b++) {
                similar_text($normalize($items[$a]['title']), $normalize($items[$b]['title']), $pct);
                if ($pct >= 75) {
                    $ai = $items[$a]['i'];
                    $bi = $items[$b]['i'];
                    $aTour = (string) ($reports[$ai]['tour'] ?? '');
                    $bTour = (string) ($reports[$bi]['tour'] ?? '');
                    foreach ([$ai => $bTour, $bi => $aTour] as $self => $sib) {
                        $reports[$self]['findings'][] = wps_qa_finding(
                            'warn',
                            'title-cannibalization',
                            "page_title is " . round($pct) . "% similar to sibling '{$sib}' in cluster '{$clusterSlug}'. Differentiate the title-tag angle to avoid SERP competition."
                        );
                        if ($reports[$self]['overall'] === 'pass') {
                            $reports[$self]['overall'] = 'warning';
                        }
                    }
                }
            }
        }
    }

    // -- S11: freshness pass for published packages -------------------------
    $today = new DateTimeImmutable('today', new DateTimeZone('UTC'));
    foreach ($reports as $i => $report) {
        $meta = $report['meta'] ?? [];
        if (!is_array($meta)) {
            continue;
        }
        if ((string) ($meta['publish_status'] ?? '') !== 'published') {
            continue;
        }
        // Freshness anchors on last_content_refresh_at (set when copy is
        // actually rewritten) and falls back to first_published_at, so a
        // passive QA stamp does not reset the staleness clock.
        $anchor = (string) ($meta['last_content_refresh_at']
            ?? $meta['first_published_at']
            ?? $meta['last_qa_date']
            ?? '');
        if ($anchor === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $anchor)) {
            $reports[$i]['findings'][] = wps_qa_finding(
                'warn',
                'freshness-unknown',
                'Published package has no first_published_at / last_content_refresh_at; cannot evaluate freshness.'
            );
            if ($reports[$i]['overall'] === 'pass') {
                $reports[$i]['overall'] = 'warning';
            }
            continue;
        }
        $lastDate = DateTimeImmutable::createFromFormat('!Y-m-d', $anchor, new DateTimeZone('UTC'));
        if ($lastDate === false) {
            continue;
        }
        $ageDays = (int) $today->diff($lastDate)->days;
        if ($ageDays > WPS_QA_FRESHNESS_THRESHOLD_DAYS) {
            $reports[$i]['findings'][] = wps_qa_finding(
                'warn',
                'content-stale',
                "Published content is {$ageDays} days old (threshold: " . WPS_QA_FRESHNESS_THRESHOLD_DAYS . " days). Refresh prices, hours, seasonal claims and re-run QA."
            );
            if ($reports[$i]['overall'] === 'pass') {
                $reports[$i]['overall'] = 'warning';
            }
        }
    }
}

/**
 * Lightweight schema validator. Supports the subset of JSON Schema
 * actually used in content-system/meta.schema.json: required, type,
 * enum, pattern, minLength, maxLength, minimum, items.type,
 * additionalProperties (object value types).
 */
function wps_meta_schema_findings(array $meta): array
{
    $findings = [];
    if (!is_file(WPS_META_SCHEMA_PATH)) {
        return $findings;
    }

    $schema = json_decode((string) file_get_contents(WPS_META_SCHEMA_PATH), true);
    if (!is_array($schema)) {
        return [wps_qa_finding('warn', 'schema-load', 'meta.schema.json could not be parsed.')];
    }

    foreach ($schema['required'] ?? [] as $field) {
        if (!array_key_exists($field, $meta) || $meta[$field] === '' || $meta[$field] === null) {
            $findings[] = wps_qa_finding('fail', 'meta-field', "meta.{$field} is required.");
        }
    }

    foreach ($schema['properties'] ?? [] as $field => $rules) {
        if (!array_key_exists($field, $meta) || $meta[$field] === null) {
            continue;
        }
        $value = $meta[$field];

        if (isset($rules['enum']) && !in_array($value, $rules['enum'], true)) {
            $findings[] = wps_qa_finding('fail', 'meta-enum', "meta.{$field} value " . wps_qa_format_value($value) . ' is not in enum.');
        }

        if (isset($rules['pattern']) && is_string($value) && !preg_match('/' . str_replace('/', '\\/', $rules['pattern']) . '/', $value)) {
            $findings[] = wps_qa_finding('fail', 'meta-pattern', "meta.{$field} '{$value}' does not match pattern.");
        }

        if (isset($rules['minLength']) && is_string($value) && mb_strlen($value) < $rules['minLength']) {
            $findings[] = wps_qa_finding('warn', 'meta-min-length', "meta.{$field} is shorter than {$rules['minLength']} characters.");
        }

        if (isset($rules['maxLength']) && is_string($value) && mb_strlen($value) > $rules['maxLength']) {
            $findings[] = wps_qa_finding('warn', 'meta-max-length', "meta.{$field} is longer than {$rules['maxLength']} characters.");
        }

        if (isset($rules['minimum']) && is_numeric($value) && $value < $rules['minimum']) {
            $findings[] = wps_qa_finding('fail', 'meta-minimum', "meta.{$field} is below minimum {$rules['minimum']}.");
        }

        if (isset($rules['type']) && !wps_qa_type_matches($value, $rules['type'])) {
            $findings[] = wps_qa_finding('fail', 'meta-type', "meta.{$field} has wrong type.");
        }
    }

    return $findings;
}

function wps_qa_type_matches($value, $type): bool
{
    $types = is_array($type) ? $type : [$type];

    foreach ($types as $t) {
        switch ($t) {
            case 'string':
                if (is_string($value)) return true;
                break;
            case 'number':
                if (is_int($value) || is_float($value)) return true;
                break;
            case 'integer':
                if (is_int($value)) return true;
                break;
            case 'boolean':
                if (is_bool($value)) return true;
                break;
            case 'array':
                if (is_array($value) && array_keys($value) === range(0, count($value) - 1)) return true;
                if ($value === []) return true;
                break;
            case 'object':
                if (is_array($value) && (count($value) === 0 || array_keys($value) !== range(0, count($value) - 1))) return true;
                break;
            case 'null':
                if ($value === null) return true;
                break;
        }
    }

    return false;
}

/**
 * Pull bullet items from a named ## section of keywords.md. Matching is
 * substring-and-case-insensitive on the heading, so a needle of
 * "long-tail" still resolves "Long-tail booking-intent keywords".
 */
function wps_qa_keywords_section_items(string $content, string $sectionNeedle): array
{
    if (!preg_match_all('/^##\s+(.+?)\s*$\R(.*?)(?=^##\s|\z)/ms', $content, $matches, PREG_SET_ORDER)) {
        return [];
    }
    $needle = mb_strtolower($sectionNeedle);
    foreach ($matches as $section) {
        if (mb_strpos(mb_strtolower($section[1]), $needle) === false) {
            continue;
        }
        $items = [];
        if (preg_match_all('/^\s*[-*]\s+(.+?)\s*$/m', $section[2], $li)) {
            foreach ($li[1] as $item) {
                $item = trim($item);
                if ($item !== '') {
                    $items[] = $item;
                }
            }
        }
        return $items;
    }
    return [];
}

function wps_qa_format_value($value): string
{
    if (is_string($value)) return "'{$value}'";
    if (is_bool($value)) return $value ? 'true' : 'false';
    if (is_array($value)) return json_encode($value);
    return (string) $value;
}

function wps_clarifications_findings(array $meta, bool $isPublished): array
{
    $findings = [];
    $clarifications = $meta['clarifications_needed'] ?? null;

    if (!is_array($clarifications) || $clarifications === []) {
        return $findings;
    }

    foreach ($clarifications as $i => $item) {
        if (!is_array($item)) {
            continue;
        }
        $field = (string) ($item['field'] ?? "clarification[{$i}]");
        $blocking = $item['blocking'] ?? true;
        $severity = $blocking ? 'fail' : 'warn';
        $question = (string) ($item['question'] ?? 'unresolved clarification');
        $findings[] = wps_qa_finding($severity, 'clarification-pending', "Pending WPS:CLARIFY for meta.{$field}: {$question}");
    }

    return $findings;
}

/**
 * Stamp last_qa_date and qa_status into meta.json based on a report.
 * Returns the updated meta array, or null when meta.json is missing.
 */
function wps_qa_stamp_meta(string $tourDir, array $report): ?array
{
    $metaPath = $tourDir . '/meta.json';
    if (!is_file($metaPath)) {
        return null;
    }

    $meta = json_decode((string) file_get_contents($metaPath), true);
    if (!is_array($meta)) {
        return null;
    }

    $statusMap = [
        'pass' => 'passing',
        'warning' => 'warning',
        'fail' => 'needs_fix',
    ];

    $resolvedStatus = $statusMap[$report['overall']] ?? 'pending';

    // If freshness was the only thing keeping the report at 'warning',
    // surface that as qa_status='stale' so the dashboard can show a
    // distinct refresh CTA instead of a generic warning.
    if ($report['overall'] === 'warning') {
        $codes = array_column($report['findings'] ?? [], 'code');
        if (in_array('content-stale', $codes, true)) {
            $resolvedStatus = 'stale';
        }
    }

    $meta['qa_status'] = $resolvedStatus;
    $meta['last_qa_date'] = gmdate('Y-m-d');

    file_put_contents(
        $metaPath,
        json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n"
    );

    if (function_exists('wps_archive_index_invalidate')) {
        wps_archive_index_invalidate();
    }

    return $meta;
}
