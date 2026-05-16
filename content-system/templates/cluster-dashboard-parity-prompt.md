# Prompt — Fix Cluster Dashboard Parity (Source Content vs BOFU)

Use this prompt when dashboard counts/rows are inconsistent.

## Prompt text
WPS:IMPROVE_SYSTEM_WORKFLOW

Fix cluster dashboard parity and status semantics.

Requirements:
1) Keep `source_content` as static canonical package only (not a blog asset).
2) Keep BOFU `main-booking-post` as a blog content asset for publication tracking and blog listing.
3) Published-blog counter must use only required blog assets: BOFU+MOFU+TOFU+FAQ.
4) Show source-content completeness separately from blog publication count.
5) Add/adjust rules so future runs must verify dashboard parity before close-out.
6) Backfill inconsistent cluster notes/status labels where BOFU was described as non-blog while counted as published.

Output required:
- exact files changed
- before/after behavior for counters and row display
- checklist proving parity for each cluster
