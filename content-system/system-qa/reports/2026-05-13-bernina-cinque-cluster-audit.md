## Tour Identity Confirmation
- Requested command: `WPS:PROCESS_QA` (manual audit request)
- Scope: `swiss-alps-from-milan` (Bernina) + `cinque-terre-from-milan` clusters
- Canonical tour titles:
  - Bernina: Full Day Tour in Bernina Red Train and St Moritz from Milan
  - Cinque Terre: Cinque Terre Full-Day Tour from Milan
- Primary conversion assets:
  - `bernina-red-train-and-st-moritz-from-milan`
  - `cinque-terre-full-day-tour-from-milan`
- Report date: 2026-05-13
- Coverage: generation + cluster-registry readiness for conversion (not live URL rendering)

## Findings summary
1. **Both clusters have one BOFU asset complete and multiple planned supporting assets** (MOFU/TOFU/FAQ). This is acceptable for phased rollout, but conversion coverage remains narrow because only one page per cluster carries booking intent today.
2. **Bernina cluster registry is out of sync with package metadata**:
   - `cluster-registry.json` still has `website_url: {{WebsiteLink}}`, but the Bernina package already has a real website booking URL in `meta.json`.
   - `next_recommended_generation` asks for a booking permalink that already exists.
3. **Cinque Terre cluster has expected non-blocking source gaps** (no direct website URL, cancellation unit inferred, accessibility missing). Existing BOFU package remains viable via OTA primary CTA.

## Detailed checks
### A) Bernina cluster
- Registry state (as of audit, 2026-05-13): primary BOFU + v2/v3/v4 variants were `ready_for_review`; core MOFU/TOFU/FAQ assets still `planned`.
- **2026-05-14 update:** the v2/v3/v4 variants were retired (see `SYSTEM-QA-BACKLOG.md`) — the `-v<N>` variant mechanism produced thin near-duplicate clones and was removed. The Bernina cluster now consists of the single BOFU plus the new typed MOFU (`bernina-express-guided-vs-diy-from-milan`), TOFU (`swiss-alps-from-milan-guide`), and FAQ (`bernina-st-moritz-from-milan-faq`) assets.
- Conversion risk: addressed — typed MOFU/TOFU/FAQ assets now exist.
- Data integrity gap: registry-level website URL fallback remains placeholder despite package-level confirmed URL.

### B) Cinque Terre cluster
- Registry state: BOFU ready_for_review; MOFU/TOFU/FAQ planned.
- Conversion risk: primary CTA depends on OTA because no direct website booking URL is provided.
- Non-blocking QA debt: cancellation unit + accessibility remain unresolved source inputs.

## Recommended improvements (efficiency-first)
1. **Sync Bernina cluster registry website URL from package metadata** to ensure dashboard and fallback CTA logic always resolve to direct booking first.
2. **Generate one FAQ support post per cluster next** (before additional intent variants) to improve conversion without over-expanding content volume.
3. **Use MOFU comparison as third asset** after FAQ for each cluster, since it handles "guided vs DIY" hesitation that blocks booking.
4. **Keep one clear CTA target per post** (website first when available; OTA fallback when not) and avoid adding extra CTAs that split decision flow.

## Classification
- System instruction gap: none
- Workflow enforcement gap: minor (registry sync lag vs package reality)
- User input gap: present for Cinque Terre website URL/accessibility/cancellation unit
- Generated package issue: none blocking
- Front-end rendering risk: medium (fallback link source divergence at registry level)
- Publish verification gap: live URL verification not covered by this audit

## Outcome
- Bernina cluster: usable now, but registry sync should be corrected before next generation pass.
- Cinque Terre cluster: usable now with OTA-led conversion path; should collect direct website URL to improve direct-booking efficiency.
