# QA Report — Bernina MOFU Comparison Post

## Tour Identity Confirmation
- Requested command: `WPS:GENERATE_CONTENT`
- Actual package folder: `content-system/tours/bernina-express-guided-vs-diy-from-milan/`
- Canonical tour title: Full Day Tour in Bernina Red Train and St Moritz from Milan
- MOFU post title: Bernina Express from Milan: Guided Tour vs DIY — Which Is Better?
- Product/reference code: missing (non-blocking, inherited)
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
- PASS: Source-facts provenance matrix present; guided-tour facts from confirmed base package; DIY comparison facts flagged as inferred from general destination knowledge.
- PASS: No deprecated field aliases used.
- PASS: Blog-post.md contains exactly one H1.
- PASS: Brand (Milano Adventures) present in blog-post.md.
- PASS: Primary CTA uses confirmed website booking URL.
- PASS: No placeholder tokens (`{{...}}`) in blog-post.md.
- PASS: Clarify gate — no blocking clarifications; auto_resolved.
- PASS: Status triad block present in this report.
- PASS: DIY comparison facts are clearly flagged as inferred in source-facts.md; not presented as operator-confirmed data.
- WARNING: Product reference code missing (inherited non-blocking).
- WARNING: Exclusions list missing (inherited non-blocking).
- WARNING: Viator / TripAdvisor OTA links missing (inherited non-blocking).
- NOTE: Comparison table includes DIY data inferred from general destination knowledge; human reviewer should validate DIY cost and timing estimates before publish.

## Verdict
Generation readiness: ready_for_review with inherited warnings and human review note on DIY comparison data.
Publish readiness: not yet verified.
