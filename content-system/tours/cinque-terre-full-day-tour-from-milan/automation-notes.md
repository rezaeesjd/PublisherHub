# Automation Notes: Cinque Terre Full-Day Tour from Milan

## Reuse Cadence (Weekly/Daily)

Use this same 9-file package structure for every tour product. Recommended baseline cadence:
- 1 booking-intent landing-blog hybrid post per week (like this one)
- 1 informational or comparison post per week
- 1 weekly refresh of existing high-performing pages

Scale to near-daily publishing only when the factual review process can reliably keep pace with output volume.

## Scalable AI-Assisted Production Workflow

1. **Intake:** Collect structured product facts from supplier or OTA source (duration, inclusions, meeting point, pricing, policies, links).
2. **Map:** Extract facts into canonical fields following the source-facts.md template. Flag conflicts and missing inputs before writing copy.
3. **Keyword cluster:** Generate intent-grouped keywords (primary → long-tail → informational → comparison → title variations).
4. **Draft blog post:** Follow the fixed template order: hook → value section → who it's for → what to expect → soft CTA → know before booking → strong CTA.
5. **Draft FAQ:** Populate from structured source facts only. Avoid guessing or inventing details.
6. **Fill meta.json:** Complete all required schema fields. Populate clarifications_needed for any ambiguous values.
7. **Human QA:** Review source facts, pricing, links, and policies before publishing. Replace any remaining placeholders.
8. **Publish and link:** Deploy and add internal links into the Milan day-trips content cluster.

## Keep Layout and Design Consistent

- Keep section order fixed in every blog-post.md: H1 → hook → value → who it's for → what to expect → soft CTA → know before booking → [optional social proof] → strong CTA.
- Reuse the same soft CTA sentence and strong CTA block pattern across all posts.
- Keep "Who this tour is best for" and "What to know before booking" in every page for conversion consistency.
- Use consistent placeholder conventions (`{{WebsiteLink}}`, `{{TripAdvisorLink}}`, `{{ViatorLink}}`) only when real links are missing.

## Adapting This Template to Similar Tours

This package structure can be reused with minimal adaptation for products such as:
- Cinque Terre tours departing from Florence or Rome
- Portofino and Santa Margherita coastal day trips from Milan
- Italian Riviera excursions (seasonal ferry variations)
- Hiking and scenic rail routes along the Ligurian coast
- Other multi-stop Milan day trips (Verona + Lake Garda, Tuscany highlights)

Swap only verified product facts, pricing, links, and keyword intent. Preserve template logic, section order, and QA workflow.

## Growth Impact Alignment

This workflow supports all four core platform goals:
- **Lead generation:** Targets long-tail coastal day-trip searches from Milan with booking-intent content.
- **Customer acquisition:** Answers pre-booking objections (ferry seasonality, village logistics, language support) to reduce hesitation before checkout.
- **Direct bookings:** Keeps `{{WebsiteLink}}` as the primary CTA throughout the package; OTA links are secondary trust signals only.
- **Sustainable growth:** Creates a repeatable, consistently structured production system that can scale across destination clusters without re-engineering the template each time.
