<?php
require_once __DIR__ . '/functions.php';

function wps_publish_status_states(): array
{
    return [
        'draft',
        'needs_clarification',
        'ready_for_review',
        'needs_fix',
        'published',
        'archived',
    ];
}

function wps_publish_status_allowed_transitions(): array
{
    return [
        'draft'               => ['draft', 'needs_clarification', 'ready_for_review', 'needs_fix'],
        'needs_clarification' => ['needs_clarification', 'draft', 'ready_for_review'],
        'ready_for_review'    => ['ready_for_review', 'needs_fix', 'published', 'draft'],
        'needs_fix'           => ['needs_fix', 'draft', 'ready_for_review'],
        'published'           => ['published', 'needs_fix', 'ready_for_review', 'archived'],
        'archived'            => ['archived', 'draft'],
    ];
}


function wps_publish_status_normalize_legacy(string $state): string
{
    $legacyMap = [
        'ready_for_sync' => 'published',
        'needs_live_verification' => 'published',
    ];
    return $legacyMap[$state] ?? $state;
}

function wps_publish_status_is_valid(string $state): bool
{
    return in_array(wps_publish_status_normalize_legacy($state), wps_publish_status_states(), true);
}

function wps_publish_status_can_transition(string $from, string $to): bool
{
    $map = wps_publish_status_allowed_transitions();
    return isset($map[$from]) && in_array($to, $map[$from], true);
}

function wps_publish_status_transition(array $meta, string $next, ?string $reason = null, ?string $actor = null): array
{
    $currentRaw = (string) ($meta['publish_status'] ?? 'draft');
    $current = wps_publish_status_normalize_legacy($currentRaw);
    if (!wps_publish_status_is_valid($next)) return ['ok'=>false,'meta'=>$meta,'error'=>"Unknown publish_status '{$next}'."];
    if (!wps_publish_status_is_valid($current)) {
        $current='draft';
    }
    if ($current === $next) return ['ok'=>true,'meta'=>$meta,'error'=>''];
    if (!wps_publish_status_can_transition($current, $next)) return ['ok'=>false,'meta'=>$meta,'error'=>"publish_status: '{$current}' → '{$next}' is not an allowed transition."];

    $meta['publish_status'] = $next;
    if (in_array($next, ['ready_for_review', 'published'], true)) $meta['generation_phase_completed'] = true;
    if ($next === 'published') {
        $meta['publish_phase_completed'] = true;
        if (empty($meta['first_published_at'])) $meta['first_published_at'] = gmdate('Y-m-d');
    }
    if ($next === 'needs_clarification') { $meta['clarify_phase_required']=true; $meta['clarify_phase_completed']=false; }
    if ($next === 'needs_fix') $meta['qa_status'] = 'needs_fix';

    $history = is_array($meta['publish_status_history'] ?? null) ? $meta['publish_status_history'] : [];
    $history[] = array_filter(['from'=>$currentRaw,'to'=>$next,'at'=>gmdate('c'),'reason'=>$reason?:null,'actor'=>$actor?:null], fn($v)=>$v!==null);
    $meta['publish_status_history'] = $history;
    return ['ok'=>true,'meta'=>$meta,'error'=>''];
}
