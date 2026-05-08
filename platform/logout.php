<?php
require_once __DIR__ . '/auth.php';

wps_logout();
header('Location: login.php');
exit;
