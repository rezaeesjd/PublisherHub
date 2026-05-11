# System QA Backlog

This is the cumulative system-level QA backlog for WebPublisherSystem.

It is not tied to one tour. It is the long-running improvement file for the full system and process from intake/source facts through content generation, QA, review, publishing preparation, sync, live verification, and future performance optimization.

Every content generation run should append new system/process findings here when the AI detects opportunities to improve the platform, prompts, templates, schemas, QA rules, UI/UX, internal linking, publishing workflow, or product strategy.

## Current status

- Backlog status: active
- Scope: full WebPublisherSystem process
- Business goal: traffic -> trust -> direct booking growth, while using Viator/TripAdvisor as secondary trust/fallback channels
- Usage: generation agents append findings; improvement agents read and implement open action items

---

## How agents must use this file

### During content generation
After generating a content package, the generation agent must run a short system/process self-QA pass and append findings here if the run reveals reusable improvements.

The agent should ask:
- Did the prompt/template miss anything?
- Did the system need a manual decision that could be standardized?
- Did cluster metadata or internal links require guessing?
- Did CTA, trust, UX, SEO, schema, or publishing logic show weakness?
- Did the generated content expose a repeatable issue that should be fixed at the system level?

### During system improvement
A later improvement agent must read this backlog, select open items by priority, implement fixes, and update each item status.

Do not delete resolved items. Mark them as `resolved` and add the implementation note.

---

## Action item format

Each new item must use this exact block format.

```md
### SYSQA-YYYYMMDD-001: Short title
- Date added: YYYY-MM-DD
- Added after run/package: `package-slug` or `system-wide`
- Priority: P0 | P1 | P2 | P3
- Owner agent: SEO | Software | UI/UX | Product | Cross-functional
- Area: prompt | template | schema | qa-rule | platform-code | content-package | publishing | ux | analytics | docs | internal-linking
- Status: open | in-progress | resolved | deferred
- Problem:
- Why it matters:
- Recommended fix:
- Files likely affected:
  - `path/to/file`
- Implementation steps:
  1. ...
  2. ...
- Acceptance criteria:
  - ...
- Risk if ignored:
- Implementation note:
```

Priority definitions:
- `P0`: blocks safe/accurate generation, QA, publishing, or source-fact integrity
- `P1`: materially affects SEO, conversion, direct-booking growth, automation quality, or funnel structure
- `P2`: improves maintainability, scalability, UX, or operational clarity
- `P3`: helpful but non-urgent enhancement

---

## Open items

_No open items yet. Future generation runs should append findings here._

---

## Resolved items

_No resolved items yet._
