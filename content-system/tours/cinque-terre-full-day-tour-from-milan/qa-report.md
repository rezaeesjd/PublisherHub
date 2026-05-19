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

> Rows F, G, I, J, M are SEO checks added in Group 1. Rows A, B, D, E are now enforced by `platform/qa-rules.php` (title 50–60, meta description 140–160, H1 count, H1↔title parity, title cannibalization). Row L remains manual until an internal-link runner is added.
