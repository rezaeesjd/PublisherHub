# QA Report — Cinque Terre Full-Day Tour from Milan

## File checklist
- [x] source-facts.md
- [x] brief.md
- [x] keywords.md
- [x] blog-post.md
- [x] faq.md
- [x] meta.json
- [x] internal-links.md
- [x] automation-notes.md
- [x] qa-report.md

## Metadata checklist
- [x] `meta.json` is valid JSON
- [x] Required meta fields present
- [x] `public_slug` format valid
- [x] `publish_status` is honest for current state (`needs_fix`)
- [x] `human_review_required` = true
- [x] `last_qa_date` present

## Source-fact checklist
- [x] Tour identity captured
- [x] Brand rule applied (Milano Adventures as public brand)
- [x] Logistics, itinerary, inclusions/exclusions captured
- [x] Missing inputs listed
- [x] Human-review facts listed

## Public article cleanliness checklist
- [x] H1 exists
- [x] No admin/SEO labels in public article body
- [x] Public-facing structure present (hook/value/who/expectations/CTA/booking notes)

## Link handling checklist
- [x] Viator and TripAdvisor real links preserved
- [x] Website placeholder used because direct website URL not provided
- [x] Placeholder usage flagged as blocking issue
- [x] OTA links are secondary, not primary CTA

## Conversion checklist
- [x] Soft CTA included
- [x] Strong CTA near end included
- [x] Booking-confidence facts included
- [x] “Who this tour is best for” included
- [x] “What to know before booking” included

## Review/social proof checklist
- [x] No invented review/rating claims
- [x] No exaggerated social proof statements
- [x] Social-proof section omitted because structured rating/review metrics were not provided

## Source-facts-only checklist
- [x] No invented pricing/duration/meeting-point facts
- [x] Ambiguous fields captured in clarifications

## Publish readiness status
- Not ready for publish workflow completion.
- Package is structurally upgraded and reviewable, but blocking clarifications remain.

## Issues found
1. Product code conflict: `187808P82` vs `187808P109`.
2. Cancellation window unit unresolved for value `9`.
3. Direct website booking URL missing; `{{WebsiteLink}}` placeholder still in use.

## Recommended fixes
- Confirm canonical product code and update `product_reference_code`.
- Confirm cancellation unit and store typed field.
- Provide final website booking URL and replace `{{WebsiteLink}}` across files.

## Final status
- `publish_status`: **needs_fix**
- `qa_status`: **needs_fix**
- Not published; live verification has not been performed.
