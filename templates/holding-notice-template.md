# {{Public H1 Tour Title}}

This {{ActiveBrand}} tour is currently being finalized. Full availability, pricing, and booking details will be added once the listing is complete.

In the meantime, you can review the listing on the marketplaces below:

- [TripAdvisor]({{TripAdvisorLink}})
- [Viator]({{ViatorLink}})

---

## Holding-notice rules (do not include below this line in the published file)

This template renders the `blog-post.md` for a tour whose hard clarify gate is active. Use it whenever `meta.clarifications_needed` contains any `"blocking": true` entry and the user has **not** explicitly approved provisional mode.

The notice MUST:

- contain exactly one Markdown H1 with the canonical (or near-canonical) tour title
- mention the active brand at least once in natural prose (default `Milano Adventures`)
- be ≤150 words total
- avoid every claim that depends on an unresolved blocking clarification, including but not limited to: pricing, departure days, start time, duration, cancellation window, specific itinerary order, specific stop durations, hotel pickup status, languages list, accessibility status, ratings or review counts
- avoid all admin/SEO labels (`Page Title`, `URL Slug`, `Meta Description`, etc.)
- use real OTA URLs only when they are confirmed; otherwise omit those bullets entirely (do not ship `{{TripAdvisorLink}}` or `{{ViatorLink}}` placeholders inside the notice)

The notice MAY:

- name the destination at a high level (e.g. "Cinque Terre", "Lake Como")
- list the canonical village/area names if they are present in `source-facts.md`
- offer OTA links as fallback discovery, when those URLs are real

In `meta.json`, set:

```json
"public_copy_state": "holding_notice",
"qa_status": "needs_clarification",
"intake_questions_resolved": false
```

Once the user resolves the blocking clarifications, run `WPS:GENERATE_CONTENT` again. The follow-up run uses `templates/blog-post-public-template.md` to produce the final article and updates `public_copy_state` to `final`.
