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

### SYSQA-20260511-001: Workflow completion enforcement missing
- Date added: 2026-05-11
- Added after run/package: `system-wide`
- Priority: P1
- Owner agent: Cross-functional
- Area: qa-rule
- Status: resolved
- Problem:
  Content generation workflows could finish after creating package files while silently skipping cluster-registry updates and cumulative system-QA updates.
- Why it matters:
  This creates operational drift where AI workflows, dashboard state, and QA history become inconsistent.
- Recommended fix:
  Add a workflow completion contract/checklist and require all generation prompts to enforce registry updates, package QA updates, and system-QA backlog updates before reporting success.
- Files likely affected:
  - `structures/workflow-completion-checklist.md`
  - `templates/content-generation-agent-prompt.md`
- Implementation steps:
  1. Create reusable workflow completion checklist.
  2. Update generation prompt with explicit workflow enforcement rules.
  3. Require cluster-registry and system-QA updates before workflow success.
- Acceptance criteria:
  - Generation prompts reference workflow completion checklist.
  - Registry updates and system-QA updates are explicitly required.
  - Workflow cannot silently skip operational state updates.
- Risk if ignored:
  Dashboard and AI operational state diverge over time.
- Implementation note:
  Added workflow-completion-checklist.md and updated content-generation-agent-prompt.md to enforce required workflow completion steps.

---

## Resolved items

_No resolved items yet._

### SYSQA-20260511-002: Missing per-run process QA artifact linkage
- Date added: 2026-05-11
- Added after run/package: `cinque-terre-full-day-tour-from-milan`
- Priority: P1
- Owner agent: Cross-functional
- Area: qa-rule
- Status: open
- Problem:
  A package can ship with content QA (`qa-report.md`) but without an explicit system/process QA report connected to that run.
- Why it matters:
  Process defects can repeat across tours, reducing automation quality and governance traceability.
- Recommended fix:
  Require one `WPS:PROCESS_QA` report per generation run and require package-level linkage to that report.
- Files likely affected:
  - `content-system/WORKFLOW.md`
  - `content-system/COMMANDS.md`
  - `content-system/QA-CHECKLIST.md`
- Implementation steps:
  1. Add process QA artifact requirement to generation completion gate.
  2. Define report naming/location convention.
  3. Add QA checklist item validating report existence and linkability.
- Acceptance criteria:
  - Every new package has a corresponding process QA report.
  - QA checklist fails when report is missing.
- Risk if ignored:
  System-level process regressions remain invisible while content appears compliant.
- Implementation note:

### SYSQA-20260511-003: Enforce generation/publish/live status separation in process reports
- Date added: 2026-05-11
- Added after run/package: `cinque-terre-full-day-tour-from-milan`
- Priority: P2
- Owner agent: Cross-functional
- Area: docs
- Status: open
- Problem:
  Stakeholders can misread package completion as publication because process QA summaries are not consistently standardized.
- Why it matters:
  Incorrect status communication can trigger premature business actions.
- Recommended fix:
  Add a required status triad block (generation, publish, live verification) in process QA templates/checklists.
- Files likely affected:
  - `content-system/QA-CHECKLIST.md`
  - `content-system/WORKFLOW.md`
- Implementation steps:
  1. Update checklist with triad-status requirement.
  2. Add template snippet for process reports.
- Acceptance criteria:
  - New process QA reports contain the triad status block.
- Risk if ignored:
  Ongoing confusion between draft readiness and published state.
- Implementation note:

- 2026-05-13 | bernina-red-train-and-st-moritz-from-milan | WPS:GENERATE_CONTENT | action items: add product reference code and exclusions list when available.

- 2026-05-13 | bernina-red-train-and-st-moritz-from-milan-v2 | WPS:GENERATE_CONTENT | none found beyond inherited non-blocking source gaps.

- 2026-05-13 | bernina-red-train-and-st-moritz-from-milan-v3 | WPS:GENERATE_CONTENT | none found beyond inherited non-blocking source gaps.

- 2026-05-13 | bernina-red-train-and-st-moritz-from-milan-v4 | WPS:GENERATE_CONTENT | none found beyond inherited non-blocking source gaps.
