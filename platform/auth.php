<?php
/**
 * Authentication & CSRF for the WebPublisherSystem admin.
 *
 * Single-admin model for Phase 1. The admin record lives in
 * platform/data/auth.json and is never committed to GitHub. On a
 * fresh install, setup.php walks the operator through creating it.
 */

require_once __DIR__ . '/functions.php';

const WPS_AUTH_FILE = WPS_DATA_DIR . '/auth.json';
// Legacy fallback used only when settings.admin_email is empty — preserves
// access on installs that predate the per-install admin-email setting.
const WPS_LEGACY_ADMIN_EMAIL = 'bardiaa.rz@gmail.com';
const WPS_AUTH_FAILED_LOGIN_LIMIT = 8;
const WPS_AUTH_FAILED_LOGIN_WINDOW_SECONDS = 600;

function wps_session_start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $cookieParams = [
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => wps_request_is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ];

    session_name('WPSADMIN');
    session_set_cookie_params($cookieParams);
    session_start();
}

function wps_auth_load(): ?array
{
    if (!is_file(WPS_AUTH_FILE)) {
        return null;
    }

    $json = file_get_contents(WPS_AUTH_FILE);
    $data = json_decode((string) $json, true);

    if (!is_array($data) || empty($data['email']) || empty($data['password_hash'])) {
        return null;
    }

    return $data;
}

function wps_auth_save(string $email, string $passwordHash): bool
{
    wps_ensure_data_dir();
    $payload = [
        'email' => $email,
        'password_hash' => $passwordHash,
        'created_at' => gmdate('c'),
        'updated_at' => gmdate('c'),
    ];

    return wps_atomic_write(
        WPS_AUTH_FILE,
        json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
    );
}

function wps_auth_is_configured(): bool
{
    return wps_auth_load() !== null;
}

function wps_login(string $email): void
{
    wps_session_start();
    session_regenerate_id(true);
    $_SESSION['wps_admin'] = [
        'email' => $email,
        'logged_in_at' => time(),
    ];
}

function wps_is_allowed_admin_email(string $email): bool
{
    $normalized = strtolower(trim($email));
    $allowed = wps_admin_email();
    return $normalized !== '' && $allowed !== '' && hash_equals($allowed, $normalized);
}

function wps_logout(): void
{
    wps_session_start();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

function wps_is_logged_in(): bool
{
    wps_session_start();
    return !empty($_SESSION['wps_admin']['email']);
}

function wps_current_admin_email(): string
{
    wps_session_start();
    return (string) ($_SESSION['wps_admin']['email'] ?? '');
}

function wps_login_url(): string
{
    return 'login.php';
}

function wps_setup_url(): string
{
    return 'setup.php';
}

function wps_require_auth(): void
{
    wps_enforce_https();

    if (!wps_auth_is_configured()) {
        header('Location: ' . wps_setup_url());
        exit;
    }

    if (!wps_is_logged_in()) {
        $current = $_SERVER['REQUEST_URI'] ?? '';
        $next = $current ? '?next=' . rawurlencode($current) : '';
        header('Location: ' . wps_login_url() . $next);
        exit;
    }
}

function wps_auth_failed_attempts_path(): string
{
    return WPS_DATA_DIR . '/auth-attempts.json';
}

/**
 * Per-requester throttle key. Combining the lower-cased email with the
 * client IP keeps an attacker hammering one address from locking out
 * other admins or other source addresses.
 */
function wps_auth_throttle_key(string $email): string
{
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    return strtolower(trim($email)) . '|' . $ip;
}

function wps_auth_load_attempts(): array
{
    $path = wps_auth_failed_attempts_path();
    if (!is_file($path)) {
        return [];
    }
    $decoded = json_decode((string) file_get_contents($path), true);
    return is_array($decoded) ? $decoded : [];
}

function wps_auth_filter_active(array $attempts, int $now): array
{
    $window = WPS_AUTH_FAILED_LOGIN_WINDOW_SECONDS;
    return array_values(array_filter(
        $attempts,
        fn($entry) => is_array($entry) && isset($entry['at']) && ($now - (int) $entry['at']) < $window
    ));
}

function wps_auth_record_failed_attempt(string $key): int
{
    wps_ensure_data_dir();
    $now = time();
    $attempts = wps_auth_filter_active(wps_auth_load_attempts(), $now);

    $attempts[] = ['key' => $key, 'at' => $now];

    wps_atomic_write(wps_auth_failed_attempts_path(), json_encode($attempts, JSON_PRETTY_PRINT));

    return wps_auth_failed_attempt_count($key);
}

function wps_auth_failed_attempt_count(string $key): int
{
    $now = time();
    $attempts = wps_auth_filter_active(wps_auth_load_attempts(), $now);

    return count(array_filter(
        $attempts,
        fn($entry) => is_array($entry) && (string) ($entry['key'] ?? '') === $key
    ));
}

function wps_auth_clear_failed_attempts(string $key): void
{
    $attempts = wps_auth_load_attempts();
    if (!$attempts) {
        return;
    }

    $remaining = array_values(array_filter(
        $attempts,
        fn($entry) => is_array($entry) && (string) ($entry['key'] ?? '') !== $key
    ));

    if ($remaining === $attempts) {
        return;
    }

    if (empty($remaining)) {
        @unlink(wps_auth_failed_attempts_path());
        return;
    }

    wps_atomic_write(wps_auth_failed_attempts_path(), json_encode($remaining, JSON_PRETTY_PRINT));
}

function wps_csrf_token(): string
{
    wps_session_start();
    if (empty($_SESSION['wps_csrf'])) {
        $_SESSION['wps_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['wps_csrf'];
}

function wps_csrf_field(): string
{
    $token = wps_csrf_token();
    return '<input type="hidden" name="wps_csrf" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

function wps_csrf_validate_or_die(): void
{
    wps_session_start();
    $expected = (string) ($_SESSION['wps_csrf'] ?? '');
    $provided = (string) ($_POST['wps_csrf'] ?? '');

    if ($expected === '' || $provided === '' || !hash_equals($expected, $provided)) {
        http_response_code(403);
        echo 'Security check failed (invalid or missing CSRF token). Reload the page and try again.';
        exit;
    }
}
