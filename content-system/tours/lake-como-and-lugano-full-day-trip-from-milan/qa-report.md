# QA Report

- Date: 2026-05-12
- Package: `lake-como-and-lugano-full-day-trip-from-milan`
- Command: `WPS:GENERATE_CONTENT`

## Required files check
- PASS: Folder scaffolded with all 9 required files.

## Content and compliance checks
- PASS: `source-facts.md` created before public post drafting.
- PASS: Canonical title used verbatim from source.
- PASS: Holding notice included in `blog-post.md`.
- BLOCKING: No booking permalink (Viator, TripAdvisor, or website) provided.
- BLOCKING: No price band provided.
- BLOCKING: Operating days and start time missing.
- WARNING: Cancellation policy missing.
- WARNING: Phone number listed as US-format toll-free; verify routing.

## Status
- qa_status: `needs_clarification`
- publish_status: `needs_clarification`

## Action items
1. Operator: provide a real booking permalink (one channel is sufficient).
2. Operator: provide retail price per traveler in EUR.
3. Operator: confirm operating days and start time.
4. Operator: confirm cancellation policy (hours/days).
5. Re-run `WPS:GENERATE_CONTENT` after answers land.
