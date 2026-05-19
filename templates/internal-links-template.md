# Internal Linking Suggestions

- Main tour page: `{{TourPageLink}}`
- Related guide page: `{{RelatedGuideLink}}`
- Comparison page: `{{ComparisonPageLink}}` (Suggested future page)
- FAQ/destination info page: `{{DestinationFAQLink}}`
- Booking/contact page: `{{ContactPageLink}}`

Notes:
- Do not invent final internal URLs.
- Use placeholders when unknown.
- Keep OTA links secondary if included.

## Cross-cluster links by package_slug

Cross-cluster references (sibling clusters, or any other internal asset whose
public slug may not be live yet) MUST be authored by `package_slug` using the
`wps-cluster:` markdown link scheme in `blog-post.md` / `faq.md`:

```md
See our [Lake Como guide](wps-cluster:lake-como-travel-guide) for context.
```

The publish-time renderer resolves each `wps-cluster:` link against the
cluster registry:
- target asset `status: published` and a non-empty `public_slug` -> live `<a href>` to the canonical post URL.
- otherwise -> the link degrades to plain text (no broken `href` is ever emitted).

When the sibling later publishes, every post that references it auto-upgrades
to a live link on the next render — no hand-edits to the source asset.

Authoring rules:
- Never hardcode a sibling's public URL in prose; always use `wps-cluster:<package_slug>`.
- Continue to list each cross-cluster reference in `internal-links.md` by
  `package_slug` (not by guessed public URL).
- Run `./scripts_cluster_links_audit.sh` (or call
  `wps_audit_cluster_links_in_package()` directly) before publish; append the
  resulting "Cross-cluster link decisions" block to `qa-report.md` so the
  per-publish decision log is preserved with the run.
