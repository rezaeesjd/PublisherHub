# QA Report

- Date: 2026-05-13
- Package: `lake-como-and-lugano-full-day-trip-from-milan`
- Command: `WPS:GENERATE_CONTENT` (source-only intake; blog-post.md intentionally omitted)

## Required files check
- PASS: source-facts.md present
- PASS: brief.md present
- PASS: keywords.md present
- PASS: faq.md present
- PASS: internal-links.md present
- PASS: meta.json present
- PASS: automation-notes.md present
- INFO: blog-post.md intentionally omitted at this stage per user instruction.

## Content and compliance checks
- PASS: source-facts.md created before any public copy drafting.
- PASS: Canonical title preserved as supplied.
- PASS: OTA links preserved as provided (Viator + TripAdvisor).
- PASS: At least one real booking permalink is present.
- PASS: Brand mention noted in source.
- PASS: No unsupported review/rating claims.
- PASS: Operating-days typo (`Mon;Wed; Fri;Sat`) auto-normalized and logged in `clarify_decisions`.
- WARNING: Exclusions left blank in source; cannot publish negative inclusion claims.
- WARNING: Switzerland border-crossing language (passport vs national ID) not specified in source.

## Status
- qa_status: `warning`
- publish_status: `archived` (source content only; awaiting public-copy generation phase)

## Action items
1. Confirm minimum traveler count to operate.
2. Confirm whether a passport or national ID note should be added for the Lugano segment.
3. When generating public copy, retrieve exclusions list from supplier or omit explicit "what's not included" section.


## SEO Scorecard

Measured by `platform/qa-rules.php` (machine) + reviewer judgment (manual). Targets and verdicts follow Group-1 SEO rules.

| # | Check | Value | Target | Verdict |
|---|---|---|---|---|
| A | `meta.page_title` length | 65 chars | 50–60 | warn |
| B | `meta.meta_description` length | 120 chars | 140–160 | warn |
| C | `meta.public_slug` length & kebab-case | 45 chars | ≤ 50, kebab | pass |
| D | Single H1 in `blog-post.md` | 0 H1 | exactly 1 | n/a |
| E | H1 ↔ `page_title` similarity | 0% | ≥ 60% | n/a |
| F | Primary keyword in `page_title` prefix | no | yes | warn |
| G | Primary keyword in H1 | n/a | yes | n/a |
| H | Brand in `blog-post.md` | no | yes | n/a |
| I | Cluster `primary_keyword` cannibalization | no | no | pass |
| J | Cluster `page_title` ≥75% sibling overlap | no | no | pass |
| K | Hero image + alt | no images/ | when images/ present | n/a |
| L | Internal links: hub + sibling | see `internal-links.md` | both | manual |
| M | Word count (not_started) | 0 words | n/a (archived/source-only) | n/a |
| N | Retired `-vN` slug | no | no | pass |
| O | Primary keyword in first 100 words of `blog-post.md` | n/a | yes | n/a |
| P | Primary keyword in ≥ 1 H2 of `blog-post.md` | no H2 | yes | n/a |
| Q | Primary keyword in last 200 words (conclusion) | n/a | yes | n/a |
| R | Long-tail keyword coverage from `keywords.md` | n/a | > 50% present | n/a |
| S | `public_slug` stop-word hits | and | none | warn |
| T | `meta.canonical_url` override (optional) — well-formed + matches `public_slug` | computed (from public_slug) | n/a unless overridden | pass |
| U | FAQPage JSON-LD ready (`faq.md` ≥ 3 Q&A) | 9 pairs | ≥ 3 (final) | pass |
| V | TouristTrip/Product JSON-LD fields present | missing: image | none missing (final) | n/a |
| W | `internal-links.md` cross-funnel coverage | BOFU/MOFU/TOFU/FAQ | ≥ 2 stages | pass |
| X | `internal-links.md` anchor-text variety | no duplicates (6 anchors) | none duplicate | pass |
| Y | H2/H3 hierarchy + no duplicates | ok | ok | pass |
| Z | Hero image: present + descriptive filename + alt with keyword | not set | yes (final) | n/a |

> Rows F, G, I, J, M are SEO checks added in Group 1. Rows A, B, D, E are now enforced by `platform/qa-rules.php` (title 50–60, meta description 140–160, H1 count, H1↔title parity, title cannibalization). Row L remains manual until an internal-link runner is added.
>
> Rows O–S are on-page SEO checks added in Group 2a (primary-keyword distribution: first-100-words / H2 / conclusion; long-tail keywords-coverage from `keywords.md`; `public_slug` stop-word hits). All enforced by `platform/qa-rules.php`.
>
> Rows T–Z are structural/technical SEO checks added in Group 2b (`canonical_url`, JSON-LD readiness for FAQPage and TouristTrip/Product, internal-links cross-funnel + anchor variety, H2/H3 hierarchy, hero image readiness).
