<?php
// auth/login.php
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_PATH . '/?route=login');
    exit;
}

// İngilizce değişken adları kullanıyoruz: email, password
$email    = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL) ?: '';
$password = $_POST['password'] ?? '';

// Basit doğrulamalar (sanity checks)
if ($email === '' || $password === '' ) {
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
// Session içine ilk/son isimleri ayrı kaydet. Bazı eski yerler full_name okuyabilir — bu nedenle tekrar oluşturuyoruz.
$_SESSION['user'] = [
    'id'         => $user['id'],
    'first_name' => $user['first_name'] ?? ($user['full_name'] ? explode(' ', $user['full_name'])[0] : ''),
    'last_name'  => $user['last_name'] ?? ($user['full_name'] ? array_slice(explode(' ', $user['full_name']), -1)[0] : ''),
    'full_name'  => trim((string)($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')),
    'email'      => $user['email'],
    'role'       => $user['role'] ?? 'client',
    'firm_name'  => $user['firm_name'] ?? null,
];

// Eğer user_profiles tablosunda company_name varsa, session içindeki firm_name'i ona göre güncelle
$stmt = $pdo->prepare('SELECT company_name FROM user_profiles WHERE user_id = :uid LIMIT 1');
$stmt->execute(['uid' => $user['id']]);
$profileCompany = $stmt->fetchColumn();
if ($profileCompany !== false && $profileCompany !== null && $profileCompany !== '') {
    $_SESSION['user']['firm_name'] = $profileCompany;
}

// Role göre yönlendir
if ($user['role'] === 'admin') {
    header('Location: ' . BASE_PATH . '/?route=admin-dashboard');
} else {
    header('Location: ' . BASE_PATH . '/?route=client-dashboard');
}
exit;
