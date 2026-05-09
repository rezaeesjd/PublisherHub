# Command Reference

## WPS:GENERATE_CONTENT
- **Purpose:** Generate or update one tour content package.
- **Should do:** Extract source facts first, build provenance matrix, run clarify gate, generate all required files when allowed.
- **Must not do:** Claim published/live verification; bypass blocking clarification gate.
- **Allowed file changes:** package folder files plus templates/checklists if explicitly requested.
- **Final expected status:** `publish_status` usually `draft` or `ready_for_review` (only when no blockers).

## WPS:CLARIFY
- **Purpose:** Resolve blocking ambiguities and missing critical inputs.
- **Should do:** Record ambiguity list in `source-facts.md`, `meta.json.clarifications_needed`, and `qa-report.md`.
- **Must not do:** Generate final public copy while blockers remain (unless provisional mode approved).
- **Allowed file changes:** `source-facts.md`, `meta.json`, `qa-report.md`.
- **Final expected status:** `qa_status: needs_clarification` until resolved.

## WPS:PUBLISH_BLOG
- **Purpose:** Validate package for publishing readiness.
- **Should do:** Check files, metadata, links, source-facts integrity, content cleanliness, publish-path status.
- **Must not do:** Claim live publishing without verification.
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
