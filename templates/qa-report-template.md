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
- [ ] **[machine]** Canonical commerce keys present (`product_reference_code`, `channel_product_codes`, `website_link`, `cta_primary_link`)
- [ ] **[machine]** No deprecated key aliases in new package output (`product_code`, `channel_codes`, `website_url`)
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
- [ ] **[machine]** clarify markers present and consistent: `clarification_questions_presented`, `clarification_questions_presented_at`, `clarification_mode_selected`

## Clarification Interaction Log

- Questions presented? (yes/no):
- Presented at (YYYY-MM-DD):
- Mode selected: resolve / holding_notice / provisional / unknown
- Clarify pass: Pass A (clarify-only) / Pass B (resume)

| Field | Raw value | Question presented to user |
|---|---|---|
|  |  |  |

## Link Provenance Checklist

| Link type | Value | Source | Status | Blocking? |
|---|---|---|---|---|
| Website booking URL |  |  | provided / missing / placeholder | only when **no** booking URL of any channel exists (otherwise warning, not blocker) |
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
- [ ] **[machine]** Missing website URL with at least one OTA URL present is recorded as a `meta.json.warnings[]` entry (not a `conversion_blockers[]` entry) and the OTA URL drives `cta_primary_link`
- [ ] **[machine]** Missing website URL with **zero** booking URLs of any channel is recorded as a `conversion_blockers[]` entry and triggers the hard clarify gate

- [ ] **[machine]** `direct_booking_followup_required == true` when website URL is missing and any OTA URL is used as fallback
- [ ] **[manual]** follow-up action to replace OTA primary CTA with website CTA is recorded
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

## SEO Scorecard

Hand-fillable scorecard of on-page SEO signals. Where the `platform/qa-rules.php` runner enforces the check, copy the runner's verdict into the **Verdict** column verbatim (`pass` / `warn` / `fail`). Manual rows record reviewer judgment.

| # | Check | Value (measured) | Target | Verdict |
|---|---|---|---|---|
| A | `meta.page_title` length | `{{N}} chars` | 50–60 | pass / warn / fail |
| B | `meta.meta_description` length | `{{N}} chars` | 140–160 | pass / warn / fail |
| C | `meta.public_slug` length & kebab-case | `{{N}} chars` | ≤ 50, kebab, no stop-word bloat | pass / warn / fail |
| D | Single H1 in `blog-post.md` | `{{N}} H1` | exactly 1 | pass / warn / fail |
| E | H1 ↔ `page_title` similarity | `{{%}}` | ≥ 60% | pass / warn / fail |
| F | Primary keyword in `page_title` prefix | yes / no | yes | pass / warn / fail |
| G | Primary keyword in H1 | yes / no | yes | pass / warn / fail |
| H | Brand in `blog-post.md` | yes / no | yes | pass / warn / fail |
| I | Cluster cannibalization — `primary_keyword` collides with sibling | yes / no | no | pass / warn / fail |
| J | Cluster cannibalization — `page_title` ≥ 75% similar to sibling | yes / no | no | pass / warn / fail |
| K | Hero image set + alt text | yes / no / n/a | yes when images/ exists | pass / warn / fail |
| L | Internal links: hub link + sibling cluster link | yes / no | both | pass / warn / fail (manual) |
| M | Word count (final mode) | `{{N}} words` | 500–900 | pass / warn / fail (manual) |
| N | Retired `-vN` variant slug | yes / no | no | pass / fail |
| O | Primary keyword in first 100 words of `blog-post.md` | yes / no | yes | pass / warn |
| P | Primary keyword in ≥ 1 H2 of `blog-post.md` | yes / no | yes | pass / warn |
| Q | Primary keyword in last 200 words (conclusion) | yes / no | yes | pass / warn |
| R | Long-tail keyword coverage from `keywords.md` | `{{X}}/{{Y}}` | > 50% present | pass / warn |
| S | `public_slug` stop-word hits | none / list | none | pass / warn |
| T | `meta.canonical_url` override (optional) — well-formed + matches `public_slug` | computed / override ok / mismatch / malformed | n/a unless overridden | pass / warn |
| U | FAQPage JSON-LD ready (`faq.md` ≥ 3 Q&A) | `{{N}} pairs` | ≥ 3 (final) | pass / warn |
| V | TouristTrip/Product JSON-LD fields present | missing list / none | name/description/offers.price (image optional) | pass / warn |
| W | `internal-links.md` cross-funnel coverage | stages count | ≥ 2 stages (BOFU/MOFU/TOFU/FAQ) | pass / warn |
| X | `internal-links.md` anchor-text variety | duplicates / none | none (final) | pass / warn |
| Y | H2/H3 hierarchy + no duplicates | ok / broken / dupes | ok | pass / warn |
| Z | Hero image (optional) — alt + descriptive filename when set | computed / set / missing / generic | required only when `images/` exists | pass / warn |

## Issues Found

Use the structured table below. Every row must classify the issue using one of the standard `WPS:PROCESS_QA` issue types so generation QA reports stay lint-comparable to process QA reports.

Allowed values for **Issue type**:
- `system_instruction_gap` — a rule in `AGENTS.md` / `COMMANDS.md` / `WORKFLOW.md` is missing, ambiguous, or self-contradictory
- `workflow_enforcement_gap` — the rule exists but the agent or runner did not enforce it
- `user_input_gap` — the supplier intake is missing a field
- `generated_package_issue` — the generated package itself is wrong (file missing, wrong content, schema break)
- `front_end_rendering_risk` — the package is structurally valid but will render badly on the public site
- `publish_verification_gap` — claims about live state cannot be verified

Allowed values for **Severity**: `high | medium | low`.

| # | Issue type | Severity | Owner | Blocking? | Evidence | Recommended action |
|---|---|---|---|---|---|---|
| 1 |  |  |  |  |  |  |

## Template Traceability

- Templates used:
  - source-facts template path:
  - holding-notice or public-blog template path:
  - deferred stub template path:
  - qa-report template path:

## Recommended Fixes

1.

## Final Status

- publish_status:
- qa_status:
- public_copy_state:
- intake_questions_resolved:
- blocker summary:


## Template Traceability & Decision Evidence

- [ ] **[manual]** `automation-notes.md` lists all template paths used in this run
- [ ] **[manual]** `meta.json.clarify_decisions[]` entries exist for each ambiguity handled
- [ ] **[manual]** conversion follow-up task exists when OTA fallback is primary CTA
