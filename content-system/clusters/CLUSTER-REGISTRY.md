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
- `ready_for_sync`
- `needs_live_verification`
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

_No clusters registered yet. Add the first cluster when the first content package is generated._
