# QA Report — Cinque Terre Day Trip from Milan (Variant v2)

**Date:** 2026-05-10
**Command:** `WPS:GENERATE_CONTENT`
**Variant:** v2 (BOFU "day-trip" keyword variant)
**Base package:** `cinque-terre-full-day-tour-from-milan-riomaggiore-manarola`

## Tour Identity Confirmation
- Requested command: `WPS:GENERATE_CONTENT`
- Actual package folder: `content-system/tours/cinque-terre-full-day-tour-from-milan-riomaggiore-manarola-v2/`
- Canonical tour title: Cinque Terre Full-Day Tour from Milan
- Product/reference code: 187808P109 (supplier); channel codes — viator: 187808P82, tripadvisor: 33344981
- Active brand: Milano Adventures
- Website URL status: missing → `{{WebsiteLink}}` placeholder (non-blocking)
- TripAdvisor URL status: provided
- Viator URL status: provided (used as primary CTA)
- Package created: 2026-05-10
- Report scope: generation only (no publish or live verification)

## Variant compliance
- **Multi-variant rule applied:** existing finalized package found at `cinque-terre-full-day-tour-from-milan-riomaggiore-manarola` with `public_copy_state: final`. Per the multi-variant generation rule in `AGENTS.md`, this run created a new variant package rather than overwriting prior content. PASS.
- `variant_of` set to base slug. PASS.
- `variant_index` set to 2. PASS.
- `slug` and `public_slug` both unique vs. existing package. PASS.

## File checks
- All 9 required files exist (`source-facts.md`, `brief.md`, `keywords.md`, `blog-post.md`, `faq.md`, `meta.json`, `internal-links.md`, `automation-notes.md`, `qa-report.md`). PASS.
- Filenames correct. PASS.
- Folder name is valid kebab-case. PASS.

## Metadata checks
- `meta.json` is valid JSON. PASS.
- Required schema fields present (brand, canonical_tour_title, page_title, slug, public_slug, meta_description, primary_keyword, funnel_stage, cta_primary, cta_primary_link, website_link, publish_status, human_review_required, qa_status, plus phase markers). PASS.
- `slug` matches folder name. PASS.
- `public_slug` differs from base variant's `public_slug`. PASS.
- `publish_status: draft`, `human_review_required: true`. PASS.

## Source-facts checks
- `source-facts.md` extracted before public copy. PASS.
- All facts used in `blog-post.md`/`faq.md` appear in the provenance matrix. PASS.
- Missing inputs explicitly listed (website URL, accessibility, cancellation unit, role of `15`, role of `May 1, 2026`). PASS.
- No supplier/third-party brand used as the public brand. PASS.

## Public article checks
- `blog-post.md` uses one Markdown H1. PASS.
- No admin labels in body ("Page Title", "URL Slug", "Meta Description", "Internal Linking Suggestions", etc.). PASS.
- Active brand ("Milano Adventures") appears in body. PASS.
- CTA in first half of post. PASS.
- Strong CTA near the end. PASS.

## Link checks
- Provided real Viator URL used (primary CTA). PASS.
- Provided real TripAdvisor URL used as secondary trust reference. PASS.
- `{{WebsiteLink}}` only used in places where no real URL was supplied; flagged as not fully publish-ready. WARN.
- TripAdvisor / Viator are not promoted as the long-term primary CTA — they're the fallback per AGENTS.md until a direct website URL is supplied. PASS.

## Conversion checklist
- One direct booking CTA in the first half of the post. PASS.
- One strong booking CTA near the end. PASS.
- Booking confidence details (duration, meeting point, group size, languages, included items). PASS.
- "Who this tour is best for" section. PASS.
- "What to know before booking" section. PASS.

## Review / social proof checks
- No invented review/rating claims. PASS.
- No review counts cited (none provided in intake). PASS.

## Source-facts-only check
- All factual claims trace to a row in the `source-facts.md` provenance matrix. PASS.

## Publish readiness
- Publish path: not yet attempted. `publish_status` remains `draft`.
- Live verification: not attempted. `live_verification_completed: false`.

## Issues found
1. `{{WebsiteLink}}` placeholder is in use because no direct booking URL was supplied. Non-blocking; CTA falls back to Viator per AGENTS.md.
2. Wheelchair accessibility status missing — omitted from public copy. Non-blocking warning.
3. Cancellation window unit unresolved (`9, Relatively to Start Time`). Public copy points travelers to the booking page. Non-blocking warning.
4. `15` (unlabeled numeric policy value) and `May 1, 2026` (date with unclear role) — both omitted from public copy and recorded in `source-facts.md`. Non-blocking.

## Recommended fixes
- Supply a direct website booking URL so the CTA can be re-pointed away from Viator across both variants in this product cluster.
- Confirm wheelchair accessibility status (yes / no / not applicable).
- Resolve the cancellation window unit (hours or days) so the cancellation specifics can re-enter public copy.

## Final status
- `qa_status`: `warning`
- `publish_status`: `draft`
- `public_copy_state`: `final`
- `intake_questions_resolved`: `true`
- `human_review_required`: `true`
