# Source Facts — All-in-One Lake Como, Bellagio & Lugano from Milan + Scenic Cruise

## Canonical identity

- Canonical tour title: **All-in-One Lake Como, Bellagio & Lugano from Milan + Scenic Cruise**
- Active system brand: **Milano Adventures**
- Raw supplier/operator name in source: **Official**
- Package command: `WPS:GENERATE_CONTENT`
- Variant context: Not a variant — base package.

## Raw input snapshot

- Funnel stage: BOFU
- Language: English
- Duration: 10 to 11 hours
- Meeting point: Piazza IV Novembre, Milano MI, Italy
- Start time: 08:00
- Operating days: Daily (Mon–Sun)
- Max travelers: 22
- Max travelers per booking: 15
- Lead time: 9 hours before start
- Booking links:
  - Viator: https://www.viator.com/tours/Milan/All-in-one-Lake-Como-Bellagio-and-Lugano-from-Milan-Scenic-Cruise/d512-187808P57
  - TripAdvisor: https://www.tripadvisor.com/AttractionProductReview-g187849-d33353135-All_in_One_Lake_Como_Bellagio_Lugano_from_Milan_Scenic_Cruise-Milan_Lombardy.html
  - Website: missing

## Provenance matrix

| Field | Raw value | Source | Status | Notes |
|---|---|---|---|---|
| active system brand | Milano Adventures | AGENTS default | confirmed | |
| raw supplier/operator name | Official | User input | confirmed | Kept as raw fact only |
| canonical tour title | All-in-One Lake Como, Bellagio & Lugano from Milan +Scenic Cruise | User input | inferred | Spacing normalized |
| internal product/reference code | 1578 | User input | confirmed | Supplier-side ID |
| channel product codes | Viator: 187808P57; TripAdvisor: 33353135; Supplier: 1578; Listing Product ID: 187808P57 | User input + URLs | confirmed | |
| website booking URL | not provided | User input | missing | Permalinks present elsewhere |
| TripAdvisor URL | provided | User input | confirmed | |
| Viator URL | provided | User input | confirmed | Primary CTA |
| price (per band) | Adult 297; Child 195; Infant 0 (EUR) | User input | confirmed | Per person |
| pricing valid from | May 8, 2026 | User input | confirmed | |
| duration | 10 to 11 hours | User input | confirmed | |
| start time | 08:00 | User input | confirmed | |
| departure days | Mon;Tue;Wed;Thu;Fri;Sat;Sun | User input | confirmed | Daily |
| meeting point | Piazza IV Novembre, in front of Hotel Gallia bus stop | User input | confirmed | Near Milano Centrale |
| end point | Piazza IV Novembre, Milano MI, Italy | User input | confirmed | "End Same as Start = Yes" |
| itinerary stops | Como (2 hr) → Argegno scenic ferry crossing (1 hr) → Bellagio (2 hr) → Lugano (2 hr) → Milano Centrale (90 min return) | User input | confirmed | Order may vary |
| itinerary durations | 90 min outbound; 2 hr Como; 1 hr ferry; 2 hr Bellagio; 2 hr Lugano | User input | confirmed | Adds within 10–11 hr window |
| inclusions | Roundtrip transportation by GT coach or minivan; Professional tour leader; Free time in Como, Bellagio and Lugano; Lake Como panoramic cruise by ferry; Earphones for live commentary | User input | confirmed | Aggregated across stop rows |
| exclusions | Hotel pickup and drop-off | User input | confirmed | |
| languages | English, Spanish | User input | confirmed | |
| accessibility | Wheelchair: No; Stroller: No; Service Animals: No; Public transport access: No; Infants on laps: Yes; Infant seats: No | User input | confirmed | Public transport access marked No — disclose |
| traveler cap / group size | 22 | User input | confirmed | |
| min travelers to operate | not provided | User input | missing | |
| cancellation policy | 15 (unit unspecified) | User input | needs_human_review | Unit ambiguous |
| seasonal/weather notes | Itinerary order may be subject to change for logistical reasons | User input | confirmed | Disclosed in public copy |
| review rating | none provided | User input | missing | |
| review count | none provided | User input | missing | |
| review text/source | none provided | User input | missing | |
| destination context | Lake Como; Bellagio (Pearl of Lake Como); Lugano (Switzerland, Canton of Ticino); Villa del Balbianello (referenced in 007 and Star Wars) | User input | confirmed | Promoted before public copy use |
| missing critical inputs | none | Derived | not_applicable | |
| conflicts detected | none | Derived | not_applicable | |

## Clarifications needed (blocking)

- None.

## Missing inputs (non-blocking warnings)

- Cancellation policy unit (hours vs days) for "15".
- Minimum travelers to operate.

## Inferred facts

- Spacing in canonical title normalized from "+Scenic Cruise" to "+ Scenic Cruise".
