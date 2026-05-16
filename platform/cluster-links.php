<?php
/**
 * Cross-cluster internal-link resolver.
 *
 * Authors write cross-cluster links by package_slug using the custom URL
 * scheme `wps-cluster:<package_slug>` inside blog-post.md / faq.md, e.g.
 *
 *     See our [Lake Como guide](wps-cluster:lake-como-travel-guide).
 *
 * At render time, wps_resolve_cluster_link() looks the slug up in the
 * cluster registry. If the target asset is `status: published` and has a
 * non-empty `public_slug`, the renderer emits a real `<a>` to that URL.
 * Otherwise the link degrades to plain text so we never ship an `href` to
 * an unpublished sibling slug. Each decision is collected so the publish
 * pipeline can append a per-run cross-link report to qa-report.md.
 *
 * See SYSQA-20260516-001 for the original requirement.
 */

require_once __DIR__ . '/functions.php';

const WPS_CLUSTER_LINK_SCHEME = 'wps-cluster:';

/**
 * Build / cache the slug -> asset lookup from the cluster registry.
 *
 * Cached per-request only (not on disk); the registry file is small and
 * already loaded by the dashboard on the same requests that need this.
 *
 * @return array<string, array{cluster: array<string,mixed>, asset: array<string,mixed>}>
 */
function wps_cluster_link_index(bool $forceReload = false): array
{
    static $cache = null;
    if ($cache !== null && !$forceReload) {
        return $cache;
    }

    $cache = [];
    $result = wps_load_cluster_registry();
    if (empty($result['ok']) || empty($result['registry']['clusters'])) {
        return $cache;
    }

    foreach ($result['registry']['clusters'] as $cluster) {
        if (!is_array($cluster)) {
            continue;
        }
        foreach (($cluster['assets'] ?? []) as $asset) {
            if (!is_array($asset)) {
                continue;
            }
            $slug = trim((string) ($asset['package_slug'] ?? ''));
            if ($slug === '') {
                continue;
            }
            $cache[$slug] = ['cluster' => $cluster, 'asset' => $asset];
        }
    }

    return $cache;
}

/**
 * Resolve a package_slug to a live public URL, or return an unresolved
 * decision so the caller can degrade to plain text.
 *
 * Returned shape:
 *   [
 *     'resolved'     => bool,
 *     'href'         => string, // empty when unresolved
 *     'public_slug'  => string, // registry value (may be set even when not published)
 *     'package_slug' => string,
 *     'status'       => string, // registry asset status, '' when not found
 *     'reason'       => string, // 'ok' | 'not_found' | 'not_published' | 'no_public_slug'
 *   ]
 *
 * @return array{resolved:bool,href:string,public_slug:string,package_slug:string,status:string,reason:string}
 */
function wps_resolve_cluster_link(string $packageSlug): array
{
    $packageSlug = trim($packageSlug);
    $out = [
        'resolved'     => false,
        'href'         => '',
        'public_slug'  => '',
        'package_slug' => $packageSlug,
        'status'       => '',
        'reason'       => 'not_found',
    ];
    if ($packageSlug === '') {
        return $out;
    }

    $index = wps_cluster_link_index();
    if (!isset($index[$packageSlug])) {
        return $out;
    }

    $asset = $index[$packageSlug]['asset'];
    $status = (string) ($asset['status'] ?? '');
    $publicSlug = trim((string) ($asset['public_slug'] ?? ''));
    $out['public_slug'] = $publicSlug;
    $out['status'] = $status;

    if ($status !== 'published') {
        $out['reason'] = 'not_published';
        return $out;
    }
    if ($publicSlug === '') {
        $out['reason'] = 'no_public_slug';
        return $out;
    }

    $out['resolved'] = true;
    $out['href'] = wps_public_post_url($publicSlug);
    $out['reason'] = 'ok';
    return $out;
}

/**
 * Record a single resolver decision in the process-wide decision log.
 * The markdown renderer calls this whenever it expands a `wps-cluster:`
 * link; the audit + publish-report helpers read the log back out.
 *
 * @internal
 */
function wps_cluster_link_log_decision(array $decision): void
{
    if (!isset($GLOBALS['__wps_cluster_link_decisions']) || !is_array($GLOBALS['__wps_cluster_link_decisions'])) {
        $GLOBALS['__wps_cluster_link_decisions'] = [];
    }
    $GLOBALS['__wps_cluster_link_decisions'][] = $decision;
}

/**
 * Read and clear the decision log accumulated since the last call.
 * Caller is responsible for persisting the returned decisions if desired
 * (e.g. appending to qa-report.md during a publish run).
 *
 * @return array<int, array<string,mixed>>
 */
function wps_cluster_link_consume_decisions(): array
{
    $decisions = $GLOBALS['__wps_cluster_link_decisions'] ?? [];
    $GLOBALS['__wps_cluster_link_decisions'] = [];
    return is_array($decisions) ? $decisions : [];
}

/**
 * Audit a single tour package by scanning its blog-post.md + faq.md for
 * `wps-cluster:` links and returning the resolver decision for each.
 * Does not render or modify anything on disk.
 *
 * @return array<int, array<string,mixed>>
 */
function wps_audit_cluster_links_in_package(string $folderPath): array
{
    $decisions = [];
    foreach (['blog-post.md', 'faq.md'] as $name) {
        $path = $folderPath . '/' . $name;
        if (!is_file($path)) {
            continue;
        }
        $contents = (string) @file_get_contents($path);
        if ($contents === '') {
            continue;
        }
        if (!preg_match_all(
            '/\[([^\]]+)\]\(wps-cluster:([a-z0-9][a-z0-9-]*)\)/i',
            $contents,
            $matches,
            PREG_SET_ORDER
        )) {
            continue;
        }
        foreach ($matches as $m) {
            $decision = wps_resolve_cluster_link($m[2]);
            $decision['label'] = $m[1];
            $decision['source_file'] = $name;
            $decisions[] = $decision;
        }
    }
    return $decisions;
}

/**
 * Format a list of resolver decisions as a markdown block ready to append
 * to qa-report.md (or print from a CLI script).
 *
 * @param array<int, array<string,mixed>> $decisions
 */
function wps_format_cluster_link_report(array $decisions): string
{
    if (empty($decisions)) {
        return "## Cross-cluster link decisions\n\n_No `wps-cluster:` links found._\n";
    }

    $lines = ["## Cross-cluster link decisions\n"];
    foreach ($decisions as $d) {
        $pkg = (string) ($d['package_slug'] ?? '');
        $label = (string) ($d['label'] ?? '');
        $reason = (string) ($d['reason'] ?? '');
        $where = (string) ($d['source_file'] ?? '');
        if (!empty($d['resolved'])) {
            $href = (string) ($d['href'] ?? '');
            $lines[] = "- [linked] `{$pkg}` in `{$where}` -> {$href} (label: \"{$label}\")";
        } else {
            $lines[] = "- [deferred:{$reason}] `{$pkg}` in `{$where}` (label: \"{$label}\")";
        }
    }
    return implode("\n", $lines) . "\n";
}
