# QA Report — Best Day Trips from Milan (TOFU Destination Guide)

## Tour Identity Confirmation
- Requested command: `WPS:GENERATE_CONTENT`
- Actual package folder: `content-system/tours/best-day-trips-from-milan/`
- Canonical post title: Best Day Trips from Milan: Cinque Terre, Lake Como, the Swiss Alps and More
- Cluster: `cinque-terre-from-milan`
- Funnel stage: TOFU (destination-guide)
- Headline option (primary CTA): Cinque Terre Full-Day Tour from Milan
- Active brand: Milano Adventures
- Cinque Terre website URL status: confirmed (inherited from base package)
- Cinque Terre TripAdvisor URL status: confirmed (inherited from base package)
- Cinque Terre Viator URL status: confirmed (inherited from base package)
- Sibling cluster URLs (Lake Como + Lugano, Bernina + St Moritz): confirmed from `cluster-registry.json`
- Package created: 2026-05-15
- Report scope: generation

## Status
- Generation: complete
- Publish: verified (package publish checks passed)
- Live verification: not yet verified

## QA Findings
- PASS: All 9 required files present (`meta.json`, `source-facts.md`, `brief.md`, `keywords.md`, `blog-post.md`, `faq.md`, `internal-links.md`, `automation-notes.md`, `qa-report.md`).
- PASS: Source-facts provenance matrix present; headline (Cinque Terre) facts inherited from confirmed base package; sibling cluster facts inherited from `cluster-registry.json`; overview framing flagged as inferred.
- PASS: Blog-post.md contains exactly one H1.
- PASS: Brand (Milano Adventures) present in blog-post.md.
- PASS: Primary CTA uses confirmed website booking URL for the Cinque Terre full-day tour.
- PASS: OTA links placed as secondary trust references after the primary CTA.
- PASS: No placeholder tokens (`{{...}}`) in blog-post.md.
- PASS: Clarify gate — no blocking clarifications; auto_resolved.
- PASS: Status triad block present in this report.
- PASS: MOFU cross-link present in blog-post.md (`cinque-terre-full-day-tour-from-milan-vs-diy`).
- PASS: TOFU funnel routing correct — `link-to-mofu` (and BOFU) priority maintained; sibling cluster links are discovery-level only and below the primary CTA.
- NOTE: Cancellation unit for the Cinque Terre headline tour is unspecified in source data; default handling policy applies (24 hours prior to departure) per system rules.
- NOTE: Accessibility details are not provided for the headline tour; treated as non-blocking metadata gap per system rules.
- WARNING: Lake Como + Lugano TOFU/FAQ public slugs not yet live; cluster cross-links to that sibling cluster are conditional and should be re-checked at publish time.
- NOTE: Overview framing (typical day-trip duration, seasonal sweet spots) is inferred from general destination knowledge — appropriate for a TOFU discovery asset but human reviewer should validate before publish.

## Verdict
Generation readiness: passed.
Publish readiness: passed (published).


## SEO Scorecard

Measured by `platform/qa-rules.php` (machine) + reviewer judgment (manual). Targets and verdicts follow Group-1 SEO rules.

| # | Check | Value | Target | Verdict |
|---|---|---|---|---|
| A | `meta.page_title` length | 75 chars | 50–60 | warn |
| B | `meta.meta_description` length | 172 chars | 140–160 | warn |
| C | `meta.public_slug` length & kebab-case | 34 chars | ≤ 50, kebab | pass |
| D | Single H1 in `blog-post.md` | 1 H1 | exactly 1 | pass |
| E | H1 ↔ `page_title` similarity | 100% | ≥ 60% | pass |
| F | Primary keyword in `page_title` prefix | yes | yes | pass |
| G | Primary keyword in H1 | yes | yes | pass |
| H | Brand in `blog-post.md` | yes | yes | pass |
| I | Cluster `primary_keyword` cannibalization | no | no | pass |
| J | Cluster `page_title` ≥75% sibling overlap | no | no | pass |
| K | Hero image + alt | no images/ | when images/ present | n/a |
| L | Internal links: hub + sibling | see `internal-links.md` | both | manual |
| M | Word count (final) | 1184 words | 500–900 | warn |
| N | Retired `-vN` slug | no | no | pass |

> Rows F, G, I, J, M are SEO checks added in Group 1. Rows A, B, D, E are now enforced by `platform/qa-rules.php` (title 50–60, meta description 140–160, H1 count, H1↔title parity, title cannibalization). Row L remains manual until an internal-link runner is added.
