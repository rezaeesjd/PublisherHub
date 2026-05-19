# QA Report — Bernina MOFU Comparison Post

## Tour Identity Confirmation
- Requested command: `WPS:GENERATE_CONTENT`
- Actual package folder: `content-system/tours/bernina-express-guided-vs-diy-from-milan/`
- Canonical tour title: Full Day Tour in Bernina Red Train and St Moritz from Milan
- MOFU post title: Bernina Express from Milan: Guided Tour vs DIY — Which Is Better?
- Product/reference code: missing (non-blocking, inherited)
- Active brand: Milano Adventures
- Website URL status: confirmed (inherited from base package)
- TripAdvisor URL status: missing (non-blocking)
- Viator URL status: missing (non-blocking)
- Package created: 2026-05-13
- Report scope: generation

## Status
- Generation: complete
- Publish: not yet verified
- Live verification: not yet verified

## QA Findings
- PASS: All 9 required files present.
- PASS: Source-facts provenance matrix present; guided-tour facts from confirmed base package; DIY comparison facts flagged as inferred from general destination knowledge.
- PASS: No deprecated field aliases used.
- PASS: Blog-post.md contains exactly one H1.
- PASS: Brand (Milano Adventures) present in blog-post.md.
- PASS: Primary CTA uses confirmed website booking URL.
- PASS: No placeholder tokens (`{{...}}`) in blog-post.md.
- PASS: Clarify gate — no blocking clarifications; auto_resolved.
- PASS: Status triad block present in this report.
- PASS: DIY comparison facts are clearly flagged as inferred in source-facts.md; not presented as operator-confirmed data.
- WARNING: Product reference code missing (inherited non-blocking).
- WARNING: Exclusions list missing (inherited non-blocking).
- WARNING: Viator / TripAdvisor OTA links missing (inherited non-blocking).
- NOTE: Comparison table includes DIY data inferred from general destination knowledge; human reviewer should validate DIY cost and timing estimates before publish.

## Verdict
Generation readiness: ready_for_review with inherited warnings and human review note on DIY comparison data.
Publish readiness: not yet verified.


## SEO Scorecard

Measured by `platform/qa-rules.php` (machine) + reviewer judgment (manual). Targets and verdicts follow Group-1 SEO rules.

| # | Check | Value | Target | Verdict |
|---|---|---|---|---|
| A | `meta.page_title` length | 65 chars | 50–60 | warn |
| B | `meta.meta_description` length | 153 chars | 140–160 | pass |
| C | `meta.public_slug` length & kebab-case | 45 chars | ≤ 50, kebab | pass |
| D | Single H1 in `blog-post.md` | 1 H1 | exactly 1 | pass |
| E | H1 ↔ `page_title` similarity | 100% | ≥ 60% | pass |
| F | Primary keyword in `page_title` prefix | yes | yes | pass |
| G | Primary keyword in H1 | yes | yes | pass |
| H | Brand in `blog-post.md` | yes | yes | pass |
| I | Cluster `primary_keyword` cannibalization | no | no | pass |
| J | Cluster `page_title` ≥75% sibling overlap | no | no | pass |
| K | Hero image + alt | no images/ | when images/ present | n/a |
| L | Internal links: hub + sibling | see `internal-links.md` | both | manual |
| M | Word count (final) | 830 words | 500–900 | pass |
| N | Retired `-vN` slug | no | no | pass |

> Rows F, G, I, J, M are SEO checks added in Group 1. Rows A, B, D, E are now enforced by `platform/qa-rules.php` (title 50–60, meta description 140–160, H1 count, H1↔title parity, title cannibalization). Row L remains manual until an internal-link runner is added.
