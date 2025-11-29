<?php
// Localde: http://localhost/turkuazit
// Sunucuya atınca bunu '' yapacaksın (örn: turkuazit.com'un köküne atarsan)
define('BASE_PATH', '/turkuazit'); // canlıda köke atarsan '' yaparsın

// Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// DB bağlantısı (kendi bilgine göre düzenle)
try {
    $dbHost = 'localhost:3307';
    $dbName = 'turkuazit';      // kendi db adın
    $dbUser = 'root';           // kendi kullanıcı
    $dbPass = '';               // kendi şifre

    $pdo = new PDO(
        "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die('Veritabanı bağlantı hatası: ' . $e->getMessage());
}

// AUTH yardımcıları
function current_user()
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool
{
    return isset($_SESSION['user']);
}

function is_admin(): bool
{
    return is_logged_in() && ($_SESSION['user']['role'] ?? null) === 'admin';
}

function require_login()
{
    if (!is_logged_in()) {
        header('Location: ' . BASE_PATH . '/?route=login');
        exit;
    }
}

function require_admin()
{
    if (!is_admin()) {
        header('Location: ' . BASE_PATH . '/?route=login');
        exit;
    }
}