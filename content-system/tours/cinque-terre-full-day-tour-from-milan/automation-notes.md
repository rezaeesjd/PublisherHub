# Automation Notes

## Reuse model
This tour package can be reused as a template for other Milano Adventures products by swapping:
- tour title
- departure city
- stops and route
- duration
- inclusions
- restrictions
- booking placeholders

The overall section order should remain the same.

## Weekly or daily reuse
### Weekly model
- publish 1 commercial landing-blog hybrid post for one high-value tour
- publish 1 supporting informational or comparison article linked to that tour
- refresh 1 existing high-value page with stronger CTA, updated metadata, or internal links

### Daily model
Use daily production only when there are enough distinct tours and enough review capacity to avoid thin or repetitive content.

## Scalable workflow with AI
A repeatable AI-assisted workflow can use these inputs:
- brand name
- product title
- product reference code
- departure city
- meeting point
- duration
- itinerary stops
- what makes the tour unique
- inclusions and exclusions
- traveler restrictions
- booking placeholders

From those fields, AI can generate:
- brief.md
- keywords.md
- blog-post.md
- faq.md
- meta.json
- internal-links.md
- automation-notes.md

## CMS integration idea
Store the source product data in structured fields or a spreadsheet, then map them into reusable prompts.

Useful input fields for automation:
- `BrandName`
- `ProductTitle`
- `ProductReferenceCode`
- `MeetingPoint`
- `EndPoint`
- `Duration`
- `Stops`
- `UniqueSellingPoints`
- `Inclusions`
- `Exclusions`
- `Languages`
- `Restrictions`
- `WebsiteLink`
- `TripAdvisorLink`
- `ViatorLink`

## Publishing placeholders
Keep `{{WebsiteLink}}` dynamic until the website booking URL is provided. Once the real URL exists, the platform can replace it via `platform/post-overrides.php` or by updating `meta.json` directly.

For this tour, the TripAdvisor and Viator links are already verified and stored as full URLs in `meta.json` and `internal-links.md`. The `{{TripAdvisorLink}}` and `{{ViatorLink}}` placeholders are no longer needed for this product.

## Link state for this tour (snapshot)
- Website booking URL: still placeholder `{{WebsiteLink}}` — needs human input before publish
- TripAdvisor URL: verified
- Viator URL: verified

## Design consistency
Keep the same page layout for every generated tour article:
1. title and short intro
2. main value section
3. soft CTA
4. FAQ or practical planning section
5. strong CTA
6. related links

This helps both speed and consistency across the site.

## Quality control
Before publishing, a human should review:
- factual accuracy of stops and included items
- final booking links
- any destination-specific wording
- seasonal notes such as ferry availability
- unsupported claims that may need removal

## Content cluster scaling
For this tour specifically, future supporting content can include:
- how to visit Cinque Terre from Milan
- Cinque Terre tour from Milan vs train alone
- what to wear for a Cinque Terre day trip
- is Cinque Terre worth a day trip from Milan
- best time to visit Cinque Terre from Milan

Each of those pages should link back to the main booking page using `{{WebsiteLink}}`.
