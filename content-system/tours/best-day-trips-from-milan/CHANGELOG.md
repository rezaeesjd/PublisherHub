# Changelog — Best Day Trips from Milan (TOFU destination guide)

One bullet per refresh. Newest entry on top.

## Entries

- 2026-05-15 — `WPS:FIX_PACKAGE` — addressed Codex review feedback on PR #82: removed unsupported "passport stamp" phrasing from the Bernina section, populated `meta.json.clarify_decisions[]`, and added this CHANGELOG.md per AGENTS.md cluster-asset rule. Trigger: PR review comments.
- 2026-05-15 — `WPS:FIX_PACKAGE` — inherited `product_reference_code` (`187808P109`) and `channel_product_codes` from base package `cinque-terre-full-day-tour-from-milan`; added structured `inherited_warnings[]` per AGENTS.md source-fact inheritance handshake. Trigger: Codex P1 review on PR #82.
- 2026-05-15 — `WPS:GENERATE_CONTENT` — initial generation as TOFU destination-guide asset for the `cinque-terre-from-milan` cluster. `cluster_parent: cinque-terre-from-milan`, `cluster_type: TOFU`, `cluster_role: destination-guide`. Headline option: Cinque Terre Full-Day Tour from Milan; sibling cluster mentions for Lake Como + Lugano and Bernina + St Moritz. Status: ready_for_review. Trigger: user request (next recommended generation per cluster registry).
