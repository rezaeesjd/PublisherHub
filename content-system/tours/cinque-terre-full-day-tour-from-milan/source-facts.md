# Source Facts — Cinque Terre Full-Day Tour from Milan

## Canonical identity
- Canonical tour title: **Cinque Terre Full-Day Tour from Milan**
- Active system brand: **Milano Adventures**
- Raw supplier/operator name in source: **Official**
- Package command: `WPS:GENERATE_CONTENT`

## Raw input snapshot
- Funnel stage: MOFU
- Language: English
- Duration: 13 hours 30 minutes
- Meeting point: Piazza IV Novembre, 20124 Milano MI, Italy
- Start time: 07:00
- Operating days: Mon, Wed, Sat
- Max travelers: 22
- Lead time: 9 hours before start
- Booking links:
  - Viator: https://www.viator.com/tours/Milan/Full-Day-Cinque-Terre-Tour-from-Milan-Riomaggiore-Manarola/d512-187808P82
  - TripAdvisor: https://www.tripadvisor.com/AttractionProductReview-g187849-d33344981-Cinque_Terre_Full_Day_Tour_from_Milan_Riomaggiore_Manarola-Milan_Lombardy.html
  - Website: missing

## Provenance matrix

| Field | Raw value | Source | Status | Notes |
|---|---|---|---|---|
| active system brand | Milano Adventures | AGENTS default | confirmed | Default brand applied |
| raw supplier/operator name | Official | User input | confirmed | Kept as raw fact only |
| canonical tour title | Cinque Terre Full-Day Tour from Milan: Riomaggiore, Manarola &… | User input | inferred | Trailing ellipsis removed; broader canonical title used |
| product/reference code | 187808P109 | User input | confirmed | Non-OTA code treated as primary |
| channel product codes | Viator: 187808P82; TripAdvisor: 33344981; Supplier: 187808P109 | User input + URLs | confirmed | Channel-specific mapping |
| website booking URL | not provided | User input | missing | Non-blocking because OTA links exist |
| TripAdvisor URL | provided | User input | confirmed | Secondary trust/alternate booking |
| Viator URL | provided | User input | confirmed | Selected as primary CTA fallback |
| price | Adult 275 EUR; Child 157 EUR; Infant 0 EUR | User input | confirmed | Per person |
| duration | 13 Hrs and 30 minutes | User input | confirmed | One-day tour |
| start time | 07:00 | User input | confirmed | Relative booking cutoff 9h |
| meeting point | Piazza IV Novembre, in front of Hotel Gallia bus stop | User input | confirmed | Arrive 15 minutes early |
| end point | Piazza IV Novembre, 20124 Milano MI, Italy | User input | confirmed | Same as departure area |
| itinerary stops | Riomaggiore, Manarola, Monterosso al Mare, Vernazza, Corniglia | User description | confirmed | Description names main 5 Terre towns |
| itinerary durations | 150 minutes listed (partial) | User input | needs_human_review | Stop-level split not fully structured |
| inclusions | Expert tour leader; land transport; train/rail; seasonal panoramic ferry | User input | confirmed | Ferry weather/season dependent |
| exclusions | Hotel pickup and drop-off | User input | confirmed | No pickup service |
| languages | English, Spanish | User input | confirmed | Guide/service languages listed |
| accessibility | not provided | User input | missing | Omitted from public claim |
| traveler cap / group size | 22 | User input | confirmed | Max travelers |
| cancellation policy | 15 | User input | needs_human_review | Unit unspecified; excluded from public specifics |
| seasonal/weather notes | Ferry may not operate due to sea/weather conditions | User input | confirmed | Must be disclosed |
| review rating | none provided | User input | missing | No rating claim allowed |
| review count | none provided | User input | missing | No review count claim allowed |
| review text/source | none provided | User input | missing | No testimonial claim allowed |
| missing critical inputs | direct website booking URL missing | Derived | needs_human_review | Non-blocking OTA fallback applied |
| conflicts detected | Title names 2 villages while description covers all 5 villages | User input | inferred | Canonical scope aligned to full Cinque Terre |
