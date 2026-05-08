# Automation Notes

## 3-step no-human-block workflow
1. **Generate step (AI):** extract source facts and generate full package from available inputs.
2. **Review step (AI):** run QA checks, validate links, fact consistency, and publish rules in a separate pass.
3. **Publish step (AI):** if QA passes or has only non-blocking warnings, set publish-ready status and hand off for sync/live verification.

## Missing-data handling standard
- Do not block generation when non-critical fields are missing.
- Use any provided booking-capable link as the primary CTA if a direct website link is unavailable.
- Keep additional OTA links as secondary references when present.
- Only block when **zero booking/reference links** are provided.
- For missing non-link facts (e.g., incomplete exclusions, incomplete policy wording), skip unsupported claims and generate around confirmed facts.

## Reusable QA policy
- Mark unclear but non-critical fields as warnings, not failures.
- Fail only on hard blockers: no booking/reference link at all, invalid JSON, or fabricated claims.
- Keep structure stable so batch generation and QA can run across multiple tours automatically.
