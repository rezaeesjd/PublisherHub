# Content Freshness Policy

- `last_qa_date` is the canonical freshness timestamp used on public blog posts.
- Public post templates should display the last QA/content check date in human-readable form.
- If freshness age exceeds **180 days**, UI should show a refresh recommendation.
- Dashboard/QA workflows should continue to stamp `last_qa_date` during release checks.
