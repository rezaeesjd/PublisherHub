# QA Report

## Checks
- Required 9 files present: PASS
- Source facts extracted before copy: PASS
- Real OTA links preserved: PASS
- Primary CTA present: PASS (Viator fallback)
- Website booking URL available: WARNING (missing)
- Blocking clarifications: NONE
- Public/admin cleanliness: PASS
- Unsupported review claims: PASS (none made)

## Status verdict
- qa_status: warning
- publish_status: draft
- human_review_required: true

## Notes
- Missing direct website booking URL is non-blocking due to existing Viator/TripAdvisor URLs.
- Cancellation value `15` lacks unit; omitted from public copy by policy.
