# QA Report: Cinque Terre Full-Day Tour from Milan

Last run: 2026-05-09 (WPS:GENERATE_CONTENT — hard clarify gate triggered)
Overall status: **needs_fix** — Hard clarify gate is active. `blog-post.md` is a holding notice only (per AGENTS.md). Full public copy will be regenerated once blocking clarifications are resolved.

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

`blog-post.md` is currently a **holding notice** because the hard clarify gate is active. Full public copy is intentionally not generated. The following checks apply to the holding notice and will be re-run against the final public article once blocking clarifications are resolved.

- [x] No admin/SEO labels visible
- [x] Single H1 at top of file
- [x] Brand "Milano Adventures" referenced
- [x] No raw supplier name leaks
- [x] No factual claims dependent on unresolved clarifications (no pricing, cancellation window, departure days, durations, or itinerary specifics in holding notice)
- [x] No invented reviews, ratings, or testimonials
- [ ] Ferry seasonality, full inclusions, and CTAs — deferred to final public copy after clarifications resolved
- [ ] Soft CTA in first half — deferred to final public copy
- [ ] Strong CTA at end — deferred to final public copy

---

## Link handling checklist

- [ ] **FAIL** — Real website booking URL not in place of `{{WebsiteLink}}` — conversion blocker
- [x] Real TripAdvisor URL used in blog-post.md and faq.md
- [x] Real Viator URL used in blog-post.md and faq.md
- [x] Placeholders flagged; package not declared publish-ready
- [x] Website link is primary CTA; TripAdvisor and Viator are secondary references only

---

## Conversion checklist

Conversion structure is **deferred**. Hard clarify gate is active and `blog-post.md` is a holding notice only. The conversion checklist will be re-run once blocking clarifications are resolved and the final public article is generated.

- [ ] Primary CTA pointing to real website link — deferred (website URL not provided)
- [ ] Soft CTA in first half — deferred
- [ ] Strong CTA block at end — deferred
- [ ] Secondary trust signals (TripAdvisor, Viator) after primary CTA — links present in holding notice
- [ ] "Who this tour is best for" section — deferred
- [ ] "What to know before booking" section — deferred
- [ ] Booking confidence details — deferred

---

## Review / social proof checklist

- [x] No invented review counts, ratings, or testimonials
- [x] No unverifiable claims ("best", "top-rated", "most popular") made without evidence
- [x] Social proof section correctly omitted (no review data provided)

---

## Source-facts-only checklist

- [x] All claims in current holding-notice blog-post.md are traceable to source-facts.md (only brand identity, village list, and OTA links present)
- [x] No invented pricing, durations, inclusions, meeting points, or policies in holding notice
- [x] No cancellation window stated in holding notice (avoids the unit-ambiguity issue entirely)

---

## Publish readiness

- **publish_status:** `draft`
- **qa_status:** `needs_fix`
- **human_review_required:** true
- **public_copy_state:** `blocked_pending_clarifications`
- **generation_phase_completed:** false (hard clarify gate active — final blog-post.md not generated)
- **clarify_phase_required:** true
- **clarify_phase_completed:** false
- **publish_phase_completed:** false
- **live_verification_completed:** false

### Hard clarify gate

Per `content-system/AGENTS.md` (Enforcement Addendum — Hard clarify gate), final `blog-post.md` is **not** generated when blocking clarifications exist. The current `blog-post.md` is a minimal holding notice only. Once the three blocking clarifications below are resolved, run `WPS:GENERATE_CONTENT` again to generate the final public article.

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

**Not publish-ready. Hard clarify gate active.** `blog-post.md` is a holding notice only; final public copy will be generated after the three blocking clarifications are resolved. The supporting package files (source-facts.md, brief.md, keywords.md, faq.md, internal-links.md, automation-notes.md) are complete and ready to inform the final public copy regeneration.
