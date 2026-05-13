# QA Report

- Date: 2026-05-13
- Package: `bernina-red-train-and-st-moritz-from-milan`
- Command: `WPS:GENERATE_CONTENT`

## Required files check
- PASS: source-facts.md present
- PASS: brief.md present
- PASS: keywords.md present
- PASS: blog-post.md present
- PASS: faq.md present
- PASS: internal-links.md present
- PASS: meta.json present
- PASS: automation-notes.md present
- PASS: qa-report.md present

## Content and compliance checks
- PASS: source-facts.md exists and is populated before public copy.
- PASS: blog-post.md is public-facing and includes one primary CTA.
- PASS: primary CTA uses real website booking URL.
- PASS: passport requirement is disclosed.
- PASS: non-refundable cancellation policy is disclosed.
- PASS: mobility restriction is disclosed.
- WARNING: product reference code is still missing in source input.
- WARNING: exclusions field remains blank in source input.

## Process QA linkage
- See: `content-system/system-qa/reports/2026-05-13-bernina-red-train-and-st-moritz-from-milan-process-qa.md`

## Status
- qa_status: `passing`
- publish_status: `ready_for_review`
- public_copy_state: `final`

## Action items
1. Add internal product reference code when available.
2. Add explicit exclusions list when available.
