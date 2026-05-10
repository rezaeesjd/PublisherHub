# QA Report — Cinque Terre Full-Day Tour from Milan

- Command evaluated: `WPS:GENERATE_CONTENT`
- Date (UTC): 2026-05-10
- QA status: `warning`
- Publish status: `draft`
- Public copy state: `final`

## Tour Identity Confirmation
- Requested command: `WPS:GENERATE_CONTENT`
- Actual package folder: `content-system/tours/cinque-terre-full-day-tour-from-milan-riomaggiore-manarola/`
- Canonical tour title: Cinque Terre Full-Day Tour from Milan
- Product/reference code: `187808P109` (supplier); channel codes: viator `187808P82`, tripadvisor `33344981`
- Active brand: Milano Adventures
- Website URL status: missing (placeholder, non-blocking)
- TripAdvisor URL status: provided
- Viator URL status: provided (used as primary CTA)
- Report scope: generation

## Gate decision
Hard clarify gate **not** triggered. All previously flagged items were reclassified as non-blocking under the updated AGENTS.md auto-resolution rules:

| Previous "blocker" | New treatment |
|---|---|
| Truncated canonical title (`…`) | Auto-derived clean title; ellipsis stripped |
| Multiple product codes | Treated as channel-specific via `channel_product_codes` |
| Missing website URL | Viator used as primary CTA (highest-priority available booking URL) |
| Cancellation window unit unresolved | Excluded from public copy |
| Unlabeled numeric `15` | Ignored, recorded in source-facts only |
| Itinerary scope (2 vs 5 villages) | Picked broader 5-village scope |
| Missing wheelchair accessibility | Omitted from public copy; warning only |

## Checks

### Package / file checks
- ✅ all 9 required files exist: `source-facts.md`, `brief.md`, `keywords.md`, `blog-post.md`, `faq.md`, `meta.json`, `internal-links.md`, `automation-notes.md`, `qa-report.md`
- ✅ folder name is valid kebab-case

### Metadata checks
- ✅ `meta.json` is valid JSON
- ✅ required schema fields present
- ✅ `slug` and `public_slug` match required regex
- ✅ `publish_status` ∈ allowed enum (`draft`)
- ✅ `qa_status` is `warning` (non-blocking issues exist)
- ✅ `public_copy_state` is `final`
- ✅ `cta_primary_link` is a real URL (Viator)

### Source-facts checks
- ✅ provenance matrix present
- ✅ all rows use allowed Status values
- ✅ "missing critical inputs" and "conflicts detected" rows present
- ✅ UNESCO claim sourced to supplier description before appearing in `blog-post.md`

### Public article checks
- ✅ exactly one H1 in `blog-post.md`
- ✅ no admin/SEO labels in public article body
- ✅ active brand "Milano Adventures" appears in the public article
- ✅ length within target (≈500–700 words)
- ✅ CTA in the first half + strong CTA at the end
- ✅ "Who this tour is best for" section present
- ✅ "What to know before booking" section present

### Link checks
- ✅ real Viator and TripAdvisor URLs preserved and used
- ✅ Viator used as primary CTA (channel = `viator`)
- ✅ TripAdvisor used only as secondary trust / alternate booking
- ⚠️ `website_link` is a placeholder — non-blocking warning (Viator fallback in use)

### Review / social proof checks
- ✅ no review/rating claims (no source data was provided)

### Conversion checklist (final mode)
- ✅ primary CTA present and uses real URL
- ✅ OTA links positioned as primary (Viator) + secondary (TripAdvisor) per fallback rule
- ✅ booking-confidence details included (duration, meeting point, languages, group cap, days)

## Warnings (non-blocking)
1. Website booking URL missing — switch primary CTA to website when supplied; current CTA channel is Viator.
2. Wheelchair accessibility status not supplied — omitted from public copy.
3. Cancellation window unit unresolved — public copy avoids cancellation specifics and points to the booking page.
4. Numeric policy value `15` and date `May 1, 2026` unlabeled — omitted from public copy.

## Recommended fixes (optional, none blocking)
1. Add a direct website booking URL when available; demote Viator to secondary.
2. Confirm cancellation window unit for the booking page.
3. Confirm wheelchair accessibility and add a one-line accessibility note when known.

## Final status
- publish_status: `draft`
- qa_status: `warning`
- public_copy_state: `final`
- intake_questions_resolved: `true`
- blocker summary: none
