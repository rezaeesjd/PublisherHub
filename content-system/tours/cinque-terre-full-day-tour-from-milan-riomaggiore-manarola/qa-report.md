# QA Report

- Command: WPS:GENERATE_CONTENT
- Date: 2026-05-09
- Package: content-system/tours/cinque-terre-full-day-tour-from-milan-riomaggiore-manarola/
- QA status: needs_clarification
- Publish status: draft
- Public copy state: holding_notice

## Result
Generation is intentionally paused under the hard clarification gate. A holding notice was created in `blog-post.md`.

## Blocking clarifications
1. Conflicting product/reference code: `187808P82` vs `187808P109`.
2. Missing direct website booking URL for primary CTA.
3. Cancellation window unit unresolved: `9` relative to start time (hours vs days).
4. Date role unresolved: `May 1, 2026` has no explicit field meaning.

## Pass checks
- `source-facts.md` created before final public copy.
- `meta.json` created and valid JSON.
- `qa-report.md` created.
- `blog-post.md` is in holding-notice format and includes active brand mention.

## Not executed due to hard gate
- Full SEO post generation.
- FAQ generation.
- Keyword clustering.
- Internal link plan.
- Automation notes.
