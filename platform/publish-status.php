<?php
/**
 * Publish-status state machine.
 *
 * The meta.publish_status field is the spine of the generate → review →
 * publish workflow. Until now transitions happened ad-hoc across AI runs,
 * QA stamps, and hand-edited JSON, which made invalid transitions easy
 * (e.g. jumping straight from "draft" to "published" without ever going
 * through "ready_for_review"). This module centralizes the legal moves so
 * any code path that mutates publish_status routes through one helper.
 *
 * Callers:
 *   wps_publish_status_states()             → list of allowed states
 *   wps_publish_status_allowed_transitions  → state → list of legal nexts
 *   wps_publish_status_transition($meta, $next, ?$reason) → returns
 *     ['ok' => bool, 'meta' => array, 'error' => string]
 *
 * Transitions are append-only logged in meta.publish_status_history so
 * the dashboard can show "who moved this to X and when".
 */

require_once __DIR__ . '/functions.php';

function wps_publish_status_states(): array
{
    return [
        'draft',
        'needs_clarification',
        'ready_for_review',
        'needs_fix',
        'ready_for_sync',
        'needs_live_verification',
        'published',
        'archived',
    ];
}

function wps_publish_status_allowed_transitions(): array
{
    return [
        'draft'                   => ['draft', 'needs_clarification', 'ready_for_review', 'needs_fix'],
        'needs_clarification'     => ['needs_clarification', 'draft', 'ready_for_review'],
        'ready_for_review'        => ['ready_for_review', 'needs_fix', 'ready_for_sync', 'draft'],
        'needs_fix'               => ['needs_fix', 'draft', 'ready_for_review'],
        'ready_for_sync'          => ['ready_for_sync', 'needs_live_verification', 'needs_fix', 'ready_for_review'],
        'needs_live_verification' => ['needs_live_verification', 'published', 'needs_fix', 'ready_for_review'],
        'published'               => ['published', 'needs_fix', 'archived'],
        'archived'                => ['archived', 'draft'],
    ];
}

function wps_publish_status_is_valid(string $state): bool
{
    return in_array($state, wps_publish_status_states(), true);
}

function wps_publish_status_can_transition(string $from, string $to): bool
{
    $map = wps_publish_status_allowed_transitions();
    if (!isset($map[$from])) {
        return false;
    }
    return in_array($to, $map[$from], true);
}

/**
 * Apply a transition to a meta array. Pure: returns a new array on
 * success, never throws, never mutates the caller's array.
 *
 * @param array  $meta    Current meta.json contents
 * @param string $next    Target publish_status
 * @param string|null $reason Optional reason logged in history
 * @param string|null $actor  Optional actor (email / "system" / "ai")
 * @return array{ok: bool, meta: array, error: string}
 */
function wps_publish_status_transition(array $meta, string $next, ?string $reason = null, ?string $actor = null): array
{
    $current = (string) ($meta['publish_status'] ?? 'draft');

    if (!wps_publish_status_is_valid($next)) {
        return ['ok' => false, 'meta' => $meta, 'error' => "Unknown publish_status '{$next}'."];
    }

    if (!wps_publish_status_is_valid($current)) {
        // Bootstrap: unknown current state — coerce to draft so we can
        // recover instead of hard-failing.
        $current = 'draft';
        $meta['publish_status'] = 'draft';
    }

    if ($current === $next) {
        return ['ok' => true, 'meta' => $meta, 'error' => ''];
    }

    if (!wps_publish_status_can_transition($current, $next)) {
        return [
            'ok'    => false,
            'meta'  => $meta,
            'error' => "publish_status: '{$current}' → '{$next}' is not an allowed transition.",
        ];
    }

    $meta['publish_status'] = $next;

    // Maintain phase markers so the rest of the system stays consistent.
    if ($next === 'ready_for_review' || $next === 'ready_for_sync' || $next === 'needs_live_verification' || $next === 'published') {
        $meta['generation_phase_completed'] = true;
    }
    if ($next === 'ready_for_sync' || $next === 'needs_live_verification' || $next === 'published') {
        $meta['publish_phase_completed'] = true;
    }
    if ($next === 'published') {
        $meta['live_verification_completed'] = true;
        if (empty($meta['first_published_at'])) {
            $meta['first_published_at'] = gmdate('Y-m-d');
        }
    }
    if ($next === 'needs_clarification') {
        $meta['clarify_phase_required'] = true;
        $meta['clarify_phase_completed'] = false;
    }
    if ($next === 'needs_fix') {
        $meta['qa_status'] = 'needs_fix';
    }

    $history = is_array($meta['publish_status_history'] ?? null) ? $meta['publish_status_history'] : [];
    $history[] = array_filter([
        'from'    => $current,
        'to'      => $next,
        'at'      => gmdate('c'),
        'reason'  => $reason ?: null,
        'actor'   => $actor ?: null,
    ], fn($v) => $v !== null);
    $meta['publish_status_history'] = $history;

    return ['ok' => true, 'meta' => $meta, 'error' => ''];
}
