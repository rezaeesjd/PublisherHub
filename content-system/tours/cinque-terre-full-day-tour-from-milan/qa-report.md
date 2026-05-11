# QA Report

- Date: 2026-05-11
- Command: WPS:GENERATE_CONTENT
- Package: `content-system/tours/cinque-terre-full-day-tour-from-milan`

## Checks

1. Required 9 files present: **PASS**
2. `meta.json` valid JSON: **PASS**
3. Canonical title cleaned from truncation: **PASS**
4. Real Viator and TripAdvisor links preserved: **PASS**
5. Website booking link provided: **WARNING** (missing, placeholder retained)
6. Primary CTA available: **PASS** (Viator fallback)
7. Blocking clarifications detected: **PASS** (none)
8. Cancellation numeric unit clarity: **WARNING** (raw value `15` unclear; excluded from specific public promise)
9. Brand mention in blog post: **PASS**
10. Publish status honesty: **PASS** (`draft`)

## QA Summary
- `qa_status`: **warning**
- `publish_status`: **draft**
- `public_copy_state`: **final**

## Follow-ups
- Add direct website booking URL and switch primary CTA to website when available.
