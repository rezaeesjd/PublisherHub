# End-to-End Publishing & Booking Funnel Process (0 → Publish)

```mermaid
flowchart TD
    A0([0. Input Received]) --> A1[Collect core facts\n• tour title\n• price/duration\n• itinerary\n• links]
    A1 --> A1b[Source Facts Matrix\nconfirmed/inferred/missing]
    A1b --> A2{Blocking ambiguity?}

    A2 -->|Yes| A3[Clarification Gate\nAsk focused questions]
    A3 --> A4[Holding notice or provisional draft]
    A4 --> A1b

    A2 -->|No| B2[SEO + Conversion Strategy\nTOFU/MOFU/BOFU mapping]
    B2 --> B3[Keyword Cluster\nPrimary + long-tail + comparison]
    B3 --> B4[Draft Landing-Blog Post\nClear promise + value + CTA blocks]

    B4 --> C1[Insert Trust Layer\nTripAdvisor/Viator as secondary proof]
    C1 --> C2[Primary CTA Logic\nWebsite first, OTA fallback]
    C2 --> C3[Internal Linking\nRelated posts → money page]
    C3 --> C4[FAQ + Metadata + Automation Notes]

    C4 --> D1[QA Gate\nclaims traceable\nlinks valid\npublic-clean copy]
    D1 --> D2{QA pass?}
    D2 -->|No| D3[Fix package + re-run QA]
    D3 --> D1

    D2 -->|Yes| E1[Ready for Review/Sync]
    E1 --> E2[Publish Sync]
    E2 --> E3[Live Verification\narchive + single post + CTA render]
    E3 --> E4{Live checks pass?}
    E4 -->|No| E5[needs_live_verification / fix]
    E5 --> E3
    E4 -->|Yes| F1([Published])

    F1 --> G1[User Interaction Stage\nVisitor lands from search/social]
    G1 --> G2[Engagement Signals\nscroll depth, time on page, FAQ clicks]
    G2 --> G3[CTA Click Stage\nBook on Website (primary)\nor OTA (secondary)]
    G3 --> G4[Booking Page Landing]
    G4 --> G5{Booked?}
    G5 -->|Yes| G6([Conversion])
    G5 -->|No| G7[Retargeting Loop\nemail/social/new BOFU content]
    G7 --> G1
```

## Professional operating sequence

1. **Data integrity first**: never write conversion claims before source-fact validation.
2. **Clarify early**: unresolved blockers pause final copy generation.
3. **Conversion-oriented content**: each post moves users toward booking, not just ranking.
4. **CTA hierarchy**: direct website booking is primary whenever available; OTA is fallback.
5. **QA as gate**: no publish status escalation without QA traceability.
6. **Live verification required**: “published” only after real front-end checks.
7. **Behavior loop**: interaction metrics feed next content variant and optimization cycle.

## Most important data to capture end-to-end

- Canonical tour identity and product codes
- Brand identity and booking channel priority
- Price, duration, inclusions, exclusions, timing, meeting point
- Real booking URLs (website + OTA)
- Provenance status for each public claim
- Funnel stage coverage (TOFU/MOFU/BOFU)
- CTA clicks and booking-page landings
- Publish readiness and live verification status
