<?php
require_once __DIR__ . '/functions.php';

const WPS_POST_OVERRIDES_DIR = __DIR__ . '/data/post-overrides';

function wps_post_safe_slug(string $slug): string
{
    $slug = trim($slug);
    return preg_match('/^[a-zA-Z0-9_-]+$/', $slug) ? $slug : '';
}

function wps_post_override_path(string $baseSlug): string
{
    return WPS_POST_OVERRIDES_DIR . '/' . wps_post_safe_slug($baseSlug) . '.json';
}

function wps_ensure_post_overrides_dir(): void
{
    if (!is_dir(WPS_POST_OVERRIDES_DIR)) {
        mkdir(WPS_POST_OVERRIDES_DIR, 0755, true);
    }
}

function wps_load_post_override(string $baseSlug): array
{
    $safeSlug = wps_post_safe_slug($baseSlug);
    if ($safeSlug === '') {
        return [];
    }

    $path = wps_post_override_path($safeSlug);
    if (!is_file($path)) {
        return [];
    }

    $json = file_get_contents($path);
    $data = json_decode((string) $json, true);

    return is_array($data) ? $data : [];
}

/**
 * Persist a post override. Writes are atomic and protected against
 * public_slug collisions with other tours.
 *
 * @return array{ok: bool, error: string}
 */
function wps_save_post_override(string $baseSlug, array $override): array
{
    $safeSlug = wps_post_safe_slug($baseSlug);
    if ($safeSlug === '') {
        return ['ok' => false, 'error' => 'Invalid base slug.'];
    }

    wps_ensure_post_overrides_dir();
    $override['base_slug'] = $safeSlug;
    $override['updated_at'] = gmdate('c');

    if (isset($override['public_slug'])) {
        $override['public_slug'] = wps_post_safe_slug((string) $override['public_slug']);
        if ($override['public_slug'] === '') {
            $override['public_slug'] = $safeSlug;
        }

        if ($override['public_slug'] !== $safeSlug && function_exists('wps_load_settings')) {
            $settings = wps_load_settings();
            if (wps_public_slug_in_use($settings, $override['public_slug'], $safeSlug)) {
                return ['ok' => false, 'error' => 'That public slug is already used by another post.'];
            }
        }
    }

    $ok = wps_atomic_write(
        wps_post_override_path($safeSlug),
        json_encode($override, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    );

    return $ok
        ? ['ok' => true, 'error' => '']
        : ['ok' => false, 'error' => 'Could not write override file. Check that platform/data/ is writable.'];
}

/**
 * Returns true if any other tour already exposes the given public slug.
 * Pass the current tour's base_slug as $excludeBaseSlug so a tour can
 * keep its own slug.
 */
function wps_public_slug_in_use(array $settings, string $publicSlug, string $excludeBaseSlug): bool
{
    $publicSlug = wps_post_safe_slug($publicSlug);
    $excludeBaseSlug = wps_post_safe_slug($excludeBaseSlug);
    if ($publicSlug === '' || !function_exists('wps_get_posts')) {
        return false;
    }

    $postsResult = wps_get_posts($settings);
    if (!$postsResult['ok']) {
        return false;
    }

    foreach ($postsResult['posts'] as $post) {
        $postBaseSlug = wps_post_safe_slug((string) ($post['slug'] ?? ''));
        if ($postBaseSlug === $excludeBaseSlug) {
            continue;
        }

        $applied = wps_apply_post_override($post);
        $appliedPublic = wps_post_safe_slug((string) ($applied['public_slug'] ?? ''));
        $appliedBase = wps_post_safe_slug((string) ($applied['base_slug'] ?? ''));

        if ($appliedPublic === $publicSlug || $appliedBase === $publicSlug) {
            return true;
        }
    }

    return false;
}

function wps_post_public_slug(array $post): string
{
    $baseSlug = wps_post_safe_slug((string) ($post['base_slug'] ?? $post['slug'] ?? ''));
    $override = wps_load_post_override($baseSlug);
    $publicSlug = wps_post_safe_slug((string) ($override['public_slug'] ?? ''));

    return $publicSlug !== '' ? $publicSlug : $baseSlug;
}

function wps_apply_post_override(array $post): array
{
    $baseSlug = wps_post_safe_slug((string) ($post['slug'] ?? ''));
    $post['base_slug'] = $baseSlug;

    $override = wps_load_post_override($baseSlug);
    if (!$override) {
        $post['public_slug'] = $baseSlug;
        $post['has_local_edits'] = false;
        $post['legacy_slugs'] = [];
        return $post;
    }

    foreach (['title', 'meta_description', 'primary_keyword', 'funnel_stage', 'product_reference_code'] as $field) {
        if (array_key_exists($field, $override)) {
            $post[$field] = (string) $override[$field];
        }
    }

    $publicSlug = wps_post_safe_slug((string) ($override['public_slug'] ?? ''));
    $post['public_slug'] = $publicSlug !== '' ? $publicSlug : $baseSlug;
    $post['has_local_edits'] = true;
    $post['local_override'] = $override;

    $legacy = $override['legacy_slugs'] ?? [];
    $post['legacy_slugs'] = is_array($legacy)
        ? array_values(array_filter(array_map(fn($s) => wps_post_safe_slug((string) $s), $legacy)))
        : [];

    return $post;
}

function wps_find_post_by_public_or_base_slug(array $settings, string $requestedSlug): array
{
    $requestedSlug = wps_post_safe_slug($requestedSlug);
    if ($requestedSlug === '' || !function_exists('wps_get_posts')) {
        return ['ok' => false, 'error' => 'Post not found.', 'post' => null];
    }

    $postsResult = wps_get_posts($settings);
    if (!$postsResult['ok']) {
        return ['ok' => false, 'error' => $postsResult['error'], 'post' => null];
    }

    foreach ($postsResult['posts'] as $post) {
        $post = wps_apply_post_override($post);
        if (($post['base_slug'] ?? '') === $requestedSlug || ($post['public_slug'] ?? '') === $requestedSlug) {
            return ['ok' => true, 'error' => '', 'post' => $post, 'matched_via' => 'current'];
        }
        if (in_array($requestedSlug, $post['legacy_slugs'] ?? [], true)) {
            // Legacy slug hit — the caller should 301 to the current public slug.
            return ['ok' => true, 'error' => '', 'post' => $post, 'matched_via' => 'legacy'];
        }
    }

    return ['ok' => false, 'error' => 'Post not found.', 'post' => null];
}
