<?php
// auth/login.php
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_PATH . '/?route=login');
    exit;
}

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    header('Location: ' . BASE_PATH . '/?route=login&error=1');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email AND is_active = 1 LIMIT 1');
$stmt->execute(['email' => $email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    header('Location: ' . BASE_PATH . '/?route=login&error=1');
    exit;
}

// Giriş başarılı → session'a yaz
$_SESSION['user'] = [
    'id'        => $user['id'],
    'full_name' => $user['full_name'],
    'email'     => $user['email'],
    'role'      => $user['role'],
    'firm_name' => $user['firm_name'],
];

// Role göre yönlendir
if ($user['role'] === 'admin') {
    header('Location: ' . BASE_PATH . '/?route=admin-dashboard');
} else {
    header('Location: ' . BASE_PATH . '/?route=client-dashboard');
}
exit;
