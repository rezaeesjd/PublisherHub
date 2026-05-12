<?php
// The Clusters page has been merged into the dashboard. Keep this file as a
// permanent redirect so existing bookmarks and anchor links still work.
$target = 'index.php';
$anchor = isset($_SERVER['QUERY_STRING']) ? (string) $_SERVER['QUERY_STRING'] : '';
if ($anchor !== '') {
    $target .= '?' . $anchor;
}
header('Location: ' . $target, true, 301);
exit;
