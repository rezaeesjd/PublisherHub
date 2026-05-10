# QA Report
- Date: 2026-05-10
- Command: WPS:GENERATE_CONTENT
- Package: content-system/tours/cinque-terre-full-day-tour-from-milan

## Checks
- ✅ Required 9 files present
- ✅ `meta.json` valid JSON
- ✅ Brand mention present in public copy
- ✅ Source-facts created before public content
- ✅ Primary CTA available (Viator fallback)
- ⚠️ Direct website CTA missing (`{{WebsiteLink}}` placeholder retained)
- ⚠️ Cancellation window unit unclear in source; omitted from claims
- ⚠️ Accessibility data missing in source

## Status outcome
- `qa_status`: `warning`
- `publish_status`: `draft`
- Follow-up: replace OTA primary CTA with direct website booking URL when available.
