# QA Checklist (Package + Publish Readiness + Process QA)

This checklist mirrors what the QA runner (`platform/qa-rules.php`) verifies. Items marked **[machine]** are intended to be enforced by the runner; items marked **[manual]** are reviewer judgment. The agent should not hand-mark **[machine]** items as passing — attach the runner output instead.

---

## Tour Identity Confirmation (required first section)

- [ ] requested command
- [ ] actual package folder
- [ ] canonical tour title
- [ ] product/reference code (and any channel codes if conflicted)
- [ ] active brand
- [ ] website URL status
- [ ] TripAdvisor URL status
- [ ] Viator URL status
- [ ] package created/updated date (if known)
- [ ] report scope: generation / publishing / live verification

## Clarify Gate Enforcement

- [ ] **[machine]** conflict and missing-input detection completed before any public copy was written
- [ ] **[machine]** `meta.clarifications_needed` populated (or empty if none detected)
- [ ] **[machine]** if any `clarifications_needed[*].blocking == true`, then `qa_status == "needs_clarification"` and `public_copy_state` is `holding_notice` or `provisional`
- [ ] **[machine]** intake_questions_resolved is `true` only when no blocking clarifications remain (or provisional mode was explicitly approved)
- [ ] **[machine]** missing website URL surfaces as a `conversion_blockers` entry
- [ ] **[manual]** intake questions were presented to the user via `AskUserQuestion` (or a clearly labeled question batch) before public copy generation
- [ ] **[machine]** `clarification_questions_presented == true` when any blocking clarification exists
- [ ] **[machine]** `can_generate_public_copy == false` when any blocking clarification exists and provisional mode is not explicitly authorized

## File and Structure

- [ ] **[machine]** correct single tour folder used (no duplicate slug)
- [ ] **[machine]** all 9 required files exist
- [ ] **[machine]** if `qa_status == needs_clarification`, blocked-state stub files exist for `brief.md`, `keywords.md`, `faq.md`, `internal-links.md`, `automation-notes.md`
- [ ] **[machine]** `source-facts.md` exists and is non-empty
- [ ] **[machine]** `qa-report.md` exists and is non-empty

## Metadata and Phase Markers

- [ ] **[machine]** `meta.json` valid JSON
- [ ] **[machine]** required schema fields present
- [ ] **[machine]** no deprecated/alias key substitution for required fields (`product_code`, `channel_codes`, `website_url` are invalid substitutes)
- [ ] **[machine]** canonical commerce keys present and populated: `product_reference_code`, `channel_product_codes`, `website_link`, `cta_primary_link`
- [ ] **[machine]** `publish_status` ∈ allowed enum
- [ ] **[machine]** `qa_status` ∈ `{pending, passing, warning, needs_fix, needs_clarification}`
- [ ] **[machine]** `public_copy_state` ∈ `{not_started, holding_notice, provisional, final}`
- [ ] **[machine]** phase markers present: `generation_phase_completed`, `clarify_phase_required`, `clarify_phase_completed`, `publish_phase_completed`, `live_verification_completed`, `intake_questions_resolved`
- [ ] **[machine]** clarify interaction markers present: `clarification_questions_presented`, `clarification_questions_presented_at`, `clarification_mode_selected`
- [ ] **[machine]** `publish_status != "published"` while `live_verification_completed == false`

## Link Handling

- [ ] **[machine]** if a real website booking URL is provided, `website_link` and `cta_primary_link` use it (not `{{WebsiteLink}}`)
- [ ] **[machine]** if website URL is missing but at least one OTA URL (Viator/TripAdvisor/GYG) is provided, `cta_primary_link` uses the highest-priority OTA URL and `cta_primary_channel` is set; missing website URL is recorded as a non-blocking warning, **not** a `conversion_blockers[]` entry
- [ ] **[machine]** missing website URL **and** missing all OTA URLs is a `conversion_blockers[]` entry (zero possible CTA)
- [ ] **[machine]** if real TripAdvisor / Viator URLs are provided, they are used in `meta.json`
- [ ] **[machine]** placeholders (`{{WebsiteLink}}`, `{{TripAdvisorLink}}`, `{{ViatorLink}}`) only appear where the corresponding source field is missing
- [ ] **[machine]** `blog-post.md` does not contain a malformed `{{...}}` token

## Source-Facts Provenance

- [ ] **[machine]** provenance matrix table present in `source-facts.md`
- [ ] **[machine]** all rows use allowed `Status` values
- [ ] **[manual]** every assertive sentence in `blog-post.md` traces to a row in the provenance matrix (provenance-to-claim binding)
- [ ] **[manual]** marketing-flavored facts (UNESCO, "iconic", "world-famous", etc.) appear in the matrix before they appear in public copy
- [ ] **[machine]** cancellation policy row present
- [ ] **[machine]** review rating/count/text source rows present
- [ ] **[machine]** "missing critical inputs" and "conflicts detected" rows present

## Public Content Cleanliness

- [ ] **[machine]** exactly one Markdown H1 in `blog-post.md`
- [ ] **[machine]** no admin/SEO labels in `blog-post.md` (`Page Title`, `URL Slug`, `Meta Description`, `Primary Keyword`, `Funnel Stage`, `Internal Linking Suggestions`, `CTA Primary Link`, `QA Notes`, `Source Facts`)
- [ ] **[machine]** active brand (`Milano Adventures` by default) appears at least once in `blog-post.md`
- [ ] **[machine]** length: holding-notice mode → ≤150 words; final mode → 500–900 words (warning outside that range)
- [ ] **[manual]** raw supplier/operator name does not leak into public copy

## Conversion Checklist (final mode only)

- [ ] **[machine]** website URL used as primary CTA when provided
- [ ] **[machine]** missing website URL classified as conversion blocker unless waived
- [ ] **[machine]** OTA links used as secondary trust/reference only (appear after the primary CTA in `blog-post.md`)
- [ ] **[machine]** at least one CTA in the first half of the post; one strong CTA at the end
- [ ] **[manual]** "Who this tour is best for" section present
- [ ] **[manual]** "What to know before booking" section present

## Conversion Checklist (holding-notice mode)

- [ ] **[machine]** `blog-post.md` does not contain any specific facts dependent on unresolved blocking clarifications (no pricing, no cancellation window, no departure days, no specific durations, no specific itinerary order)
- [ ] **[machine]** OTA fallback links are real URLs only; placeholder OTA tokens are forbidden inside the holding notice

## Review / Social Proof Checklist

- [ ] **[manual]** no invented review counts or ratings
- [ ] **[machine]** if review claims appear in `blog-post.md`, the source row exists in the provenance matrix
- [ ] **[manual]** single reviews are not phrased as broad market proof

## Front-End Renderer Readiness

- [ ] **[machine]** `slug` and `public_slug` match the regex `^[a-z0-9][a-z0-9-]*[a-z0-9]$`
- [ ] **[manual]** `blog-post.md` renders cleanly in the public template (no broken Markdown)
- [ ] **[machine]** `faq.md` exists and is parseable as a Q&A list

## Publish Path Status

- [ ] generation complete?
- [ ] clarify required?
- [ ] clarify complete?
- [ ] publish phase complete?
- [ ] live verification complete?

## PROCESS_QA Behavior Constraints

- [ ] no file modifications
- [ ] no content rewriting
- [ ] no PR creation unless requested
- [ ] generation readiness separated from publish readiness
- [ ] missing user input separated from generation mistakes
- [ ] issues classified by type

## Issue Categories (PROCESS_QA)

- [ ] System instruction gap
- [ ] Workflow enforcement gap
- [ ] User input gap
- [ ] Generated package issue
- [ ] QA/reporting gap
- [ ] Front-end rendering risk
- [ ] Publish verification gap
- [ ] Goal/conversion gap

## Issues Found

1.

For every issue, include:
- Type: System instruction gap / Workflow enforcement gap / User input gap / Generated package issue / QA-reporting gap / Goal-conversion gap
- Severity: High / Medium / Low
- Owner: Agent / User input / System maintainer
- Next action: one concrete action sentence
- Blocking?: yes/no
- Root cause class: instruction gap / enforcement gap / user-input gap / schema gap

## Template & Provenance Traceability

- [ ] **[manual]** `automation-notes.md` records template file names used in this run
- [ ] **[manual]** clarification question batch is copied verbatim in QA report or linked reference

## Recommended Fixes

1.

## Final Status

- publish_status:
- qa_status:
- public_copy_state:
- intake_questions_resolved:
- blocker summary:


## End-user Readiness (Lead-Gen Outcome)

- [ ] **[manual]** opening paragraph communicates clear traveler outcome (not generic destination filler)
- [ ] **[manual]** primary CTA is explicit, low-friction, and placed before long explanatory sections
- [ ] **[manual]** objection-handling present (timing, transport, exertion level, cancellation confidence)
- [ ] **[manual]** OTA links positioned as trust/backup references, not the primary action
- [ ] **[manual]** copy supports direct booking intent and customer acquisition goals
