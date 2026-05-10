# Source Facts — Cinque Terre Full-Day Tour from Milan: Riomaggiore, Manarola & …

## Raw intake snapshot
- Command: `WPS:GENERATE_CONTENT`
- Active system brand: Milano Adventures (default)
- Canonical tour title (provisional from intake): Cinque Terre Full-Day Tour from Milan: Riomaggiore, Manarola & …
- Product/reference code values detected: `187808P109` and `187808P82` (conflict)

## URLs and booking links
- Website booking URL: **missing** (no direct website booking link provided)
- Viator URL: https://www.viator.com/tours/Milan/Full-Day-Cinque-Terre-Tour-from-Milan-Riomaggiore-Manarola/d512-187808P82
- TripAdvisor URL: https://www.tripadvisor.com/AttractionProductReview-g187849-d33344981-Cinque_Terre_Full_Day_Tour_from_Milan_Riomaggiore_Manarola-Milan_Lombardy.html
- Supplier photo folder: https://drive.google.com/drive/folders/17NvbPnhFf_0d3VNgxo3Mo_lEQQwHxNwE

## Core operational facts (raw)
- Type: Tour
- Category: Shore Excursion / Sightseeing / Culture / Travel
- Duration: 13 hours 30 minutes
- Duration unit details: `13 Hrs and 30 minutes`
- Departure city: Milan
- Start point: Piazza IV Novembre, 20124 Milano MI, Italy
- Meeting instructions: Milano Centrale station, Piazza IV Novembre, in front of Hotel Gallia bus stop; arrive 15 minutes early; staff in fuchsia shirts
- End point: Piazza IV Novembre, 20124 Milano MI, Italy
- Transport: Bus/Coach; Minibus; Train/Rail
- Languages: English, Spanish
- Guide: Expert tour leader
- Max travelers: 22
- Difficulty: Easy
- Confirmation: Instant confirmation
- Ticket type: Paper or mobile accepted; one per booking; direct entry ticket = No
- Availability days: Mon; Wed; Sat
- Start time: 07:00
- Price currency: EUR
- Per person pricing:
  - Adult (12–99): 275
  - Child (4–11): 157
  - Infant (0–3): 0

## Inclusions / exclusions (raw)
- Included (raw indication): Official guide; multilingual support (English/Spanish)
- Excluded (raw indication): Hotel pickup and drop-off
- Seasonal note: Panoramic ferry ride is seasonal and subject to weather/sea conditions; may not operate on some days

## Policy and timing fields needing clarification
- `May 1, 2026` appears without explicit label context
- Cancellation value appears as: `Relatively to Start Time` + `9` (unit unclear)
- Value `15` appears in policy area with unclear semantic role

## Provenance matrix

| Field | Raw value | Source | Status | Notes |
|---|---|---|---|---|
| active system brand | Milano Adventures (default) | AGENTS.md brand rule + no override in user input | confirmed | Default retained until user changes it |
| raw supplier/operator name | Not explicitly supplied as operator brand | User intake row | missing | No supplier brand found in clean field |
| canonical tour title | Cinque Terre Full-Day Tour from Milan: Riomaggiore, Manarola & … | User intake first field | needs_human_review | Title appears truncated with ellipsis |
| product/reference code | 187808P109 | User intake final field | conflicted | Conflicts with Viator URL code |
| channel product codes | Viator: 187808P82; Intake: 187808P109 | User intake + Viator URL | conflicted | Must confirm canonical code mapping |
| website booking URL | Not provided | User intake | missing | Conversion blocker |
| TripAdvisor URL | Provided | User intake | confirmed | Preserved as secondary channel URL |
| Viator URL | Provided | User intake | confirmed | Preserved as secondary channel URL |
| price | Adult 275 EUR; Child 157 EUR; Infant 0 EUR | User intake | confirmed | Start validity date unclear |
| duration | 13 Hrs and 30 minutes | User intake | confirmed | |
| start time | 07:00 | User intake | confirmed | Operating days also provided |
| meeting point | Piazza IV Novembre…Hotel Gallia bus stop | User intake | confirmed | |
| end point | Piazza IV Novembre, 20124 Milano MI, Italy | User intake | confirmed | Same as start |
| itinerary stops | Riomaggiore, Manarola plus 5 Terre towns list in long description | User intake | needs_human_review | Scope conflict (2-town title vs 5-town description) |
| itinerary durations | 150 minutes appears once; context unclear | User intake | needs_human_review | Stop/time mapping incomplete |
| inclusions | Official guide; transport and tickets implied in long description | User intake | needs_human_review | Structured inclusion fields incomplete |
| exclusions | Hotel pickup and drop-off | User intake | confirmed | |
| languages | English, Spanish | User intake | confirmed | |
| accessibility | No clear wheelchair accessibility field found | User intake | missing | Blocking per intake standards |
| traveler cap / group size | 22 | User intake | confirmed | |
| cancellation policy | 9 relative to start time | User intake | conflicted | Unit (hours/days) unresolved |
| seasonal/weather notes | Ferry ride seasonal, weather-dependent | User intake | confirmed | |
| review rating | Not provided | User intake | missing | |
| review count | Not provided | User intake | missing | |
| review text/source | Not provided | User intake | not_applicable | |
| missing critical inputs | Website URL, cancellation unit, accessibility, final product code, title expansion | Derived from intake | confirmed | Blocking clarifications |
| conflicts detected | Code mismatch; title/itinerary scope mismatch; unlabeled policy numbers | Derived from intake | confirmed | Clarification required before public copy |
