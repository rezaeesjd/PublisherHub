# QA Report — Cinque Terre Full-Day Tour from Milan: Riomaggiore, Manarola & …

- Command evaluated: `WPS:GENERATE_CONTENT`
- Date (UTC): 2026-05-10
- QA status: `needs_clarification`
- Publish status: `draft`

## Gate decision
Hard clarify gate is active. Public copy generation is blocked until user resolves blocking clarifications.

## Checks

### Package/file checks
- ✅ `source-facts.md` created
- ✅ `meta.json` created and valid JSON
- ✅ `qa-report.md` created
- ⛔ Full 9-file package generation intentionally paused by hard clarify gate

### Blocking clarifications detected
1. Canonical tour title appears truncated (`...`).
2. Product/reference code conflict (`187808P109` vs `187808P82`).
3. Missing direct website booking URL (primary CTA blocker).
4. Cancellation policy has unresolved unit (`9` relative to start time).
5. Unlabeled numeric policy value (`15`).
6. Itinerary scope conflict (2-town naming vs 5-town description).
7. Wheelchair accessibility status missing.

## Required user responses before next generation pass
- Full canonical title
- Canonical + channel product code mapping
- Primary website booking URL
- Cancellation window with unit
- Meaning of numeric field `15`
- Confirm final itinerary scope to market
- Wheelchair accessibility value

## Next action
Await user clarification answers, then continue `WPS:GENERATE_CONTENT` and generate remaining package files.
