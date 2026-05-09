<?php
const WPS_ASSET_BASE = '.';
const WPS_SETTINGS_URL = 'settings.php';

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/github-import-engine.php';

wps_require_auth();

$results = [];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $connections = array_values(array_filter(
        ghimport_load_connections(),
        fn($conn) => (bool) ($conn['enabled'] ?? true)
    ));

    if (empty($connections)) {
        $error = 'No enabled GitHub Import connections found. Add/enable a connection first.';
    } else {
        foreach ($connections as $conn) {
            $connResults = ghimport_sync_connection($conn);
            $ownerRepo = ($conn['owner'] ?? 'unknown') . '/' . ($conn['repo'] ?? 'unknown');

            foreach ($connResults as $item) {
                $item['path'] = $ownerRepo . ': ' . $item['path'];
                $results[] = $item;
            }

            $summary = ghimport_results_summary($connResults);
            ghimport_update_connection_status((string) ($conn['id'] ?? ''), $summary['status']);
        }

        $settings = wps_load_settings();
        wps_ensure_archive_alias($settings);

        $errors = array_filter($results, fn($item) => $item['status'] === 'error');
        $success = empty($errors)
            ? 'System sync completed successfully using active GitHub Import connection(s).'
            : 'System sync completed with ' . count($errors) . ' error(s).';
    }
}

wps_render_header('System Sync');
?>
<section class="panel">
    <h1>Sync WebPublisherSystem from GitHub</h1>
    <p class="muted">System Sync now uses your active GitHub Import connection(s). It refreshes files from connected repositories, preserves protected runtime data such as <code>platform/data/</code>, and removes stale files that were deleted from source repositories.</p>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo wps_h($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert <?php echo str_contains($success, 'error') ? 'alert-error' : 'alert-success'; ?>"><?php echo wps_h($success); ?></div>
    <?php endif; ?>

    <form method="post" class="actions">
        <button type="submit">Sync from Active GitHub Connection(s)</button>
        <a class="button-secondary" href="settings.php">Back to Settings</a>
        <a class="button-secondary" href="github-import.php">Manage GitHub Import</a>
    </form>
</section>

<?php if ($results): ?>
    <section class="panel">
        <h2>Sync Results</h2>
        <div class="result-box">
            <ul>
                <?php foreach ($results as $item): ?>
                    <li>
                        <strong><?php echo wps_h(strtoupper($item['status'])); ?></strong>
                        — <?php echo wps_h($item['path']); ?>
                        <br><small><?php echo wps_h($item['message']); ?></small>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>
<?php endif; ?>

<?php wps_render_footer(); ?>
