# Exemplar Notes — All-in-One Lake Como, Bellagio & Lugano

> **This package is not a model.** It predates the current hard clarify gate and the brand-mention requirement. Do not cite it as precedent for bypassing rules in `AGENTS.md`.

## What this folder does NOT satisfy under current rules

- **Hard clarify gate.** `meta.json` ships with placeholder URLs (`{{WebsiteLink}}`, `{{TripAdvisorLink}}`, `{{ViatorLink}}`) — i.e. a missing website booking URL — yet `blog-post.md` contains a full final article (~500 words). Under the current rules this would require the article to be a holding notice (`templates/holding-notice-template.md`) until the user resolves the missing URL or explicitly authorizes provisional mode.
- **`public_copy_state` / `intake_questions_resolved` markers.** The current `meta.json` does not include these fields.
- **Provenance-to-claim binding.** The provenance matrix exists, but the agent must verify that every claim in the public article maps to a row before re-using this folder as a structural template.
- **Brand-mention check.** The blog post may not include a natural `Milano Adventures` mention (current rules require at least one).

## What this folder DOES satisfy and is still useful for

- File set: all 9 required files exist and follow the naming convention.
- Folder slug: kebab-case, derived from the canonical title.
- `source-facts.md` provenance matrix: structurally correct.
- Honest publish status: `publish_status: draft`, `qa_status: needs_fix`, `human_review_required: true`.
- Section structure of the public article (hook → value → who it's for → what to expect → soft CTA → know before booking → strong CTA).

## How to use this folder safely

- Copy the **structure** (file list, folder name conventions, section ordering) — not the **content**.
- Do not cite this folder when reasoning about whether to generate a final `blog-post.md` while blocking clarifications remain.
- When this folder is regenerated, run `WPS:GENERATE_CONTENT` and apply the current hard clarify gate. Replace `blog-post.md` with a holding notice if the website URL is still unresolved.

## Path to compliance

To bring this folder into compliance with the current rules:

1. Provide the real direct website booking URL.
2. Re-run `WPS:GENERATE_CONTENT` so the agent regenerates `blog-post.md` from the source facts (and adds the brand mention).
3. Update `meta.json` to include `public_copy_state: final`, `intake_questions_resolved: true`, and the resolved `cancellation_window_hours`.
4. Remove this `EXEMPLAR_NOTES.md` once the package passes the QA runner.
