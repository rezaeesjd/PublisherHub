# QA Report

- Date: 2026-05-13
- Package: `cinque-terre-full-day-tour-from-milan`
- Command: `WPS:GENERATE_CONTENT`

## Required files check
- PASS: All 9 required package files are present.

## Generation checks
- PASS: `source-facts.md` exists and includes required provenance matrix rows.
- PASS: Public copy is active in `blog-post.md` (not holding notice, not disabled).
- PASS: Brand mention present in public copy (Milano Adventures).
- PASS: Website booking URL used as primary CTA.
- PASS: Viator/TripAdvisor links retained as secondary trust options.
- PASS: Funnel assets include strategy, keywords, FAQ, internal links, and automation notes.

## Warnings
- WARNING: Cancellation field remains unit-ambiguous in source (`15`); no precise cancellation promise is made in public copy.
- WARNING: Accessibility details remain missing in source data.

## Status
- qa_status: `warning`
- publish_status: `ready_for_review`

## Next actions
1. Confirm cancellation unit (hours or days) if you want exact policy text in public article/FAQ.
2. Add wheelchair accessibility details if available.

## Process QA linkage
- `content-system/system-qa/reports/2026-05-13-cinque-terre-full-day-tour-from-milan-process-qa.md`
