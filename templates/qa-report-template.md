# QA Report — {{Canonical Tour Title}}

> Items marked **[machine]** should be filled from the `platform/qa-rules.php` runner output, not hand-marked. Items marked **[manual]** are reviewer judgment.

## Tour Identity Confirmation

- Command used:
- Package folder:
- Canonical tour title:
- Internal product/reference code:
- Channel-specific product codes (if conflicted):
- Active system brand:
- Website URL status:
- TripAdvisor URL status:
- Viator URL status:
- Assessment type: generation / publishing / live verification
- Public copy state: not_started / holding_notice / provisional / final

## File Checklist

- [ ] **[machine]** source-facts.md
- [ ] **[machine]** brief.md
- [ ] **[machine]** keywords.md
- [ ] **[machine]** blog-post.md _(holding notice ≤150 words OR final 500–900 words)_
- [ ] **[machine]** faq.md
- [ ] **[machine]** meta.json
- [ ] **[machine]** internal-links.md
- [ ] **[machine]** automation-notes.md
- [ ] **[machine]** qa-report.md

## Metadata Checklist

- [ ] **[machine]** JSON valid
- [ ] **[machine]** Required fields present
- [ ] **[machine]** Phase markers present (`generation_phase_completed`, `clarify_phase_required`, `clarify_phase_completed`, `publish_phase_completed`, `live_verification_completed`, `intake_questions_resolved`)
- [ ] **[machine]** `publish_status` ∈ allowed enum
- [ ] **[machine]** `qa_status` ∈ `{pending, passing, warning, needs_fix, needs_clarification}`
- [ ] **[machine]** `public_copy_state` ∈ `{not_started, holding_notice, provisional, final}`
- [ ] **[machine]** if blocking clarifications exist: `qa_status == needs_clarification` AND `public_copy_state ∈ {holding_notice, provisional}`

## Source-Facts Provenance Checklist

- [ ] **[machine]** Provenance matrix present
- [ ] **[machine]** All required rows present
- [ ] **[machine]** Statuses use allowed values
- [ ] **[manual]** Every assertive sentence in `blog-post.md` traces to a matrix row (provenance-to-claim binding)
- [ ] **[manual]** Marketing-flavored facts (UNESCO, "iconic", etc.) are matrix rows before they appear in public copy

## Clarification / Ambiguity Checklist

- [ ] **[machine]** blocking ambiguities listed in `meta.clarifications_needed`
- [ ] **[machine]** every blocking entry mirrored in `source-facts.md`
- [ ] **[manual]** intake questions presented to the user via `AskUserQuestion` (or labeled question batch) before public copy generation
- [ ] **[machine]** `intake_questions_resolved` is `true` only when no blocking entry remains (or provisional mode authorized)

## Link Provenance Checklist

| Link type | Value | Source | Status | Blocking? |
|---|---|---|---|---|
| Website booking URL |  |  | provided / missing / placeholder | yes (if missing or placeholder, unless waived) |
| TripAdvisor URL |  |  | provided / missing / placeholder | no |
| Viator URL |  |  | provided / missing / placeholder | no |

## Public Content Cleanliness Checklist

- [ ] **[machine]** Single Markdown H1
- [ ] **[machine]** No forbidden admin/SEO labels visible
- [ ] **[machine]** Brand "Milano Adventures" (or override) appears at least once in `blog-post.md`
- [ ] **[machine]** Word count appropriate for `public_copy_state`: holding_notice ≤150; final 500–900
- [ ] **[machine]** No malformed `{{...}}` tokens
- [ ] **[manual]** Raw supplier name does not leak into public copy

## Conversion Checklist (final mode only)

- [ ] **[machine]** Website URL used as primary CTA when provided
- [ ] **[machine]** Missing website URL classified as conversion blocker unless waived
- [ ] **[machine]** OTA links used as secondary trust/reference only (after primary CTA)
- [ ] **[machine]** Soft CTA in first half + strong CTA at end
- [ ] **[manual]** "Who this tour is best for" section present
- [ ] **[manual]** "What to know before booking" section present

## Conversion Checklist (holding-notice mode)

- [ ] **[machine]** No specific facts dependent on unresolved blocking clarifications (no pricing, no cancellation window, no departure days, no specific durations, no specific itinerary order)
- [ ] **[machine]** OTA fallback links are real URLs only

## Review / Social Proof Checklist

- [ ] **[manual]** No invented review claims
- [ ] **[machine]** If review claims appear, source row exists in provenance matrix
- [ ] **[manual]** If absent, omission reason documented

## Front-End Renderer Readiness

- [ ] **[machine]** `slug` and `public_slug` regex-valid for routing
- [ ] **[manual]** `blog-post.md` render-safe for public template
- [ ] **[machine]** FAQ separation intact

## Publish Path Status

- [ ] generation complete
- [ ] clarify required?
- [ ] clarify complete?
- [ ] publish phase complete?
- [ ] live verification complete?

## Issues Found

1.

## Recommended Fixes

1.

## Final Status

- publish_status:
- qa_status:
- public_copy_state:
- intake_questions_resolved:
- blocker summary:
