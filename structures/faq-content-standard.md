# FAQ Content Standard

This standard defines when FAQs should be embedded inside a main content asset and when they should become a standalone FAQ/support blog package.

## Core rule

Every main BOFU or MOFU tour asset may include a short embedded FAQ section, but standalone FAQ packages should only be created for major searchable questions or important booking objections.

Do not create generic duplicate FAQ posts for every tour by default.

## Embedded FAQ inside main assets

Use embedded FAQs inside:

- BOFU main booking posts
- MOFU comparison posts
- landing-blog hybrid posts
- tour landing pages

Purpose:

- reduce booking hesitation
- answer final practical questions
- support conversion
- improve page completeness
- help schema/structured data later

Recommended size:

- 3 to 6 questions
- short answers
- directly related to the page intent

Good embedded FAQ examples:

- How long is the tour?
- Where is the meeting point?
- Is hotel pickup included?
- Is the ferry guaranteed?
- What languages are available?
- Can I book directly on the operator website?

## Standalone FAQ/support packages

Create a standalone FAQ/support package only when the topic has its own clear search intent or important conversion-objection value.

A standalone FAQ package must not be a generic duplicate of the embedded FAQ section.

Good standalone FAQ/support topics:

- Can you visit Cinque Terre from Milan in one day?
- Can you visit all five Cinque Terre villages in one day from Milan?
- Is the Cinque Terre ferry guaranteed on a day trip from Milan?
- Cinque Terre from Milan by train or guided tour: which is easier?
- Is the Bernina Red Train worth it from Milan?
- What should you know before booking Lake Como and Lugano from Milan?

Avoid standalone FAQ topics like:

- FAQ about Cinque Terre Tour
- Cinque Terre tour questions
- Tour FAQ
- Common questions about this tour

These are too generic and often duplicate the main page.

## Required standalone FAQ metadata

Standalone FAQ packages must use:

```json
{
  "funnel_stage": "FAQ",
  "cluster_type": "FAQ",
  "cluster_role": "faq-support-post",
  "cluster_parent": "{{BaseClusterSlug}}",
  "cluster_next_step": "{{MainBofuAssetSlug}}",
  "cluster_linking_priority": "link-to-bofu"
}
```

## Required standalone FAQ content behavior

A standalone FAQ/support post must:

- target one primary question or booking objection
- answer that question more deeply than the embedded FAQ section
- link back to the main BOFU/tour booking asset
- avoid copying the same wording from the embedded FAQ section
- avoid becoming a miscellaneous FAQ dump
- use the website booking CTA as primary when available
- use Viator/TripAdvisor only as secondary trust/fallback links

## Duplicate-intent check

Before creating a standalone FAQ package, the agent must check:

1. Does this question deserve its own page?
2. Is the primary keyword different from the main BOFU/MOFU page?
3. Will this page answer the question more deeply than the embedded FAQ?
4. Does it clearly support the main conversion asset?
5. Is it not just a paraphrased copy of existing content?

If the answer is no, keep the FAQ inside the main asset only.

## Cluster registry rule

Standalone FAQ packages should count as FAQ/support assets in `cluster-registry.json` only when they meet the standalone criteria above.

Embedded FAQs inside BOFU/MOFU pages do not count as standalone cluster assets.

## Recommended system behavior

Default behavior:

- Generate embedded `faq.md` for each main package.
- Do not automatically publish `faq.md` as a separate blog.

Create standalone FAQ package only when:

- user explicitly requests FAQ/support content, or
- cluster registry shows FAQ/support asset missing, and
- the agent can define a specific search-intent question.
