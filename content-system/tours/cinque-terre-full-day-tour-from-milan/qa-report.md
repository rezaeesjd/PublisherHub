# QA Report: Cinque Terre Full-Day Tour from Milan

Last run: 2026-05-09 (WPS:GENERATE_CONTENT initial generation)
Overall status: **needs_fix** — Package is structurally complete but has three blocking clarifications and a placeholder website URL that must be resolved before publish.

---

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
- [ ] CHANGELOG.md — optional; not yet created
- [ ] images/ — optional; not yet created (hero_image omitted from meta.json accordingly)

---

## Metadata checklist (meta.json)

- [x] brand — Milano Adventures
- [x] product_reference_code — 187808P109 (user-provided; conflict flagged — see issues)
- [x] canonical_tour_title
- [x] page_title
- [x] slug — cinque-terre-full-day-tour-from-milan (valid kebab-case)
- [x] public_slug — matches slug
- [x] meta_description — within 50–200 char bounds
- [x] primary_keyword
- [x] funnel_stage — BOFU
- [x] cta_primary — Check Availability
- [x] cta_primary_link — {{WebsiteLink}} (placeholder; see blocking issue #1)
- [x] website_link — {{WebsiteLink}} (placeholder; see blocking issue #1)
- [x] tripadvisor_link — real URL provided and stored
- [x] viator_link — real URL provided and stored
- [x] publish_status — draft
- [x] human_review_required — true
- [x] qa_status — needs_fix
- [x] clarifications_needed — populated (3 blocking, 1 warning)
- [x] generation_phase_completed — true
- [x] clarify_phase_required — true
- [x] clarify_phase_completed — false

---

## Source-fact checklist

- [x] Active brand recorded (Milano Adventures)
- [x] Tour identity recorded
- [x] Logistics recorded (meeting point, duration, start time, departure days)
- [x] Inclusions listed (coach, trains, seasonal ferry, expert guide)
- [x] Exclusions inferred and flagged (meals, personal expenses)
- [x] Pricing recorded (adult €275, child €157, infant free; valid from May 1, 2026)
- [x] Languages recorded (English, Spanish)
- [x] Seasonal note recorded (ferry subject to sea/weather)
- [x] Missing inputs flagged
- [x] Conflicts flagged (product codes, hotel pickup)
- [ ] Cancellation window unit — recorded as raw; unit unconfirmed (blocking)
- [ ] Hotel pickup status — conflicted; flagged as warning (non-blocking)
- [ ] Wheelchair accessibility — not stated in source data
- [ ] Review/rating data — not provided; no claims made in public copy ✓

## Provenance matrix
Present and complete in source-facts.md. All required rows populated.

---

## Public article cleanliness checklist (blog-post.md)

- [x] No admin/SEO labels visible ("Page Title", "URL Slug", "Meta Description", "Primary Keyword", "Funnel Stage", "Internal Linking Suggestions" — all absent)
- [x] Single H1 at top of file
- [x] Brand "Milano Adventures" referenced naturally in blog-post.md (hook paragraph)
- [x] No raw supplier name leaks in public copy
- [x] No invented facts — all claims traceable to source-facts.md
- [x] No invented reviews, ratings, or testimonials
- [x] Ferry seasonality condition accurately represented in public copy
- [x] CTA present in first half of post (soft CTA after "What to expect")
- [x] Strong CTA block present at end of post

---

## Link handling checklist

- [ ] **FAIL** — Real website booking URL not in place of `{{WebsiteLink}}` — conversion blocker
- [x] Real TripAdvisor URL used in blog-post.md and faq.md
- [x] Real Viator URL used in blog-post.md and faq.md
- [x] Placeholders flagged; package not declared publish-ready
- [x] Website link is primary CTA; TripAdvisor and Viator are secondary references only

---

## Conversion checklist

- [x] Primary CTA present and points to website link ({{WebsiteLink}})
- [x] Soft CTA present after "What to expect" section
- [x] Strong CTA block at end of blog-post.md
- [x] Secondary trust signals (TripAdvisor, Viator) appear after primary CTA, not before
- [x] "Who this tour is best for" section present
- [x] "What to know before booking" section present
- [x] Booking confidence details present (duration, meeting point, group size, inclusions, pricing, languages, departure days, difficulty)

---

## Review / social proof checklist

- [x] No invented review counts, ratings, or testimonials
- [x] No unverifiable claims ("best", "top-rated", "most popular") made without evidence
- [x] Social proof section correctly omitted (no review data provided)

---

## Source-facts-only checklist

- [x] All claims in blog-post.md traceable to source-facts.md
- [x] No invented pricing, durations, inclusions, meeting points, or policies
- [x] Cancellation window not stated as a specific number in public copy (users directed to check at booking — appropriate given unit ambiguity)

---

## Publish readiness

- **publish_status:** `draft`
- **qa_status:** `needs_fix`
- **human_review_required:** true
- **clarify_phase_completed:** false
- **publish_phase_completed:** false
- **live_verification_completed:** false

---

## Issues found

### BLOCKING

1. **Missing website booking URL** — `{{WebsiteLink}}` is a placeholder throughout this package (blog-post.md, faq.md, meta.json). The real direct website booking URL must be provided and substituted before this package can be published. This is a conversion blocker.

2. **Cancellation window unit ambiguous** — Source data says "9, Relatively to Start Time" with no unit specified. The field `cancellation_window_hours` in meta.json cannot be set until this is confirmed as hours or days. Blog-post.md directs users to check cancellation terms at booking rather than stating a specific window — this is a safe workaround but the meta.json field remains unresolved.

3. **Conflicting product reference codes** — User provided 187808P109 as the product code, but the Viator URL contains 187808P82. Both are stored (187808P109 as primary, 187808P82 as secondary Viator code). A human must confirm whether these are the same product with channel-specific IDs or different products entirely.

### WARNING (non-blocking)

4. **Hotel pickup status conflicted** — Source data indicates "No, we meet all travelers at a meeting point" but also lists "Hotel pickup and drop-off" as a feature. Recorded as not included (`hotel_pickup_dropoff: false`) pending human clarification. If hotel pickup is confirmed as included, update meta.json, source-facts.md, and blog-post.md.

5. **Wheelchair accessibility not stated** — Source data does not include accessibility status. Not mentioned in public copy. Update source-facts.md and meta.json if confirmed.

6. **No review or rating data** — No review data was provided. Social proof section is correctly omitted from blog-post.md. If review data is available, add it to source-facts.md and include a single, sourced sentence in the blog post.

7. **Individual stop durations and village visit order not provided** — The structured itinerary only details the departure leg (150 minutes from Milan). Stop durations and visit order within Cinque Terre are not specified. Blog post does not fabricate these details.

---

## Recommended fixes

1. Provide the direct website booking URL and replace all instances of `{{WebsiteLink}}` in blog-post.md, faq.md, and meta.json.
2. Confirm whether the cancellation window is 9 hours or 9 days relative to start time. Update `cancellation_window_hours` in meta.json and, if needed, add a specific statement to blog-post.md.
3. Confirm the primary product reference code (187808P109 vs 187808P82) and update meta.json accordingly.
4. Clarify hotel pickup: if included, update `hotel_pickup_dropoff` in meta.json, add it to the inclusions in source-facts.md, and add a note to the "What to expect" section of blog-post.md.
5. If wheelchair accessibility status is known, add it to source-facts.md and update meta.json.
6. If review or rating data is available, add it to source-facts.md and include a single sourced sentence in blog-post.md under an optional social proof section.

---

## Final status

**Not publish-ready.** Three blocking issues must be resolved before this package can be marked `ready_for_review` or progressed toward publish. The package is structurally complete and internally clean — all content is source-based, no facts are invented, and the conversion structure follows platform standards.
