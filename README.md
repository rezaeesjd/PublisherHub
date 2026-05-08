# WebPublisherSystem

**WebPublisherSystem** is the deployed name of the platform. The source of this codebase lives in the `rezaeesjd/PublisherHub` repository, which mirrors the deploy layout without the `WebPublisherSystem/` prefix.

| Concept | Repository path | Deploy path |
| --- | --- | --- |
| Platform code | `platform/` | `/WebPublisherSystem/platform/` |
| Tours content | `content-system/tours/` | `/WebPublisherSystem/content-system/tours/` |
| Public blog | `blog/` | `/WebPublisherSystem/blog/` |

When docs reference `/WebPublisherSystem/...`, treat that as the deploy path. When working in source, drop the prefix.

**WebPublisherSystem** is an **Automated SEO & Social Content Marketing Platform** for travel, tours, and local experience businesses.

The ultimate business goal is **lead generation, customer acquisition, direct bookings, and growth** through content marketing automation.

Technically, it is a **marketing automation platform focused on organic lead generation through content operations**.

This first implementation is focused on SEO blog publishing: generating, organizing, syncing, and publishing booking-focused blog content from structured markdown files.

This is not WordPress. It is designed to be uploaded as a normal folder to PHP web hosting.

## Current scope
This version includes:
- public blog archive page shell at `/WebPublisherSystem/blog/`
- settings page without password
- public GitHub repository connection settings
- GitHub connection test
- local blog archive and single blog pages powered by generated markdown files
- local JSON settings storage

No images are included yet.

## Strategic scope
The current scope is blog-first, but the platform direction can later expand into:
- landing page generation
- social media post generation
- Google Business Profile content
- email/newsletter content
- multi-channel publishing workflows
- content calendar and scheduling automation

The core idea is to turn product/tour data into reusable SEO and marketing assets that support direct bookings and customer acquisition.

## Main structure
```text
WebPublisherSystem/
  README.md
  index.php                  # redirects to blog/
  settings.php               # redirects to platform/settings.php
  blog/                      # default public blog archive
    index.php
    post.php
  platform/                  # runnable platform/admin/support files
    index.php
    settings.php
    functions.php
    github.php
    content-loader.php
    system-sync.php
    assets/
      style.css
    data/
      .gitkeep
  content-system/            # AI/Codex content instructions and generated tour content
    AGENTS.md
    meta.schema.json         # JSON Schema for tour meta.json files
    tours/
      cinque-terre-full-day-tour-from-milan/
        source-facts.md
        brief.md
        keywords.md
        blog-post.md
        faq.md
        meta.json
        internal-links.md
        automation-notes.md
        qa-report.md
        CHANGELOG.md          # recommended; one bullet per refresh
        images/               # optional; hero + gallery assets
  settings/                  # reserved for future reusable setting definitions
    .gitkeep
  publishers/                # reserved for future publisher/sync modules
    .gitkeep
  templates/                 # reserved for future page/layout templates
    .gitkeep
  structures/                # reserved for future content/data structures
    .gitkeep
```

## Upload folder
Upload the full folder to your web space:

```text
WebPublisherSystem/
```

Then open the public blog archive:

```text
https://your-domain.com/WebPublisherSystem/blog/
```

Or open the settings page:

```text
https://your-domain.com/WebPublisherSystem/settings.php
```

The top-level `index.php` redirects to the blog archive. The top-level `settings.php` redirects to the platform settings page.

## Default GitHub settings
The system is prefilled for this public repository:

```text
Owner: rezaeesjd
Repo: WebSage-Solutions
Branch: main
Content path: WebPublisherSystem/content-system/tours
```

## Important
The `platform/data/` folder must be writable by PHP because settings are saved to:

```text
platform/data/settings.json
```

## Current limitation
This version can render local markdown-based blog content from the uploaded `content-system/tours/` folder. GitHub should be used mainly through the system sync/update process, not on every public page load.

Next phase can add:
- Sync from GitHub button improvements
- local cached posts
- cleaner permalink routing
- landing page generation
- social media content generation
- multi-channel publisher modules


## Workflow commands
WebPublisherSystem supports explicit command-driven workflows:
- `WPS:GENERATE_CONTENT` → create/update package files only (not published)
- `WPS:PUBLISH_BLOG` → validate package + publish readiness checks
- `WPS:GENERATE_AND_PUBLISH` → run generation then publish checks sequentially
- `WPS:PROCESS_QA` → process QA report only (no file edits)
- `WPS:FIX_PACKAGE` → repair one existing package
- `WPS:IMPROVE_SYSTEM_WORKFLOW` → update system docs/templates/checklists only
- `WPS:LIVE_VERIFY` → live archive + single-post verification only

Generation and publishing are separate. A post is not "published" until live archive and single-post verification is complete.
