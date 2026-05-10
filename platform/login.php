<?php
const WPS_ASSET_BASE = '.';
const WPS_SETTINGS_URL = 'settings.php';

require_once __DIR__ . '/auth.php';

if (!wps_auth_is_configured()) {
    header('Location: ' . wps_setup_url());
    exit;
}

if (wps_is_logged_in()) {
    header('Location: settings.php');
    exit;
}

$error = '';
$email = '';
$next = (string) ($_GET['next'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    wps_csrf_validate_or_die();

    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $throttleKey = wps_auth_throttle_key($email);

    if (wps_auth_failed_attempt_count($throttleKey) >= WPS_AUTH_FAILED_LOGIN_LIMIT) {
        $error = 'Too many failed login attempts from this address for this email. Wait a few minutes and try again.';
    } else {
        $admin = wps_auth_load();
        $valid = $admin
            && wps_is_allowed_admin_email((string) $admin['email'])
            && hash_equals(strtolower((string) $admin['email']), strtolower($email))
            && password_verify($password, (string) $admin['password_hash']);

        if ($valid) {
            wps_auth_clear_failed_attempts($throttleKey);
            wps_login($admin['email']);

            if (password_needs_rehash((string) $admin['password_hash'], PASSWORD_DEFAULT)) {
                wps_auth_save($admin['email'], password_hash($password, PASSWORD_DEFAULT));
            }

            $target = 'settings.php';
            if ($next !== '' && preg_match('#^/[A-Za-z0-9_\-/.?=&%]*$#', $next)) {
                $target = $next;
            }

            header('Location: ' . $target);
            exit;
        }

        wps_auth_record_failed_attempt($throttleKey);
        usleep(random_int(150000, 400000));
        $error = 'Invalid email or password.';
    }
}

wps_render_header('Sign in');
?>

<section class="panel auth-panel">
    <h1>Sign in to WebPublisherSystem</h1>
    <p class="muted">Admin access is required to manage settings, edit posts, run sync, and download backups.</p>

    <?php if ($error !== ''): ?>
        <div class="alert alert-error"><?php echo wps_h($error); ?></div>
    <?php endif; ?>

    <form method="post" class="form" autocomplete="off">
        <?php echo wps_csrf_field(); ?>

        <label>
            Email
            <input type="email" name="email" value="<?php echo wps_h($email); ?>" required autocomplete="username" autofocus>
        </label>

        <label>
            Password
            <input type="password" name="password" required autocomplete="current-password">
        </label>

        <div class="actions">
            <button type="submit">Sign in</button>
        </div>
    </form>
</section>

<?php wps_render_footer(); ?>
