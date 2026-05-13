# WebPublisherSystem Workflow

## End-to-end process

1. User provides tour data (free text or structured intake; see `templates/intake-form-template.md`).
2. Command is detected (`WPS:*`).
2a. **Multi-variant routing check.** Before any file is created, the agent looks up the canonical tour title against existing folders under `content-system/tours/`. If a base package for this tour already exists and is **approved** (`publish_status` ∈ `ready_for_review` / `ready_for_sync` / `needs_live_verification` / `published`), the agent must create a **new variant package** at `<base-slug>-v<N>/` per the multi-variant rule in `AGENTS.md` and set `variant_of` / `variant_index` / `variant_angle` in the new `meta.json`. If the base package is still in `publish_status: draft` or `needs_fix`, the agent continues to refine it in place — draft re-runs do **not** fork a variant. Overwriting an approved base package is forbidden under `WPS:GENERATE_CONTENT`; in-place rewrites of approved packages must be routed through `WPS:FIX_PACKAGE`.
3. Source facts are extracted to `source-facts.md`.
4. Provenance matrix is created and normalized.
5. Conflict + missing-input detection runs (the clarify gate).
6. **If any blocking clarification exists:** the agent enters a strict two-pass clarify handshake.
   - **Pass A (clarify-only pass):** write/update only `source-facts.md`, `meta.json`, `qa-report.md`; present questions; stop.
   - **Pass B (resume pass):** continue only after user answers blockers, or explicitly approves provisional mode, or confirms holding-notice mode.
   The user picks one of:
   - **Resolve** — answer in chat → blockers removed → continue to step 7.
   - **Holding notice** — agent writes a minimal holding-notice `blog-post.md`, sets `public_copy_state: holding_notice`, and stops.
   - **Provisional mode** — explicit user authorization → agent generates a draft, sets `public_copy_state: provisional`, and adds `provisional_mode: true`.
7. Generate package files according to mode:
   - **Final mode (no blockers):** generate all 9 required files with full content.
   - **Clarify mode (blocking clarifications open):** generate `source-facts.md`, `meta.json`, `qa-report.md`, `blog-post.md` (holding notice), and create blocked-state stubs for `brief.md`, `keywords.md`, `faq.md`, `internal-links.md`, `automation-notes.md` with a one-line deferred marker.
   - **Hard fail condition:** if any of the 9 required files is missing in clarify mode, generation is incomplete and must not be reported as structurally ready.
8. Generate/update `qa-report.md` and run the QA checklist.
9. **Automatic system/process QA pass (always-on for generation) — completion gate:**
   - run a `WPS:PROCESS_QA`-equivalent pass automatically (no extra user command)
   - **[hard gate]** write `content-system/system-qa/reports/<YYYY-MM-DD>-<slug>-process-qa.md` — generation is **not complete** until this file exists with a non-empty status triad block (see § Process report status triad below)
   - append system-level findings (or `none found`) to `content-system/system-qa/SYSTEM-QA-BACKLOG.md`
   - **[hard gate]** link the process report path in package `automation-notes.md` when `public_copy_state` is `final` or `provisional` — the `generation_phase_completed` flag must not be set to `true` until this linkage is present
   - if `public_copy_state` is `holding_notice`, keep `automation-notes.md` as the required deferred stub and place the process-report reference in `qa-report.md` instead
   - the process report must include a status triad block in this exact format:
     ```
     - Generation: complete | incomplete
     - Publish: not yet verified | verified | needs_fix
     - Live verification: not yet verified | verified
     ```
10. Open or update the PR. PR description must mirror both `qa-report.md` and process-QA findings.
11. Human review resolves any open clarifications and any QA findings.
12. Publish workflow (`WPS:PUBLISH_BLOG`) validates package.
13. Server sync occurs.
14. Live verification (`WPS:LIVE_VERIFY`) checks archive + single post.
15. Only then may status become `published`.

## Hard gate behavior (must-ask-first)

- `meta.clarifications_needed` non-empty with any `"blocking": true` entry → final `blog-post.md` is **forbidden** unless the user explicitly authorizes provisional mode in chat. Implicit precedent (e.g., other tour folders) is **not** authorization.
- The agent must invoke `AskUserQuestion` (or end the chat with a clearly labeled question batch) before generating any public copy.
- `meta.json` must include `clarification_questions_presented: true/false`, `clarification_questions_presented_at` (YYYY-MM-DD), and `clarification_mode_selected` (`resolve`, `holding_notice`, `provisional`, `unknown`).
- When blocking clarifications exist, these three clarify interaction markers are mandatory and must be populated before PR creation.
- Allowed states under the hard gate: **resolve**, **holding notice**, or **explicit provisional mode**. Default if the user does not pick: **holding notice**.
- Missing website booking URL **with at least one OTA booking URL provided** → not a blocker. Record as `meta.json.warnings[]`; set `cta_primary_link` to the highest-priority OTA URL (website → Viator → TripAdvisor → GetYourGuide → other); keep `website_link` as `{{WebsiteLink}}`. Do **not** populate `conversion_blockers[]` for this case.
- Missing website booking URL **and** no OTA booking URL of any channel → real conversion blocker. Append to `conversion_blockers[]` and `clarifications_needed[*].blocking=true` and force the hard clarify gate.
- Canonical title conflicted/truncated → set `canonical_title_status: unconfirmed` and keep `can_generate_public_copy: false` until resolved or provisional mode is explicitly approved.
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
- `can_generate_public_copy` true only when blocking clarifications are empty OR provisional mode is explicitly approved.
- `publish_phase_completed` true only after `WPS:PUBLISH_BLOG` finishes its checks.
- `live_verification_completed` true only after `WPS:LIVE_VERIFY` confirms archive + single post (informational marker).
- `publish_status` can be `published` without requiring `live_verification_completed == true`.
- `public_copy_state` transitions: `not_started` → (`holding_notice` | `provisional` | `final`). `holding_notice` and `provisional` can transition to `final` only via a re-run of `WPS:GENERATE_CONTENT` after blockers are resolved.

## Generation vs publishing vs live verification

- **Generation:** creates package assets and QA state.
- **Publishing:** validates package and prepares/sync states.
- **Live verification:** confirms real front-end availability.

## Exemplar compliance

- Tour folders inside `content-system/tours/` are reference structures, not blanket permission to bypass current rules.
- A tour folder that predates a current rule must contain `EXEMPLAR_NOTES.md` at its root listing the rules it does not satisfy and stating that it is not a model.
- Agents must not cite an unmarked tour folder as precedent for bypassing a hard gate.

## Link + product-code provenance guards

- Website URL is the *preferred* primary CTA when supplied. When missing but an OTA URL exists, fall back to the OTA per the auto-resolution table; record as a warning, **not** a `conversion_blockers[]` entry. When missing **and** no OTA URL exists, append `conversion_blockers[]` and force the hard clarify gate.
- Never overwrite a real provided URL with a placeholder token.
- Keep product code separation explicit: `product_reference_code` is primary, `channel_product_codes` holds channel IDs (e.g., Viator/TripAdvisor).
- Deprecated aliases (`product_code`, `channel_codes`, `website_url`) must not be used in newly generated packages.
- If two product codes conflict and mapping is unclear, mark `conflicted` in provenance and block final copy.

## Clarify-mode minimum package contract (machine-checkable)

When blockers are open, a package is considered structurally complete only if the following files exist and are non-empty:

- `source-facts.md`
- `meta.json`
- `qa-report.md`
- `blog-post.md` (holding notice)
- `brief.md` (deferred stub)
- `keywords.md` (deferred stub)
- `faq.md` (deferred stub)
- `internal-links.md` (deferred stub)
- `automation-notes.md` (deferred stub)

Deferred stub format must be exactly one heading plus one line:

```md
# Deferred (Clarification Required)
This file is intentionally deferred until blocking clarifications are resolved.
```


## Direct-booking follow-up (required when OTA fallback is primary)

- When `website_link` is missing and `cta_primary_link` uses an OTA URL, set `direct_booking_followup_required: true` in `meta.json`.
- Add one actionable follow-up in `qa-report.md` to replace OTA primary CTA with direct website CTA once available.
- Record rationale in `meta.json.warnings[]` and in the clarify decision ledger.


## Clarify decision ledger (machine-readable)

- For every ambiguity detected (blocking or non-blocking), populate `meta.json.clarify_decisions[]` with: `field`, `raw_value`, `blocking`, `decision`, `reason`, and optional `resolved_value`.
- Allowed `decision` values: `auto_resolved`, `asked_user`, `blocked`, `resolved`.
