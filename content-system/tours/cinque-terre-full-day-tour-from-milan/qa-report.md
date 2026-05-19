# QA Report

- Date: 2026-05-13
- Package: `cinque-terre-full-day-tour-from-milan`
- Command: `WPS:GENERATE_CONTENT`

## Required files check
- PASS: All 9 required package files are present.

## Generation checks
- PASS: `source-facts.md` exists and includes required provenance matrix rows.
- PASS: Public copy is active in `blog-post.md` (not holding notice, not disabled).
- PASS: Brand mention present in public copy (Milano Adventures).
- PASS: Website booking URL used as primary CTA.
- PASS: Viator/TripAdvisor links retained as secondary trust options.
- PASS: Funnel assets include strategy, keywords, FAQ, internal links, and automation notes.

## Warnings
- WARNING: Cancellation field remains unit-ambiguous in source (`15`); no precise cancellation promise is made in public copy.
- WARNING: Accessibility details remain missing in source data.

## Status
- qa_status: `warning`
- publish_status: `ready_for_review`

## Next actions
1. Confirm cancellation unit (hours or days) if you want exact policy text in public article/FAQ.
2. Add wheelchair accessibility details if available.

## Process QA linkage
- `content-system/system-qa/reports/2026-05-13-cinque-terre-full-day-tour-from-milan-process-qa.md`


## SEO Scorecard

Measured by `platform/qa-rules.php` (machine) + reviewer judgment (manual). Targets and verdicts follow Group-1 SEO rules.

| # | Check | Value | Target | Verdict |
|---|---|---|---|---|
| A | `meta.page_title` length | 73 chars | 50–60 | warn |
| B | `meta.meta_description` length | 121 chars | 140–160 | warn |
| C | `meta.public_slug` length & kebab-case | 51 chars | ≤ 50, kebab | warn |
| D | Single H1 in `blog-post.md` | 1 H1 | exactly 1 | pass |
| E | H1 ↔ `page_title` similarity | 95% | ≥ 60% | pass |
| F | Primary keyword in `page_title` prefix | yes | yes | pass |
| G | Primary keyword in H1 | yes | yes | pass |
| H | Brand in `blog-post.md` | yes | yes | pass |
| I | Cluster `primary_keyword` cannibalization | no | no | pass |
| J | Cluster `page_title` ≥75% sibling overlap | no | no | pass |
| K | Hero image + alt | no images/ | when images/ present | n/a |
| L | Internal links: hub + sibling | see `internal-links.md` | both | manual |
| M | Word count (final) | 369 words | 500–900 | warn |
| N | Retired `-vN` slug | no | no | pass |
| O | Primary keyword in first 100 words of `blog-post.md` | yes | yes | pass |
| P | Primary keyword in ≥ 1 H2 of `blog-post.md` | no | yes | warn |
| Q | Primary keyword in last 200 words (conclusion) | yes | yes | pass |
| R | Long-tail keyword coverage from `keywords.md` | 0/8 | > 50% present | warn |
| S | `public_slug` stop-word hits | none | none | pass |
| T | `meta.canonical_url` override (optional) — well-formed + matches `public_slug` | computed (from public_slug) | n/a unless overridden | pass |
| U | FAQPage JSON-LD ready (`faq.md` ≥ 3 Q&A) | 7 pairs | ≥ 3 (final) | pass |
| V | TouristTrip/Product JSON-LD fields present | missing: image | none missing (final) | warn |
| W | `internal-links.md` cross-funnel coverage | BOFU/MOFU/TOFU | ≥ 2 stages | pass |
| X | `internal-links.md` anchor-text variety | no duplicates (6 anchors) | none duplicate | pass |
| Y | H2/H3 hierarchy + no duplicates | ok | ok | pass |
| Z | Hero image: present + descriptive filename + alt with keyword | not set | yes (final) | warn |

> Rows F, G, I, J, M are SEO checks added in Group 1. Rows A, B, D, E are now enforced by `platform/qa-rules.php` (title 50–60, meta description 140–160, H1 count, H1↔title parity, title cannibalization). Row L remains manual until an internal-link runner is added.
>
> Rows O–S are on-page SEO checks added in Group 2a (primary-keyword distribution: first-100-words / H2 / conclusion; long-tail keywords-coverage from `keywords.md`; `public_slug` stop-word hits). All enforced by `platform/qa-rules.php`.
>
> Rows T–Z are structural/technical SEO checks added in Group 2b (`canonical_url`, JSON-LD readiness for FAQPage and TouristTrip/Product, internal-links cross-funnel + anchor variety, H2/H3 hierarchy, hero image readiness).
