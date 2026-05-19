# QA Report — Cinque Terre MOFU Comparison Post

## Tour Identity Confirmation
- Requested command: `WPS:GENERATE_CONTENT`
- Actual package folder: `content-system/tours/cinque-terre-full-day-tour-from-milan-vs-diy/`
- Canonical tour title: Cinque Terre Full-Day Tour from Milan
- MOFU post title: Cinque Terre from Milan: Guided Tour vs DIY by Train — Honest Comparison
- Product/reference code: 187808P109
- Active brand: Milano Adventures
- Website URL status: confirmed (inherited from base package)
- TripAdvisor URL status: confirmed (inherited from base package)
- Viator URL status: confirmed (inherited from base package)
- Package created: 2026-05-13
- Report scope: generation

## Status
- Generation: complete
- Publish: not yet verified
- Live verification: not yet verified

## QA Findings
- PASS: All 9 required files present.
- PASS: Source-facts provenance matrix present; guided-tour facts from confirmed base package; DIY comparison facts flagged as inferred.
- PASS: No deprecated field aliases used.
- PASS: Blog-post.md contains exactly one H1.
- PASS: Brand (Milano Adventures) present in blog-post.md.
- PASS: Primary CTA uses confirmed website booking URL.
- PASS: OTA links placed as secondary trust references after primary CTA.
- PASS: No placeholder tokens (`{{...}}`) in blog-post.md.
- PASS: Clarify gate — no blocking clarifications; auto_resolved.
- PASS: Status triad block present in this report.
- PASS: DIY comparison facts clearly flagged as inferred in source-facts.md.
- WARNING: Cancellation unit unspecified ("15"); specific policy excluded from public copy (inherited non-blocking).
- WARNING: Accessibility details not provided; omitted from public copy (inherited non-blocking).
- NOTE: Comparison table includes DIY data inferred from general destination knowledge; human reviewer should validate DIY logistics and cost estimates before publish.

## Verdict
Generation readiness: ready_for_review with inherited warnings and human review note on DIY comparison data.
Publish readiness: not yet verified.


## SEO Scorecard

Measured by `platform/qa-rules.php` (machine) + reviewer judgment (manual). Targets and verdicts follow Group-1 SEO rules.

| # | Check | Value | Target | Verdict |
|---|---|---|---|---|
| A | `meta.page_title` length | 72 chars | 50–60 | warn |
| B | `meta.meta_description` length | 163 chars | 140–160 | warn |
| C | `meta.public_slug` length & kebab-case | 51 chars | ≤ 50, kebab | warn |
| D | Single H1 in `blog-post.md` | 1 H1 | exactly 1 | pass |
| E | H1 ↔ `page_title` similarity | 100% | ≥ 60% | pass |
| F | Primary keyword in `page_title` prefix | yes | yes | pass |
| G | Primary keyword in H1 | yes | yes | pass |
| H | Brand in `blog-post.md` | yes | yes | pass |
| I | Cluster `primary_keyword` cannibalization | no | no | pass |
| J | Cluster `page_title` ≥75% sibling overlap | no | no | pass |
| K | Hero image + alt | no images/ | when images/ present | n/a |
| L | Internal links: hub + sibling | see `internal-links.md` | both | manual |
| M | Word count (final) | 970 words | 500–900 | warn |
| N | Retired `-vN` slug | no | no | pass |
| O | Primary keyword in first 100 words of `blog-post.md` | no | yes | warn |
| P | Primary keyword in ≥ 1 H2 of `blog-post.md` | no | yes | warn |
| Q | Primary keyword in last 200 words (conclusion) | no | yes | warn |
| R | Long-tail keyword coverage from `keywords.md` | 0/5 | > 50% present | warn |
| S | `public_slug` stop-word hits | by | none | warn |

> Rows F, G, I, J, M are SEO checks added in Group 1. Rows A, B, D, E are now enforced by `platform/qa-rules.php` (title 50–60, meta description 140–160, H1 count, H1↔title parity, title cannibalization). Row L remains manual until an internal-link runner is added.
>
> Rows O–S are on-page SEO checks added in Group 2a (primary-keyword distribution: first-100-words / H2 / conclusion; long-tail keywords-coverage from `keywords.md`; `public_slug` stop-word hits). All enforced by `platform/qa-rules.php`.
