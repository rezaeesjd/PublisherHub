<?php
/**
 * QA gate: runs the Phase 1 rules in qa-rules.php against every tour
 * package. Works both as a web view (admin-only) and as a CLI script.
 *
 * Web:  GET /platform/qa.php
 * CLI:  php platform/qa.php       (exit 0 = pass, 1 = warnings, 2 = failures)
 */

require_once __DIR__ . '/qa-rules.php';

$toursRoot = realpath(__DIR__ . '/../content-system/tours');

if ($toursRoot === false) {
    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, "content-system/tours folder not found.\n");
        exit(2);
    }
    require_once __DIR__ . '/auth.php';
    wps_require_auth();
    wps_render_header('QA report');
    echo '<section class="panel"><div class="alert alert-error">content-system/tours folder not found.</div></section>';
    wps_render_footer();
    exit;
}

$reports = wps_qa_run_all($toursRoot);

$totals = ['pass' => 0, 'warning' => 0, 'fail' => 0];
foreach ($reports as $r) {
    $key = $r['overall'] === 'pass' ? 'pass' : ($r['overall'] === 'warning' ? 'warning' : 'fail');
    $totals[$key]++;
}

if (PHP_SAPI === 'cli') {
    foreach ($reports as $report) {
        $tag = strtoupper($report['overall']);
        echo "[{$tag}] {$report['tour']}\n";
        foreach ($report['findings'] as $finding) {
            $sev = strtoupper($finding['severity']);
            echo "  - [{$sev}] {$finding['code']}: {$finding['message']}\n";
        }
    }
    echo "\nTotals: pass={$totals['pass']} warning={$totals['warning']} fail={$totals['fail']}\n";

    if ($totals['fail'] > 0) {
        exit(2);
    }
    if ($totals['warning'] > 0) {
        exit(1);
    }
    exit(0);
}

require_once __DIR__ . '/auth.php';
wps_require_auth();

const WPS_ASSET_BASE = '.';
const WPS_SETTINGS_URL = 'settings.php';

wps_render_header('QA report');
?>

<section class="panel">
    <h1>QA report</h1>
    <p class="muted">Each tour package is checked against the Phase 1 publish-readiness rules. <strong>Fail</strong>-level findings block publish; <strong>warn</strong>-level findings should be reviewed before going live.</p>
    <ul>
        <li>Pass: <strong><?php echo (int) $totals['pass']; ?></strong></li>
        <li>Warning: <strong><?php echo (int) $totals['warning']; ?></strong></li>
        <li>Fail: <strong><?php echo (int) $totals['fail']; ?></strong></li>
    </ul>
</section>

<?php foreach ($reports as $report): ?>
    <section class="panel">
        <h2>
            <?php echo wps_h($report['tour']); ?>
            <span class="qa-pill qa-pill-<?php echo wps_h($report['overall']); ?>"><?php echo wps_h(strtoupper($report['overall'])); ?></span>
        </h2>
        <?php if (empty($report['findings'])): ?>
            <p class="muted">No findings. Package passes Phase 1 QA.</p>
        <?php else: ?>
            <ul class="qa-findings">
                <?php foreach ($report['findings'] as $finding): ?>
                    <li>
                        <strong><?php echo wps_h(strtoupper($finding['severity'])); ?></strong>
                        — <code><?php echo wps_h($finding['code']); ?></code>
                        <br><?php echo wps_h($finding['message']); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
<?php endforeach; ?>

<section class="panel muted-panel">
    <div class="actions">
        <a class="button-secondary" href="settings.php">Back to Settings</a>
    </div>
</section>

<?php wps_render_footer(); ?>
