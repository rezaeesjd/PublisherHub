# Automation Notes — Cinque Terre Day Trip from Milan (Variant v2)

## Why this variant exists
This package is the second content variant of the same supplier product as `cinque-terre-full-day-tour-from-milan-riomaggiore-manarola`. It was created automatically per the multi-variant generation rule in `AGENTS.md`: when a finalized package matching the canonical tour title already exists, a new `WPS:GENERATE_CONTENT` run must produce a new package folder rather than overwrite the prior approved package. Each variant carries the same source facts but targets a different keyword angle / page title / structure to expand SERP coverage and A/B-test conversion copy.

## Reusing this structure weekly or daily
- Treat the per-tour folder family `cinque-terre-full-day-tour-from-milan-riomaggiore-manarola*` as one **product cluster**. The base package is `-v1` (no suffix), this is `-v2`, future variants are `-v3`, `-v4`, etc.
- Plan one new variant per refresh window (e.g., one new variant per week per high-value product) until the cluster covers: BOFU landing, day-trip BOFU, comparison MOFU, informational TOFU, seasonal/FAQ.
- Avoid two variants that target the same head keyword and the same funnel stage at the same time — split them by intent or angle to prevent cannibalization.

## Scaling production with AI assistance
- Reuse the same source intake row across all variants in the cluster. Variants must not invent facts beyond `source-facts.md`.
- Per variant, only the following should change: `page_title`, `public_slug`, `primary_keyword`, hook paragraph, section ordering, FAQ angle, and CTA copy phrasing. Pricing, duration, departures, transport, languages, and meeting points must remain identical across variants.
- For each new variant, add a `variant_of` and `variant_index` field in `meta.json` pointing to the base slug — this lets the platform group variants for analytics later.

## Keeping the design and section order consistent
- All variants follow the same public article shape from `AGENTS.md`: H1 → hook → main value section → who-this-is-best-for → what-to-expect → soft CTA → what-to-know-before-booking → optional verified social proof → strong CTA block.
- Variant differentiation lives inside that shape, not by inventing new sections.

## Adapting this template to similar tour topics
- For other Milan-departing day trips (Como, Lugano, Verona, Venice), copy this folder, swap the source facts and the keyword cluster, and keep the BOFU shape intact.
- The `variant_of` mechanism applies equally to other products: each tour can have its own variant cluster.

## How this content supports lead generation, customer acquisition, bookings, and growth
- Variants increase organic surface area for the same product without diluting any single page.
- BOFU variants always end on a real booking URL (Viator until a direct website URL is supplied), keeping every variant a usable conversion path.
- Once a direct website booking URL exists, all variants in the cluster should be re-pointed to it in a single sweep so the website becomes the primary CTA.
