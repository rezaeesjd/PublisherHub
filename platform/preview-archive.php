<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/cache.php';

wps_require_auth();

$settings = wps_load_settings();
$index = wps_archive_index($settings);
$allRecords = is_array($index['posts'] ?? null) ? $index['posts'] : [];
$records = wps_records_by_publish_status($allRecords, ['ready_for_review', 'published', 'needs_live_verification']);

wps_render_header('Preview archive');
?>
<section class="panel">
    <h1>Preview archive (not indexed)</h1>
    <p class="muted">Internal-only queue of review/sync-stage posts. Public <code>/blog/</code> now shows only published posts.</p>
    <div class="alert" style="margin-top:10px; border-left:4px solid #d97706;">This view is private for editors and QA only. Do not share links externally.</div>
</section>

<section class="panel">
    <?php if (empty($records)): ?>
        <p class="muted">No review-stage posts right now.</p>
    <?php else: ?>
        <ul style="list-style:none; padding:0; margin:0; display:grid; gap:12px;">
            <?php foreach ($records as $record): ?>
                <?php $slug = (string) ($record['public_slug'] ?? ''); ?>
                <li class="card" style="padding:16px;">
                    <h2 style="margin-top:0;"><a href="<?php echo wps_h(wps_public_post_url($slug)); ?>" rel="nofollow"><?php echo wps_h((string) ($record['title'] ?? $slug)); ?></a></h2>
                    <p class="muted" style="margin:0 0 8px; display:flex; gap:8px; flex-wrap:wrap;">
                        <span class="qa-pill qa-pill-warning"><?php echo wps_h(wps_human_publish_status((string) ($record['publish_status'] ?? 'preview'))); ?></span>
                        <?php if (!empty($record['funnel_stage'])): ?><span class="qa-pill qa-pill-muted"><?php echo wps_h((string) $record['funnel_stage']); ?></span><?php endif; ?>
                        <?php if (!empty($record['destination'])): ?><span class="qa-pill qa-pill-muted"><?php echo wps_h((string) $record['destination']); ?></span><?php endif; ?>
                    </p>
                    <?php if (!empty($record['meta_description'])): ?><p><?php echo wps_h(wps_trim_description((string) $record['meta_description'], 170)); ?></p><?php endif; ?>
                    <?php if (!empty($record['last_qa_date'])): ?><p class="muted"><small>Last reviewed: <?php echo wps_h(wps_human_date((string) $record['last_qa_date'])); ?></small></p><?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
<?php wps_render_footer(); ?>
