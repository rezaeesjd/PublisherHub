# Cluster Metadata Standard

This standard makes tour content clusters machine-readable so AI generation, QA, publishing, and future internal-link automation can understand how each post fits into the booking funnel.

## Required metadata fields

Every tour content package `meta.json` should include these cluster fields.

```json
{
  "cluster_parent": "cinque-terre-full-day-tour-from-milan",
  "cluster_type": "MOFU",
  "cluster_role": "comparison-post",
  "cluster_next_step": "cinque-terre-full-day-tour-from-milan",
  "cluster_previous_step": "best-day-trips-from-milan",
  "cluster_sibling_assets": [
    "cinque-terre-from-milan-train-or-tour",
    "lake-como-vs-cinque-terre-from-milan"
  ],
  "cluster_primary_conversion_asset": "cinque-terre-full-day-tour-from-milan",
  "cluster_linking_priority": "link-to-bofu",
  "cluster_notes": "MOFU comparison article designed to move travelers from DIY research toward the main BOFU tour asset."
}
```

## Field definitions

### `cluster_parent`
The canonical base tour/package slug that this content asset supports.

Examples:
- `cinque-terre-full-day-tour-from-milan`
- `lake-como-bellagio-lugano-tour-from-milan`

For the main BOFU asset, `cluster_parent` should usually equal its own base slug.

### `cluster_type`
The funnel stage of the asset.

Allowed values:
- `TOFU`
- `MOFU`
- `BOFU`
- `FAQ`
- `SUPPORT`
- `LANDING`

Usage:
- `TOFU`: broad discovery/informational content
- `MOFU`: comparison or decision-support content
- `BOFU`: high-intent booking/conversion content
- `FAQ`: practical question/objection-removal content
- `SUPPORT`: supporting guide, logistics, seasonal, or refresh content
- `LANDING`: primary tour landing page if separated from blog-style BOFU

### `cluster_role`
The specific role of the content asset inside the cluster.

Allowed values:
- `main-booking-post`
- `tour-landing-page`
- `comparison-post`
- `destination-guide`
- `itinerary-guide`
- `faq-support-post`
- `seasonal-post`
- `trust-proof-post`
- `direct-booking-support-post`
- `refresh-update`

### `cluster_next_step`
The next content asset this post should naturally send users to.

Examples:
- TOFU asset -> MOFU comparison asset
- MOFU asset -> BOFU booking asset
- FAQ asset -> BOFU booking asset
- BOFU asset -> booking URL or FAQ support asset when useful

Use a slug when the next asset exists. Use a placeholder when it does not exist yet:
- `{{SuggestedMofuAsset}}`
- `{{SuggestedBofuAsset}}`
- `{{SuggestedFaqAsset}}`

### `cluster_previous_step`
The asset that should commonly send users into this asset.

Examples:
- MOFU post previous step may be a TOFU guide.
- BOFU post previous step may be a MOFU comparison post.

Use an empty string when unknown.

### `cluster_sibling_assets`
Array of related package slugs in the same tour cluster.

Use existing slugs when known. Use placeholders or leave empty when no siblings exist yet.

### `cluster_primary_conversion_asset`
The main BOFU or landing asset that should receive the strongest conversion traffic.

For most clusters this is the base tour package slug.

### `cluster_linking_priority`
The automatic internal-linking behavior expected for this asset.

Allowed values:
- `link-to-mofu`
- `link-to-bofu`
- `link-to-faq`
- `link-to-booking`
- `link-to-related-guide`
- `balanced`

### `cluster_notes`
Short human-readable note explaining why this asset exists and how it should support bookings.

## Minimum valid examples

### BOFU main booking asset
```json
{
  "cluster_parent": "cinque-terre-full-day-tour-from-milan",
  "cluster_type": "BOFU",
  "cluster_role": "main-booking-post",
  "cluster_next_step": "{{WebsiteLink}}",
  "cluster_previous_step": "{{SuggestedMofuAsset}}",
  "cluster_sibling_assets": [],
  "cluster_primary_conversion_asset": "cinque-terre-full-day-tour-from-milan",
  "cluster_linking_priority": "link-to-booking",
  "cluster_notes": "Primary booking-intent asset for travelers ready to reserve this tour."
}
```

### MOFU comparison asset
```json
{
  "cluster_parent": "cinque-terre-full-day-tour-from-milan",
  "cluster_type": "MOFU",
  "cluster_role": "comparison-post",
  "cluster_next_step": "cinque-terre-full-day-tour-from-milan",
  "cluster_previous_step": "{{SuggestedTofuAsset}}",
  "cluster_sibling_assets": ["{{RelatedComparisonAsset}}"],
  "cluster_primary_conversion_asset": "cinque-terre-full-day-tour-from-milan",
  "cluster_linking_priority": "link-to-bofu",
  "cluster_notes": "Decision-support article comparing options and moving users toward the main booking asset."
}
```

### TOFU discovery asset
```json
{
  "cluster_parent": "cinque-terre-full-day-tour-from-milan",
  "cluster_type": "TOFU",
  "cluster_role": "destination-guide",
  "cluster_next_step": "{{SuggestedMofuAsset}}",
  "cluster_previous_step": "",
  "cluster_sibling_assets": ["{{RelatedTofuAsset}}"],
  "cluster_primary_conversion_asset": "cinque-terre-full-day-tour-from-milan",
  "cluster_linking_priority": "link-to-mofu",
  "cluster_notes": "Broad discovery article designed to attract early-stage travelers and introduce the tour cluster."
}
```

### FAQ/support asset
```json
{
  "cluster_parent": "cinque-terre-full-day-tour-from-milan",
  "cluster_type": "FAQ",
  "cluster_role": "faq-support-post",
  "cluster_next_step": "cinque-terre-full-day-tour-from-milan",
  "cluster_previous_step": "{{SuggestedBofuAsset}}",
  "cluster_sibling_assets": [],
  "cluster_primary_conversion_asset": "cinque-terre-full-day-tour-from-milan",
  "cluster_linking_priority": "link-to-bofu",
  "cluster_notes": "Objection-removal content for travelers with practical pre-booking questions."
}
```

## Generation rule
When creating or updating any package, the AI must populate these cluster fields in `meta.json`. If the exact related asset is not known, use safe placeholders rather than inventing URLs or slugs.

## Publishing rule
Publishing/QA should warn when cluster fields are missing, invalid, or internally inconsistent with `funnel_stage`.
