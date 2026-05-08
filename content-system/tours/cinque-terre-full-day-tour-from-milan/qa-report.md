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
- [x] Folder naming is valid kebab-case

## Source-facts checklist
- [x] Canonical title extracted
- [x] Brand handling stated
- [x] Meeting/end point extracted
- [x] Duration/start time extracted
- [x] Transport and itinerary details extracted
- [x] Inclusions/exclusions captured from provided text
- [x] OTA links preserved
- [x] Missing direct website booking link flagged
- [x] Ambiguities explicitly documented

## JSON validation checklist
- [x] meta.json is valid JSON
- [x] Required marketing fields present
- [x] publish_status set to draft after generation
- [x] human_review_required set true
- [x] qa_status set pending
- [x] clarifications_needed populated for ambiguous inputs

## Public content cleanliness checklist
- [x] Public H1 present
- [x] No admin-only labels exposed in blog-post.md
- [x] Traveler-facing structure followed

## Link handling checklist
- [x] Viator and TripAdvisor links preserved as secondary references
- [x] Website CTA kept primary (placeholder used because missing)
- [x] Placeholder usage flagged as not publish-ready

## Conversion checklist
- [x] Soft CTA in first half of article
- [x] Strong CTA near end
- [x] Booking-confidence details included
- [x] "Who this tour is best for" section included
- [x] "What to know before booking" section included

## Review/social proof checklist
- [x] No invented rating or review count claims
- [x] No unsupported "best/top-rated" language

## Source-facts-only checklist
- [x] No invented logistics or policy facts beyond source input
- [x] Seasonal ferry limitation accurately reflected

## Issues found
1. Blocking: conflicting product codes (`187808P82` vs `187808P109`).
2. Blocking: cancellation value `9` has no explicit unit.
3. Non-blocking: role of date "May 1, 2026" unclear.
4. Blocking for live booking readiness: missing direct website booking URL.

## Recommended fixes
- Confirm canonical product code.
- Confirm cancellation policy unit and policy wording.
- Confirm meaning of May 1, 2026 in pricing context.
- Add final website booking URL and replace `{{WebsiteLink}}`.

## Publish readiness status
- Current status: **needs_fix** for publish workflow due to blocking clarifications and missing website link.
- Generation workflow status: **complete** (all required files produced).
