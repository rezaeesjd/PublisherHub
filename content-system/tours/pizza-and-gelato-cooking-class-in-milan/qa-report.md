# QA Report

- Date: 2026-05-12
- Package: `pizza-and-gelato-cooking-class-in-milan`
- Command: `WPS:GENERATE_CONTENT`

## Required files check
- PASS: All 9 required files present.

## Content and compliance checks
- PASS: `source-facts.md` created before public post drafting.
- PASS: Canonical title used verbatim from source.
- PASS: OTA links preserved as provided.
- PASS: At least one real booking permalink is present (Viator + TripAdvisor).
- PASS: Brand mention included in public copy.
- PASS: No unsupported review/rating claims.
- PASS: Allergy and accessibility disclosure included.
- PASS: Schedule Start confirmed by operator as 15:00 (3 PM); CSV "03:00" was a typo, now corrected.
- WARNING: Cancellation field value lacks unit; specifics omitted from public claims.

## Status
- qa_status: `warning`
- publish_status: `ready_for_review`

## Action items
1. Confirm cancellation policy unit (hours/days) for "9".
2. Confirm minimum traveler count to operate.


## SEO Scorecard

Measured by `platform/qa-rules.php` (machine) + reviewer judgment (manual). Targets and verdicts follow Group-1 SEO rules.

| # | Check | Value | Target | Verdict |
|---|---|---|---|---|
| A | `meta.page_title` length | 61 chars | 50–60 | warn |
| B | `meta.meta_description` length | 122 chars | 140–160 | warn |
| C | `meta.public_slug` length & kebab-case | 39 chars | ≤ 50, kebab | pass |
| D | Single H1 in `blog-post.md` | 1 H1 | exactly 1 | pass |
| E | H1 ↔ `page_title` similarity | 83% | ≥ 60% | pass |
| F | Primary keyword in `page_title` prefix | yes | yes | pass |
| G | Primary keyword in H1 | yes | yes | pass |
| H | Brand in `blog-post.md` | no | yes | warn |
| I | Cluster `primary_keyword` cannibalization | no | no | pass |
| J | Cluster `page_title` ≥75% sibling overlap | no | no | pass |
| K | Hero image + alt | no images/ | when images/ present | n/a |
| L | Internal links: hub + sibling | see `internal-links.md` | both | manual |
| M | Word count (not_started) | 40 words | n/a (archived/source-only) | n/a |
| N | Retired `-vN` slug | no | no | pass |
| O | Primary keyword in first 100 words of `blog-post.md` | yes | yes | pass |
| P | Primary keyword in ≥ 1 H2 of `blog-post.md` | no H2 | yes | n/a |
| Q | Primary keyword in last 200 words (conclusion) | yes | yes | pass |
| R | Long-tail keyword coverage from `keywords.md` | 0/5 | > 50% present | warn |
| S | `public_slug` stop-word hits | and, in | none | warn |
| T | `meta.canonical_url` override (optional) — well-formed + matches `public_slug` | computed (from public_slug) | n/a unless overridden | pass |
| U | FAQPage JSON-LD ready (`faq.md` ≥ 3 Q&A) | 8 pairs | ≥ 3 (final) | pass |
| V | TouristTrip/Product JSON-LD fields present | all present | name/description/offers.price (image optional) | pass |
| W | `internal-links.md` cross-funnel coverage | BOFU/MOFU/TOFU/FAQ | ≥ 2 stages | pass |
| X | `internal-links.md` anchor-text variety | no duplicates (4 anchors) | none duplicate | pass |
| Y | H2/H3 hierarchy + no duplicates | ok | ok | pass |
| Z | Hero image (optional) — alt + descriptive filename when set | not set (optional, no images/) | required only when images/ exists | pass |

> Rows F, G, I, J, M are SEO checks added in Group 1. Rows A, B, D, E are now enforced by `platform/qa-rules.php` (title 50–60, meta description 140–160, H1 count, H1↔title parity, title cannibalization). Row L remains manual until an internal-link runner is added.
>
> Rows O–S are on-page SEO checks added in Group 2a (primary-keyword distribution: first-100-words / H2 / conclusion; long-tail keywords-coverage from `keywords.md`; `public_slug` stop-word hits). All enforced by `platform/qa-rules.php`.
>
> Rows T–Z are structural/technical SEO checks added in Group 2b (`canonical_url`, JSON-LD readiness for FAQPage and TouristTrip/Product, internal-links cross-funnel + anchor variety, H2/H3 hierarchy, hero image readiness).
