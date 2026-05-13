# Source Facts — Full Day Tour in Bernina Red Train and St Moritz from Milan

## Canonical identity

- Canonical tour title: **Full Day Tour in Bernina Red Train and St Moritz from Milan**
- Active system brand: **Milano Adventures**
- Raw supplier/operator name in source: **Official**
- Package command: `WPS:GENERATE_CONTENT`
- Variant context: New Swiss-Alps cluster — first asset under `swiss-alps-from-milan`.

## Raw input snapshot

- Funnel stage: BOFU (cluster-opening asset; only conversion option for this cluster today)
- Language: English
- Duration: 12 to 13 hours
- Meeting point: Largo Cairoli, 18, 20121 Milano MI, Italy (Milan Visitor Centre, corner of Via Cusani with Largo Cairoli; MM1 Cairoli / MM2 Lanza)
- Start time: 07:15
- Operating days: Daily (Mon–Sun)
- Pricing valid from: January 9, 2027
- Max travelers: 20
- Max travelers per booking: 15
- Lead time: 9 hours before start (last-minute < 12 h cannot be guaranteed)
- Booking links:
  - Viator: missing
  - TripAdvisor: missing
  - Website: https://www.milano-adventures.com/tours/full-day-tour-in-bernina-red-train-and-st-moritz-from-milan/

## Provenance matrix

| Field | Raw value | Source | Status | Notes |
|---|---|---|---|---|
| active system brand | Milano Adventures | AGENTS default | confirmed | |
| raw supplier/operator name | Official | User input | confirmed | Kept as raw fact only |
| canonical tour title | Full Day Tour in Bernina red train and St Moritz from Milan | User input | inferred | Capitalization normalized ("Bernina red train" → "Bernina Red Train") |
| internal product/reference code | not provided | User input | missing | Excel cell blank |
| channel product codes | none provided | User input | missing | No Viator/TripAdvisor/Product ID in source |
| website booking URL | https://www.milano-adventures.com/tours/full-day-tour-in-bernina-red-train-and-st-moritz-from-milan/ | User input | confirmed | Adopted as primary CTA |
| TripAdvisor URL | not provided | User input | missing | |
| Viator URL | not provided | User input | missing | |
| price (per band) | Adult 250.20; Child 113.85; Infant 0 (EUR) | User input | confirmed | Per person |
| pricing valid from | January 9, 2027 | User input | confirmed | Derived from Excel serial 46396 |
| duration | 12 to 13 hours | User input | confirmed | |
| start time | 07:15 | User input | confirmed | Derived from Excel time fraction 0.3020833 |
| departure days | Mon, Tue, Wed, Thu, Fri, Sat, Sun | User input | confirmed | Daily |
| meeting point | Milan Visitor Centre, corner of Via Cusani with Largo Cairoli (MM1 Cairoli / MM2 Lanza); fuchsia shirts | User input | confirmed | |
| end point | Largo Cairoli 18, Milan | User input | confirmed | "End Same as Start = Yes" |
| itinerary stops | Tirano (1 hr) → Bernina Express scenic crossing (90 min) → Alp Grüm panoramic stop (40 min) → Bernina Diavolezza glacier viewpoint (1 hr) → Pontresina (1 hr) → St. Moritz free time (140 min) → Lake St. Moritz (1 hr) → Bernina Express return journey (3 hr) | User input | confirmed | Nine stop rows aggregated |
| itinerary durations | 80 min outbound + 1 h Tirano + 90 min train + 40 min Alp Grüm + 1 h Diavolezza + 1 h Pontresina + 140 min St. Moritz + 1 h Lake St. Moritz + 3 h return | User input | confirmed | Adds within 12–13 hr window |
| inclusions | Bernina Express 2nd-class ticket (opening windows); Roundtrip transportation by GT coach; Expert tour leader; Free time in St Moritz; Ride the Bernina Express along one of the world's most spectacular railway routes; Discover a UNESCO World Heritage site; Travel with an expert tour leader assisting during the whole trip | User input | confirmed | Aggregated across stop rows |
| exclusions | not provided | User input | missing | Source row left blank |
| languages | English | User input | confirmed | Guide/service language |
| accessibility | Wheelchair: No; Stroller: No; Service Animals: No; Public transport access: Yes; Infants on laps: No; Infant seats: Yes | User input | confirmed | Source notes "not suitable for guests with wheelchairs or impaired mobility" |
| traveler cap / group size | 20 | User input | confirmed | |
| min travelers to operate | not provided | User input | missing | |
| cancellation policy | Free cancellation up to 24 hours before departure; within 24 hours non-refundable | User input (confirmed 2026-05-13) | confirmed | Applies across all Milano Adventures tours; source data was incorrect |
| seasonal/weather notes | Winter-season pricing valid from January 9, 2027; alpine route susceptible to weather disruption (implied, not stated in source) | User input | inferred | |
| review rating | none provided | User input | missing | |
| review count | none provided | User input | missing | |
| review text/source | none provided | User input | missing | |
| destination context | Bernina Express (UNESCO World Heritage railway); Tirano, Italy; Alp Grüm, Switzerland; Bernina Diavolezza glacier station; Pontresina; St. Moritz; Lake St. Moritz; Swiss Alps; Canton of Graubünden | User input | confirmed | |
| critical health/legal note | "A VALID PASSPORT IS REQUIRED to join this tour" | User input | confirmed | Must surface prominently in public copy |
| late-booking note | Bookings made less than 12 hours before departure cannot be guaranteed; valid email + active phone required | User input | confirmed | Operational disclosure |
| missing critical inputs | Website booking link now available; Viator/TripAdvisor still optional and missing | Derived | missing | Non-blocking metadata gap only |
| conflicts detected | none | Derived | not_applicable | |

## Clarifications needed (blocking)

- None.

## Missing inputs (non-blocking warnings)

- Internal product reference code.
- Exclusions list.
- Minimum travelers to operate.
- Explicit seasonal-availability statement (winter vs summer route differences).

## Inferred facts

- Canonical title capitalized: "Bernina red train" → "Bernina Red Train"; "St Moritz" preserved as in source (without dot to match supplier copy).
- Start time derived from Excel time fraction 0.3020833 = 07:15.
- Pricing-valid-from date derived from Excel serial 46396 = January 9, 2027.
- Funnel stage classified as BOFU because this is the only conversion asset planned for the `swiss-alps-from-milan` cluster at intake.
