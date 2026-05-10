# Automation Notes — Cinque Terre Full-Day Tour from Milan

## Templates used in this run
- `templates/blog-post-public-template.md`
- `templates/source-facts-template.md`
- `templates/keywords-template.md`
- `templates/intake-form-template.md`
- `templates/meta-template.json`
- `templates/qa-report-template.md`
- `templates/internal-links-template.md`
- `templates/automation-notes-template.md`

## Reuse pattern (weekly cadence)
- 1 BOFU landing-blog hybrid like this one per week, per priority destination
- 1 informational TOFU post per week feeding the same destination cluster
- 1 weekly refresh of an older post (price, dates, ferry seasonality)

## Scaling with AI assistance
- Reuse the section order from this post: H1 → hook → why-worth-it → who-it's-best-for → what-to-expect → what-to-know → strong CTA → secondary OTA reference
- Keep the CTA block consistent: primary button to the highest-priority booking URL, secondary line to TripAdvisor when available
- For each new tour, fill the intake form, run `WPS:GENERATE_CONTENT_FROM_INTAKE`, and let the non-blocking auto-resolution rules handle truncated titles, channel-specific codes, missing website URL, missing accessibility, unlabeled numerics, and itinerary-scope mismatches without a clarification round

## Design / layout consistency
- Single H1; no admin/SEO labels in the public body
- Always include duration, meeting point, languages, and group cap as booking-confidence facts
- Never invent cancellation specifics, ratings, or review counts
- When the website URL is missing, switch CTA copy to match the booking channel ("Book on Viator", "Reserve on TripAdvisor")

## Adapting to similar tour topics
- Lake Como, Verona, Lake Garda, and Tuscany day trips from Milan can reuse this exact structure; only the village/destination names and the booking URL change
- Keep `channel_product_codes` populated with all OTA codes so the platform can route booking buttons per channel later

## Lead-gen alignment
- Every section is written to push toward the Viator booking link while the website URL is missing; once the website URL is added, swap `cta_primary_link` to the website and demote Viator to secondary
- This post supports the platform goal of organic lead generation through content operations: BOFU intent capture for `cinque terre tour from milan` plus internal links into a future Milan day-trips cluster
