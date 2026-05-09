# WebPublisherSystem Workflow

## End-to-end process
1. User provides tour data.
2. Command is detected (`WPS:*`).
3. Source facts are extracted to `source-facts.md`.
4. Provenance matrix is created and normalized.
5. Clarify gate runs (conflicts + missing critical inputs).
6. If blocking issues exist, stop before public copy.
7. If no blockers (or provisional mode explicitly approved), generate package content.
8. Generate/update `qa-report.md`.
9. Human review resolves open clarifications.
10. Publish workflow (`WPS:PUBLISH_BLOG`) validates package.
11. Server sync occurs.
12. Live verification (`WPS:LIVE_VERIFY`) checks archive + single post.
13. Only then may status become `published`.

## Hard gate behavior
- `clarifications_needed` non-empty => block `blog-post.md` final generation unless user explicitly approves provisional mode.
- Missing website booking URL => conversion blocker unless explicitly waived.
- Missing OTA URLs => warnings, not blockers.

## Status transition guardrails
- `generation_phase_completed` true only after required package files exist.
- `clarify_phase_required` true whenever blocking ambiguity exists.
- `clarify_phase_completed` true only after user resolves/waives blockers.
- `publish_phase_completed` true only after publish workflow is completed.
- `live_verification_completed` true only after live checks succeed.
- `publish_status` must not be `published` when `live_verification_completed` is false.

## Generation vs publishing vs live verification
- **Generation:** creates package assets and QA state.
- **Publishing:** validates package and prepares/sync states.
- **Live verification:** confirms real front-end availability.
