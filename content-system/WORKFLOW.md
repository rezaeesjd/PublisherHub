# WebPublisherSystem Workflow

## End-to-end flow
1. User provides tour data.
2. Codex detects command prefix.
3. Source facts are extracted first into `source-facts.md`.
4. Real links are captured and preserved.
5. Content package files are generated.
6. Public article is kept separate from metadata/SEO/admin notes.
7. `qa-report.md` is created.
8. `publish_status` and `qa_status` are set honestly.
9. Human review resolves clarifications.
10. Server sync/deploy occurs.
11. Live archive and single post are verified.
12. Only then can status become `published`.

## Boundary rules
- Generation ≠ publishing.
- Publishing ≠ live verification.
- `published` requires verified live archive + single post.
