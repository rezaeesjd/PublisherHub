# Cluster Plan

## Primary tour
- Canonical tour title: {{CanonicalTourTitle}}
- Base package slug: {{BasePackageSlug}}
- Main conversion URL: {{WebsiteLink}}
- Secondary OTA links: {{ViatorLink}}, {{TripAdvisorLink}}

## Cluster goal
Turn one tour into a connected content cluster that attracts travelers at different stages of the booking journey and moves them toward reservation.

Preferred flow:
TOFU informational content -> MOFU comparison/decision content -> BOFU booking page/post -> website booking CTA.

## Funnel assets

### BOFU booking asset
- Role: Main commercial booking-intent page/post
- Intent: traveler is close to booking this exact tour
- Example title: {{CanonicalTourTitle}}
- CTA strength: strong direct booking CTA
- Primary link target: website booking URL first, OTA fallback only when website URL is missing

### MOFU comparison asset 1
- Role: Compare tour vs self-guided/DIY option
- Intent: traveler knows the destination but is deciding how to visit
- Example title: {{Destination}} from {{Origin}}: Guided Tour or Do It Yourself?
- CTA strength: medium/strong
- Link target: BOFU booking asset or main tour page

### MOFU comparison asset 2
- Role: Compare this tour against another destination, tour style, route, or timing option
- Intent: traveler is deciding between alternatives
- Example title: {{DestinationA}} vs {{DestinationB}} from {{Origin}}: Which Day Trip Is Better?
- CTA strength: medium
- Link target: relevant BOFU asset(s)

### TOFU guide asset 1
- Role: Broad destination or itinerary discovery guide
- Intent: traveler is researching what to do in the city/region
- Example title: Best Day Trips from {{Origin}} for First-Time Visitors
- CTA strength: soft
- Link target: MOFU comparison asset and BOFU tour page

### TOFU guide asset 2
- Role: Practical early-stage guide
- Intent: traveler is validating whether the destination/tour is worth it or possible
- Example title: Is {{Destination}} Worth Visiting from {{Origin}}?
- CTA strength: soft/medium
- Link target: MOFU or BOFU asset

### FAQ/support asset
- Role: Remove pre-booking doubts
- Intent: traveler has practical objections or questions
- Example title: Can You Visit {{Destination}} from {{Origin}} in One Day?
- CTA strength: medium/strong
- Link target: BOFU booking asset

## Publishing order
1. Publish BOFU asset first so all later content has a conversion destination.
2. Publish MOFU comparison assets next because they usually convert better than broad informational guides.
3. Publish TOFU guides after the BOFU and MOFU paths exist.
4. Publish FAQ/support content to reduce hesitation and support internal links.
5. Refresh and relink the cluster monthly or after major tour/product changes.

## Internal linking map
- TOFU assets link to at least one MOFU asset and the BOFU tour asset.
- MOFU assets link to the BOFU tour asset and one related TOFU guide.
- BOFU asset links to relevant FAQ/support content only when it helps booking confidence.
- Every asset must include a clear next-step CTA.

## Variant rules
- Each content asset is its own package under `content-system/tours/`.
- The base BOFU package is treated as v1.
- Additional versions or alternative angles must use the sibling folder pattern `<base-slug>-v<N>` once the base package is approved.
- Variants must share the same confirmed tour facts but differ by funnel stage, keyword intent, title angle, section ordering, FAQ angle, and CTA copy.
- Never change price, duration, inclusions, meeting point, cancellation rules, or other source facts just to create a new variant.

## QA expectations
Each cluster asset must pass checks for:
- funnel stage clarity
- primary keyword intent
- non-duplicative title and public slug
- internal links to the correct next funnel step
- direct website CTA priority
- OTA links used only as secondary trust/fallback unless no website URL exists
- no invented tour facts, reviews, ratings, awards, or rankings
