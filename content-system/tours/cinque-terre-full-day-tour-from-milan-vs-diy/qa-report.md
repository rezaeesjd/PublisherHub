# QA Report — Cinque Terre MOFU Comparison Post

## Tour Identity Confirmation
- Requested command: `WPS:GENERATE_CONTENT`
- Actual package folder: `content-system/tours/cinque-terre-full-day-tour-from-milan-vs-diy/`
- Canonical tour title: Cinque Terre Full-Day Tour from Milan
- MOFU post title: Cinque Terre from Milan: Guided Tour vs DIY by Train — Honest Comparison
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
- PASS: Source-facts provenance matrix present; guided-tour facts from confirmed base package; DIY comparison facts flagged as inferred.
- PASS: No deprecated field aliases used.
- PASS: Blog-post.md contains exactly one H1.
- PASS: Brand (Milano Adventures) present in blog-post.md.
- PASS: Primary CTA uses confirmed website booking URL.
- PASS: OTA links placed as secondary trust references after primary CTA.
- PASS: No placeholder tokens (`{{...}}`) in blog-post.md.
- PASS: Clarify gate — no blocking clarifications; auto_resolved.
- PASS: Status triad block present in this report.
- PASS: DIY comparison facts clearly flagged as inferred in source-facts.md.
- WARNING: Cancellation unit unspecified ("15"); specific policy excluded from public copy (inherited non-blocking).
- WARNING: Accessibility details not provided; omitted from public copy (inherited non-blocking).
- NOTE: Comparison table includes DIY data inferred from general destination knowledge; human reviewer should validate DIY logistics and cost estimates before publish.

## Verdict
Generation readiness: ready_for_review with inherited warnings and human review note on DIY comparison data.
Publish readiness: not yet verified.
