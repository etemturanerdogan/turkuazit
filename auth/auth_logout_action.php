<?php
// auth/auth_logout_action.php
require_once __DIR__ . '/../app_config.php';

$_SESSION = [];
session_destroy();

header('Location: ' . BASE_PATH . '/?route=login');
exit;
