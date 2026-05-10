# QA Report

- Command: WPS:GENERATE_CONTENT
- Date: 2026-05-10
- Package: content-system/tours/cinque-terre-full-day-tour-from-milan-riomaggiore-manarola/
- QA status: needs_clarification
- Publish status: draft
- Public copy state: holding_notice

## Result
Generation is intentionally paused under the hard clarification gate. A holding notice was created in `blog-post.md` and dependent assets are deferred as stubs.

## Blocking clarifications
1. Conflicting product/reference code: `187808P82` vs `187808P109`.
2. Missing direct website booking URL for primary CTA.
3. Cancellation window unit unresolved: `9` relative to start time (hours vs days).
4. Date role unresolved: `May 1, 2026` has no explicit field meaning.
5. Wheelchair accessibility status is not explicitly confirmed in source fields.

## Pass checks
- `source-facts.md` created before final public copy.
- `meta.json` created and valid JSON.
- `qa-report.md` created.
- `blog-post.md` is in holding-notice format and includes active brand mention.
- All 9 required package files now exist; blocked files are marked deferred.

## Not executed due to hard gate
- Full SEO post generation.
- Final FAQ generation.
- Final keyword clustering.
- Final internal link plan.
- Final automation notes.
