# QA Report

- Date: 2026-05-12
- Package: `all-in-one-lake-como-bellagio-and-lugano-from-milan`
- Command: `WPS:GENERATE_CONTENT`

## Required files check
- PASS: All 9 required files present.

## Content and compliance checks
- PASS: `source-facts.md` created before public post drafting.
- PASS: Canonical title spacing normalized; recorded in `clarify_decisions`.
- PASS: OTA links preserved as provided.
- PASS: At least one real booking permalink is present (Viator + TripAdvisor).
- PASS: Brand mention included in public copy.
- PASS: No unsupported review/rating claims.
- PASS: Itinerary-order disclosure included in copy.
- WARNING: Cancellation field value lacks unit; specifics omitted from public claims.
- WARNING: Source flags public-transport access as No.

## Status
- qa_status: `warning`
- publish_status: `ready_for_review`

## Action items
1. Confirm cancellation policy unit (hours/days) before publishing a specific policy statement.
2. Confirm minimum traveler count to operate.
3. Confirm whether a Switzerland passport reminder should be added in copy/FAQ.


## SEO Scorecard

Measured by `platform/qa-rules.php` (machine) + reviewer judgment (manual). Targets and verdicts follow Group-1 SEO rules.

| # | Check | Value | Target | Verdict |
|---|---|---|---|---|
| A | `meta.page_title` length | 63 chars | 50–60 | warn |
| B | `meta.meta_description` length | 119 chars | 140–160 | warn |
| C | `meta.public_slug` length & kebab-case | 51 chars | ≤ 50, kebab | warn |
| D | Single H1 in `blog-post.md` | 1 H1 | exactly 1 | pass |
| E | H1 ↔ `page_title` similarity | 75% | ≥ 60% | pass |
| F | Primary keyword in `page_title` prefix | yes | yes | pass |
| G | Primary keyword in H1 | yes | yes | pass |
| H | Brand in `blog-post.md` | no | yes | warn |
| I | Cluster `primary_keyword` cannibalization | no | no | pass |
| J | Cluster `page_title` ≥75% sibling overlap | no | no | pass |
| K | Hero image + alt | no images/ | when images/ present | n/a |
| L | Internal links: hub + sibling | see `internal-links.md` | both | manual |
| M | Word count (not_started) | 41 words | n/a (archived/source-only) | n/a |
| N | Retired `-vN` slug | no | no | pass |
| O | Primary keyword in first 100 words of `blog-post.md` | no | yes | warn |
| P | Primary keyword in ≥ 1 H2 of `blog-post.md` | no H2 | yes | n/a |
| Q | Primary keyword in last 200 words (conclusion) | no | yes | warn |
| R | Long-tail keyword coverage from `keywords.md` | 0/5 | > 50% present | warn |
| S | `public_slug` stop-word hits | in, and | none | warn |

> Rows F, G, I, J, M are SEO checks added in Group 1. Rows A, B, D, E are now enforced by `platform/qa-rules.php` (title 50–60, meta description 140–160, H1 count, H1↔title parity, title cannibalization). Row L remains manual until an internal-link runner is added.
>
> Rows O–S are on-page SEO checks added in Group 2a (primary-keyword distribution: first-100-words / H2 / conclusion; long-tail keywords-coverage from `keywords.md`; `public_slug` stop-word hits). All enforced by `platform/qa-rules.php`.
