# Automation Notes

- Treat `WPS:GENERATE_CONTENT` and `WPS:PUBLISH_BLOG` as separate stages.
- Extract `source-facts.md` first, then draft traveler-facing copy.
- Preserve real provided links (website, Viator, TripAdvisor); only use placeholders for missing fields.
- Keep `blog-post.md` public-facing only; store metadata/SEO in `meta.json`, `keywords.md`, and `internal-links.md`.
- Always create/update `qa-report.md` with pass/fail checks and publish readiness.
- Never mark `published` unless live archive and single-post pages are verified.
- Reuse this structure for similar Milan departure day trips with only fact-level substitutions.
