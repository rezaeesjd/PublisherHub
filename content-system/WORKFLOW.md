# WebPublisherSystem Workflow

## End-to-end process

1. User provides tour data (free text or structured intake; see `templates/intake-form-template.md`).
2. Command is detected (`WPS:*`).
3. Source facts are extracted to `source-facts.md`.
4. Provenance matrix is created and normalized.
5. Conflict + missing-input detection runs (the clarify gate).
6. **If any blocking clarification exists:** the agent presents the questions to the user via `AskUserQuestion` and stops before public copy. The user picks one of:
   - **Resolve** — answer in chat → blockers removed → continue to step 7.
   - **Holding notice** — agent writes a minimal holding-notice `blog-post.md`, sets `public_copy_state: holding_notice`, and stops.
   - **Provisional mode** — explicit user authorization → agent generates a draft, sets `public_copy_state: provisional`, and adds `provisional_mode: true`.
7. Generate the remaining package files (`brief.md`, `keywords.md`, `blog-post.md` (final or holding notice), `faq.md`, `internal-links.md`, `automation-notes.md`, `meta.json`).
8. Generate/update `qa-report.md` and run the QA checklist.
9. Open or update the PR. PR description must mirror `qa-report.md` blockers and warnings.
10. Human review resolves any open clarifications and any QA findings.
11. Publish workflow (`WPS:PUBLISH_BLOG`) validates package.
12. Server sync occurs.
13. Live verification (`WPS:LIVE_VERIFY`) checks archive + single post.
14. Only then may status become `published`.

## Hard gate behavior (must-ask-first)

- `meta.clarifications_needed` non-empty with any `"blocking": true` entry → final `blog-post.md` is **forbidden** unless the user explicitly authorizes provisional mode in chat. Implicit precedent (e.g., other tour folders) is **not** authorization.
- The agent must invoke `AskUserQuestion` (or end the chat with a clearly labeled question batch) before generating any public copy.
- Allowed states under the hard gate: **resolve**, **holding notice**, or **explicit provisional mode**. Default if the user does not pick: **holding notice**.
- Missing website booking URL → `conversion_blockers[]` entry + blocking clarification unless explicitly waived.
- Missing OTA URLs → warnings, not blockers.
- Cancellation window with no unit → blocking clarification (typed numeric field with unresolved unit).

## Provenance-to-claim binding

- Every assertive sentence in `blog-post.md` (and in `faq.md`, `keywords.md`, `internal-links.md` where applicable) must trace to a row in the provenance matrix in `source-facts.md`.
- Marketing-flavored facts must be promoted to a `confirmed (User input — product description)` row before they appear in public copy.
- Inferred facts (e.g., "end point same as starting point" derived from address equality) must be `inferred`, not `confirmed`.

## Holding-notice mode

- Use `templates/holding-notice-template.md`.
- ≤150 words.
- One H1 with the canonical (or near-canonical) tour title.
- Brand mentioned once.
- No claims dependent on unresolved blocking fields.
- OTA fallback links only when the URLs are real.
- `meta.json` sets `public_copy_state: holding_notice`, `qa_status: needs_clarification`, `intake_questions_resolved: false`.

## Status transition guardrails

- `generation_phase_completed` true only after all 9 required package files exist with non-empty content.
- `clarify_phase_required` true whenever any blocking ambiguity exists.
- `clarify_phase_completed` true only after the user resolves blockers OR explicitly authorizes provisional mode.
- `intake_questions_resolved` true only after every blocking-required intake field is answered (or waived in provisional mode).
- `publish_phase_completed` true only after `WPS:PUBLISH_BLOG` finishes its checks.
- `live_verification_completed` true only after `WPS:LIVE_VERIFY` confirms archive + single post.
- `publish_status` must not be `published` while `live_verification_completed == false`.
- `public_copy_state` transitions: `not_started` → (`holding_notice` | `provisional` | `final`). `holding_notice` and `provisional` can transition to `final` only via a re-run of `WPS:GENERATE_CONTENT` after blockers are resolved.

## Generation vs publishing vs live verification

- **Generation:** creates package assets and QA state.
- **Publishing:** validates package and prepares/sync states.
- **Live verification:** confirms real front-end availability.

## Exemplar compliance

- Tour folders inside `content-system/tours/` are reference structures, not blanket permission to bypass current rules.
- A tour folder that predates a current rule must contain `EXEMPLAR_NOTES.md` at its root listing the rules it does not satisfy and stating that it is not a model.
- Agents must not cite an unmarked tour folder as precedent for bypassing a hard gate.
