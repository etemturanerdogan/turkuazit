<?php
// auth/logout.php
require_once __DIR__ . '/../config.php';

$_SESSION = [];
session_destroy();

header('Location: ' . BASE_PATH . '/?route=login');
exit;
