# Source Facts — Pisa Afternoon Tour from Florence + Skip-the-Line Access

## Canonical identity

- Canonical tour title: **Pisa Afternoon Tour from Florence + Skip-the-Line Access**
- Active system brand: **Milano Adventures**
- Raw supplier/operator name in source: **Official**
- Package command: `WPS:GENERATE_CONTENT`
- Variant context: Not a variant — base package.

## Raw input snapshot

- Funnel stage: BOFU
- Language: English
- Duration: 5 hours 30 minutes
- Meeting point: Piazzale Montelungo, Firenze FI, Italy
- Start time: 14:15 (departure 02:15 PM from Tours & News Kiosk)
- Operating days: Daily per Viator schedule
- Max travelers: 22
- Max travelers per booking: 15
- Lead time: 12 hours before start
- Booking links:
  - Viator: https://www.viator.com/tours/Florence/Premier-Pisa-Afternoon-Tour-from-Florence-Skip-the-Line-Access/d519-187808P50
  - TripAdvisor: https://www.tripadvisor.com/AttractionProductReview-g187895-d27742572-Pisa_Afternoon_Tour_from_Florence_Skip_the_Line_Access-Florence_Tuscany.html
  - Website: missing

## Provenance matrix

| Field | Raw value | Source | Status | Notes |
|---|---|---|---|---|
| active system brand | Milano Adventures | AGENTS default | confirmed | Default brand applied |
| raw supplier/operator name | Official | User input | confirmed | Kept as raw fact only |
| canonical tour title | Pisa Afternoon Tour from Florence + Skip-the-Line Access | User input | confirmed | Used verbatim |
| internal product/reference code | not provided | User input | missing | OTA codes used as primary |
| channel product codes | Viator: 187808P50; TripAdvisor: 27742572 | Derived from URLs | confirmed | Channel-specific mapping |
| website booking URL | not provided | User input | missing | Permalinks present on Viator + TripAdvisor |
| TripAdvisor URL | provided | User input | confirmed | Secondary booking |
| Viator URL | provided | User input | confirmed | Primary CTA |
| price (per band) | Adult 139; Youth 69; Child 69; Infant 0 (EUR) | User input | confirmed | Per person |
| pricing valid from | April 14, 2026 | User input | confirmed | Schedule start |
| duration | 5 hrs 30 minutes | User input | confirmed | Half-day |
| start time | 14:15 | User input | confirmed | Afternoon departure |
| departure days | Daily per Viator | Inferred | inferred | CSV did not include explicit weekly pattern |
| meeting point | Piazzale Montelungo, Firenze FI, Italy | User input | confirmed | 5–10 min walk from Florence Central Station |
| end point | Piazzale Montelungo, Firenze FI, Italy | User input | confirmed | "End Same as Start = Yes" |
| itinerary stops | Florence (departure) → Pisa → Piazza dei Miracoli → Duomo di Pisa → Baptistery & Monumental Graveyard → Leaning Tower of Pisa → Free time → Florence | User input | confirmed | 7 stops |
| itinerary durations | Pisa stop pass-by; Piazza dei Miracoli 1 hr; Duomo 1 hr; Baptistery/Graveyard 1 hr; Leaning Tower 90 min; Free time 1 hr | User input | confirmed | Adds to ~5h plus transit |
| inclusions | Roundtrip journey; Expert guides; Guided tour of Piazza dei Miracoli with professional guide; Admission fee to Pisa Cathedral; All Fees and Taxes; Leaning Tower entrance tickets with priority access; Free time in Pisa | User input | confirmed | Aggregated across stop rows |
| exclusions | Not explicitly listed in CSV | User input | missing | None enumerated |
| languages | English, Spanish | User input | confirmed | Guide languages |
| accessibility | Wheelchair: No; Stroller: No; Service Animals: No; Public transport access: Yes; Infants on laps: No; Infant seats: Yes | User input | confirmed | Health restriction: not suitable for guests with wheelchairs or impaired mobility |
| traveler cap / group size | 22 | User input | confirmed | Maximum participants |
| min travelers to operate | not provided | User input | missing | Not in source |
| cancellation policy | 12 (unit unspecified) | User input | needs_human_review | Unit ambiguous; not used in public specifics |
| seasonal/weather notes | Children under 8 not allowed to climb the Leaning Tower; comfortable walking shoes recommended | User input | confirmed | Operational note |
| review rating | none provided | User input | missing | No rating claim allowed |
| review count | none provided | User input | missing | No review count claim allowed |
| review text/source | none provided | User input | missing | No testimonial claim allowed |
| destination context | Piazza dei Miracoli (UNESCO World Heritage Site); Cathedral; Baptistery; Monumental Graveyard; Leaning Tower | User input | confirmed | Promoted here for use in public copy |
| missing critical inputs | none (Viator + TripAdvisor permalinks satisfy booking-link requirement) | Derived | not_applicable | |
| conflicts detected | none | Derived | not_applicable | |

## Clarifications needed (blocking)

- None.

## Missing inputs (non-blocking warnings)

- Cancellation policy unit (hours vs days) for "12".
- Internal product/reference code (Product ID column blank).
- Explicit weekly schedule (departure days).

## Inferred facts

- Departure days assumed daily based on Viator afternoon schedule pattern; treat as `inferred` until confirmed by operator.
