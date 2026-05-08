# QA Report: Cinque Terre Full-Day Tour from Milan

Last run: 2026-05-08 (Phase 1 backfill)
Overall status: **needs_fix** — package is structurally complete but has placeholder URLs that must be replaced before publish.

## File checklist

- [x] source-facts.md
- [x] brief.md
- [x] keywords.md
- [x] blog-post.md
- [x] faq.md
- [x] meta.json
- [x] internal-links.md
- [x] automation-notes.md
- [x] qa-report.md

## Metadata checklist (meta.json)

- [x] brand
- [x] product_reference_code
- [x] tour_title
- [x] page_title
- [x] slug
- [x] meta_description
- [x] primary_keyword
- [x] funnel_stage
- [x] cta_primary
- [x] website_link
- [x] tripadvisor_link
- [x] viator_link
- [x] publish_status
- [x] human_review_required
- [x] qa_status

## Source-fact checklist

- [x] Active brand recorded
- [x] Tour identity recorded
- [x] Logistics (meeting point, duration, group type) recorded
- [ ] Specific start time recorded
- [x] Inclusions listed
- [x] Exclusions implied
- [ ] Cancellation policy recorded
- [ ] Review/rating data recorded
- [x] Missing inputs flagged

## Public article cleanliness checklist

- [x] No `# Page Title`, `## URL Slug`, `## Meta Description`, `# H1`, `## Hook paragraph`, `## Main value section`, `## Internal linking suggestions` labels in blog-post.md
- [x] Single H1 at top of blog-post.md
- [x] Brand referenced is Milano Adventures (matches meta.brand)
- [x] No raw supplier name leaks in public copy

## Link handling checklist

- [ ] Real website booking URL in place of `{{WebsiteLink}}`
- [ ] Real TripAdvisor URL in place of `{{TripAdvisorLink}}`
- [ ] Real Viator URL in place of `{{ViatorLink}}`
- [x] Placeholders flagged here so the post is not declared publish-ready

## Conversion checklist

- [x] Primary CTA present and points to website link
- [x] Secondary trust signals (TripAdvisor, Viator) appear after the primary CTA, not above it

## Review / social proof checklist

- [x] No invented review counts or ratings
- [ ] Real review/rating data populated in source-facts.md

## Publish readiness

- **publish_status:** `draft`
- **qa_status:** `needs_fix`
- **human_review_required:** true

## Issues found

1. Placeholder URLs (`{{WebsiteLink}}`, `{{TripAdvisorLink}}`, `{{ViatorLink}}`) must be replaced before publish.
2. Specific start time and cancellation policy missing from source-facts.
3. No review/rating data captured.

## Recommended fixes

1. Provide real URLs in platform settings (Phase 2 deliverable).
2. Add start time and cancellation policy to source-facts.md.
3. If review/rating data exists, add it to source-facts.md and surface a single, sourced sentence in blog-post.md.

## Final status

**Not publish-ready.** Block transitions to `published` until issues 1 and 2 are resolved.
