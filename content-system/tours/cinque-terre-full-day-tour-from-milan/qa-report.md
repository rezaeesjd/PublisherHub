# QA Report

- Date: 2026-05-11
- Command: WPS:GENERATE_CONTENT
- Package: `content-system/tours/cinque-terre-full-day-tour-from-milan/`

## Required files check
- ✅ source-facts.md
- ✅ brief.md
- ✅ keywords.md
- ✅ blog-post.md
- ✅ faq.md
- ✅ meta.json
- ✅ internal-links.md
- ✅ automation-notes.md
- ✅ qa-report.md

## Validation checks
- ✅ Source facts extracted before public copy.
- ✅ Canonical title normalized from truncated source input.
- ✅ Primary CTA present using real Viator URL.
- ✅ TripAdvisor used as secondary trust/reference link.
- ✅ Brand mention present (Milano Adventures).
- ⚠️ Direct website booking URL missing; placeholder retained in meta and follow-up required.
- ⚠️ Cancellation policy value lacks unit; excluded from public copy.
- ✅ No unsupported review/rating claims included.

## Status decision
- `qa_status`: `warning`
- `publish_status`: `ready_for_review`
- `public_copy_state`: `final`

## Follow-up actions
1. Replace OTA-primary CTA with direct website booking URL once available.
2. Confirm cancellation policy unit before adding exact policy text to public content.
