# QA Report — Bernina FAQ Support Post

## Tour Identity Confirmation
- Requested command: `WPS:GENERATE_CONTENT`
- Actual package folder: `content-system/tours/bernina-st-moritz-from-milan-faq/`
- Canonical tour title: Full Day Tour in Bernina Red Train and St Moritz from Milan
- FAQ post title: Bernina Express + St Moritz from Milan: Passport, Weather & Timing FAQ
- Product/reference code: missing (non-blocking warning, inherited)
- Active brand: Milano Adventures
- Website URL status: confirmed (inherited from base package)
- TripAdvisor URL status: missing (non-blocking)
- Viator URL status: missing (non-blocking)
- Package created: 2026-05-13
- Report scope: generation

## Status
- Generation: complete
- Publish: not yet verified
- Live verification: not yet verified

## QA Findings
- PASS: All 9 required files present.
- PASS: Source-facts provenance matrix present; all facts inherited from confirmed base package.
- PASS: No deprecated field aliases used.
- PASS: Blog-post.md contains exactly one H1.
- PASS: Brand (Milano Adventures) present in blog-post.md.
- PASS: Primary CTA uses confirmed website booking URL.
- PASS: No placeholder tokens (`{{...}}`) in blog-post.md.
- PASS: Clarify gate — no blocking clarifications; auto_resolved.
- PASS: Status triad block present in this report.
- WARNING: Product reference code missing (inherited non-blocking).
- WARNING: Exclusions list missing (inherited non-blocking).
- WARNING: Viator / TripAdvisor OTA links missing (inherited non-blocking; website URL is primary CTA).

## Verdict
Generation readiness: ready_for_review.
Publish readiness: not yet verified.
