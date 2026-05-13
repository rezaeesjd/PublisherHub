# QA Report

- Date: 2026-05-13
- Package: `lake-como-and-lugano-full-day-trip-from-milan`
- Command: `WPS:GENERATE_CONTENT` (source-only intake; blog-post.md intentionally omitted)

## Required files check
- PASS: source-facts.md present
- PASS: brief.md present
- PASS: keywords.md present
- PASS: faq.md present
- PASS: internal-links.md present
- PASS: meta.json present
- PASS: automation-notes.md present
- INFO: blog-post.md intentionally omitted at this stage per user instruction.

## Content and compliance checks
- PASS: source-facts.md created before any public copy drafting.
- PASS: Canonical title preserved as supplied.
- PASS: OTA links preserved as provided (Viator + TripAdvisor).
- PASS: At least one real booking permalink is present.
- PASS: Brand mention noted in source.
- PASS: No unsupported review/rating claims.
- PASS: Operating-days typo (`Mon;Wed; Fri;Sat`) auto-normalized and logged in `clarify_decisions`.
- WARNING: Exclusions left blank in source; cannot publish negative inclusion claims.
- WARNING: Switzerland border-crossing language (passport vs national ID) not specified in source.

## Status
- qa_status: `warning`
- publish_status: `archived` (source content only; awaiting public-copy generation phase)

## Action items
1. Confirm minimum traveler count to operate.
2. Confirm whether a passport or national ID note should be added for the Lugano segment.
3. When generating public copy, retrieve exclusions list from supplier or omit explicit "what's not included" section.
