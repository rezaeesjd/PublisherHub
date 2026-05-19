# QA Report — Bernina FAQ Support Post

## Tour Identity Confirmation
- Requested command: `WPS:GENERATE_CONTENT`
- Actual package folder: `content-system/tours/bernina-st-moritz-from-milan-faq/`
- Canonical tour title: Full Day Tour in Bernina Red Train and St Moritz from Milan
- FAQ post title: Bernina Express + St Moritz from Milan: Passport, Weather & Timing FAQ
- Product/reference code: missing (non-blocking warning, inherited)
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
- PASS: Source-facts provenance matrix present; all facts inherited from confirmed base package.
- PASS: No deprecated field aliases used.
- PASS: Blog-post.md contains exactly one H1.
- PASS: Brand (Milano Adventures) present in blog-post.md.
- PASS: Primary CTA uses confirmed website booking URL.
- PASS: No placeholder tokens (`{{...}}`) in blog-post.md.
- PASS: Clarify gate — no blocking clarifications; auto_resolved.
- PASS: Status triad block present in this report.
- WARNING: Product reference code missing (inherited non-blocking).
- WARNING: Exclusions list missing (inherited non-blocking).
- WARNING: Viator / TripAdvisor OTA links missing (inherited non-blocking; website URL is primary CTA).

## Verdict
Generation readiness: ready_for_review.
Publish readiness: not yet verified.


## SEO Scorecard

Measured by `platform/qa-rules.php` (machine) + reviewer judgment (manual). Targets and verdicts follow Group-1 SEO rules.

| # | Check | Value | Target | Verdict |
|---|---|---|---|---|
| A | `meta.page_title` length | 70 chars | 50–60 | warn |
| B | `meta.meta_description` length | 185 chars | 140–160 | warn |
| C | `meta.public_slug` length & kebab-case | 40 chars | ≤ 50, kebab | pass |
| D | Single H1 in `blog-post.md` | 1 H1 | exactly 1 | pass |
| E | H1 ↔ `page_title` similarity | 73% | ≥ 60% | pass |
| F | Primary keyword in `page_title` prefix | no | yes | warn |
| G | Primary keyword in H1 | no | yes | warn |
| H | Brand in `blog-post.md` | yes | yes | pass |
| I | Cluster `primary_keyword` cannibalization | no | no | pass |
| J | Cluster `page_title` ≥75% sibling overlap | no | no | pass |
| K | Hero image + alt | no images/ | when images/ present | n/a |
| L | Internal links: hub + sibling | see `internal-links.md` | both | manual |
| M | Word count (final) | 567 words | 500–900 | pass |
| N | Retired `-vN` slug | no | no | pass |
| O | Primary keyword in first 100 words of `blog-post.md` | no | yes | warn |
| P | Primary keyword in ≥ 1 H2 of `blog-post.md` | no | yes | warn |
| Q | Primary keyword in last 200 words (conclusion) | no | yes | warn |
| R | Long-tail keyword coverage from `keywords.md` | 0/5 | > 50% present | warn |
| S | `public_slug` stop-word hits | none | none | pass |
| T | `meta.canonical_url` override (optional) — well-formed + matches `public_slug` | computed (from public_slug) | n/a unless overridden | pass |
| U | FAQPage JSON-LD ready (`faq.md` ≥ 3 Q&A) | 9 pairs | ≥ 3 (final) | pass |
| V | TouristTrip/Product JSON-LD fields present | missing: image | none missing (final) | warn |
| W | `internal-links.md` cross-funnel coverage | BOFU/MOFU/TOFU/FAQ | ≥ 2 stages | pass |
| X | `internal-links.md` anchor-text variety | no duplicates (1 anchors) | none duplicate | pass |
| Y | H2/H3 hierarchy + no duplicates | ok | ok | pass |
| Z | Hero image: present + descriptive filename + alt with keyword | not set | yes (final) | warn |

> Rows F, G, I, J, M are SEO checks added in Group 1. Rows A, B, D, E are now enforced by `platform/qa-rules.php` (title 50–60, meta description 140–160, H1 count, H1↔title parity, title cannibalization). Row L remains manual until an internal-link runner is added.
>
> Rows O–S are on-page SEO checks added in Group 2a (primary-keyword distribution: first-100-words / H2 / conclusion; long-tail keywords-coverage from `keywords.md`; `public_slug` stop-word hits). All enforced by `platform/qa-rules.php`.
>
> Rows T–Z are structural/technical SEO checks added in Group 2b (`canonical_url`, JSON-LD readiness for FAQPage and TouristTrip/Product, internal-links cross-funnel + anchor variety, H2/H3 hierarchy, hero image readiness).
