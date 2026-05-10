# QA Report
- Date: 2026-05-10
- Command: WPS:PUBLISH_BLOG
- Package: content-system/tours/cinque-terre-full-day-tour-from-milan

## Checks
- ✅ Required 9 files present
- ✅ `meta.json` valid JSON and phase markers present
- ✅ `public_copy_state` is `final`
- ✅ Brand mention present in public copy
- ✅ Source-facts integrity preserved
- ✅ Primary CTA available (Viator fallback)
- ⚠️ Direct website CTA missing (`{{WebsiteLink}}` placeholder retained)
- ⚠️ Cancellation window unit unclear in source; omitted from claims
- ⚠️ Accessibility data missing in source
- ✅ No blocking clarification issues

## Publish path status
- publish phase complete: ✅
- live verification complete: ❌ (not run in this step)
- final status chosen: `ready_for_sync`

## Status outcome
- `qa_status`: `warning`
- `publish_status`: `ready_for_sync`
- `publish_phase_completed`: `true`
- `live_verification_completed`: `false`
- Follow-up: sync/deploy package, then run `WPS:LIVE_VERIFY` to validate archive + single post URL before marking `published`.
