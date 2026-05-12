<?php
const WPS_ASSET_BASE = '.';
const WPS_SETTINGS_URL = 'settings.php';

require_once __DIR__ . '/auth.php';

if (wps_auth_is_configured()) {
    header('Location: login.php');
    exit;
}

// Start the session unconditionally so the CSRF token is issued on the
// initial GET (rendered by wps_csrf_field()) and validated on POST.
wps_session_start();

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    wps_csrf_validate_or_die();

    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['confirm'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid admin email address.';
    } elseif (strlen($password) < 12) {
        $error = 'Password must be at least 12 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Password and confirmation do not match.';
    } elseif (!wps_auth_save($email, password_hash($password, PASSWORD_DEFAULT))) {
        $error = 'Could not write platform/data/auth.json. Make sure platform/data/ is writable.';
    } else {
        // Persist the admin email into settings so wps_admin_email() works
        // for this install without falling back to the legacy constant.
        $settings = wps_load_settings();
        $settings['admin_email'] = strtolower($email);
        wps_save_settings($settings);
        wps_login($email);
        header('Location: settings.php');
        exit;
    }
}

wps_render_header('First-run setup');
?>

<section class="panel auth-panel">
    <h1>Create your admin account</h1>
    <p class="muted">No admin user has been configured yet. Pick the credentials you want to use to manage this WebPublisherSystem install. The credentials are stored as a one-way hash in <code>platform/data/auth.json</code>, which is excluded from GitHub sync.</p>

    <?php if ($error !== ''): ?>
        <div class="alert alert-error"><?php echo wps_h($error); ?></div>
    <?php endif; ?>

    <form method="post" class="form" autocomplete="off">
        <?php echo wps_csrf_field(); ?>

        <label>
            Admin email
            <input type="email" name="email" value="<?php echo wps_h($email); ?>" required autocomplete="username" autofocus>
        </label>

        <label>
            Password (12+ characters)
            <input type="password" name="password" required minlength="12" autocomplete="new-password">
        </label>

        <label>
            Confirm password
            <input type="password" name="confirm" required minlength="12" autocomplete="new-password">
        </label>

        <div class="actions">
            <button type="submit">Create admin and sign in</button>
        </div>
    </form>
</section>

<?php wps_render_footer(); ?>
