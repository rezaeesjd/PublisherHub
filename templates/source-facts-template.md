# Source Facts — {{Canonical Tour Title}}

These are raw and normalized facts extracted before public copy generation.

> **Provenance-to-claim binding:** Every assertive sentence in `blog-post.md`, `faq.md`, `keywords.md`, and `internal-links.md` must trace to a row in the matrix below. Marketing-flavored facts (e.g., UNESCO status, "iconic", "world-famous", "scenic") count as claims and must be added as rows here before they appear in public copy.

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
| website booking URL |  |  |  | conversion blocker if missing |
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
| cancellation policy |  |  |  | unit must be hours or days; ambiguous unit is blocking |
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
