# Source Facts — Lake Como and Lugano Full Day Trip from Milan

## Canonical identity

- Canonical tour title: **Lake Como and Lugano Full Day Trip from Milan**
- Active system brand: **Milano Adventures**
- Raw supplier/operator name in source: **Official**
- Package command: `WPS:GENERATE_CONTENT`
- Variant context: Two-lake variant within the `lake-como-and-lugano-from-milan` cluster (no Bellagio stop, Como historic centre instead).

## Raw input snapshot

- Funnel stage: MOFU
- Language: English
- Duration: 10 to 11 hours
- Meeting point: Largo Cairoli, 18, 20121 Milano MI, Italy (Milan Visitor Centre, corner of Via Cusani with Largo Cairoli; MM1 Cairoli / MM2 Lanza)
- Start time: 08:30
- Operating days: Mon, Wed, Fri, Sat (source had "Mon;Wed; Fri;Sat" with stray whitespace)
- Pricing valid from: October 11, 2026
- Max travelers: 22
- Max travelers per booking: 15
- Lead time: 10 hours before start
- Booking links:
  - Viator: https://www.viator.com/tours/Milan/Lake-Como-and-Lugano-Full-Day-Trip-from-Milan/d512-187808P95
  - TripAdvisor: https://www.tripadvisor.com/AttractionProductReview-g187849-d33960421-Lake_Como_and_Lugano_Full_Day_Trip_from_Milan-Milan_Lombardy.html
  - Website: missing

## Provenance matrix

| Field | Raw value | Source | Status | Notes |
|---|---|---|---|---|
| active system brand | Milano Adventures | AGENTS default | confirmed | |
| raw supplier/operator name | Official | User input | confirmed | Kept as raw fact only |
| canonical tour title | Lake Como and Lugano Full Day Trip from Milan | User input | confirmed | |
| internal product/reference code | 101 | User input | confirmed | Supplier-side ID |
| channel product codes | Viator: 187808P95; TripAdvisor: 33960421; Supplier: 101; Listing Product ID: 187808P95 | User input + URLs | confirmed | |
| website booking URL | not provided | User input | missing | Permalinks present elsewhere |
| TripAdvisor URL | provided | User input | confirmed | |
| Viator URL | provided | User input | confirmed | Primary CTA |
| price (per band) | Adult 275; Child 175; Infant 0 (EUR) | User input | confirmed | Per person |
| pricing valid from | October 11, 2026 | User input | confirmed | |
| duration | 10 to 11 hours | User input | confirmed | |
| start time | 08:30 | User input | confirmed | Derived from Excel time fraction 0.3541667 |
| departure days | Mon, Wed, Fri, Sat | User input | confirmed | Source spacing normalized |
| meeting point | Milan Visitor Centre, corner of Via Cusani with Largo Cairoli (MM1 Cairoli / MM2 Lanza) | User input | confirmed | |
| end point | Largo Cairoli 18, Milan | User input | confirmed | "End Same as Start = Yes" |
| itinerary stops | Como historic centre (3 hr) → Lake Como sightseeing cruise (1 hr) → Lugano / Canton of Ticino (3 hr) → return to Milan (90 min) | User input | confirmed | Five stop rows aggregated |
| itinerary durations | 90 min outbound; 3 hr Como; 1 hr cruise; 3 hr Lugano; 90 min return | User input | confirmed | Adds within 10–11 hr window |
| inclusions | Free time in Como and Lugano; Boat cruise across Lake Como; Professional tour leader; Panoramic tour of Como and its surroundings; Roundtrip journey by GT coach or minivan from Milan to Como | User input | confirmed | Aggregated across stop rows |
| exclusions | not provided | User input | missing | Source row left blank |
| languages | English | User input | confirmed | Guide/service language |
| accessibility | Wheelchair: No; Stroller: No; Service Animals: No; Public transport access: Yes; Infants on laps: Yes; Infant seats: No | User input | confirmed | |
| traveler cap / group size | 22 | User input | confirmed | |
| min travelers to operate | not provided | User input | missing | |
| cancellation policy | Standard | User input | confirmed | OTA standard policy applies |
| seasonal/weather notes | none provided | User input | not_applicable | |
| review rating | none provided | User input | missing | |
| review count | none provided | User input | missing | |
| review text/source | none provided | User input | missing | |
| destination context | Lake Como (Lombardy, Italy); Como historic centre; Lugano (Canton of Ticino, Switzerland); Italy ↔ Switzerland border crossing | User input | confirmed | |
| missing critical inputs | none | Derived | not_applicable | Viator + TripAdvisor permalinks satisfy CTA requirement |
| conflicts detected | none | Derived | not_applicable | |

## Clarifications needed (blocking)

- None.

## Missing inputs (non-blocking warnings)

- Exclusions list left blank in source.
- Minimum travelers to operate.
- Switzerland border crossing language (passport vs national ID) not specified in source.

## Inferred facts

- Operating-days string `Mon;Wed; Fri;Sat` normalized to `Mon, Wed, Fri, Sat` (extra space before "Fri" treated as a typo).
- Start time derived from Excel time fraction 0.3541667 = 08:30.
- Pricing-valid-from date derived from Excel serial 46306 = October 11, 2026.
