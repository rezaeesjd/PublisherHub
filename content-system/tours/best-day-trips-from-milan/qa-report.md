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
| O | Primary keyword in first 100 words of `blog-post.md` | yes | yes | pass |
| P | Primary keyword in ≥ 1 H2 of `blog-post.md` | no | yes | warn |
| Q | Primary keyword in last 200 words (conclusion) | no | yes | warn |
| R | Long-tail keyword coverage from `keywords.md` | 0/6 | > 50% present | warn |
| S | `public_slug` stop-word hits | none | none | pass |
| T | `meta.canonical_url` override (optional) — well-formed + matches `public_slug` | computed (from public_slug) | n/a unless overridden | pass |
| U | FAQPage JSON-LD ready (`faq.md` ≥ 3 Q&A) | 8 pairs | ≥ 3 (final) | pass |
| V | TouristTrip/Product JSON-LD fields present | missing: offers.price | name/description/offers.price (image optional) | warn |
| W | `internal-links.md` cross-funnel coverage | BOFU/MOFU/FAQ | ≥ 2 stages | pass |
| X | `internal-links.md` anchor-text variety | no duplicates (8 anchors) | none duplicate | pass |
| Y | H2/H3 hierarchy + no duplicates | ok | ok | pass |
| Z | Hero image (optional) — alt + descriptive filename when set | not set (optional, no images/) | required only when images/ exists | pass |

> Rows F, G, I, J, M are SEO checks added in Group 1. Rows A, B, D, E are now enforced by `platform/qa-rules.php` (title 50–60, meta description 140–160, H1 count, H1↔title parity, title cannibalization). Row L remains manual until an internal-link runner is added.
>
> Rows O–S are on-page SEO checks added in Group 2a (primary-keyword distribution: first-100-words / H2 / conclusion; long-tail keywords-coverage from `keywords.md`; `public_slug` stop-word hits). All enforced by `platform/qa-rules.php`.
>
> Rows T–Z are structural/technical SEO checks added in Group 2b (`canonical_url`, JSON-LD readiness for FAQPage and TouristTrip/Product, internal-links cross-funnel + anchor variety, H2/H3 hierarchy, hero image readiness).
