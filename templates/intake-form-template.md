# Tour Intake Form

Use this form when receiving raw tour data from a supplier, OTA listing, or internal product team. Filling every field marked **Required (blocking if missing)** allows `WPS:GENERATE_CONTENT` to skip the clarification round and produce final public copy in one pass.

A machine-readable companion lives at `structures/intake-form.schema.json`.

---

## Section 1 — Brand and identity

- **Active system brand** _(Required, blocking if conflicted)_: e.g. `Milano Adventures`
- **Raw supplier/operator name** _(optional)_: appears only in `source-facts.md`, never in public copy
- **Canonical tour title** _(Required, blocking if missing)_: e.g. `Cinque Terre Full-Day Tour from Milan`
- **Internal product/reference code** _(Required, blocking if missing or conflicted with channel codes)_: e.g. `187808P109`
- **Channel-specific product codes** _(optional)_: e.g. `{ "viator": "187808P82", "getyourguide": "GYG-1234" }`

## Section 2 — Booking and CTA links

- **Direct website booking URL** _(Required, blocking if missing — conversion blocker)_: full HTTPS URL
- **TripAdvisor URL** _(optional, warning if missing)_: full HTTPS URL
- **Viator URL** _(optional, warning if missing)_: full HTTPS URL
- **Other OTA URLs** _(optional)_: GetYourGuide, Klook, Civitatis, etc.

## Section 3 — Logistics

- **Meeting point address** _(Required, blocking if missing)_
- **Meeting point details** _(optional)_: e.g. "Look for fuchsia shirts; arrive 15 minutes early"
- **End point** _(Required, blocking if missing)_: same address as meeting point counts; mark `same_as_meeting_point: true`
- **Duration** _(Required, blocking if missing)_: e.g. `13 hours 30 minutes`
- **Start time** _(Required, blocking if missing)_: 24-hour format `HH:MM`
- **Departure days** _(Required, blocking if missing)_: array of `Mon|Tue|Wed|Thu|Fri|Sat|Sun`
- **Group type** _(Required, blocking if missing)_: `Shared` | `Private`
- **Max travelers** _(optional)_: integer
- **Min travelers to operate** _(optional)_: integer
- **Difficulty** _(optional)_: `Easy` | `Moderate` | `Challenging`
- **Languages** _(Required, blocking if missing)_: array of language names; mark per-language whether `live`, `audio`, or `written`
- **Hotel pickup/drop-off** _(Required, blocking if conflicted with meeting point)_: `included` | `not_included` | `optional_addon`
- **Wheelchair accessibility** _(optional, warning if missing)_: `accessible` | `not_accessible` | `partial`

## Section 4 — Itinerary

- **Stops** _(Required, blocking if missing for itinerary-driven tours)_: ordered array of stop names
- **Stop durations** _(optional, warning if missing)_: per-stop minutes
- **Visit order may vary** _(optional)_: boolean

## Section 5 — Inclusions and exclusions

- **Inclusions** _(Required, blocking if missing)_: array of strings
- **Exclusions** _(optional)_: array of strings; if absent, generation will infer common exclusions and mark them `inferred` in the provenance matrix

## Section 6 — Pricing

- **Currency** _(Required, blocking if missing)_: ISO 4217 code, e.g. `EUR`
- **Pricing model** _(Required, blocking if missing)_: `per_person` | `per_group` | `per_booking`
- **Pricing valid from** _(optional)_: ISO date `YYYY-MM-DD`
- **Age bands** _(Required, blocking if missing for per-person pricing)_: e.g. `{ "adult": "12-99", "child": "4-11", "infant": "0-3" }`
- **Per-band prices** _(Required, blocking if missing for per-person pricing)_: e.g. `{ "adult": 275, "child": 157, "infant": 0 }`

## Section 7 — Policies

- **Cancellation window** _(Required, blocking if unit unclear)_: number **and** unit (`hours` or `days`)
- **Confirmation type** _(Required, blocking if missing)_: `instant` | `manual`
- **Ticket format** _(optional)_: `Paper ticket`, `Mobile ticket`, etc.
- **Tickets per booking** _(optional)_: integer
- **Booking info required** _(optional)_: e.g. `["Lead traveler name", "Phone number", "Date of birth for all travelers"]`
- **Seasonal or weather-dependent components** _(optional)_: free text — note any feature that may not always run

## Section 8 — Social proof

- **Rating** _(optional)_: numeric, must include source
- **Review count** _(optional)_: integer, must include source
- **Notable review quotes** _(optional)_: include source URL and author handle/initials

## Section 9 — Destination context

These items get promoted into the provenance matrix so they can be referenced in public copy.

- **UNESCO / heritage status** _(optional)_
- **Region / coast / lake / mountain** _(optional)_
- **Named landmarks present in supplier description** _(optional)_

## Section 10 — Generation overrides

- **Provisional mode** _(optional)_: boolean. If `true`, generation will proceed despite blocking clarifications and `public_copy_state` will be set to `provisional`.
- **Funnel stage override** _(optional)_: `TOFU` | `MOFU` | `BOFU` (default `BOFU`)

---

## How the agent should use this form

1. If any **Required (blocking)** field is missing, conflicted, or has an ambiguous unit, the agent must ask the user via `AskUserQuestion` before generating any public copy.
2. Fields marked **warning if missing** populate `meta.clarifications_needed` with `"blocking": false`. Generation may proceed.
3. Fields marked **optional** are recorded in `source-facts.md` if provided, omitted otherwise.
4. When all blocking-required fields are answered, set `meta.intake_questions_resolved: true`.
