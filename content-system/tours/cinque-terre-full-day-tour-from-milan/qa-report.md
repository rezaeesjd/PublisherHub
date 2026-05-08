# QA Report: Cinque Terre Full-Day Tour from Milan

Last run: 2026-05-08 (refresh after WPS:GENERATE_CONTENT with expanded source data)
Overall status: **needs_fix** — package is structurally complete and TripAdvisor/Viator URLs are now verified, but the website booking URL is still a placeholder and a few raw values still need human review.

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

## Folder check

- [x] Folder name `cinque-terre-full-day-tour-from-milan` is valid kebab-case and matches the canonical tour title

## Metadata checklist (meta.json)

- [x] brand
- [x] product_reference_code (`187808P109`, with secondary codes recorded)
- [x] tour_title
- [x] page_title
- [x] slug / public_slug
- [x] meta_description
- [x] primary_keyword
- [x] funnel_stage
- [x] cta_primary
- [x] website_link (placeholder)
- [x] tripadvisor_link (verified)
- [x] viator_link (verified)
- [x] start_time, departure_days
- [x] pricing (recorded for QA, not surfaced in public body)
- [x] publish_status
- [x] human_review_required
- [x] qa_status

## Source-fact checklist

- [x] Active brand recorded
- [x] Tour identity recorded
- [x] Logistics (meeting point, duration, group type, start time, departure days) recorded
- [x] Inclusions listed
- [x] Exclusions listed
- [x] Pricing recorded (EUR, per person)
- [x] Required booking fields recorded
- [x] Ticket format recorded
- [ ] Cancellation window unit confirmed
- [ ] Canonical product reference code confirmed (P109 vs P82 vs 108)
- [ ] Minimum traveler threshold confirmed (raw "15")
- [ ] Review/rating data recorded
- [x] Missing inputs flagged

## Public article cleanliness checklist

- [x] No `# Page Title`, `## URL Slug`, `## Meta Description`, `## Internal Linking Suggestions`, or similar admin labels in blog-post.md
- [x] Single H1 at top of blog-post.md
- [x] Brand referenced is Milano Adventures (matches meta.brand)
- [x] No raw supplier name leaks in public copy
- [x] Public copy follows the structured itinerary (3 villages with free time) and does not over-claim from the broader marketing description (5 villages)

## Link handling checklist

- [ ] Real website booking URL in place of `{{WebsiteLink}}`
- [x] Real TripAdvisor URL is used in blog-post.md, faq.md, internal-links.md, meta.json
- [x] Real Viator URL is used in blog-post.md, faq.md, internal-links.md, meta.json
- [x] TripAdvisor and Viator are placed only as secondary references after the primary CTA
- [x] Placeholder for website link is flagged so the post is not declared publish-ready

## Conversion checklist

- [x] Primary CTA present in the first half of the post pointing to `{{WebsiteLink}}`
- [x] Strong CTA near the end pointing to `{{WebsiteLink}}`
- [x] Booking confidence details (duration, meeting point, group type, languages, accessibility, infants, departure days, start time) included
- [x] "Who this tour is best for" section present
- [x] "What to know before booking" section present
- [x] Direct website booking is the preferred action
- [x] TripAdvisor and Viator appear only as secondary trust references

## Review / social proof checklist

- [x] No invented review counts or ratings
- [x] No invented testimonials
- [ ] Approved review/rating data populated in source-facts.md (none currently provided)

## Source-facts-only checklist

- [x] Itinerary stops mentioned in public copy match `source-facts.md`
- [x] Duration, meeting point, departure days, start time, languages, accessibility, infant policy match `source-facts.md`
- [x] Inclusions match `source-facts.md`
- [x] Pricing kept out of the public article body to avoid stale data; available on the booking page

## Publish readiness

- **publish_status:** `draft`
- **qa_status:** `needs_fix`
- **human_review_required:** true

## Issues found

1. Website booking URL is still `{{WebsiteLink}}`. This must be replaced with the real direct booking URL before publish.
2. Cancellation window unit is ambiguous (raw value `9, Relatively to Start Time`). Confirm hours vs days.
3. Three product codes appear in the input (`187808P109` provided, `187808P82` from Viator URL, `108` internal). Confirm canonical code.
4. Raw `15` value associated with traveler count is ambiguous. Confirm whether this is a minimum-to-operate threshold.
5. No approved review/rating data is recorded; public copy currently makes no review claims, which is correct, but a single sourced sentence could be added once data is approved.

## Recommended fixes

1. Provide the real direct website booking URL and replace `{{WebsiteLink}}` across `meta.json`, `blog-post.md`, `faq.md`, `internal-links.md`, and `automation-notes.md`.
2. Confirm the cancellation window unit and update both `source-facts.md` and `meta.json` (and add an FAQ answer if appropriate).
3. Confirm the canonical product reference code and adjust `meta.json` if needed.
4. Confirm the meaning of the raw `15` value and either record it as `min_travelers_to_operate` in `meta.json` or remove it.
5. If review/rating data is approved, add it to `source-facts.md` and surface a single, sourced sentence in `blog-post.md`.

## Final status

**Not publish-ready.** Block transitions to `published` until at least issues 1 and 2 are resolved. Once the website URL is in place and the cancellation unit is confirmed, the package can move to `ready_for_sync` and then to `needs_live_verification` for live front-end verification.
