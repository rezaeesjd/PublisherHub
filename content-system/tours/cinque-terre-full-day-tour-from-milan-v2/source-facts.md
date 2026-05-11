# Source Facts — Cinque Terre Full-Day Tour from Milan

- Canonical tour title: **Cinque Terre Full-Day Tour from Milan** (inferred from truncated input title ending with ellipsis).
- Active system brand: **Milano Adventures**.
- Raw supplier/operator name in source: Not explicitly provided.

## Variant context

- Variant package: **cinque-terre-full-day-tour-from-milan-v2**
- Variant of: **cinque-terre-full-day-tour-from-milan**
- Variant angle: **BOFU day-trip / five-villages keyword variant**
- Open warnings inherited by default from base package: missing direct website booking URL; cancellation policy unit unclear.

## Provenance Matrix

| Field | Raw value | Source | Status | Notes |
|---|---|---|---|---|
| active system brand | Milano Adventures | AGENTS brand rule | confirmed | Default brand rule applied |
| raw supplier/operator name | Not provided | User input | missing | Not shown in payload |
| canonical tour title | Cinque Terre Full-Day Tour from Milan: Riomaggiore, Manarola &… | User input | inferred | Ellipsis removed per auto-resolution |
| product/reference code | 187808P109 | User input | confirmed | Supplier-side reference treated as primary |
| channel product codes | Viator: 187808P82; TripAdvisor product id: d33344981 | User input URLs/text | confirmed | Channel-specific codes, non-conflicting |
| website booking URL | Not provided | User input | missing | Placeholder retained in meta |
| TripAdvisor URL | https://www.tripadvisor.com/AttractionProductReview-g187849-d33344981-Cinque_Terre_Full_Day_Tour_from_Milan_Riomaggiore_Manarola-Milan_Lombardy.html | User input | confirmed | Real OTA link |
| Viator URL | https://www.viator.com/tours/Milan/Full-Day-Cinque-Terre-Tour-from-Milan-Riomaggiore-Manarola/d512-187808P82 | User input | confirmed | Real OTA link |
| price | Adult EUR 275; Child EUR 157; Infant EUR 0 | User input | confirmed | Per person |
| duration | 13 hrs 30 min | User input | confirmed | One-day tour |
| start time | 07:00 | User input | confirmed | Monday/Wednesday/Saturday departures |
| meeting point | Piazza IV Novembre, 20124 Milano MI, Italy (Hotel Gallia bus stop at Milano Centrale) | User input | confirmed | Arrival requested 15 min early |
| end point | Piazza IV Novembre, 20124 Milano MI, Italy | User input | confirmed | Returns to origin |
| itinerary stops | Riomaggiore, Manarola, Monterosso al Mare, Vernazza, Corniglia (5 Terre scope from description) | User input description | inferred | Description broader than truncated title scope |
| itinerary durations | Transfer to coast: 150 min; remaining stop timing not itemized | User input | needs_human_review | No per-village minutes provided |
| inclusions | Expert tour leader; land transport (bus/coach/minibus/train); seasonal panoramic ferry ride; likely transport tickets per description | User input | confirmed | Hotel pickup excluded |
| exclusions | Hotel pickup and drop-off | User input | confirmed | Explicit exclusion |
| languages | English, Spanish | User input | confirmed | Listed for guide/product |
| accessibility | Not provided | User input | missing | Omitted from public claims |
| traveler cap / group size | 22 travelers max | User input | confirmed | |
| cancellation policy | “15” (unit unclear) | User input | needs_human_review | Number lacks unit; excluded from specific public claim |
| seasonal/weather notes | Ferry ride seasonal and weather-dependent | User input | confirmed | May be replaced by train/logistics |
| review rating | Not provided | User input | missing | |
| review count | Not provided | User input | missing | |
| review text/source | Viator + TripAdvisor listing URLs only | User input | needs_human_review | URLs present, no rating values supplied |
| missing critical inputs | Direct website booking URL missing | User input | needs_human_review | Non-blocking due to OTA links |
| conflicts detected | Title mentions 2 towns; description names all five villages | User input | inferred | Resolved to broader five-village scope |

## Clarify Decisions Ledger

| field | raw_value | blocking | decision | reason | resolved_value |
|---|---|---|---|---|---|
| canonical_tour_title | "...Riomaggiore, Manarola &…" | false | auto_resolved | Truncated title; removed ellipsis and kept stable clear title | Cinque Terre Full-Day Tour from Milan |
| itinerary_scope | Title subset vs description full five villages | false | auto_resolved | Broader supplier description used for public scope | Five villages included |
| website_link | missing | false | auto_resolved | OTA links exist; Viator used as primary CTA fallback | Viator URL |
| cancellation_policy_unit | "15" | false | auto_resolved | Unit unclear; excluded from numeric public claim | Generic policy note only |
