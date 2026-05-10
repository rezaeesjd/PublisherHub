# Source Facts — Cinque Terre Full-Day Tour from Milan

## Core identity
- Canonical tour title: **Cinque Terre Full-Day Tour from Milan** (inferred from truncated input and description scope)
- Active system brand: **Milano Adventures**
- Raw supplier/operator label in input: **Official**
- Product/reference code (primary): **187808P109**
- Channel product codes:
  - viator: `187808P82`
  - tripadvisor: `33344981`
  - supplier: `187808P109`

## Provenance matrix
| Field | Raw value | Source | Status | Notes |
|---|---|---|---|---|
| active system brand | Milano Adventures | System rule default | confirmed | Brand for public copy |
| raw supplier/operator name | Official | User input | confirmed | Kept as source only |
| canonical tour title | Cinque Terre Full-Day Tour from Milan: Riomaggiore, Manarola &… | User input | inferred | Trimmed ellipsis |
| product/reference code | 187808P109 | User input | confirmed | Primary reference selected |
| website booking URL | Not provided | User input | missing | Placeholder retained |
| TripAdvisor URL | https://www.tripadvisor.com/AttractionProductReview-g187849-d33344981-Cinque_Terre_Full_Day_Tour_from_Milan_Riomaggiore_Manarola-Milan_Lombardy.html | User input | confirmed | Secondary/alternate trust channel |
| Viator URL | https://www.viator.com/tours/Milan/Full-Day-Cinque-Terre-Tour-from-Milan-Riomaggiore-Manarola/d512-187808P82 | User input | confirmed | Used as primary CTA fallback |
| price | Adult EUR 275; Child EUR 157; Infant EUR 0 | User input | confirmed | Per person |
| duration | 13 hrs 30 min | User input | confirmed | Full-day format |
| start time | 07:00 | User input | confirmed | Mon/Wed/Sat |
| meeting point | Piazza IV Novembre, 20124 Milano MI, Italy | User input | confirmed | In front of Hotel Gallia bus stop |
| end point | Piazza IV Novembre, 20124 Milano MI, Italy | User input | confirmed | Same as departure |
| itinerary stops | Monterosso, Vernazza, Corniglia, Manarola, Riomaggiore | User input description | confirmed | Broader 5-villages scope adopted |
| itinerary durations | Milan transfer 150 minutes (other stop durations N/A) | User input | needs_human_review | Partial timings only |
| inclusions | Expert tour leader; Land transport; Train/Rail; Seasonal ferry ride | User input | confirmed | Ferry subject to conditions |
| exclusions | Hotel pickup and drop-off | User input | confirmed | Explicit no hotel pickup |
| languages | English, Spanish | User input | confirmed | Guide language set |
| accessibility | Not provided | User input | missing | Omit from public claims |
| traveler cap / group size | 22 travelers max | User input | confirmed | One booking per reservation |
| cancellation policy | 9 (relative to start time) | User input | needs_human_review | Unit unclear; not used in claims |
| seasonal/weather notes | Ferry is seasonal and weather dependent | User input | confirmed | Include caveat in copy |
| review rating | Not provided | User input | missing | No rating claim allowed |
| review count | Not provided | User input | missing | No review-volume claim |
| review text/source | Not provided | User input | missing | No testimonials |
| missing critical inputs | Direct website booking URL missing | User input | needs_human_review | Non-blocking due to OTA links |
| conflicts detected | Title names two villages; description lists five villages | User input | inferred | Canonicalized to full Cinque Terre scope |

## Clarify decisions ledger
| field | raw_value | blocking | decision | reason | resolved_value |
|---|---|---:|---|---|---|
| canonical_tour_title | Cinque Terre Full-Day Tour from Milan: Riomaggiore, Manarola &… | false | auto_resolved | Truncated title; cleaned per rule | Cinque Terre Full-Day Tour from Milan |
| itinerary_scope | Title mentions 2 villages, description lists 5 | false | auto_resolved | Broader scope better matches description | Full five-village scope |
| website_link | Missing website URL | false | auto_resolved | Viator and TripAdvisor available; CTA fallback allowed | Primary CTA set to Viator |
| cancellation_policy_unit | 9 relative to start time | false | auto_resolved | Unit unclear; excluded from public claim | Not stated in article |
| unlabeled_numeric_policy | 15 | false | auto_resolved | Unlabeled numeric policy ignored | Not stated in article |
