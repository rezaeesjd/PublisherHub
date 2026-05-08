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

`WPS:CLARIFY` should also fire **automatically inside `WPS:GENERATE_CONTENT`** when the agent detects ambiguities. Resolve them first, then continue generating. If the user cannot resolve them in time, leave the entries in `meta.clarifications_needed` so the QA runner blocks publish.

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
Create one folder inside `/WebPublisherSystem/content-system/tours/` for each tour title.

Folder name format:
- lowercase
- kebab-case
- based on the canonical tour title
- no special characters except hyphens

Example:
`Cinque Terre Full-Day Tour from Milan` → `/WebPublisherSystem/content-system/tours/cinque-terre-full-day-tour-from-milan/`

The folder name is a stable source/content identifier. The public URL slug may later be edited separately in the platform without renaming the folder.

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

### Recommended (not required) files
- `CHANGELOG.md` — one bullet per refresh: date, command (`WPS:GENERATE_CONTENT`, `WPS:PUBLISH_BLOG`, `WPS:CLARIFY`), what changed, who/what triggered it. Makes refresh diffs auditable.
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
- Target length for the main post: approximately 500 to 900 words unless the user requests otherwise.
- The hook paragraph should be short, emotionally clear, and conversion-aware.
- The hook should also be suitable for reuse as a short summary or meta-style introduction.
- The content should be easy to scan, with relatively short paragraphs and subheadings.
- The post should feel like a blog and landing page hybrid.
- Avoid long generic destination history unless directly useful for booking decisions.

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

Default after generation:

```json
"publish_status": "draft",
"human_review_required": true,
"qa_status": "pending"
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

Examples of ambiguous values worth flagging:
- a number with no unit (`9` for cancellation — hours? days?)
- a count with no label (`15` — minimum to operate? minimum booking notice?)
- conflicting product reference codes (e.g. `187808P109` provided by user vs `187808P82` in a Viator URL)
- a date with no role (`May 1, 2026` — pricing valid from? listing publish date?)
- a brand or supplier name that conflicts with the active system brand

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
