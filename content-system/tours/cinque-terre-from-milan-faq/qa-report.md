# QA Report — Cinque Terre FAQ Support Post

## Tour Identity Confirmation
- Requested command: `WPS:GENERATE_CONTENT`
- Actual package folder: `content-system/tours/cinque-terre-from-milan-faq/`
- Canonical tour title: Cinque Terre Full-Day Tour from Milan
- FAQ post title: Cinque Terre from Milan: Practical FAQs — Ferry, Train, Pickup & More
- Product/reference code: 187808P109
- Active brand: Milano Adventures
- Website URL status: confirmed (inherited from base package)
- TripAdvisor URL status: confirmed (inherited from base package)
- Viator URL status: confirmed (inherited from base package)
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
- PASS: OTA links placed as secondary trust references after primary CTA.
- PASS: No placeholder tokens (`{{...}}`) in blog-post.md.
- PASS: Clarify gate — no blocking clarifications; auto_resolved.
- PASS: Status triad block present in this report.
- WARNING: Cancellation unit unspecified ("15"); specific cancellation promise omitted; travellers directed to booking page (inherited non-blocking).
- WARNING: Accessibility details not provided; omitted from public copy (inherited non-blocking).

## Verdict
Generation readiness: ready_for_review with inherited warnings.
Publish readiness: not yet verified.
