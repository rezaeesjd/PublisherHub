# Source Facts — Private Full Day Trip to Lake Como from Milan

## Canonical identity

- Canonical tour title: **Private Full Day Trip to Lake Como from Milan**
- Active system brand: **Milano Adventures**
- Raw supplier/operator name in source: not provided
- Package command: `WPS:GENERATE_CONTENT`
- Variant context: Functions as a private-vehicle MOFU comparison companion to the All-in-One Lake Como cluster parent.

## Raw input snapshot

- Funnel stage: BOFU (private upgrade tier)
- Language: English
- Mode of transportation: Land Transport — Luxury Car or Minivan
- Tour duration: One day or less
- Pick-up option: "We can pick up travelers or meet them at a meeting point"
- Themes: Outdoor & Adventure (Outdoors); Travel (Shore Excursion, Sightseeing)
- Reference code: 2230
- Image folder: https://drive.google.com/drive/folders/15e-BkgDqAdnwtrKFtuGvr5CnVL5gVn4W
- All other operational fields: missing

## Provenance matrix

| Field | Raw value | Source | Status | Notes |
|---|---|---|---|---|
| active system brand | Milano Adventures | AGENTS default | confirmed | |
| raw supplier/operator name | not provided | User input | missing | |
| canonical tour title | Private Full Day Trip to Lake Como from Milan | User input | confirmed | |
| internal product/reference code | 2230 | User input | confirmed | |
| channel product codes | not provided | User input | missing | |
| website booking URL | not provided | User input | missing | Blocking — no permalink of any channel |
| TripAdvisor URL | not provided | User input | missing | |
| Viator URL | not provided | User input | missing | |
| price (per band) | not provided | User input | missing | Blocking |
| pricing valid from | not provided | User input | missing | |
| duration | One day or less | User input | confirmed | Coarse; needs hours |
| start time | not provided | User input | missing | |
| departure days | not provided | User input | missing | |
| meeting point | "We can pick up travelers or meet them at a meeting point" | User input | needs_human_review | Pickup OR meeting point — needs canonical default |
| end point | not provided | User input | missing | |
| itinerary stops | not provided | User input | missing | Blocking for public copy |
| itinerary durations | not provided | User input | missing | |
| inclusions | not provided | User input | missing | |
| exclusions | not provided | User input | missing | |
| languages | not provided | User input | missing | |
| accessibility | not provided | User input | missing | |
| traveler cap / group size | not provided (private — implies small group) | Inferred | inferred | |
| min travelers to operate | not provided | User input | missing | |
| cancellation policy | not provided | User input | missing | |
| seasonal/weather notes | not provided | User input | missing | |
| review rating | none provided | User input | missing | |
| review count | none provided | User input | missing | |
| review text/source | none provided | User input | missing | |
| destination context | Lake Como (Lombardy) — to be confirmed which lake towns are visited | User input | inferred | |
| missing critical inputs | booking permalink, price, meeting point, itinerary, languages, accessibility | Derived | not_applicable | Multiple blockers |
| conflicts detected | none | Derived | not_applicable | |

## Clarifications needed (blocking)

- field: booking_permalink
  raw_value: missing
  question: Provide at least one booking permalink (own website, Viator, GetYourGuide, or TripAdvisor) for the private Lake Como tour.

- field: price
  raw_value: missing
  question: Provide retail price per traveler (and currency) for the private Lake Como tour.

- field: meeting_point_and_pickup
  raw_value: "We can pick up travelers or meet them at a meeting point"
  question: Where is the default meeting point in Milan, and what is the pickup zone for hotel pickup?

- field: itinerary
  raw_value: missing
  question: Confirm the standard itinerary stops (e.g., Como, Bellagio, Varenna) and approximate time at each.

## Missing inputs (non-blocking warnings)

- Languages and accessibility details.
- Cancellation policy and lead time.
- Operating days.

## Inferred facts

- "Private" implies a single-party booking with capped group size; treated as `inferred` until confirmed.
