# Source Facts — Cinque Terre Full-Day Tour from Milan

## Raw intake snapshot
- Command: `WPS:GENERATE_CONTENT`
- Active system brand: Milano Adventures (default)
- Canonical tour title (intake): `Cinque Terre Full-Day Tour from Milan: Riomaggiore, Manarola & …` (truncated)
- Derived canonical title: **Cinque Terre Full-Day Tour from Milan** (auto-resolved per AGENTS.md non-blocking rules — ellipsis stripped; scope expanded to the full five-village itinerary described in the long description)
- Product/reference codes detected: `187808P109` (intake / supplier), `187808P82` (Viator URL), `33344981` (TripAdvisor URL) — treated as channel-specific, not conflicting

## URLs and booking links
- Website booking URL: not provided → `{{WebsiteLink}}` placeholder (non-blocking warning)
- Viator URL: https://www.viator.com/tours/Milan/Full-Day-Cinque-Terre-Tour-from-Milan-Riomaggiore-Manarola/d512-187808P82
- TripAdvisor URL: https://www.tripadvisor.com/AttractionProductReview-g187849-d33344981-Cinque_Terre_Full_Day_Tour_from_Milan_Riomaggiore_Manarola-Milan_Lombardy.html
- Supplier photo folder (internal use only): https://drive.google.com/drive/folders/17NvbPnhFf_0d3VNgxo3Mo_lEQQwHxNwE
- Primary CTA channel: **Viator** (highest-priority booking URL available; per AGENTS.md fallback order website → Viator → TripAdvisor)

## Core operational facts
- Type: Tour (Shore Excursion / Sightseeing / Culture / Travel)
- Duration: 13 hours 30 minutes
- Departure city: Milan
- Start point: Piazza IV Novembre, 20124 Milano MI, Italy (in front of Hotel Gallia, Milano Centrale area)
- Meeting instructions: arrive 15 minutes early; staff in fuchsia shirts
- End point: Piazza IV Novembre, 20124 Milano MI, Italy (same as start)
- Transport: Bus/Coach; Minibus; Train/Rail; seasonal panoramic ferry (weather-dependent)
- Languages: English, Spanish (live guide)
- Guide: Expert tour leader (official)
- Max travelers: 22
- Difficulty: Easy
- Confirmation: Instant
- Ticket: paper or mobile accepted; one per booking
- Operating days: Mon, Wed, Sat
- Start time: 07:00
- Currency: EUR
- Per-person pricing: Adult (12–99) €275; Child (4–11) €157; Infant (0–3) €0

## Itinerary scope (auto-resolved)
The long description names all five Cinque Terre villages — Monterosso al Mare, Vernazza, Corniglia, Manarola, Riomaggiore. Per AGENTS.md non-blocking rule (itinerary scope conflict → pick broader scope), public copy markets the **five-village** experience while keeping Riomaggiore and Manarola visible as named highlights.

Itinerary stops:
1. Departure from Milan: meet at Piazza IV Novembre / Hotel Gallia bus stop; board GT coach for the Ligurian coast (≈150 minutes road segment).
2. Cinque Terre coastal villages by train and (seasonal) ferry: Monterosso al Mare, Vernazza, Corniglia, Manarola, Riomaggiore.
3. Return coach to Milan; drop-off at Piazza IV Novembre.

## Inclusions / exclusions
- Included (from supplier description): expert tour leader, GT coach transport, train tickets between villages, ferry ride when seasonally available
- Excluded: hotel pickup and drop-off (meeting-point only)

## Policies recorded but not used in public copy
- Cancellation: raw value `9, Relatively to Start Time` — unit unclear, **excluded from public copy** per AGENTS.md non-blocking rule
- Numeric policy value `15` — meaning unclear, **ignored** per AGENTS.md non-blocking rule
- Date `May 1, 2026` — role unclear (pricing valid-from?), **omitted** from public copy
- Wheelchair accessibility: not provided — **omitted** from public copy

## Reviews / social proof
- Rating: not provided
- Review count: not provided
- Review text: not provided
- Public copy contains no review claims.

## Brand handling
- Active system brand: Milano Adventures
- No supplier brand name supplied as a competing public identity. Viator and TripAdvisor appear only as booking-channel references.

## Provenance matrix

| Field | Raw value | Source | Status | Notes |
|---|---|---|---|---|
| active system brand | Milano Adventures | AGENTS.md default | confirmed | |
| raw supplier/operator name | not supplied | User intake | missing | |
| canonical tour title | Cinque Terre Full-Day Tour from Milan: Riomaggiore, Manarola & … | User intake | inferred | Truncated; derived clean title "Cinque Terre Full-Day Tour from Milan" |
| product/reference code | 187808P109 | User intake | confirmed | Used as canonical supplier code |
| channel product codes | viator: 187808P82; tripadvisor: 33344981; supplier: 187808P109 | User intake + URLs | confirmed | Channel-specific, not conflicting |
| website booking URL | not provided | User intake | missing | Non-blocking; OTA fallback in use |
| TripAdvisor URL | https://www.tripadvisor.com/AttractionProductReview-g187849-d33344981-Cinque_Terre_Full_Day_Tour_from_Milan_Riomaggiore_Manarola-Milan_Lombardy.html | User intake | confirmed | |
| Viator URL | https://www.viator.com/tours/Milan/Full-Day-Cinque-Terre-Tour-from-Milan-Riomaggiore-Manarola/d512-187808P82 | User intake | confirmed | Primary CTA channel |
| price | Adult €275; Child €157; Infant €0 | User intake | confirmed | |
| duration | 13 Hrs and 30 minutes | User intake | confirmed | |
| start time | 07:00 (Mon, Wed, Sat) | User intake | confirmed | |
| meeting point | Piazza IV Novembre, Milano Centrale, in front of Hotel Gallia | User intake | confirmed | |
| end point | Piazza IV Novembre, 20124 Milano MI, Italy | User intake | confirmed | Same as start |
| itinerary stops | All five Cinque Terre villages | User intake (long description) | inferred | Broader scope chosen per non-blocking rule |
| itinerary durations | 150 minutes (Milan→coast road segment) | User intake | confirmed | |
| inclusions | Expert tour leader; coach; train tickets; seasonal ferry | User intake (long description) | confirmed | UNESCO World Heritage Site reference also from supplier description |
| exclusions | Hotel pickup and drop-off | User intake | confirmed | |
| languages | English, Spanish | User intake | confirmed | |
| accessibility | not supplied | User intake | missing | Omitted from public copy (non-blocking warning) |
| traveler cap / group size | 22 | User intake | confirmed | |
| cancellation policy | 9, Relatively to Start Time | User intake | needs_human_review | Unit unresolved; excluded from public copy |
| seasonal/weather notes | Panoramic ferry seasonal & weather-dependent | User intake | confirmed | |
| review rating | not provided | User intake | missing | |
| review count | not provided | User intake | missing | |
| review text/source | not provided | User intake | not_applicable | |
| missing critical inputs | website URL, accessibility, cancellation unit, role of `15`, role of date `May 1, 2026` | Derived from intake | confirmed | All non-blocking under updated rules |
| conflicts detected | none material; channel codes and itinerary scope auto-resolved | Derived from intake | confirmed | |
| UNESCO World Heritage Site claim | Supplier product description | User intake | confirmed | Supports the marketing line in `blog-post.md` |
