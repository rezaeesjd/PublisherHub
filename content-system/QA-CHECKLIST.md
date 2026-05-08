# QA Checklist (Package + Publish Readiness)

## File and structure
- [ ] Correct single tour folder used
- [ ] All 9 required files exist
- [ ] `source-facts.md` exists and was created before copy
- [ ] `qa-report.md` exists

## Metadata
- [ ] `meta.json` valid JSON
- [ ] required fields exist
- [ ] slug/public_slug valid
- [ ] allowed `publish_status` used

## Link handling
- [ ] Real provided links preserved
- [ ] Website link is primary CTA
- [ ] TripAdvisor/Viator are secondary only
- [ ] Placeholders only for missing links
- [ ] Placeholder usage explicitly flagged

## Public content cleanliness
- [ ] One real Markdown H1
- [ ] No admin labels in `blog-post.md`
- [ ] Public-facing sections only

## Source-facts integrity
- [ ] No invented facts
- [ ] Missing inputs listed
- [ ] Ambiguities listed for human review
- [ ] Brand rule respected (Milano Adventures public brand)

## Review/social proof
- [ ] No invented ratings/review counts
- [ ] No exaggerated social proof claims
- [ ] If review data omitted, QA notes whether intentional

## Publish integrity
- [ ] Do not call published without live verification
- [ ] Archive visibility verified (if possible)
- [ ] Single post URL verified (if possible)
