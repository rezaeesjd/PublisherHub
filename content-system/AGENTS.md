# AGENTS.md

## Purpose
This repository section is used to generate, QA, and prepare SEO and conversion-focused content for tours, day trips, and local experiences.

WebPublisherSystem is an **Automated SEO & Social Content Marketing Platform**.

The ultimate business goal is **lead generation, customer acquisition, direct bookings, and growth** through content marketing automation.

Technically, it is a **marketing automation platform focused on organic lead generation through content operations**.

The current implementation is blog-first, but the wider platform direction includes reusable SEO content, landing pages, social posts, Google Business Profile posts, email/newsletter content, and multi-channel publishing workflows.

The main immediate business goal is to increase direct bookings on the website while still benefiting from OTA visibility on Viator and TripAdvisor.

The purpose of each post is to move the traveler one step closer to booking. Do not create content only to fill a blog.

---

## Command router: generation vs publishing
Codex must distinguish between **content generation** and **publishing**. These are not the same workflow.

Use the following command prefixes when they appear at the beginning of the user prompt.

### `WPS:GENERATE_CONTENT`
Use this when the user wants a new tour content package created from supplied tour information.

Expected result:
- create or update the tour folder inside `/WebPublisherSystem/content-system/tours/`
- generate all required content package files
- create `source-facts.md`
- create `qa-report.md`
- keep `publish_status` as `draft` or `ready_for_review`
- do **not** claim the blog is published
- do **not** claim the live archive or single post URL works unless actually verified

### `WPS:PUBLISH_BLOG`
Use this when the user wants an existing generated tour package to be prepared for publishing and verified.

Expected result:
- locate the existing tour folder
- validate all required files
- validate `meta.json`
- validate source facts, links, slug, and public article content
- update `qa-report.md`
- update `publish_status` only if the publish conditions are met
- do **not** rewrite the content package unless a specific issue requires it
- do **not** say “published” unless the front-end blog archive and single post page are confirmed to work

If the live server cannot be accessed or verified from the current environment, mark the final status as `ready_for_sync` or `needs_live_verification`, not `published`.

### `WPS:GENERATE_AND_PUBLISH`
Use this only when the user explicitly wants both workflows in one task.

Expected result:
1. run `WPS:GENERATE_CONTENT`
2. run `WPS:PUBLISH_BLOG`
3. produce or update `qa-report.md`
4. clearly state which parts are complete and which parts still require server sync or live verification

### `WPS:CLARIFY`
Use this when the supplied product input contains ambiguous values that materially affect public copy or `meta.json` (for example a number with no unit, a conflict between two product codes, or a label whose meaning is unclear).

Expected behavior:
- do **not** write public copy yet
- list each ambiguous field, its raw value, and a one-sentence question
- store the same list inside `meta.clarifications_needed` so QA can detect unresolved items (see `meta.schema.json`)
- ask the user to resolve them before falling back to `WPS:GENERATE_CONTENT`

`WPS:CLARIFY` is **automatic** inside `WPS:GENERATE_CONTENT` when the agent detects blocking ambiguities. The agent must follow the Intake Questions Protocol in the Enforcement Addendum (below): present the questions to the user via `AskUserQuestion` before generating any public copy, and stop generation until the user resolves the blockers, picks holding-notice mode, or explicitly approves provisional mode. Implicit precedent is never approval.

### No command prefix
If the prompt contains tour information but no command prefix, default to `WPS:GENERATE_CONTENT`. If ambiguous values are detected during that run, escalate to `WPS:CLARIFY` before producing public copy.

If the prompt asks only for strategy, template edits, system changes, QA, or workflow improvement, do not create a tour folder unless explicitly requested.

---

## Platform positioning
When describing the system or generating strategic notes, use this positioning:

- Product category: **Automated SEO & Social Content Marketing Platform**
- Technical category: **Marketing automation platform**
- Growth focus: **Organic lead generation through content operations**
- Business outcomes: **lead generation, customer acquisition, direct bookings, and growth**
- Current channel focus: **SEO blog publishing**
- Future channel expansion: landing pages, social media posts, Google Business Profile posts, email content, and multi-channel publishing

Do not describe the system as only a blog tool. The blog workflow is the first execution layer of a broader content marketing automation platform.

---

## Brand rules
The system brand is **Milano Adventures** unless the user explicitly changes the active system brand.

When generating public-facing content:
- use only the current system brand as the operator/brand name
- remove or avoid other supplier/OTA/operator branding from public content unless the user specifically instructs otherwise
- do not publish “Supplied by”, OTA supplier names, or competing/third-party brand names as the business identity
- if source data contains another supplier or brand name, record it in `source-facts.md` as a raw source fact, but do not use it as the public brand

When generating content:
- write in a friendly, clear, travel-focused brand voice
- prioritize trust, ease, and memorable local experiences
- keep the tone practical and persuasive rather than luxury-heavy or overly formal
- use the brand name naturally only where it helps trust or conversion

---

## Link handling rules
Use provided real links whenever they are supplied by the user.

### If final links are provided
If the user provides a real website booking URL, TripAdvisor URL, or Viator URL:
- store the real URLs in `source-facts.md`
- store the real URLs in `meta.json`
- use the real website booking URL as the primary CTA link in `blog-post.md`
- use TripAdvisor and Viator URLs only as secondary trust or alternate reference links
- do not replace real provided URLs with placeholders

### If final links are missing
Use placeholders only when real links are not provided:
- Website booking page: `{{WebsiteLink}}`
- TripAdvisor booking page: `{{TripAdvisorLink}}`
- Viator booking page: `{{ViatorLink}}`

If placeholders are used, `qa-report.md` must flag the post as not fully publish-ready until final links are added or intentionally approved.

Prefer the website link as the main CTA. TripAdvisor and Viator may be used only as secondary trust or alternate booking references when appropriate.

---

## Working model
The platform is named **WebPublisherSystem** when deployed (uploaded to web hosting at `/WebPublisherSystem/`). The source of this codebase lives in the `rezaeesjd/PublisherHub` repository, which mirrors the same internal layout without the `WebPublisherSystem/` prefix.

| Concept | Repository path (source) | Deploy path (web host) |
| --- | --- | --- |
| Platform code | `platform/` | `/WebPublisherSystem/platform/` |
| Generated tours | `content-system/tours/` | `/WebPublisherSystem/content-system/tours/` |
| Agent rules | `content-system/AGENTS.md` | `/WebPublisherSystem/content-system/AGENTS.md` |
| Meta schema | `content-system/meta.schema.json` | `/WebPublisherSystem/content-system/meta.schema.json` |
| Public blog | `blog/` | `/WebPublisherSystem/blog/` |
| Runtime data | `platform/data/` | `/WebPublisherSystem/platform/data/` |

When this file or another rule mentions a path beginning with `/WebPublisherSystem/`, treat it as the deploy path. When working in source, drop that prefix.

If a needed folder does not exist, create it.

---

## Wait rule
Do not generate a sample post or create a new tour folder until the user provides a tour topic or tour title.

If the user asks only for strategy, templates, QA, or system setup, provide or update the system files without generating a tour-specific content package.

---

## Tour folder creation rule
Create one folder inside `/WebPublisherSystem/content-system/tours/` for each **content package**. A "package" is one variant of one tour, not one tour. The system is intended to host **multiple content variants per tour** for SEO and conversion testing.

Folder name format:
- lowercase
- kebab-case
- based on the canonical tour title
- no special characters except hyphens

Example:
`Cinque Terre Full-Day Tour from Milan` → `/WebPublisherSystem/content-system/tours/cinque-terre-full-day-tour-from-milan/`

The folder name is a stable source/content identifier. The public URL slug may later be edited separately in the platform without renaming the folder.

### Multi-variant rule (hard rule)
The system is a **multi-content publisher**: each tour is expected to grow a cluster of content variants over time (BOFU landing, day-trip BOFU, comparison MOFU, informational TOFU, seasonal/FAQ, etc.).

When a `WPS:GENERATE_CONTENT` (or `WPS:GENERATE_CONTENT_FROM_INTAKE`) request maps to a tour whose **base package already exists** under `content-system/tours/<base-slug>/` and that package is **approved** (i.e., it has moved past `draft`), the agent must not overwrite it. Approved means:

- `meta.json.publish_status` ∈ `{"ready_for_review", "ready_for_sync", "needs_live_verification", "published"}`

A package in `publish_status: draft` is **iterable**: a follow-up `WPS:GENERATE_CONTENT` may continue to refine the draft in place, even when `public_copy_state == "final"` and `generation_phase_completed == true`. Drafts have not yet been approved by a human reviewer; forking a `-v<N>` for every draft re-run would break normal iterative correction. Variant routing is reserved for tours where someone has already endorsed the prior package.

When the base package is approved per the predicate above, the agent must create a **new variant package** in a sibling folder using the slug pattern:

```
<base-slug>-v<N>
```

…where `<N>` is the smallest positive integer such that the resulting folder does not already exist (the base package itself is implicitly `v1`; the first explicit variant is therefore `-v2`).

The new variant package must:

1. Use the same `canonical_tour_title` as the base package.
2. Use a `slug` equal to the new folder name.
3. Use a `public_slug` that does not collide with any other package's `public_slug` (uniqueness is enforced by `platform/post-overrides.php::wps_public_slug_in_use`). Prefer a keyword-meaningful public slug (e.g., `cinque-terre-day-trip-from-milan-five-villages`), not the mechanical `-v<N>` suffix.
4. Set the variant linkage fields in `meta.json`:
   - `variant_of`: base package slug (string)
   - `variant_index`: integer ≥ 2
   - `variant_angle`: short human label (e.g., `"BOFU day-trip / five-villages keyword variant"`)
5. Differ from the base package only on `page_title`, `public_slug`, `primary_keyword`, hook paragraph, section ordering, FAQ angle, and CTA copy phrasing. Pricing, duration, departures, transport, languages, meeting points, and other source facts must remain identical across variants and must continue to trace to the same `source-facts.md` provenance rows.
6. Include its own `source-facts.md` (with a "Variant context" section that names the base package), `qa-report.md`, and the rest of the 9 required files. A variant package is not allowed to share files with the base package by reference.

Counter-cases:

- If the base package is in `publish_status: draft` (regardless of `public_copy_state` or `generation_phase_completed`), the request continues to update the existing package — do **not** fork `-v<N>` for a draft. Iterative draft refinement is the expected behavior.
- If the base package is in `publish_status: needs_fix` and the request is a re-generation to address the fixes, continue to update it in place. Do **not** fork `-v<N>`.
- If the user explicitly requests an in-place rewrite of an already-approved package, route to `WPS:FIX_PACKAGE` instead.
- The `-v<N>` mechanism applies to every tour, not just Cinque Terre.

The QA runner and the agent both treat overwriting an approved base package without explicit `WPS:FIX_PACKAGE` routing as a hard failure.

### Variant inheritance handshake (hard rule)

When the multi-variant rule fires and the agent is about to create a `-v<N>` package, it must first run a **variant inheritance handshake** before any file is written:

1. Collect the base package's open warnings and `needs_human_review` rows from `meta.json.warnings[]` and the `Status` column of `source-facts.md`'s provenance matrix.
2. If any are present, batch them into a single `AskUserQuestion` call (one focused question per inherited gap, max four). Each option offered to the user must be one of:
   - **Inherit unchanged** — the variant copies the base's auto-resolved decision (current default if the user does not answer).
   - **Resolve now** — the user supplies the missing value; the variant records it as `confirmed` and removes the warning.
   - **Escalate to blocker** — the user wants this gap blocking; the variant adds a `clarifications_needed` entry with `blocking: true` and enters the hard clarify gate.
3. Record every answer in the new variant's `meta.json.inherited_warnings[]` array (one entry per gap, with `field`, `base_value`, `decision` ∈ `inherit | resolved | escalated`, and the resolved value when applicable).
4. Only after the handshake completes may the variant package proceed to file generation.

A variant created without an `inherited_warnings[]` array (when the base had open warnings) is non-compliant and must be flagged by the QA runner.

---

## Required files inside each tour folder
Each generated tour package must include these files:

1. `source-facts.md`
2. `brief.md`
3. `keywords.md`
4. `blog-post.md`
5. `faq.md`
6. `meta.json`
7. `internal-links.md`
8. `automation-notes.md`
9. `qa-report.md`

A generation task is incomplete if any of these files are missing.



### Clarify-mode required files
When blocking clarifications are open, generation must still keep a uniform package structure for automation.

In clarify mode, all 9 required files must exist, but 5 files may be deferred stubs:
- `brief.md`
- `keywords.md`
- `faq.md`
- `internal-links.md`
- `automation-notes.md`

Stub format:

```md
# Deferred (Clarification Required)
This file is intentionally deferred until blocking clarifications are resolved.
```

This resolves the hard-gate rule (do not generate dependent final content) while preserving machine-readable package completeness.

### Recommended (not required) files
- `CHANGELOG.md` — one bullet per refresh: date, command (`WPS:GENERATE_CONTENT`, `WPS:PUBLISH_BLOG`, `WPS:CLARIFY`, `WPS:RELINK_CLUSTER`, etc.), what changed, who/what triggered it. Required (not just recommended) when the package is a variant: every `-v<N>` package must ship with a `CHANGELOG.md` whose first entry records the variant creation, the `variant_of` base slug, the `variant_index`, and the `variant_role` chosen. Use `templates/changelog-template.md` as the seed.
- `images/` folder — store hero and gallery assets here. Reference the hero from `meta.hero_image` (relative path, e.g. `images/hero.jpg`) and any others in `meta.image_gallery`.

The QA runner (`platform/qa.php`) raises a warning when these are missing or when `meta.hero_image` is unset but an `images/` folder exists.

---

## Master workflow for `WPS:GENERATE_CONTENT`
For each tour request, complete the work in this order.

### Step 0: Source fact extraction
Before writing marketing copy, extract the provided facts into `source-facts.md`.

`source-facts.md` must include:
- canonical tour title
- active system brand
- raw supplier/operator name if present in source data
- product/reference code if present
- website booking URL if provided
- TripAdvisor URL if provided
- Viator URL if provided
- price if provided
- duration
- start time or operating hours if provided
- meeting point
- end point
- itinerary stops and durations
- inclusions
- exclusions
- cancellation policy
- group size or traveler cap
- languages
- accessibility notes
- health/restriction notes
- review/rating data if provided
- source conflicts or missing facts
- facts that require human review

Do not write the public post until source facts are extracted.

### Step 1: Strategy
Define a clear and practical SEO and conversion strategy for the specific tour topic.

The strategy output must include:
- posting frequency recommendation for that tour or content cluster
- recommended content types
- funnel mapping across TOFU, MOFU, and BOFU
- internal linking strategy from blog content to tour pages and booking pages
- how Viator and TripAdvisor visibility can support direct bookings indirectly
- whether long-tail or higher-volume keywords should be prioritized first and why
- a repeatable production approach for scaling similar tour content

### Step 2: Keyword clustering
Generate a keyword cluster for the specific tour topic.

The keyword output must include these sections:
- Primary keyword: 1 high-intent keyword
- Long-tail booking-intent keywords: 5 to 10 keywords
- Informational keywords: 3 to 5 keywords
- Comparison keywords: 2 to 5 keywords when relevant
- Title variations: 4 to 8 title options

Group keywords by intent and label them clearly.

### Step 3: Reusable blog template
Use the standard public blog structure defined in this file.

### Step 4: Public post execution
Generate one fully optimized landing-blog hybrid post for the provided tour topic.

### Step 5: FAQ and supporting assets
Generate FAQ, internal linking plan, automation notes, and metadata.

### Step 6: QA report
Create `qa-report.md` and mark the package status honestly.

---

## Master workflow for `WPS:PUBLISH_BLOG`
Publishing means preparing and verifying the post for the front-end system. It is not just creating files.

Run these checks in order:

1. Confirm the tour folder exists.
2. Confirm all required files exist.
3. Confirm `meta.json` is valid JSON.
4. Confirm required `meta.json` fields exist.
5. Confirm the public slug is valid and unique among tour packages, or flag a conflict.
6. Confirm `blog-post.md` contains only public-facing article content.
7. Confirm admin-only labels are not visible in `blog-post.md`.
8. Confirm direct website CTA exists.
9. Confirm provided real URLs are used when available.
10. Confirm placeholder links are flagged if real links are missing.
11. Confirm source facts were not invented.
12. Confirm any review/rating claim is supported by provided review/rating data.
13. Confirm TripAdvisor and Viator are secondary trust/reference links, not the primary CTA.
14. Update `qa-report.md` with pass/fail items.
15. Update `publish_status` only according to the status rules below.

Do not say the post is published unless the live archive and single post page are actually verified.

---

## Publish status rules
Use only these statuses in `meta.json`:

- `draft` — generated but not reviewed
- `ready_for_review` — generated and internally complete, but not approved
- `needs_fix` — QA found issues
- `ready_for_sync` — content is approved in GitHub/local files but server sync is still needed
- `needs_live_verification` — server/live front-end could not be verified from the current environment
- `published` — live archive and single post page are verified

Default status after `WPS:GENERATE_CONTENT`:

```json
"publish_status": "draft",
"human_review_required": true
```

Do not set `publish_status` to `published` unless the live front-end blog archive and single post page were checked and confirmed.

---

## Strategy rules
- Prioritize conversions over traffic volume.
- Prioritize long-tail, booking-intent keywords before broad informational keywords.
- Use a hybrid publishing model by default: 1 booking-intent post per week, 1 informational or comparison post per week, and 1 weekly refresh of older pages.
- Build content around a repeatable cluster for each tour:
  - 1 main landing page or landing-blog hybrid
  - 2 comparison posts
  - 2 informational posts
  - 1 seasonal, FAQ, or support post
- Only recommend daily posting when there is enough tour inventory, destination breadth, or operational capacity to maintain quality.
- Tie every strategy recommendation back to lead generation, customer acquisition, bookings, or growth.

---

## Funnel rules
### TOFU
Use informational content for travelers researching destinations, timing, options, and general trip ideas.

### MOFU
Use comparison and decision-support content for travelers comparing routes, transport options, tour styles, timing, and value.

### BOFU
Use highly commercial content for travelers ready to book a specific experience.

---

## SEO rules
For each tour request generate:
- 1 primary keyword with booking intent
- 5 to 10 long-tail booking-intent keywords
- 3 to 5 informational keywords
- 2 to 5 comparison keywords when relevant
- 4 to 8 title variations with clear angles
- 1 short keyword-optimized slug
- 1 page title
- 1 meta description

---

## Title rules
- Put the primary keyword near the beginning of the title when natural.
- Add a clear benefit or conversion angle, such as easy, guided, full-day, stress-free, scenic, private, small-group, or direct booking.
- Avoid very long titles.
- Avoid overusing words like “best”, “top-rated”, or “number one” unless the user provides proof.
- Do not make exaggerated or unverifiable claims.

## Title variation rules
Include title variations across multiple angles when possible:
- direct booking angle
- convenience or stress-free angle
- comparison angle
- seasonal or timing angle
- value or experience angle

---

## Public blog post rules
The main post must be a short, high-converting landing-page hybrid, not a long generic blog article.

`blog-post.md` must contain only public-facing article content that can safely appear to a traveler on the front end.

Do **not** put these admin/SEO labels inside the public article body:
- “Page Title”
- “URL Slug”
- “Meta Description”
- “Internal Linking Suggestions”
- “Primary Keyword”
- “Funnel Stage”
- “CTA Primary Link”

Those belong in `meta.json`, `keywords.md`, or `internal-links.md`, not in the public article body.

### H1 rule
The public article must have one clear H1.

In `blog-post.md`, use a Markdown H1 such as:

```md
# Lake Como, Bellagio & Lugano Tour from Milan with Scenic Cruise
```

The platform renderer converts this to an HTML `<h1>` on the front end. Do not write a visible label like “H1”.

### Public article structure
Each `blog-post.md` should use this public-facing order:

1. H1 title
2. short hook paragraph
3. main value section
4. “Who this tour is best for” or equivalent
5. “What to expect” or equivalent booking-confidence section
6. soft CTA
7. practical “What to know before booking” section
8. optional verified social proof section, only if review/rating data was provided
9. strong CTA block

Internal links and SEO metadata should not be displayed inside the public article body unless they are written as natural traveler-facing links.

### Length and style rules
- Target length for a final main post: approximately 500 to 900 words unless the user requests otherwise.
- A holding-notice `blog-post.md` is short (≤150 words) and uses the structure in `templates/holding-notice-template.md`.
- The hook paragraph should be short, emotionally clear, and conversion-aware.
- The hook should also be suitable for reuse as a short summary or meta-style introduction.
- The content should be easy to scan, with relatively short paragraphs and subheadings.
- The post should feel like a blog and landing page hybrid.
- Avoid long generic destination history unless directly useful for booking decisions.

### Brand-mention rule
- The active system brand (`Milano Adventures` by default) must appear at least once in `blog-post.md`, written naturally — typically in the hook paragraph or the strong-CTA block.
- This applies to both final and holding-notice modes. The QA runner emits `brand-missing` when the brand name is absent.

---

## Conversion checklist
Every generated `blog-post.md` must include:
- one direct website CTA in the first half of the post or immediately after the main value section
- one strong website CTA near the end
- booking confidence details from the product input, such as duration, meeting point, group type, included items, excluded items, languages, or accessibility notes
- a clear “who this tour is best for” or equivalent section
- a clear “what to know before booking” or equivalent section
- direct website booking as the preferred action
- TripAdvisor and Viator only as secondary trust or alternate booking references when useful

---

## Review and social proof rules
If verified review/rating data is provided by the user, use it carefully to improve trust and conversions.

Allowed when provided:
- mention the rating if the source data includes it
- mention the review count if the source data includes it
- include a short review quote or paraphrase if the review text is provided
- add a short “Traveler feedback” or “Why travelers like this tour” section

Not allowed:
- do not invent reviews
- do not invent review counts
- do not invent ratings
- do not claim “top-rated”, “best”, or “most popular” unless the source supports that exact claim
- do not overstate one review as broad market proof

If only one review is provided, phrase it carefully, for example:

```text
A recent traveler review highlighted the scenic cruise, smooth organization, and the contrast between Bellagio and Lugano.
```

---

## Writing rules
- Write for real travelers, not search engines.
- Keep the tone friendly, persuasive, clear, and natural.
- Avoid keyword stuffing and generic filler.
- Avoid robotic phrasing.
- Focus on practical decision-making and booking confidence.
- Keep paragraphs relatively short and easy to scan.
- Favor clarity and usefulness over content length.
- Do not create content simply to publish something. Each section should help the traveler understand, compare, trust, or book the tour.
- Remember that the content exists to support lead generation, customer acquisition, bookings, and growth.

---

## Trust and OTA positioning
- Encourage direct booking on the website first.
- OTA platforms such as Viator and TripAdvisor may be referenced only as trust signals, review sources, or secondary discovery channels.
- Do not position OTAs as the primary conversion path unless explicitly requested.
- Where useful, mention that travelers may also recognize the business from trusted marketplaces, but the preferred booking action should remain the website.

---

## Internal linking rules
Each content asset should suggest links to:
- the main tour page
- one related blog or guide
- one FAQ or destination information page
- one booking or contact page

Preferred flow:
`informational post -> comparison post -> tour page -> booking`

### Internal URL safety
- Do not invent final internal URLs unless the user provided them.
- If the real internal URL is unknown, use a clear placeholder such as `{{MilanDayTripsHubLink}}`, `{{RelatedGuideLink}}`, `{{ComparisonPostLink}}`, or `{{ContactPageLink}}`.
- If suggesting a page that does not exist yet, label it as a “Suggested future page”.
- The main booking link should use the final website URL when provided, otherwise `{{WebsiteLink}}`.

---

## Content safety rules
- Do not invent facts.
- Do not invent pricing, durations, inclusions, departure times, meeting points, review counts, or ratings.
- If required business details are missing, use clearly labeled placeholders.
- Do not make unverifiable claims such as “best in the city” unless supported by provided evidence.
- Do not fabricate review quotes, rankings, awards, or customer numbers.

---

## Source-facts-only rules for tours
Only mention the following details if they are provided in the product input or by the user:
- exact itinerary stops
- tour duration
- meeting point or pickup rules
- end point
- included items
- excluded items
- group size or private/shared status
- languages
- accessibility notes
- health restrictions
- weather or seasonal limitations
- guide/driver details
- booking links
- review/rating data

If a detail is missing, do not guess it. Use a placeholder or omit it.

When the product input contains both a broad marketing description and a more specific structured itinerary, follow the structured itinerary for exact claims.

---

## File-specific output rules
### `source-facts.md`
Must extract and organize raw facts before content writing.

Required sections:
- Tour identity
- URLs and booking links
- Logistics
- Itinerary
- Inclusions and exclusions
- Policies and restrictions
- Review/rating data
- Brand handling notes
- Missing inputs
- Facts requiring human review

### `brief.md`
Must summarize:
- tour title
- traveler intent
- target funnel stage
- conversion goal
- business positioning
- assumptions and missing inputs

### `keywords.md`
Must include clearly labeled sections for:
- primary keyword
- long-tail booking-intent keywords
- informational keywords
- comparison keywords when relevant
- title variations

### `blog-post.md`
Must contain only public-facing article content and follow the public article rules above.

### `faq.md`
Must include practical pre-booking questions and answers that help reduce hesitation.

### `meta.json`
The full schema lives at `content-system/meta.schema.json` and is the source of truth for required fields, enums, and patterns. The QA runner (`platform/qa-rules.php`) validates every `meta.json` against it.

Required fields (see schema for full list):
- brand
- canonical_tour_title
- page_title
- slug
- public_slug
- meta_description
- primary_keyword
- funnel_stage
- cta_primary
- cta_primary_link
- website_link
- publish_status
- human_review_required
- qa_status

Use real URLs when provided. Use placeholders only when links are missing.

Default after a clean generation (no blocking clarifications):

```json
"publish_status": "draft",
"human_review_required": true,
"qa_status": "pending",
"public_copy_state": "final",
"intake_questions_resolved": true
```

Default after generation when the hard clarify gate is active:

```json
"publish_status": "draft",
"human_review_required": true,
"qa_status": "needs_clarification",
"public_copy_state": "holding_notice",
"intake_questions_resolved": false
```

When `WPS:CLARIFY` finds ambiguities, populate `clarifications_needed` (see schema). Every entry there is treated as a `fail` finding by the QA runner unless it is explicitly marked `"blocking": false`, in which case it is a `warn`.

### `internal-links.md`
Must suggest internal links by page type and explain why each suggested link matters. Use placeholders or “Suggested future page” labels when final URLs are not provided.

### `automation-notes.md`
Must explain:
- how to reuse this structure weekly or daily
- how to scale production with AI assistance
- how to keep the design and section order consistent
- how to adapt the same template to similar tour topics
- how the content supports lead generation, customer acquisition, bookings, and growth

### `qa-report.md`
Must include:
- content package file checklist
- source-facts checklist
- JSON validation checklist
- public content cleanliness checklist
- link handling checklist
- conversion checklist
- review/social proof checklist
- source-facts-only checklist
- publish readiness status
- issues found
- recommended fixes
- final status

---

## QA report checklist
Every `qa-report.md` must check the following.

### File checks
- all required files exist
- filenames are correct
- folder name is valid kebab-case

### Metadata checks
- `meta.json` is valid JSON
- required fields exist
- slug/public slug is valid
- publish status is correct
- human review flag is correct

### Source fact checks
- source facts are extracted
- facts used in content appear in the source data
- missing facts are listed
- supplier/third-party branding is not used as the public brand

### Public article checks
- `blog-post.md` does not expose admin-only labels
- public H1 is present
- no “Page Title”, “URL Slug”, “Meta Description”, or “Internal Linking Suggestions” labels appear in public article body
- CTA exists in first half of post
- strong CTA exists near the end

### Link checks
- provided real URLs are used when available
- placeholders are used only when links are missing
- placeholders are flagged as not fully publish-ready
- website link is primary CTA
- TripAdvisor/Viator are secondary references only

### Review/social proof checks
- no invented review/rating claims
- provided review/rating data is used accurately if used
- one review is not overstated as broad proof

### Publish checks
- archive visibility checked if possible
- single post URL checked if possible
- if live verification is impossible, status is `needs_live_verification` or `ready_for_sync`, not `published`

---

## Reusability and layout rules
- Keep structure consistent across all generated posts for easy CMS upload.
- Reuse the same CTA block pattern unless the user asks for a variation.
- Favor a consistent design system with identical sections and only tour-specific content swapped in.
- Keep the same section order across posts unless the user requests a different structure.

---

## CTA template
### Standard CTA block
**Title:** Ready to book your [tour name]?

**Line:** See availability, full details, and secure your spot directly on our website.

**Button:** Check Availability

### Soft CTA example
See the full tour details, inclusions, and current availability before you book.

Use the final website booking URL when provided. Use `{{WebsiteLink}}` only when the real URL is missing.

---

## Automation guidance rules
When generating automation notes, include guidance on:
- batching similar tour topics together
- using reusable prompts and CMS fields
- using a fixed page layout or section template
- scheduling posts and refreshes consistently
- refreshing high-value pages regularly instead of publishing only new pages
- preserving human review before publishing factual business details
- expanding the blog-first system later into landing pages and social media content once the blog workflow is proven

---

## Image and photo gallery rules
Image assets are optional in this phase but should follow a fixed convention so future automation can wire them up without re-organizing folders.

Convention:
- store images inside `<tour-folder>/images/`
- reference one hero image at `meta.hero_image` (e.g. `images/hero.jpg`) — relative to the tour folder, or a full HTTPS URL
- reference any additional gallery images at `meta.image_gallery` as an array of relative paths or URLs
- raw photo links from suppliers (e.g. Google Drive folders) belong only in `source-facts.md` under "URLs and booking links" or a similar internal section, not in public copy

The QA runner warns when an `images/` folder exists but `meta.hero_image` is unset, or when `meta.hero_image` points to a file that is not on disk.

If no usable image asset exists, omit `meta.hero_image` entirely rather than pointing it at a placeholder.

---

## Clarification protocol (`WPS:CLARIFY`)
When the supplied product input contains a value whose meaning is ambiguous and that value affects public copy or `meta.json`, do not guess. Run the clarification protocol instead.

Examples of ambiguous values worth flagging — **only when they would otherwise enter public copy or `meta.json`'s typed fields**:
- a brand or supplier name that conflicts with the active system brand
- inclusions/exclusions that contradict each other in ways that change what the buyer pays for
- conflicting durations or meeting points that change what the post promises
- review/rating numbers that disagree across sources

Do **not** flag (handle automatically per the non-blocking table in the Enforcement Addendum):
- a number with no unit (`9` for cancellation) — exclude from public copy
- a count with no label (`15`) — ignore
- multiple product codes from different channels — treat as channel-specific
- a date with no role — omit from public copy
- a missing website booking URL when an OTA URL exists — use OTA as primary CTA
- a truncated title — derive a clean title
- itinerary scope mismatch between title and description — pick the broader, higher-conversion scope
- missing wheelchair accessibility — omit, warn only

Behavior:
1. Ask the user one focused question per ambiguous value before generating public copy. Use the smallest set of questions that unblocks generation.
2. Record each unresolved item as an entry in `meta.clarifications_needed`:
   ```json
   "clarifications_needed": [
     {
       "field": "cancellation_window_hours",
       "raw_value": "9, Relatively to Start Time",
       "question": "Is the 9 cancellation window in hours or days?",
       "blocking": true
     }
   ]
   ```
3. Default `blocking` to `true`. Use `false` only when the ambiguity does not block publish (it will then surface as a QA `warn`, not `fail`).
4. After the user resolves an item, remove it from `clarifications_needed` and update the relevant typed field (e.g. set `cancellation_window_hours: 24`).
5. Never silently choose one interpretation. Either resolve the clarification or record it as pending.

The QA runner blocks publish (`fail`) on every pending blocking clarification.

---

## Definition of done: content generation
A `WPS:GENERATE_CONTENT` task is complete only when:
- the correct tour folder is created in `/WebPublisherSystem/content-system/tours/`
- all 9 required files are generated
- filenames follow the naming convention
- `source-facts.md` exists and is completed
- `qa-report.md` exists and is completed
- `meta.json` is valid
- content follows the workflow in this file
- the post supports direct booking as the primary goal
- the conversion checklist is satisfied
- final internal URLs are not invented; unknown URLs use placeholders or are labeled as suggested future pages
- provided real booking/OTA URLs are not lost
- public article content is clean and does not show admin/SEO labels
- the output supports the broader platform goal of organic lead generation through content operations

## Definition of done: publishing
A `WPS:PUBLISH_BLOG` task is complete only when:
- the generated package passes QA
- `publish_status` is updated honestly
- the post is synced or marked `ready_for_sync`
- the archive and single post are verified if live access is available
- the post is not called “published” unless the live archive and single post URL work

If live verification is not possible, the correct final status is `needs_live_verification`, not `published`.


---

## Additional command router rules (system-level)
These command meanings are strict and should be treated as source-of-truth behavior.

### `WPS:PROCESS_QA`
Use this to perform process/result analysis only.

Required behavior:
- do not modify files
- do not generate or rewrite package content
- produce a structured QA/process report with findings and recommendations

### `WPS:FIX_PACKAGE`
Use this to repair one existing tour package under `content-system/tours/<tour-folder>/`.

Required behavior:
- do not create a duplicate tour folder
- do not treat this as system redesign
- update package files only as needed for compliance
- maintain honest publish status and QA findings

### `WPS:IMPROVE_SYSTEM_WORKFLOW`
Use this for system-level improvements only.

Required behavior:
- improve instructions, workflow docs, templates, and checklists
- do not modify tour packages under `content-system/tours/`
- do not claim package publish success from this command

### `WPS:LIVE_VERIFY`
Use this only to verify live front-end behavior.

Required behavior:
- check blog archive visibility
- check single post URL render
- verify CTA links render
- do not generate or rewrite content
- update/report status only based on actual live checks

---

## Strict content vs system boundary
`WPS:GENERATE_CONTENT`, `WPS:GENERATE_CONTENT_FROM_INTAKE`, `WPS:FIX_PACKAGE`, and `WPS:PUBLISH_BLOG` produce **content-only** PRs. Their commits and PRs must touch only files under `content-system/tours/<slug>/`.

System rule changes — `AGENTS.md`, `COMMANDS.md`, `WORKFLOW.md`, `QA-CHECKLIST.md`, `templates/`, `structures/`, `meta.schema.json`, and `platform/` — belong in a separate PR routed through `WPS:IMPROVE_SYSTEM_WORKFLOW` or `WPS:IMPLEMENT_GENERATION_PROCESS_IMPROVEMENTS`.

When a single user message asks for both a tour package update *and* a system rule change, the agent must split the work into two PRs (the system PR first, the content PR second so the new rule applies to the new content). Bundling them is a hard failure.

## Strict generation vs publish boundary
Content package generation and publish verification are separate gates.

### Generation gate (`WPS:GENERATE_CONTENT`)
Generation is complete when package files are created and QA artifact exists. It does **not** mean live publish.

### Publish gate (`WPS:PUBLISH_BLOG` or `WPS:LIVE_VERIFY`)
A post can be called **published** only if:
1. package passes QA,
2. content is synced/deployed,
3. archive page visibly lists the post,
4. single post URL opens correctly,
5. CTA links render correctly.

Never call a post published before all live checks are verified.

---

## Source-facts precondition (hard rule)
Before writing `blog-post.md`, the agent must first create/update `source-facts.md`.
If this precondition is not met, generation is incomplete.

---

## Required package QA artifact (hard rule)
Every generated package must include `qa-report.md` with all required checks. Missing `qa-report.md` is an automatic failure.

---

## Publish statuses (allowed set)
Workflow policy allows only:
- `draft`
- `ready_for_review`
- `needs_fix`
- `ready_for_sync`
- `needs_live_verification`
- `published`

Status mapping:
- after generation: `draft` or `ready_for_review`
- QA failures: `needs_fix`
- approved but not synced: `ready_for_sync`
- live checks unavailable/unverified: `needs_live_verification`
- live verified archive + single post: `published`


## Enforcement Addendum (Hard Gates)

These rules are mandatory and override any softer guidance. They are intended to be enforced by both the agent and the QA runner (`platform/qa-rules.php`). When agent behavior and runner behavior disagree, the **runner wins**.

### Hard clarify gate

If `meta.clarifications_needed` contains any entry with `"blocking": true`, **public copy generation is blocked**.

There are exactly three lawful states under the hard gate:

1. **Resolve** — the user answers the blocking questions in the same session, the entries are removed from `clarifications_needed`, and generation proceeds normally.
2. **Holding notice** — the agent writes a minimal `blog-post.md` holding notice (see "Holding-notice mode" below), sets `public_copy_state: "holding_notice"`, and stops. Final public copy is regenerated on a later `WPS:GENERATE_CONTENT` run after the user resolves the blockers.
3. **Provisional mode** — the user **explicitly** authorizes provisional generation in chat (e.g., "go ahead in provisional mode"). The agent then sets `public_copy_state: "provisional"`, generates a draft article, and adds `provisional_mode: true` to `meta.json`. Provisional output is never `ready_for_review`.

The agent must **not** unilaterally choose option 3. Implicit precedent (e.g., "another tour folder did this") is **not** explicit approval. If the user has not chosen, the default is option 2 (holding notice).

#### Intake questions protocol

When blocking issues exist and the user has not yet answered them, the agent must:

1. Create/update `source-facts.md`, `meta.json`, and `qa-report.md`.
2. Present the blocking questions to the user using `AskUserQuestion` (or the closest available equivalent), batched into **at most four focused questions**. Each question must reference the field name and raw value so the user can answer without re-reading the source.
3. Wait for a reply before generating any further content beyond those three files. Do not proceed to `blog-post.md`, `faq.md`, `keywords.md`, `brief.md`, `internal-links.md`, or `automation-notes.md` until the user has either answered or chosen option 2 / option 3.
4. If `AskUserQuestion` is unavailable, end the chat reply with a clearly labeled **"Blocking clarifications — please answer to continue"** section listing the same questions, and stop generation.

#### Blocking clarification issue types

A clarification is **blocking** only when it prevents the agent from producing safe, conversion-ready public copy. The list is intentionally narrow.

Blocking:
- conflicting duration/timing that affects what the public post promises
- conflicting meeting point
- conflicting inclusions/exclusions that affect what the buyer is paying for
- missing **all** booking links (no website URL **and** no Viator/TripAdvisor/other OTA URL — i.e., zero possible primary CTA)
- unclear active brand
- conflicting OTA/source data that materially changes the product
- conflicting review/rating claims that would be published

Non-blocking (handle automatically — do **not** ask the user, do **not** add to `clarifications_needed[*].blocking=true`):

| Issue | Auto-resolution rule |
|---|---|
| Truncated canonical title (e.g. ends in `…` / `...`) | Derive a clean title from the longest unambiguous prefix plus the most descriptive scope from the description (e.g. "Cinque Terre Full-Day Tour from Milan"). Strip trailing ellipsis. Record the derived title in `source-facts.md` with status `inferred`. Never publish an ellipsis in `canonical_tour_title`, `page_title`, or H1. |
| Multiple product/reference codes from different channels (e.g. Viator `187808P82`, TripAdvisor `33344981`, supplier `187808P109`) | Treat as **channel-specific**, not a conflict. Map each code to its channel by URL domain in `channel_product_codes` (`viator`, `tripadvisor`, `getyourguide`, etc.). Use the supplier-provided code (or the first non-OTA code) as `product_reference_code`. If only OTA codes exist, pick the one matching the primary booking channel. |
| Missing direct website booking URL but at least one OTA booking URL is present | Use the highest-priority available booking URL as `cta_primary_link`, in this order: website → Viator → TripAdvisor → GetYourGuide → other. Set `cta_primary_channel` accordingly (`website` / `viator` / `tripadvisor` / etc.). Adjust CTA copy to match (e.g. "Book on Viator", "Reserve on TripAdvisor"). `website_link` keeps `{{WebsiteLink}}` as a placeholder and is recorded as a non-blocking warning, **not** a blocker. |
| Cancellation window without unit | Exclude cancellation specifics from public copy entirely. Do **not** mention the number, unit, or window. Record the raw value in `source-facts.md` with status `needs_human_review`. The post may say "see the booking page for the latest cancellation policy" but must not invent a number or unit. |
| Unlabeled numeric policy values (e.g. a bare `15`) | Ignore. Do not surface in public copy. Record the raw value in `source-facts.md` with status `needs_human_review` and move on. |
| Itinerary scope conflict between title and description (e.g. title names 2 towns, description names all 5) | Pick the more general / higher-conversion scope from the description (the broader tour). Use that as the canonical scope and align the title accordingly. Record the choice in `source-facts.md` with status `inferred`. |
| Wheelchair accessibility status missing | Omit from public copy. Record as `missing` in `source-facts.md`. Surface as a non-blocking warning in `qa-report.md`, never as a blocker. |
| Date with unclear role (e.g. `May 1, 2026`) | Omit from public copy. Record as `needs_human_review`. |
| Truncated free-text fields (general case) | Use the longest unambiguous portion. Strip trailing `…` / `...`. Mark `inferred`. |

The agent must never invoke the hard clarify gate for any item in the non-blocking table above. Use `AskUserQuestion` only for the blocking list.

#### When blocking issues exist

1. create/update `source-facts.md`
2. create/update `meta.json`
3. create/update `qa-report.md`
4. do **not** generate final `blog-post.md`, and do **not** populate `faq.md`, `keywords.md`, `brief.md`, `internal-links.md`, or `automation-notes.md` with content that depends on the unresolved fields
5. do **not** mark package `ready_for_review`
6. set `qa_status` to `needs_clarification`
7. set `public_copy_state` to `holding_notice` (or `provisional` only if explicitly approved)
8. keep `publish_status` as `draft` or `needs_fix`
9. ask the user using `AskUserQuestion` per the intake questions protocol above

### Standard intake questions (collect upfront when possible)

Before processing tour data, prefer to collect these fields explicitly. Whenever any of them is missing, blocking, or ambiguous in the supplied data, treat it as a blocking clarification.

- Active system brand (default: Milano Adventures)
- Direct website booking URL (**non-blocker** when at least one OTA URL is provided — auto-fallback per the non-blocking auto-resolution table below; recorded as a `meta.json.warnings[]` entry, not a `conversion_blockers[]` entry. A booking URL of *any* channel must exist; zero booking URLs is the only true blocker.)
- Primary product/reference code, plus any channel-specific codes (Viator, TripAdvisor, GetYourGuide, etc.)
- Cancellation window — number **and** unit (hours / days)
- Hotel pickup status (yes / no / optional add-on) when source data shows both a meeting point and a pickup feature
- Wheelchair accessibility (yes / no / not applicable)
- Languages (live guide / audio / written) and whether they are confirmed for every departure
- Review/rating data: rating, count, and source — or explicit "no review data available"

Tours that arrive with all of these fields can usually generate end-to-end without a clarification round.

### Provenance-to-claim binding

Every assertive sentence in `blog-post.md` (and in `faq.md`, `internal-links.md`, and `keywords.md` where applicable) must trace to a row in the provenance matrix in `source-facts.md`.

Practical rules:

- Marketing-flavored "obvious" facts (e.g., "UNESCO World Heritage Site", "iconic", "world-famous", "scenic", "world-class cuisine") count as claims and must first be added as a row in the provenance matrix with status `confirmed` and a real source — usually the supplier-provided product description.
- If a fact comes from the supplier description, capture it as `confirmed (User input — product description)` in the matrix before referencing it.
- Inferred facts (e.g., "end point same as starting point" derived from address equality) must be marked `inferred`, not `confirmed`.
- Removing a claim is always preferable to inventing a source for it.

### Brand-mention requirement

`blog-post.md` must mention the active system brand (default `Milano Adventures`) at least once in natural prose. The QA runner emits `brand-missing` when the brand name is absent. Holding-notice mode also satisfies this requirement when the brand appears in the holding paragraph.

### Holding-notice mode

When the hard gate is active, `blog-post.md` becomes a **holding notice** rather than a final article. The holding notice:

- has one Markdown H1 with the canonical tour title (or a near-equivalent)
- is short (≤150 words)
- mentions the active brand once
- contains no pricing, no cancellation window, no departure days, no specific durations, no specific itinerary order, no specific meeting-point operational details, and no claims that depend on unresolved clarifications
- may include OTA fallback links (TripAdvisor, Viator) when those URLs are real
- contains no admin/SEO labels (the public-cleanliness rules still apply)

A canonical example lives in `templates/holding-notice-template.md`. Use it directly.

### Provenance fail = generation incomplete

If any claim in `blog-post.md` cannot be traced to a row in `source-facts.md`, the generation is **incomplete**. Either remove the claim or add the supporting row. Do not ship.

### Exemplar compliance

Tour folders inside `content-system/tours/` are reference structures, not blanket permission to bypass current rules. If an older tour folder predates a current rule (for example, it ships with `{{WebsiteLink}}` and a full `blog-post.md`), it must contain an `EXEMPLAR_NOTES.md` file at its root that says, in plain language, which current rules it does not satisfy and that it is not a model. Agents must not cite an unmarked tour as precedent for bypassing a hard gate.

### qa_status enum (machine-checked)

Allowed values for `qa_status`:

- `pending` — generation in progress; no QA verdict yet
- `passing` — QA passes
- `warning` — non-blocking issues exist
- `needs_fix` — blocking issues exist that are not clarification-shaped
- `needs_clarification` — hard clarify gate is active; user must resolve blocking clarifications

These match `meta.schema.json` exactly. Do not invent values.

### WPS:GENERATE_CONTENT pre-copy gate (ordered)
Before writing `blog-post.md`, run this order:
1. extract source facts
2. run conflict + missing-input detection
3. determine whether `WPS:CLARIFY` is required
4. stop before public copy when blocking clarifications exist
5. continue only if no blockers exist or the user explicitly approves provisional generation mode

### Website link and CTA enforcement
- If real website booking URL is provided, store it in `source-facts.md` and `meta.json`, and use it as primary CTA in `blog-post.md`.
- Do not replace a real provided website URL with `{{WebsiteLink}}`.
- If website booking URL is missing **but** at least one real OTA booking URL is provided (Viator, TripAdvisor, GetYourGuide, etc.), use the highest-priority available URL as `cta_primary_link` per the order in the non-blocking auto-resolution table above. This is **not** a blocker. CTA copy must match the chosen channel (e.g. "Book on Viator", "Reserve on TripAdvisor").
- If website booking URL is missing **and** no OTA booking URL is provided either, then there is no possible primary CTA and that is a real blocker.
- Missing TripAdvisor/Viator URLs are warnings (non-blocking), but real provided OTA links must be preserved.

### Source-facts provenance matrix requirement
Every generated `source-facts.md` must include this table:

| Field | Raw value | Source | Status | Notes |
|---|---|---|---|---|

Allowed `Status` values:
- `confirmed`
- `missing`
- `conflicted`
- `inferred`
- `needs_human_review`
- `not_applicable`

Required provenance rows:
- active system brand
- raw supplier/operator name
- canonical tour title
- product/reference code
- website booking URL
- TripAdvisor URL
- Viator URL
- price
- duration
- start time
- meeting point
- end point
- itinerary stops
- itinerary durations
- inclusions
- exclusions
- languages
- accessibility
- traveler cap / group size
- cancellation policy
- seasonal/weather notes
- review rating
- review count
- review text/source
- missing critical inputs
- conflicts detected

### Machine-checkable phase markers
`meta.json` should include and maintain:
- `canonical_tour_title`
- `product_reference_code`
- `channel_product_codes`
- `website_link`
- `cta_primary_link`
- `generation_phase_completed`
- `clarify_phase_required`
- `clarify_phase_completed`
- `publish_phase_completed`
- `live_verification_completed`
- `clarifications_needed`
- `blocking_issues`
- `conversion_blockers`
- `qa_status`
- `publish_status`
- `public_copy_state`
- `intake_questions_resolved`
- `clarification_questions_presented`
- `clarification_questions_presented_at`
- `clarification_mode_selected`
- `last_qa_date`

Enforcement:
- `generation_phase_completed` can be true only after required package files exist.
- `clarify_phase_required` must be true if blocking ambiguity exists.
- `clarify_phase_completed` must be false until user resolves blockers or approves provisional mode.
- `publish_phase_completed` must be false unless `WPS:PUBLISH_BLOG` completed.
- `live_verification_completed` must be false unless `WPS:LIVE_VERIFY` completed.
- `publish_status` must never be `published` unless `live_verification_completed` is true.

### WPS:PROCESS_QA minimum protocol
`WPS:PROCESS_QA` must:
- not modify files
- not rewrite content
- not create a PR unless explicitly requested
- separate generation readiness from publish readiness
- separate missing user input from generation mistake
- classify issues by type:
  - System instruction gap
  - Workflow enforcement gap
  - User input gap
  - Generated package issue
  - Front-end rendering risk
  - Publish verification gap

Every PROCESS_QA report must begin with `## Tour Identity Confirmation` and include:
- requested command
- actual package folder
- canonical tour title
- product/reference code
- active brand
- website URL status
- TripAdvisor URL status
- Viator URL status
- package created/updated date (if known)
- whether the report covers generation, publishing, or live verification
