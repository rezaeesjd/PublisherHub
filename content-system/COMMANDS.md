# Command Reference

## WPS:GENERATE_CONTENT

- **Purpose:** Generate one tour content package. The system is a multi-content publisher: each tour is expected to grow a cluster of variant packages over time.
- **Should do:** Extract source facts first, build provenance matrix, run clarify gate, **invoke `WPS:CLARIFY` automatically** if blocking ambiguities are detected, generate remaining files only when allowed.
- **Must do (automatic system QA — completion gate):** Run an internal `WPS:PROCESS_QA` pass automatically at the end of generation, without requiring a separate user prompt/command, write a per-run process report under `content-system/system-qa/reports/<YYYY-MM-DD>-<slug>-process-qa.md` (required, non-empty, includes status triad block), and link that report path in `automation-notes.md` (or in `qa-report.md` for holding-notice mode). Generation is not complete and `generation_phase_completed` must not be set to `true` until both the report file and its linkage exist.
- **Must do (multi-variant rule):** Before writing any file, check whether a base package for this canonical tour title already exists at `content-system/tours/<base-slug>/`. If that package is **approved** (`publish_status` ∈ `ready_for_review` / `published` / `published` / `published` — see `AGENTS.md` "Multi-variant rule (hard rule)") **do not overwrite** it — create a new variant package at `content-system/tours/<base-slug>-v<N>/` with `variant_of`, `variant_index`, `variant_role`, and a unique `public_slug` set in `meta.json`. A base package still in `publish_status: draft` or `needs_fix` is iterable in place — do **not** fork a variant for draft re-runs.
- **Must not do:** Claim published/live verification; bypass blocking clarification gate; generate final `blog-post.md` while blocking clarifications remain (unless the user has explicitly approved provisional mode in chat); overwrite an approved base package (route to `WPS:FIX_PACKAGE` if an in-place rewrite of an approved package is genuinely intended); modify system files (`AGENTS.md`, `COMMANDS.md`, `WORKFLOW.md`, `QA-CHECKLIST.md`, `templates/`, `structures/`, `meta.schema.json`, `platform/`) — system rule changes belong in a separate `WPS:IMPROVE_SYSTEM_WORKFLOW` PR.
- **Allowed file changes:** files under `content-system/tours/<slug>/` only (base or new variant). Templates/checklists may be touched only if the user explicitly requests it as part of the same task.
- **Allowed file changes (automatic QA exception):** In addition to `content-system/tours/<slug>/`, the run may create/update:
  - `content-system/system-qa/reports/<YYYY-MM-DD>-<slug>-process-qa.md`
  - `content-system/system-qa/SYSTEM-QA-BACKLOG.md` (append-only entries or explicit `none found` note)
- **Final expected status (no blockers):** `publish_status: draft`, `qa_status: pending`, `public_copy_state: final`, `intake_questions_resolved: true`.
- **Final expected status (blockers, holding-notice mode):** `publish_status: draft`, `qa_status: needs_clarification`, `public_copy_state: holding_notice`, `intake_questions_resolved: false`.
- **Optional flag:** `--provisional` — user-authorized only. Skips the must-ask-first gate, sets `public_copy_state: provisional` and `provisional_mode: true`. Never default behavior.

## WPS:GENERATE_CONTENT_FROM_INTAKE

- **Purpose:** Generate a tour content package from a structured intake JSON validated against `structures/intake-form.schema.json`.
- **Should do:** Validate intake, write source-facts and meta.json directly from the structured fields, then run the same clarify gate as `WPS:GENERATE_CONTENT`. If validation passes and no blocking ambiguity remains, generate full final copy without a clarification round.
- **Must do (automatic system QA — completion gate):** Run the same internal end-of-run `WPS:PROCESS_QA` pass used by `WPS:GENERATE_CONTENT`, write the per-run process report under `content-system/system-qa/reports/<YYYY-MM-DD>-<slug>-process-qa.md` (required, non-empty, includes status triad block), and link that report path in `automation-notes.md`. Generation is not complete until both the report file and its linkage exist.
- **Must not do:** Generate copy from a partial intake without running the clarify gate.
- **Allowed file changes:** package folder files plus automatic process-QA artifacts:
  - `content-system/system-qa/reports/<YYYY-MM-DD>-<slug>-process-qa.md`
  - append-only updates to `content-system/system-qa/SYSTEM-QA-BACKLOG.md`
- **Final expected status:** Same as `WPS:GENERATE_CONTENT`.

## WPS:CLARIFY

- **Purpose:** Resolve blocking ambiguities and missing critical inputs.
- **Should do:** Record ambiguity list in `source-facts.md`, `meta.json.clarifications_needed`, and `qa-report.md`. Present the questions to the user via `AskUserQuestion` (or end the chat reply with a clearly labeled question batch) and stop further generation.
- **Must not do:** Generate final public copy while blockers remain (unless provisional mode explicitly approved in chat). Make implicit assumptions about ambiguous fields.
- **Allowed file changes:** `source-facts.md`, `meta.json`, `qa-report.md`. May write a holding-notice `blog-post.md` per `templates/holding-notice-template.md`.
- **Enforcement mode:** first run behaves as `clarify-only` (Pass A): do not populate strategy/SEO files until user response or explicit mode selection.
- **Final expected status:** `qa_status: needs_clarification`, `public_copy_state: holding_notice` (or `provisional` if explicitly authorized).
- **Auto-trigger:** `WPS:CLARIFY` is automatic inside `WPS:GENERATE_CONTENT` whenever a blocking ambiguity is detected.

## WPS:PUBLISH_BLOG

- **Purpose:** Validate package for publishing readiness.
- **Should do:** Check files, metadata, links, source-facts integrity, content cleanliness, publish-path status. Verify `public_copy_state == "final"`.
- **Must not do:** Claim live publishing without verification. Promote a holding-notice or provisional package to publish.
- **Allowed file changes:** QA and metadata adjustments required for publish prep.
- **Final expected status:** `published`, `published`, or `needs_fix`.

## WPS:GENERATE_AND_PUBLISH

- **Purpose:** Sequentially run generation then publish validation.
- **Should do:** Report each phase separately with explicit completion states.
- **Must not do:** Skip clarify gate or overstate publish state.
- **Allowed file changes:** Generation + publish-prep artifacts.
- **Final expected status:** Depends on blockers and verification availability.

## WPS:PROCESS_QA

- **Purpose:** Process-only QA analysis.
- **Should do:** Audit compliance, classify issues, separate user-input gaps from generation mistakes.
- **Must not do:** Modify files, rewrite content, or create PR unless requested.
- **Allowed file changes:** none by default.
- **Final expected status:** Structured QA report with prioritized actions.
- **Canonical alias behavior:** `WPS:GENERATION_PROCESS_QA` is a specialized alias of this command for generation-only process audits.

## WPS:GENERATION_PROCESS_QA

- **Artifact requirement:** Prefer writing/updating `content-system/generation-process-qa-report.md` as the canonical report used by `WPS:IMPLEMENT_GENERATION_PROCESS_IMPROVEMENTS`. If the user explicitly requests a no-file-change review, return the full report in chat and do not write files.

- **Purpose:** Review the full process from raw input → generated content package (no publishing).
- **Should do:** Inspect raw input, command, AGENTS.md/templates/workflow, generated package, PR diff. Produce the structured report defined in `AGENTS.md`'s PROCESS_QA section.
- **Must not do:** Modify any file. Rewrite the package. Create commits.
- **Allowed file changes:** none.
- **Final expected status:** Structured process-only review with scores, failure analysis, and prioritized improvements.
- **Canonical routing rule:** Route internally through the same PROCESS_QA engine and force scope = `generation_only`.

## WPS:IMPLEMENT_GENERATION_PROCESS_IMPROVEMENTS

- **Purpose:** Apply system improvements identified by the most recent `WPS:GENERATION_PROCESS_QA` report.
- **Should do:** Modify `AGENTS.md`, `COMMANDS.md`, `WORKFLOW.md`, `QA-CHECKLIST.md`, `templates/`, `structures/`, and schema files only.
- **Should do (required):**
  - enforce clarify-mode minimum package contract
  - align template keys with `meta.schema.json`
  - improve conversion-blocker enforcement for missing website URL
  - standardize product code split (`product_reference_code` vs `channel_product_codes`)
  - add machine-checkable clarify interaction markers
- **Must not do:** Modify content under `content-system/tours/` (except adding non-content example files such as `EXEMPLAR_NOTES.md` when explicitly recommended by the QA report).
- **Allowed file changes:** system/workflow/template/schema/documentation files.
- **Final expected status:** Clearer enforceable workflow for future runs.

## WPS:CLARIFY_ONLY

- **Purpose:** Forced pass-A clarify handshake when blockers are known.
- **Should do:** Write/update only `source-facts.md`, `meta.json`, and `qa-report.md`; record `clarifications_needed`; set `can_generate_public_copy=false`; present question batch.
- **Must not do:** Generate final `blog-post.md`, or populate `brief.md`, `keywords.md`, `faq.md`, `internal-links.md`, `automation-notes.md` except deferred stubs when requested by package contract.
- **Allowed file changes:** `source-facts.md`, `meta.json`, `qa-report.md`, optional holding notice + deferred stubs.
- **Final expected status:** `qa_status: needs_clarification`, `public_copy_state: holding_notice|not_started`, `clarify_phase_completed: false`.

## WPS:FIX_PACKAGE

- **Purpose:** Repair one existing package.
- **Should do:** Targeted fixes only for identified issues.
- **Must not do:** System-wide redesign unrelated to package issue.
- **Allowed file changes:** files inside target package + directly related metadata/QA.
- **Final expected status:** improved QA state, not automatically published.

## WPS:IMPROVE_SYSTEM_WORKFLOW

- **Purpose:** Improve system docs/templates/checklists/workflow rules.
- **Should do:** Update AGENTS/templates/checklists/command docs/workflow docs.
- **Must not do:** Modify existing tour package content unless explicitly required for non-content examples.
- **Allowed file changes:** system/workflow/template/documentation files.
- **Final expected status:** clearer enforceable workflow for future runs.

## WPS:RELINK_CLUSTER

- **Purpose:** When a real direct website booking URL becomes available for a tour that already has one or more variants, propagate that URL across every package in the cluster in a single sweep.
- **Invocation:** `WPS:RELINK_CLUSTER <base-slug> <new-website-url> [--cta "<copy>"]`
- **Should do:**
  - locate every package whose `meta.json.slug == <base-slug>` or `meta.json.variant_of == <base-slug>`
  - update each package's `meta.json.website_link`, `cta_primary_link`, `cta_primary_channel: "website"`, and `cta_primary` (default copy: "Book on our website" unless `--cta` is supplied)
  - rewrite the booking links in each `blog-post.md` so the website URL replaces the prior OTA fallback while keeping OTA links as secondary trust references
  - record one entry in each package's `CHANGELOG.md` describing the relinking
  - leave `source-facts.md` provenance rows updated: the website-URL row moves from `missing` to `confirmed`
- **Must not do:** rewrite any other field (pricing, duration, departures, copy hooks, FAQs); add new variants; mark anything published.
- **Allowed file changes:** `meta.json`, `blog-post.md`, `source-facts.md`, `qa-report.md`, and `CHANGELOG.md` for every package in the cluster. No system file changes.
- **Final expected status:** every package in the cluster has `cta_primary_channel: "website"`, the website-URL row is `confirmed` in each `source-facts.md`, and the prior `meta.json.warnings[]` entry "website_link is a placeholder…" is removed from each.

## WPS:LIVE_VERIFY

- **Purpose:** Verify live archive and single-post rendering on deployed environment.
- **Should do:** Confirm actual accessible live pages.
- **Must not do:** Generate or rewrite content packages.
- **Allowed file changes:** QA notes / status updates reflecting verification.
- **Final expected status:** `publish_status: published` when package QA/publish checks pass; `live_verification_completed` remains optional verification telemetry.


## Direct-booking follow-up (required when OTA fallback is primary)

- When `website_link` is missing and `cta_primary_link` uses an OTA URL, set `direct_booking_followup_required: true` in `meta.json`.
- Add one actionable follow-up in `qa-report.md` to replace OTA primary CTA with direct website CTA once available.
- Record rationale in `meta.json.warnings[]` and in the clarify decision ledger.


## Clarify decision ledger (machine-readable)

- For every ambiguity detected (blocking or non-blocking), populate `meta.json.clarify_decisions[]` with: `field`, `raw_value`, `blocking`, `decision`, `reason`, and optional `resolved_value`.
- Allowed `decision` values: `auto_resolved`, `asked_user`, `blocked`, `resolved`.
