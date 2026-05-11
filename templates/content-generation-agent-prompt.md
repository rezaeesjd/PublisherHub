# Content Generation Agent Prompt

Use this prompt when asking an AI/Codex agent to generate or update tour content packages.

## Required command
Start with one of these commands:

- `WPS:GENERATE_CONTENT` — create or update one content package
- `WPS:GENERATE_AND_PUBLISH` — generate, validate, and prepare for publishing
- `WPS:FIX_PACKAGE` — fix an existing package
- `WPS:PUBLISH_BLOG` — validate and publish-prep an existing package

## Required funnel instruction
Always specify the intended funnel stage and content role.

Use this format:

```text
Funnel stage: BOFU
Cluster role: main-booking-post
Cluster parent: cinque-terre-full-day-tour-from-milan
Cluster next step: {{WebsiteLink}}
Cluster linking priority: link-to-booking
```

## Allowed funnel stages

### TOFU
Use when the content is for early-stage travelers researching broad ideas, destinations, timing, or things to do.

Good for:
- destination guides
- first-time visitor guides
- broad trip-planning posts
- seasonal inspiration

Example:
```text
Funnel stage: TOFU
Cluster role: destination-guide
Cluster parent: cinque-terre-full-day-tour-from-milan
Cluster next step: {{SuggestedMofuAsset}}
Cluster linking priority: link-to-mofu
```

### MOFU
Use when the traveler already knows the destination or activity and is comparing options.

Good for:
- guided tour vs DIY
- destination A vs destination B
- private vs group tour
- one-day itinerary comparisons
- direct booking vs OTA decision support

Example:
```text
Funnel stage: MOFU
Cluster role: comparison-post
Cluster parent: cinque-terre-full-day-tour-from-milan
Cluster next step: cinque-terre-full-day-tour-from-milan
Cluster linking priority: link-to-bofu
```

### BOFU
Use when the traveler is close to booking a specific tour.

Good for:
- main booking-intent post
- landing-blog hybrid
- direct tour page support content
- high-intent tour keywords

Example:
```text
Funnel stage: BOFU
Cluster role: main-booking-post
Cluster parent: cinque-terre-full-day-tour-from-milan
Cluster next step: {{WebsiteLink}}
Cluster linking priority: link-to-booking
```

### FAQ
Use when the content removes practical booking objections or answers a specific pre-booking question.

Good for:
- can you visit X in one day?
- what is included?
- how pickup works?
- what to know before booking?

Example:
```text
Funnel stage: FAQ
Cluster role: faq-support-post
Cluster parent: cinque-terre-full-day-tour-from-milan
Cluster next step: cinque-terre-full-day-tour-from-milan
Cluster linking priority: link-to-bofu
```

## Required meta.json cluster fields
The agent must populate these fields in `meta.json` for every generated package:

```json
{
  "cluster_parent": "{{BaseTourSlug}}",
  "cluster_type": "{{TOFU|MOFU|BOFU|FAQ|SUPPORT|LANDING}}",
  "cluster_role": "{{ClusterRole}}",
  "cluster_next_step": "{{NextStepSlugOrPlaceholder}}",
  "cluster_previous_step": "{{PreviousStepSlugOrEmpty}}",
  "cluster_sibling_assets": [],
  "cluster_primary_conversion_asset": "{{BaseTourSlug}}",
  "cluster_linking_priority": "{{link-to-mofu|link-to-bofu|link-to-faq|link-to-booking|link-to-related-guide|balanced}}",
  "cluster_notes": "{{Short explanation of the asset's purpose in the funnel}}"
}
```

## Internal linking requirements
The agent must write `internal-links.md` using this funnel logic:

- TOFU assets must link toward one MOFU asset and optionally the BOFU asset.
- MOFU assets must link toward the BOFU/main booking asset.
- BOFU assets must link to the booking URL and optionally to FAQ/support content.
- FAQ assets must link back to the BOFU/main booking asset.

If the destination asset does not exist yet, use a safe placeholder such as:

- `{{SuggestedTofuAsset}}`
- `{{SuggestedMofuAsset}}`
- `{{SuggestedBofuAsset}}`
- `{{SuggestedFaqAsset}}`

Do not invent final URLs or slugs.

## Variant/version instruction
Use variants only when generating another version of an already-approved package for the same tour.

Variant prompt format:

```text
Create a new content variant for this tour.
Base package: cinque-terre-full-day-tour-from-milan
Variant angle: MOFU comparison / train vs guided tour
Funnel stage: MOFU
Cluster role: comparison-post
Keep all source facts identical unless new confirmed facts are provided.
Change only the keyword angle, title, hook, structure, FAQ angle, CTA wording, and internal-link path.
```

## Example full prompt

```text
WPS:GENERATE_CONTENT

Tour/topic: Cinque Terre Full-Day Tour from Milan

Funnel stage: MOFU
Cluster role: comparison-post
Cluster parent: cinque-terre-full-day-tour-from-milan
Cluster next step: cinque-terre-full-day-tour-from-milan
Cluster linking priority: link-to-bofu
Variant angle: train vs guided tour comparison

Goal:
Create a decision-support article for travelers comparing whether to visit Cinque Terre from Milan by train or by guided tour. The content should move users toward the main BOFU booking asset.

Use the existing WebPublisherSystem rules.
Create/update all required package files.
Populate all cluster metadata fields in meta.json.
Write internal-links.md according to the funnel path.
Do not invent tour facts, pricing, reviews, ratings, meeting points, inclusions, or cancellation rules.
Use website booking as primary CTA when available; otherwise use OTA fallback according to system rules.
```
