# Source Facts — {{Canonical Tour Title}}

These are raw and normalized facts extracted before public copy generation.

> **Provenance-to-claim binding:** Every assertive sentence in `blog-post.md`, `faq.md`, `keywords.md`, and `internal-links.md` must trace to a row in the matrix below. Marketing-flavored facts (e.g., UNESCO status, "iconic", "world-famous", "scenic") count as claims and must be added as rows here before they appear in public copy.

## Variant context (required for `-v<N>` packages)

Fill this block **only** when this package is a content variant of an existing tour. Leave the section in place but note "Not a variant — base package." when this is the base.

- Base package slug: <base slug>
- Variant index: <integer ≥ 2>
- Variant role: bofu_landing | day_trip_bofu | comparison_mofu | informational_tofu | seasonal_faq | other
- Variant angle: <short human label>
- Inheritance handshake completed (yes/no):
- Inherited warnings (one row per gap):

| Field | Base value | Decision (inherit / resolved / escalated) | Resolved value (if any) |
|---|---|---|---|
|  |  |  |  |

## Tour identity

- Command:
- Package folder:
- Canonical tour title:
- Active system brand: Milano Adventures
- Raw supplier/operator name:
- Internal product/reference code:
- Channel-specific product codes (e.g. Viator, GetYourGuide):

## Source-Facts Provenance Matrix

Allowed `Status` values: `confirmed | missing | conflicted | inferred | needs_human_review | not_applicable`

| Field | Raw value | Source | Status | Notes |
|---|---|---|---|---|
| active system brand |  |  |  |  |
| raw supplier/operator name |  |  |  |  |
| canonical tour title |  |  |  |  |
| internal product/reference code |  |  |  |  |
| channel product codes (Viator/etc.) |  |  |  |  |
| website booking URL |  |  |  | non-blocker when at least one OTA URL exists (auto-fallback, recorded as a `warnings[]` entry); real `conversion_blockers[]` entry only when no booking URL of any channel is supplied |
| TripAdvisor URL |  |  |  | warning if missing |
| Viator URL |  |  |  | warning if missing |
| price (per band) |  |  |  |  |
| pricing valid from |  |  |  |  |
| duration |  |  |  |  |
| start time |  |  |  |  |
| departure days |  |  |  |  |
| meeting point |  |  |  |  |
| end point |  |  |  | mark `inferred` when derived from address equality |
| itinerary stops |  |  |  |  |
| itinerary durations |  |  |  |  |
| inclusions |  |  |  |  |
| exclusions |  |  |  |  |
| languages |  |  |  |  |
| accessibility |  |  |  |  |
| traveler cap / group size |  |  |  |  |
| min travelers to operate |  |  |  |  |
| cancellation policy |  |  |  | **Default if not provided / unit ambiguous:** record value "Free cancellation up to 24 hours before departure; within 24 hours non-refundable" with `Status: inferred` and note "brand-default cancellation policy applied (24h before departure)" in the Source column. Only override when explicit unit-qualified source states otherwise. Never blocking. |
| seasonal/weather notes |  |  |  |  |
| review rating |  |  |  | include source |
| review count |  |  |  | include source |
| review text/source |  |  |  | include quote provenance |
| destination context (UNESCO, region, named landmarks) |  |  |  | promote here before using in public copy |
| missing critical inputs |  |  |  |  |
| conflicts detected |  |  |  |  |

## Clarifications needed (blocking)

Each entry must mirror an entry in `meta.json.clarifications_needed` with `"blocking": true`.

- field:
  raw_value:
  question:

## Missing inputs (non-blocking warnings)

- 

## Inferred facts

List every fact that was inferred (not directly stated in the source). The matching matrix row must use status `inferred`.

- 
