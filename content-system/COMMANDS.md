# Command Reference

## WPS:GENERATE_CONTENT
Create or update one tour content package only.
- Must create `source-facts.md` before public copy.
- Must create `qa-report.md`.
- Use real links when provided.
- Do not claim published/live.

## WPS:PUBLISH_BLOG
Validate an existing package for publish readiness.
- Check files/metadata/links/source-facts/public cleanliness.
- Do not rewrite content unless needed for QA fixes.
- If live checks are impossible, use `ready_for_sync` or `needs_live_verification`.

## WPS:GENERATE_AND_PUBLISH
Run generation first, then publish checks. Report each phase separately.

## WPS:PROCESS_QA
Process-only review and report. No file edits.

## WPS:FIX_PACKAGE
Repair one existing tour package. No system-wide redesign.

## WPS:IMPROVE_SYSTEM_WORKFLOW
Improve instructions/templates/checklists/workflow docs only.
Do not modify tour package content.

## WPS:LIVE_VERIFY
Live-only verification of archive + single post render.
No content generation or rewrites.
