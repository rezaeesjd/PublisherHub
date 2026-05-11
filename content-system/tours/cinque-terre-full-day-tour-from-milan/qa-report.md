# QA Report

- Date: 2026-05-11
- Command: WPS:PUBLISH_BLOG
- Package: `content-system/tours/cinque-terre-full-day-tour-from-milan`

## Checks

1. Tour folder exists: **PASS**
2. Required 9 files present: **PASS**
3. `meta.json` valid JSON: **PASS**
4. Required `meta.json` fields present: **PASS**
5. Public slug format valid: **PASS**
6. Public slug uniqueness across tour packages: **PASS**
7. `blog-post.md` contains public-facing article content only: **PASS**
8. Admin-only labels absent from `blog-post.md`: **PASS**
9. Primary CTA exists: **PASS** (Viator fallback)
10. Real provided URLs used where available: **PASS**
11. Placeholder links flagged when missing real URL: **WARNING** (`website_link` still `{{WebsiteLink}}`)
12. Source facts appear grounded and non-invented: **PASS**
13. Review/rating claims traceable to source-facts: **PASS**
14. TripAdvisor/Viator positioned as secondary trust + OTA fallback: **PASS**
15. Live archive verification: **WARNING** (not verified from this environment)
16. Live single-post URL verification: **WARNING** (not verified from this environment)

## QA Summary
- `qa_status`: **warning**
- `publish_status`: **needs_live_verification**
- `public_copy_state`: **final**

## Follow-ups
- Add direct website booking URL and switch primary CTA from Viator to website once available.
- Run `WPS:LIVE_VERIFY` after deployment sync to confirm archive listing and single-post render.
