<?php
/**
 * Secrets at rest for WebPublisherSystem.
 *
 * AES-256-GCM with a per-install key auto-generated in platform/data/.secret-key
 * (mode 0600, not committed). Wraps values with an "enc:v1:" prefix so plaintext
 * and encrypted values can coexist during migration.
 *
 * Callers should never reach into the key file directly. Use
 * wps_secret_encrypt() before storing and wps_secret_decrypt() when reading.
 */

require_once __DIR__ . '/functions.php';

const WPS_SECRET_KEY_FILE = WPS_DATA_DIR . '/.secret-key';
const WPS_SECRET_PREFIX   = 'enc:v1:';

function wps_secret_get_key(): string
{
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }

    wps_ensure_data_dir();

    if (!is_file(WPS_SECRET_KEY_FILE)) {
        $raw = random_bytes(32);
        wps_atomic_write(WPS_SECRET_KEY_FILE, base64_encode($raw));
        @chmod(WPS_SECRET_KEY_FILE, 0600);
        $cached = $raw;
        return $cached;
    }

    $decoded = base64_decode((string) file_get_contents(WPS_SECRET_KEY_FILE), true);
    $cached = ($decoded !== false && strlen($decoded) === 32) ? $decoded : '';

    return $cached;
}

function wps_secret_is_encrypted(string $value): bool
{
    return strncmp($value, WPS_SECRET_PREFIX, strlen(WPS_SECRET_PREFIX)) === 0;
}

function wps_secret_encrypt(string $plaintext): string
{
    if ($plaintext === '' || wps_secret_is_encrypted($plaintext)) {
        return $plaintext;
    }

    $key = wps_secret_get_key();
    if (strlen($key) !== 32 || !function_exists('openssl_encrypt')) {
        return $plaintext;
    }

    $iv  = random_bytes(12);
    $tag = '';
    $ct  = openssl_encrypt($plaintext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
    if ($ct === false) {
        return $plaintext;
    }

    return WPS_SECRET_PREFIX . base64_encode($iv . $tag . $ct);
}

function wps_secret_decrypt(string $value): string
{
    if ($value === '' || !wps_secret_is_encrypted($value)) {
        return $value;
    }

    $payload = base64_decode(substr($value, strlen(WPS_SECRET_PREFIX)), true);
    if ($payload === false || strlen($payload) < 28) {
        return '';
    }

    $iv  = substr($payload, 0, 12);
    $tag = substr($payload, 12, 16);
    $ct  = substr($payload, 28);

    $key = wps_secret_get_key();
    if (strlen($key) !== 32 || !function_exists('openssl_decrypt')) {
        return '';
    }

    $pt = openssl_decrypt($ct, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
    return $pt === false ? '' : $pt;
}

/**
 * Force-restrictive permissions on sensitive data files. Best-effort:
 * silently no-ops where chmod is not permitted (e.g. some shared hosts).
 */
function wps_secret_harden_path(string $path): void
{
    if (is_file($path)) {
        @chmod($path, 0600);
    }
}
