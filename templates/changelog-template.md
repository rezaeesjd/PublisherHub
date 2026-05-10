# Changelog — {{Canonical Tour Title}}

One bullet per refresh. Newest entry on top.

Each entry must include:
- date (YYYY-MM-DD)
- command (`WPS:GENERATE_CONTENT`, `WPS:GENERATE_CONTENT_FROM_INTAKE`, `WPS:CLARIFY`, `WPS:FIX_PACKAGE`, `WPS:PUBLISH_BLOG`, `WPS:RELINK_CLUSTER`, `WPS:LIVE_VERIFY`)
- one-line summary of what changed
- trigger (user request, scheduled refresh, cluster relink, etc.)

For variant packages (`-v<N>`), the first entry must also record:
- `variant_of`: <base slug>
- `variant_index`: <integer ≥ 2>
- `variant_role`: one of `bofu_landing | day_trip_bofu | comparison_mofu | informational_tofu | seasonal_faq | other`
- `variant_angle`: short human label (e.g., "BOFU day-trip / five-villages keyword variant")

## Entries

- YYYY-MM-DD — `WPS:GENERATE_CONTENT` — initial generation; status: draft.
