# QA Checklist (Package + Publish Readiness + Process QA)

## Tour Identity Confirmation (required first section)
- [ ] requested command
- [ ] actual package folder
- [ ] canonical tour title
- [ ] product/reference code
- [ ] active brand
- [ ] website URL status
- [ ] TripAdvisor URL status
- [ ] Viator URL status
- [ ] package created/updated date (if known)
- [ ] report scope: generation / publishing / live verification

## Clarify Gate Enforcement
- [ ] conflict and missing-input detection completed before copy
- [ ] `clarifications_needed` evaluated
- [ ] blocking issues stop public copy unless provisional mode explicitly approved
- [ ] missing website URL treated as conversion blocker unless waived

## File and structure
- [ ] Correct single tour folder used
- [ ] All 9 required files exist
- [ ] `source-facts.md` exists and was created before copy
- [ ] `qa-report.md` exists

## Metadata and phase markers
- [ ] `meta.json` valid JSON
- [ ] required fields exist
- [ ] phase markers exist
- [ ] allowed `publish_status` used
- [ ] allowed `qa_status` used

## Link handling
- [ ] Real provided website URL preserved
- [ ] Website link is primary CTA when provided
- [ ] OTA links are secondary only
- [ ] placeholders only for missing links
- [ ] website placeholder flagged as blocker
- [ ] missing OTA links flagged as warnings

## Source-facts provenance
- [ ] provenance matrix present
- [ ] statuses use allowed set
- [ ] cancellation policy captured
- [ ] review rating/count/text source captured
- [ ] missing critical inputs listed
- [ ] conflicts detected listed

## Public content cleanliness
- [ ] One real Markdown H1
- [ ] No admin labels in `blog-post.md`
- [ ] Public-facing sections only

## PROCESS_QA behavior constraints
- [ ] no file modifications
- [ ] no content rewriting
- [ ] no PR creation unless requested
- [ ] generation readiness separated from publish readiness
- [ ] missing user input separated from generation mistakes
- [ ] issues classified by type

## Issue Categories (PROCESS_QA)
- [ ] System instruction gap
- [ ] Workflow enforcement gap
- [ ] User input gap
- [ ] Generated package issue
- [ ] Front-end rendering risk
- [ ] Publish verification gap
