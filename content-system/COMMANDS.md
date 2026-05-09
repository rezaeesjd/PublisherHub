# Command Reference

## WPS:GENERATE_CONTENT

- **Purpose:** Generate or update one tour content package.
- **Should do:** Extract source facts first, build provenance matrix, run clarify gate, **invoke `WPS:CLARIFY` automatically** if blocking ambiguities are detected, generate remaining files only when allowed.
- **Must not do:** Claim published/live verification; bypass blocking clarification gate; generate final `blog-post.md` while blocking clarifications remain (unless the user has explicitly approved provisional mode in chat).
- **Allowed file changes:** package folder files plus templates/checklists if explicitly requested.
- **Final expected status (no blockers):** `publish_status: draft`, `qa_status: pending`, `public_copy_state: final`, `intake_questions_resolved: true`.
- **Final expected status (blockers, holding-notice mode):** `publish_status: draft`, `qa_status: needs_clarification`, `public_copy_state: holding_notice`, `intake_questions_resolved: false`.
- **Optional flag:** `--provisional` — user-authorized only. Skips the must-ask-first gate, sets `public_copy_state: provisional` and `provisional_mode: true`. Never default behavior.

## WPS:GENERATE_CONTENT_FROM_INTAKE

- **Purpose:** Generate a tour content package from a structured intake JSON validated against `structures/intake-form.schema.json`.
- **Should do:** Validate intake, write source-facts and meta.json directly from the structured fields, then run the same clarify gate as `WPS:GENERATE_CONTENT`. If validation passes and no blocking ambiguity remains, generate full final copy without a clarification round.
- **Must not do:** Generate copy from a partial intake without running the clarify gate.
- **Allowed file changes:** package folder files.
- **Final expected status:** Same as `WPS:GENERATE_CONTENT`.

## WPS:CLARIFY

- **Purpose:** Resolve blocking ambiguities and missing critical inputs.
- **Should do:** Record ambiguity list in `source-facts.md`, `meta.json.clarifications_needed`, and `qa-report.md`. Present the questions to the user via `AskUserQuestion` (or end the chat reply with a clearly labeled question batch) and stop further generation.
- **Must not do:** Generate final public copy while blockers remain (unless provisional mode explicitly approved in chat). Make implicit assumptions about ambiguous fields.
- **Allowed file changes:** `source-facts.md`, `meta.json`, `qa-report.md`. May write a holding-notice `blog-post.md` per `templates/holding-notice-template.md`.
- **Final expected status:** `qa_status: needs_clarification`, `public_copy_state: holding_notice` (or `provisional` if explicitly authorized).
- **Auto-trigger:** `WPS:CLARIFY` is automatic inside `WPS:GENERATE_CONTENT` whenever a blocking ambiguity is detected.

## WPS:PUBLISH_BLOG

- **Purpose:** Validate package for publishing readiness.
- **Should do:** Check files, metadata, links, source-facts integrity, content cleanliness, publish-path status. Verify `public_copy_state == "final"`.
- **Must not do:** Claim live publishing without verification. Promote a holding-notice or provisional package to publish.
- **Allowed file changes:** QA and metadata adjustments required for publish prep.
- **Final expected status:** `ready_for_sync`, `needs_live_verification`, or `needs_fix`.

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

## WPS:GENERATION_PROCESS_QA

- **Artifact requirement:** Write/update `content-system/generation-process-qa-report.md` as the canonical report used by `WPS:IMPLEMENT_GENERATION_PROCESS_IMPROVEMENTS`.

- **Purpose:** Review the full process from raw input → generated content package (no publishing).
- **Should do:** Inspect raw input, command, AGENTS.md/templates/workflow, generated package, PR diff. Produce the structured report defined in `AGENTS.md`'s PROCESS_QA section.
- **Must not do:** Modify any file. Rewrite the package. Create commits.
- **Allowed file changes:** none.
- **Final expected status:** Structured process-only review with scores, failure analysis, and prioritized improvements.

## WPS:IMPLEMENT_GENERATION_PROCESS_IMPROVEMENTS

- **Purpose:** Apply system improvements identified by the most recent `WPS:GENERATION_PROCESS_QA` report.
- **Should do:** Modify `AGENTS.md`, `COMMANDS.md`, `WORKFLOW.md`, `QA-CHECKLIST.md`, `templates/`, `structures/`, and schema files only.
- **Must not do:** Modify content under `content-system/tours/` (except adding non-content example files such as `EXEMPLAR_NOTES.md` when explicitly recommended by the QA report).
- **Allowed file changes:** system/workflow/template/schema/documentation files.
- **Final expected status:** Clearer enforceable workflow for future runs.

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

## WPS:LIVE_VERIFY

- **Purpose:** Verify live archive and single-post rendering on deployed environment.
- **Should do:** Confirm actual accessible live pages.
- **Must not do:** Generate or rewrite content packages.
- **Allowed file changes:** QA notes / status updates reflecting verification.
- **Final expected status:** `live_verification_completed: true` only when confirmed; only then can `publish_status` be `published`.
