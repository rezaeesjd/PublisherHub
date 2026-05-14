# Workflow Completion Checklist

This checklist is the enforcement contract for WebPublisherSystem generation, QA, and publishing workflows.

A workflow is not complete until every required completion check has either passed or been explicitly marked as skipped with a reason.

## Applies to

- `WPS:GENERATE_CONTENT`
- `WPS:GENERATE_AND_PUBLISH`
- `WPS:FIX_PACKAGE`
- `WPS:PUBLISH_BLOG`

## Required completion checks for content generation

When running `WPS:GENERATE_CONTENT`, the agent must complete these checks before reporting success.

```md
## Workflow completion check

- [ ] Source facts extracted to `source-facts.md`
- [ ] All required package files exist or clarify-mode stubs exist
- [ ] `meta.json` contains required SEO, CTA, status, and cluster fields
- [ ] `internal-links.md` follows TOFU/MOFU/BOFU/FAQ linking rules
- [ ] Package QA saved to `content-system/tours/<package-slug>/qa-report.md`
- [ ] `content-system/clusters/cluster-registry.json` read before generation
- [ ] `content-system/clusters/cluster-registry.json` updated after generation
- [ ] Cluster asset status reflects generated package status
- [ ] Cluster missing assets and next recommended generation updated
- [ ] System/process self-QA completed after generation
- [ ] Reusable system findings appended to `content-system/system-qa/SYSTEM-QA-BACKLOG.md`, or explicit note added that no reusable system findings were detected
- [ ] Final response states exactly what was completed and what still needs human review, sync, or live verification
```

## Required completion checks for fix/improvement runs

When running `WPS:FIX_PACKAGE` or implementing system QA backlog items, the agent must complete these checks.

```md
## Fix workflow completion check

- [ ] Read the relevant `qa-report.md` or `SYSTEM-QA-BACKLOG.md`
- [ ] Implement only the requested/open action items unless instructed otherwise
- [ ] Preserve source facts and avoid inventing facts
- [ ] Update affected package files, templates, schemas, docs, or platform code
- [ ] Update package `qa-report.md` when package-level issues were fixed
- [ ] Update `SYSTEM-QA-BACKLOG.md` item status and implementation note when system-level items were fixed
- [ ] Update `cluster-registry.json` if package status, asset status, or cluster completeness changed
- [ ] Final response lists changed files and remaining open items
```

## Required completion checks for publish workflows

When running `WPS:PUBLISH_BLOG`, the agent must complete these checks.

```md
## Publish workflow completion check

- [ ] Existing package located
- [ ] Required files validated
- [ ] `meta.json` validated
- [ ] Public slug checked
- [ ] Public content checked for admin labels and unsafe claims
- [ ] Links and CTA priority checked
- [ ] Package QA updated
- [ ] `cluster-registry.json` asset status updated
- [ ] Publish status is not set to `published` unless live archive and single-post verification succeeded
- [ ] If live verification was not possible, status is `published` or `needs_live_verification`
```

## Failure rule

If any required item cannot be completed, the workflow must not be reported as fully complete.

The agent must say:

```text
Workflow incomplete: [reason].
Completed: [...]
Not completed: [...]
Next required action: [...]
```

## Why this exists

This checklist prevents partial runs where content is generated but system-level state is not updated. It ensures that package files, QA, cluster registry, and cumulative system QA remain synchronized.
