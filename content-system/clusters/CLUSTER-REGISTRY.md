# Cluster Registry

This is the central content-cluster inventory for WebPublisherSystem.

It tracks each tour/content cluster, the required funnel assets, which assets already exist, their package/status, and what still needs to be created.

This file is system-level. It is not tied to one specific package.

## Purpose

Use this registry to prevent random content generation and ensure every tour grows into a complete SEO/conversion funnel.

The registry answers:

- Which tour clusters exist?
- Which assets are required for each cluster?
- Which assets have been generated?
- What is the status of each asset?
- What is missing?
- What should the AI generate next?
- Which asset is the main conversion target?
- How should TOFU/MOFU/BOFU/FAQ assets link together?

## Required cluster asset set

By default, each tour cluster should include:

| Required? | Funnel stage | Cluster role | Purpose |
| --- | --- | --- | --- |
| Required | BOFU | `main-booking-post` | Main commercial booking-intent asset |
| Required | MOFU | `comparison-post` | Compare guided tour vs DIY / transport / alternatives |
| Recommended | MOFU | `comparison-post` | Compare this tour with another destination/tour option |
| Required | TOFU | `destination-guide` | Broad discovery guide to attract early-stage travelers |
| Recommended | TOFU | `itinerary-guide` | Practical itinerary/timing/seasonal guide |
| Required | FAQ | `faq-support-post` | Remove booking doubts and link back to BOFU |

A cluster is considered minimally complete when it has:

- 1 BOFU main booking asset
- 1 MOFU comparison asset
- 1 TOFU discovery asset
- 1 FAQ/support asset

A cluster is considered strong when it has all 6 assets above.

## Allowed asset statuses

Use these statuses for each asset row:

- `not_started`
- `planned`
- `draft`
- `needs_clarification`
- `needs_fix`
- `ready_for_review`
- `published`
- `published`
- `published`
- `refresh_needed`

## Registry update rules

After every `WPS:GENERATE_CONTENT`, `WPS:GENERATE_AND_PUBLISH`, `WPS:PUBLISH_BLOG`, or `WPS:FIX_PACKAGE` run, the AI agent must update this registry.

The agent must:

1. Locate or create the cluster section for the tour.
2. Add/update the generated package row.
3. Update asset status based on `meta.json.publish_status` and `qa_status`.
4. Add missing required assets to the cluster table as `not_started` or `planned`.
5. Update `Next recommended generation`.
6. Keep `Primary conversion asset` clear.
7. Do not mark an asset `published` unless live verification is complete.

## Cluster completeness scoring

Use this simple score:

- `0/6`: no assets generated
- `1/6`: only one asset generated
- `2-3/6`: early cluster
- `4/6`: minimally complete cluster
- `5/6`: strong but missing one asset
- `6/6`: full cluster

## Cluster section template

Copy this section for each new tour cluster.

```md
---

# Cluster: {{Canonical Tour Title}}

- Cluster parent slug: `{{base-tour-slug}}`
- Primary conversion asset: `{{base-tour-slug}}`
- Website booking URL: `{{WebsiteLink}}`
- Viator URL: `{{ViatorLink}}`
- TripAdvisor URL: `{{TripAdvisorLink}}`
- Cluster status: early | minimally-complete | strong | complete | needs-review
- Completeness score: 0/6
- Last updated: YYYY-MM-DD
- Next recommended generation: `{{recommended-next-asset}}`

## Asset inventory

| Required | Funnel stage | Cluster role | Target angle/title | Package slug | Public slug | Status | Next step link target | Notes |
| --- | --- | --- | --- | --- | --- | --- | --- | --- |
| Yes | BOFU | `main-booking-post` | Main booking-intent post | `{{base-tour-slug}}` | `{{public-slug}}` | not_started | `{{WebsiteLink}}` | Main conversion asset |
| Yes | MOFU | `comparison-post` | Guided tour vs DIY / train / self-guided | `{{base-tour-slug}}-v2` | `{{public-slug}}` | not_started | `{{base-tour-slug}}` | Decision-support asset |
| No | MOFU | `comparison-post` | Destination/tour alternative comparison | `{{base-tour-slug}}-v3` | `{{public-slug}}` | planned | `{{base-tour-slug}}` | Optional but recommended |
| Yes | TOFU | `destination-guide` | Broad destination/day-trip guide | `{{base-tour-slug}}-v4` | `{{public-slug}}` | not_started | `{{SuggestedMofuAsset}}` | Discovery asset |
| No | TOFU | `itinerary-guide` | Timing/seasonal/practical itinerary guide | `{{base-tour-slug}}-v5` | `{{public-slug}}` | planned | `{{SuggestedMofuAsset}}` | Optional but recommended |
| Yes | FAQ | `faq-support-post` | Practical booking doubts / can you visit in one day | `{{base-tour-slug}}-v6` | `{{public-slug}}` | not_started | `{{base-tour-slug}}` | Objection removal |

## Internal linking plan

- TOFU assets should link to MOFU assets and optionally the BOFU asset.
- MOFU assets should link to the BOFU/main booking asset.
- FAQ assets should link to the BOFU/main booking asset.
- BOFU should link to booking URL and optionally FAQ/support content.

## Missing assets

- [ ] BOFU main booking post
- [ ] MOFU comparison post
- [ ] TOFU destination guide
- [ ] FAQ support post

## Notes

- Keep all source facts consistent across assets.
- Use variants/related packages for different funnel roles, not duplicate rewrites.
- Do not create multiple pages with the exact same search intent.
```

---

# Active clusters

> **Canonical source:** `content-system/clusters/cluster-registry.json`. The list below mirrors that JSON for human readers. If they disagree, the JSON wins — regenerate this section from it.

_Last regenerated: 2026-05-13 from `cluster-registry.json` (4 clusters, 17 declared assets, 5 generated tour packages)._

---

# Cluster: Cinque Terre Day Trips from Milan

- Cluster parent slug: `cinque-terre-from-milan`
- Primary conversion asset: `cinque-terre-full-day-tour-from-milan`
- Website booking URL: `{{WebsiteLink}}`
- Viator URL: https://www.viator.com/tours/Milan/Full-Day-Cinque-Terre-Tour-from-Milan-Riomaggiore-Manarola/d512-187808P82
- TripAdvisor URL: https://www.tripadvisor.com/AttractionProductReview-g187849-d33344981-Cinque_Terre_Full_Day_Tour_from_Milan_Riomaggiore_Manarola-Milan_Lombardy.html
- Last updated: 2026-05-12
- Next recommended generation: MOFU comparison-post — Cinque Terre guided tour vs DIY by train from Milan

## Asset inventory

| Required | Funnel stage | Cluster role | Target angle/title | Package slug | Status | Next step link target |
| --- | --- | --- | --- | --- | --- | --- |
| Yes | BOFU | `main-booking-post` | Cinque Terre Full-Day Tour from Milan | `cinque-terre-full-day-tour-from-milan` | ready_for_review | viator |
| Yes | MOFU | `comparison-post` | Cinque Terre guided tour vs DIY by train from Milan | `cinque-terre-full-day-tour-from-milan-vs-diy` | planned | `cinque-terre-full-day-tour-from-milan` |
| Yes | TOFU | `destination-guide` | Best day trips from Milan (overview) | `best-day-trips-from-milan` | planned | `cinque-terre-full-day-tour-from-milan` |
| Yes | FAQ | `faq-support-post` | Cinque Terre from Milan: practical FAQs | `cinque-terre-from-milan-faq` | planned | `cinque-terre-full-day-tour-from-milan` |

---

# Cluster: Lake Como & Lugano Day Trips from Milan

- Cluster parent slug: `lake-como-and-lugano-from-milan`
- Primary conversion asset: `all-in-one-lake-como-bellagio-and-lugano-from-milan`
- Website booking URL: `{{WebsiteLink}}`
- Viator URL: https://www.viator.com/tours/Milan/All-in-one-Lake-Como-Bellagio-and-Lugano-from-Milan-Scenic-Cruise/d512-187808P57
- TripAdvisor URL: https://www.tripadvisor.com/AttractionProductReview-g187849-d33353135-All_in_One_Lake_Como_Bellagio_Lugano_from_Milan_Scenic_Cruise-Milan_Lombardy.html
- Last updated: 2026-05-13
- Next recommended generation: Generate the private-vs-group MOFU comparison post.

## Asset inventory

| Required | Funnel stage | Cluster role | Target angle/title | Package slug | Status | Next step link target |
| --- | --- | --- | --- | --- | --- | --- |
| Yes | BOFU | `main-booking-post` | All-in-One Lake Como, Bellagio & Lugano from Milan + Scenic Cruise | `all-in-one-lake-como-bellagio-and-lugano-from-milan` | ready_for_review | viator |
| Yes | MOFU | `comparison-post` | Lake Como and Lugano Full Day Trip from Milan (two-lake variant, no Bellagio) | `lake-como-and-lugano-full-day-trip-from-milan` | archived | `all-in-one-lake-como-bellagio-and-lugano-from-milan` |
| Yes | MOFU | `comparison-post` | Lake Como private vs group day tour from Milan | `lake-como-private-vs-group-from-milan` | planned | `all-in-one-lake-como-bellagio-and-lugano-from-milan` |
| Yes | TOFU | `destination-guide` | Lake Como travel guide for first-time visitors | `lake-como-travel-guide` | planned | `all-in-one-lake-como-bellagio-and-lugano-from-milan` |
| Yes | FAQ | `faq-support-post` | Lake Como from Milan: pickup, passports, timing FAQ | `lake-como-from-milan-faq` | planned | `all-in-one-lake-como-bellagio-and-lugano-from-milan` |

---

# Cluster: Swiss Alps Day Trips from Milan

- Cluster parent slug: `swiss-alps-from-milan`
- Primary conversion asset: `bernina-red-train-and-st-moritz-from-milan`
- Website booking URL: `{{WebsiteLink}}`
- Viator URL: _pending_
- TripAdvisor URL: _pending_
- Last updated: 2026-05-13
- Next recommended generation: Supply a booking permalink for the Bernina + St Moritz tour, then generate the MOFU comparison post (guided tour vs DIY Bernina Express from Tirano).

## Asset inventory

| Required | Funnel stage | Cluster role | Target angle/title | Package slug | Status | Next step link target |
| --- | --- | --- | --- | --- | --- | --- |
| Yes | BOFU | `main-booking-post` | Full Day Tour in Bernina Red Train and St Moritz from Milan | `bernina-red-train-and-st-moritz-from-milan` | needs_clarification | pending (no booking permalink in source) |
| Yes | MOFU | `comparison-post` | Bernina Express guided tour from Milan vs DIY from Tirano | `bernina-express-guided-vs-diy-from-milan` | planned | `bernina-red-train-and-st-moritz-from-milan` |
| Yes | TOFU | `destination-guide` | Swiss Alps from Milan: Bernina Express route guide | `swiss-alps-from-milan-guide` | planned | `bernina-red-train-and-st-moritz-from-milan` |
| Yes | FAQ | `faq-support-post` | Bernina Express + St Moritz from Milan: passport, weather and timing FAQ | `bernina-st-moritz-from-milan-faq` | planned | `bernina-red-train-and-st-moritz-from-milan` |

---

# Cluster: Milan Cooking Classes

- Cluster parent slug: `milan-cooking-classes`
- Primary conversion asset: `pizza-and-gelato-cooking-class-in-milan`
- Website booking URL: `{{WebsiteLink}}`
- Viator URL: https://www.viator.com/tours/Milan/Pizza-and-Gelato-Cooking-Class-in-Milan-Small-Group-Only/d512-187808P65
- TripAdvisor URL: https://www.tripadvisor.com/AttractionProductReview-g187849-d28041866-Pizza_and_Gelato_Cooking_Class_in_Milan_Small_Group_Only-Milan_Lombardy.html
- Last updated: 2026-05-12
- Next recommended generation: MOFU comparison-post — Cooking class vs food tour in Milan

## Asset inventory

| Required | Funnel stage | Cluster role | Target angle/title | Package slug | Status | Next step link target |
| --- | --- | --- | --- | --- | --- | --- |
| Yes | BOFU | `main-booking-post` | Pizza and Gelato Cooking Class in Milan - Small Group Only | `pizza-and-gelato-cooking-class-in-milan` | ready_for_review | viator |
| Yes | MOFU | `comparison-post` | Milan: cooking class vs food tour — which to choose | `milan-cooking-class-vs-food-tour` | planned | `pizza-and-gelato-cooking-class-in-milan` |
| Yes | TOFU | `destination-guide` | Best things to do in Milan in the evening | `best-things-to-do-in-milan-evening` | planned | `pizza-and-gelato-cooking-class-in-milan` |
| Yes | FAQ | `faq-support-post` | What to expect from a small-group cooking class in Milan | `milan-cooking-class-faq` | planned | `pizza-and-gelato-cooking-class-in-milan` |
