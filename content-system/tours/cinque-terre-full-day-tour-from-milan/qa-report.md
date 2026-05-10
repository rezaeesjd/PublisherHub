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

## Live verification (WPS:LIVE_VERIFY)
- Date: 2026-05-10
- Scope: all publishable packages in content-system/tours/
- Result: ⚠️ Could not verify deployed live archive/single-post URLs from this environment because no public deployment base URL was provided.
- Status update: package moved to `needs_live_verification` with `live_verification_completed: false`.
- Next step: provide the deployed WebPublisherSystem base URL, then re-run WPS:LIVE_VERIFY to check archive listing, single post render, and CTA link rendering.
