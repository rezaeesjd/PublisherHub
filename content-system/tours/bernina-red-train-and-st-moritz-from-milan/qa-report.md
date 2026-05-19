# QA Report

- Date: 2026-05-13
- Package: `bernina-red-train-and-st-moritz-from-milan`
- Command: `WPS:GENERATE_CONTENT`

## Required files check
- PASS: source-facts.md present
- PASS: brief.md present
- PASS: keywords.md present
- PASS: blog-post.md present
- PASS: faq.md present
- PASS: internal-links.md present
- PASS: meta.json present
- PASS: automation-notes.md present
- PASS: qa-report.md present

## Content and compliance checks
- PASS: source-facts.md exists and is populated before public copy.
- PASS: blog-post.md is public-facing and includes one primary CTA.
- PASS: primary CTA uses real website booking URL.
- PASS: passport requirement is disclosed.
- PASS: non-refundable cancellation policy is disclosed.
- PASS: mobility restriction is disclosed.
- WARNING: product reference code is still missing in source input.
- WARNING: exclusions field remains blank in source input.

## Process QA linkage
- See: `content-system/system-qa/reports/2026-05-13-bernina-red-train-and-st-moritz-from-milan-process-qa.md`

## Status
- qa_status: `passing`
- publish_status: `ready_for_review`
- public_copy_state: `final`

## Action items
1. Add internal product reference code when available.
2. Add explicit exclusions list when available.


## SEO Scorecard

Measured by `platform/qa-rules.php` (machine) + reviewer judgment (manual). Targets and verdicts follow Group-1 SEO rules.

| # | Check | Value | Target | Verdict |
|---|---|---|---|---|
| A | `meta.page_title` length | 61 chars | 50–60 | warn |
| B | `meta.meta_description` length | 139 chars | 140–160 | warn |
| C | `meta.public_slug` length & kebab-case | 42 chars | ≤ 50, kebab | pass |
| D | Single H1 in `blog-post.md` | 1 H1 | exactly 1 | pass |
| E | H1 ↔ `page_title` similarity | 91% | ≥ 60% | pass |
| F | Primary keyword in `page_title` prefix | yes | yes | pass |
| G | Primary keyword in H1 | yes | yes | pass |
| H | Brand in `blog-post.md` | yes | yes | pass |
| I | Cluster `primary_keyword` cannibalization | no | no | pass |
| J | Cluster `page_title` ≥75% sibling overlap | no | no | pass |
| K | Hero image + alt | no images/ | when images/ present | n/a |
| L | Internal links: hub + sibling | see `internal-links.md` | both | manual |
| M | Word count (final) | 422 words | 500–900 | warn |
| N | Retired `-vN` slug | no | no | pass |

> Rows F, G, I, J, M are SEO checks added in Group 1. Rows A, B, D, E are now enforced by `platform/qa-rules.php` (title 50–60, meta description 140–160, H1 count, H1↔title parity, title cannibalization). Row L remains manual until an internal-link runner is added.
