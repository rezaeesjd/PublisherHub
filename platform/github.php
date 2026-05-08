<?php
require_once __DIR__ . '/functions.php';

function wps_github_api_url(array $settings, string $path = ''): string
{
    $owner = rawurlencode($settings['github_owner']);
    $repo = rawurlencode($settings['github_repo']);
    $branch = rawurlencode($settings['github_branch']);
    $cleanPath = trim($path, '/');

    if ($cleanPath === '') {
        return "https://api.github.com/repos/{$owner}/{$repo}/contents?ref={$branch}";
    }

    $encodedPath = implode('/', array_map('rawurlencode', explode('/', $cleanPath)));
    return "https://api.github.com/repos/{$owner}/{$repo}/contents/{$encodedPath}?ref={$branch}";
}

function wps_github_fetch_json(string $url): array
{
    $headers = [
        'User-Agent: WebPublisherSystem',
        'Accept: application/vnd.github+json',
    ];

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        $body = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($body === false || $error) {
            return ['ok' => false, 'status' => $httpCode, 'error' => $error ?: 'GitHub request failed.', 'data' => null];
        }
    } else {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => implode("\r\n", $headers),
                'timeout' => 20,
            ],
        ]);
        $body = @file_get_contents($url, false, $context);
        $httpCode = 0;

        if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $matches)) {
            $httpCode = (int) $matches[1];
        }

        if ($body === false) {
            return ['ok' => false, 'status' => $httpCode, 'error' => 'GitHub request failed. cURL is unavailable and file_get_contents could not fetch the URL.', 'data' => null];
        }
    }

    $data = json_decode($body, true);

    if ($httpCode < 200 || $httpCode >= 300) {
        $message = is_array($data) && isset($data['message']) ? $data['message'] : 'GitHub returned an error.';
        return ['ok' => false, 'status' => $httpCode, 'error' => $message, 'data' => $data];
    }

    return ['ok' => true, 'status' => $httpCode, 'error' => '', 'data' => $data];
}

function wps_test_github_connection(array $settings): array
{
    $path = trim($settings['github_content_path'] ?? '', '/');
    $url = wps_github_api_url($settings, $path);
    $result = wps_github_fetch_json($url);

    if (!$result['ok']) {
        return [
            'ok' => false,
            'message' => 'Connection failed: ' . $result['error'],
            'url' => $url,
            'items' => [],
        ];
    }

    $items = [];
    if (is_array($result['data'])) {
        foreach ($result['data'] as $item) {
            if (is_array($item) && isset($item['name'], $item['type'])) {
                $items[] = [
                    'name' => $item['name'],
                    'type' => $item['type'],
                    'path' => $item['path'] ?? '',
                ];
            }
        }
    }

    return [
        'ok' => true,
        'message' => 'Connection successful. Found ' . count($items) . ' item(s) in the configured path.',
        'url' => $url,
        'items' => $items,
    ];
}
