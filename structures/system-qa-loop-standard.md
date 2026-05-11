# System QA Loop Standard

This standard defines the self-QA process for WebPublisherSystem from intake/source facts through content generation, review readiness, and publishing preparation.

The goal is to make every generated content package produce two kinds of QA outputs:

1. **Package QA** — whether the generated tour/content package is accurate, complete, conversion-ready, and publish-ready.
2. **System Improvement QA** — what should be improved in the platform, prompts, templates, workflows, UX, schemas, QA rules, or publishing process to better support lead generation, customer acquisition, direct bookings, and scalable content operations.

## When this must run

Run System QA immediately after any of these workflows:

- `WPS:GENERATE_CONTENT`
- `WPS:GENERATE_AND_PUBLISH`
- `WPS:FIX_PACKAGE`
- `WPS:PUBLISH_BLOG`
- `WPS:IMPROVE_SYSTEM_WORKFLOW`

If a generation task stops in clarify/holding-notice mode, still create a System QA report and mark unresolved blockers clearly.

## Where reports must be saved

For each content package, save the report here:

```text
content-system/tours/<package-slug>/system-qa-report.md
```

For whole-platform reviews not tied to one tour, save here:

```text
content-system/system-qa/system-qa-report-YYYY-MM-DD.md
```

If the folder does not exist, create it.

## Required report sections

Every `system-qa-report.md` must include these sections:

1. Executive summary
2. Scope reviewed
3. Business goal alignment
4. Specialist review: SEO
5. Specialist review: Software Engineering
6. Specialist review: UI/UX
7. Specialist review: Product Management
8. Cross-functional findings
9. Prioritized action items
10. Implementation brief for next AI agent
11. Acceptance criteria
12. Risks / dependencies / open questions
13. Final recommendation

## Specialist responsibilities

### SEO specialist
Review whether the system and generated content support:
- long-tail booking-intent keyword targeting
- TOFU/MOFU/BOFU funnel logic
- cluster metadata and internal link flow
- unique public slug and page title strategy
- non-duplicative variants
- conversion-focused meta descriptions and headings
- direct-booking priority while using Viator/TripAdvisor as trust/fallback
- avoidance of keyword stuffing, duplicate intent, or thin content

### Software engineer
Review whether the system supports:
- required file creation
- valid `meta.json`
- schema alignment
- safe placeholders instead of invented links
- repeatable folder/package structure
- machine-readable cluster fields
- QA automation readiness
- publish status correctness
- sync/live verification separation
- future extensibility for scheduling, performance tracking, and automated linking

### UI/UX specialist
Review whether the generated public post and platform flow support:
- clear scan path
- visible CTA placement
- trust signals
- mobile-friendly reading structure
- direct booking clarity
- reduced user hesitation
- logical next-step links
- archive/card usability
- admin dashboard clarity for review/fix/publish workflow

### Product manager
Review whether the system supports:
- business goal: traffic -> trust -> booking
- operator workflow from 0 to publish
- content production scalability
- clear separation of generation, QA, review, sync, and publish
- prioritization of action items
- measurable outcomes
- reducing manual decisions for future automation
- roadmap clarity

## Action item format

Every action item must use this machine-readable block format:

```md
### ACTION-001: Short title
- Priority: P0 | P1 | P2 | P3
- Owner agent: SEO | Software | UI/UX | Product | Cross-functional
- Area: prompt | template | schema | qa-rule | platform-code | content-package | publishing | ux | analytics | docs
- Problem:
- Why it matters:
- Recommended fix:
- Files likely affected:
  - `path/to/file.md`
- Implementation steps:
  1. ...
  2. ...
- Acceptance criteria:
  - ...
- Risk if ignored:
- Status: open
```

Priority definitions:
- `P0`: blocks accurate/safe generation or publishing
- `P1`: materially affects conversion, SEO, or automation quality
- `P2`: improves maintainability, UX, or scalability
- `P3`: nice-to-have enhancement

## Implementation brief for next AI agent

The report must include a concise implementation brief that another AI agent can follow without rereading the whole conversation.

It must include:
- the top 3 to 7 actions to implement first
- exact files to edit or create when known
- what not to change
- verification steps
- expected output after implementation

## Rules for the QA agent

- Be honest about incomplete or uncertain information.
- Do not invent source facts, URLs, reviews, ratings, prices, or performance data.
- Separate content-package issues from system/workflow issues.
- Prefer actionable fixes over vague criticism.
- Do not mark an item complete unless the file/code/docs actually satisfy the acceptance criteria.
- If an issue should be handled by a later agent, keep status as `open` and write clear implementation steps.

## Rules for the fixing agent

When another AI agent consumes `system-qa-report.md`, it must:

1. Read the report.
2. Implement only open action items unless explicitly instructed otherwise.
3. Preserve source facts and public content accuracy rules.
4. Update affected docs/templates/schemas/code.
5. Add a short implementation note to the same report or a companion `system-qa-fix-log.md`.
6. Do not mark an item complete unless acceptance criteria are met.

## Minimum successful output

A successful self-QA run produces:

- normal package `qa-report.md`
- specialist `system-qa-report.md`
- prioritized action items
- implementation brief for the next AI agent
- clear pass/fail/warn status for system readiness
