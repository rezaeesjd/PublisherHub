# QA Report

- Date: 2026-05-12
- Package: `pizza-and-gelato-cooking-class-in-milan`
- Command: `WPS:GENERATE_CONTENT`

## Required files check
- PASS: All 9 required files present.

## Content and compliance checks
- PASS: `source-facts.md` created before public post drafting.
- PASS: Canonical title used verbatim from source.
- PASS: OTA links preserved as provided.
- PASS: At least one real booking permalink is present (Viator + TripAdvisor).
- PASS: Brand mention included in public copy.
- PASS: No unsupported review/rating claims.
- PASS: Allergy and accessibility disclosure included.
- WARNING: Schedule Start "03:00" almost certainly intended as 15:00 (3 PM).
- WARNING: Cancellation field value lacks unit; specifics omitted from public claims.

## Status
- qa_status: `warning`
- publish_status: `ready_for_review`

## Action items
1. Confirm class start time (likely 15:00 / 3 PM).
2. Confirm cancellation policy unit (hours/days) for "9".
3. Confirm minimum traveler count to operate.
