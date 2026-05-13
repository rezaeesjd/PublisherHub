# QA Report

- Date: 2026-05-13
- Package: `bernina-red-train-and-st-moritz-from-milan`
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
- PASS: Canonical title capitalization normalized; recorded in `clarify_decisions`.
- PASS: Mandatory passport-required disclosure captured in source-facts and FAQ.
- PASS: "All sales final" cancellation captured.
- PASS: Wheelchair / impaired-mobility restriction captured.
- PASS: Late-booking (<12 h) operational note captured.
- BLOCKER: No booking permalink supplied — Viator, TripAdvisor, and website fields are all empty in source.
- WARNING: Internal product reference code not supplied.
- WARNING: Exclusions list left blank in source.
- WARNING: Minimum traveler count not specified.
- WARNING: Seasonal/weather route disruption not explicitly stated; alpine route implies it.

## Status
- qa_status: `blocked`
- publish_status: `needs_clarification`
- `can_generate_public_copy`: false until at least one booking permalink is supplied.

## Action items (blocking)
1. Supply at least one booking permalink (Viator, TripAdvisor, or supplier website).

## Action items (non-blocking)
2. Provide internal product reference code.
3. Provide an explicit exclusions list.
4. Confirm minimum traveler count to operate.
5. Confirm whether the route changes seasonally (winter snow vs summer service).
