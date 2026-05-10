# Source Facts — Cinque Terre Full-Day Tour from Milan

## Tour identity
- Canonical tour title: **Cinque Terre Full-Day Tour from Milan** (inferred from truncated input and description scope)
- Active system brand: **Milano Adventures**
- Raw supplier/operator label in source: Official
- Product/reference code (primary): 187808P109
- Channel-specific codes:
  - Viator: 187808P82
  - TripAdvisor product id: 33344981

## Provenance matrix

| Field | Raw value | Source | Status | Notes |
|---|---|---|---|---|
| active system brand | Milano Adventures | System rule | confirmed | Default active brand |
| raw supplier/operator name | Official | User input | confirmed | Kept as source-only fact |
| canonical tour title | Cinque Terre Full-Day Tour from Milan: Riomaggiore, Manarola &… | User input | inferred | Removed trailing ellipsis |
| product/reference code | 187808P109 | User input | confirmed | Treated as primary non-OTA code |
| website booking URL | Not provided | User input | missing | No direct site URL supplied |
| TripAdvisor URL | https://www.tripadvisor.com/AttractionProductReview-g187849-d33344981-Cinque_Terre_Full_Day_Tour_from_Milan_Riomaggiore_Manarola-Milan_Lombardy.html | User input | confirmed | Secondary OTA link |
| Viator URL | https://www.viator.com/tours/Milan/Full-Day-Cinque-Terre-Tour-from-Milan-Riomaggiore-Manarola/d512-187808P82 | User input | confirmed | Used as primary CTA fallback |
| price | Adult 275 EUR; Child 157 EUR; Infant 0 EUR | User input | confirmed | Per person |
| duration | 13 Hrs and 30 minutes | User input | confirmed | One-day tour |
| start time | 07:00 | User input | confirmed | Relative to start time |
| meeting point | Piazza IV Novembre, 20124 Milano MI, Italy (Hotel Gallia bus stop, Milano Centrale area) | User input | confirmed | Arrive 15 minutes early |
| end point | Piazza IV Novembre, 20124 Milano MI, Italy | User input | confirmed | Returns to start |
| itinerary stops | Riomaggiore, Manarola, Monterosso al Mare, Vernazza, Corniglia | User input (title + description) | inferred | Scope aligned to full five villages |
| itinerary durations | Departure transfer 150 minutes; local stop timings not specified | User input | needs_human_review | Per-stop durations missing |
| inclusions | Expert tour leader; GT coach/minibus; train/rail; seasonal panoramic ferry ride | User input | confirmed | Ferry subject to weather/season |
| exclusions | Hotel pickup and drop-off | User input | confirmed | Not included |
| languages | English, Spanish | User input | confirmed | Guide/operations languages listed |
| accessibility | No wheelchair accessibility indicated | User input | missing | Omitted from public claims |
| traveler cap / group size | 22 travelers max | User input | confirmed | One per booking noted |
| cancellation policy | Value "15" provided without explicit unit | User input | needs_human_review | Do not publish specific window |
| seasonal/weather notes | Ferry may not operate due to sea/weather conditions | User input | confirmed | Must be communicated |
| review rating | Not provided | User input | missing | No rating claim allowed |
| review count | Not provided | User input | missing | No review-count claim allowed |
| review text/source | None provided | User input | missing | No testimonial claim allowed |
| missing critical inputs | Direct website booking URL missing | User input | needs_human_review | Non-blocking due to OTA links present |
| conflicts detected | Title mentions two towns but description covers five villages | User input | inferred | Broader scope selected |

## Notes for content generation
- Primary booking CTA channel should be **Viator** until a direct website booking URL is provided.
- Keep TripAdvisor as a secondary trust/reference link.
- Do not publish numeric cancellation windows until unit and policy text are confirmed.
