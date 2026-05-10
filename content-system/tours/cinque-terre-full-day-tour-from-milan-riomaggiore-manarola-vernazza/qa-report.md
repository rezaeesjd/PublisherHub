# QA Report

- Date: 2026-05-10
- Command: WPS:GENERATE_CONTENT
- Package folder: `content-system/tours/cinque-terre-full-day-tour-from-milan-riomaggiore-manarola-vernazza/`
- QA status: `needs_clarification`
- Publish status: `draft`

## Checks

- ✅ Source facts file created before public copy.
- ✅ All 9 required package files exist.
- ✅ `meta.json` is valid JSON.
- ✅ Public copy set to holding notice mode due to blocking clarifications.
- ✅ Brand mention present in `blog-post.md`.
- ⚠️ Primary website CTA link missing (conversion blocker).
- ⚠️ Product code conflict detected (`187808P109` vs `187808P82`).
- ⚠️ Cancellation window unit unresolved (`9` relative to start time).
- ⚠️ Canonical tour title appears truncated and requires confirmation.

## Blocking clarifications required
1. Provide direct website booking URL.
2. Confirm canonical product/reference code.
3. Confirm cancellation window unit (hours or days).
4. Confirm exact canonical tour title.

## Result
Generation is intentionally paused in clarification mode. Final public copy and supporting SEO files are deferred until blocking clarifications are resolved.
