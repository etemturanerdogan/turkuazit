<?php
// auth/register.php
// Yeni kullanıcı kayıt işlemini gerçekleştirir.
// Önemli: Giriş/kayıt sayfalarında değişken adları İngilizce (first_name, last_name, email, password)

require_once __DIR__ . '/../config.php';

// Basit log fonksiyonu — üretimde daha ileri bir log sistemi tercih edilmelidir.
function _log_register_error($msg)
{
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
    $file = $logDir . '/register_errors.log';
    $entry = date('Y-m-d H:i:s') . " - " . $msg . PHP_EOL;
    @file_put_contents($file, $entry, FILE_APPEND | LOCK_EX);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_PATH . '/?route=register');
    exit;
}

// Girdi okuma ve basit doğrulama (sanity checks)
$firstName = trim($_POST['first_name'] ?? '');
$lastName  = trim($_POST['last_name'] ?? '');
$email     = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL) ?: '';
$password  = $_POST['password'] ?? '';
$password2 = $_POST['password_confirm'] ?? '';
$firmName  = trim($_POST['firm_name'] ?? '');
$accepted  = isset($_POST['accepted_terms']);

// Basit validasyon kuralları — gerektiğinde genişletin.
if (!$accepted) {
    header('Location: ' . BASE_PATH . '/?route=register&error=' . urlencode('Lütfen kullanım koşullarını kabul edin.'));
    exit;
}

if ($firstName === '' || $lastName === '' || $email === '' || $password === '') {
    header('Location: ' . BASE_PATH . '/?route=register&error=' . urlencode('Lütfen tüm zorunlu alanları doldurun.'));
    exit;
}

// İsimler için basit bir sanity kontrol (harf, boşluk, tire, apostrof kabul)
if (!preg_match("/^[\p{L} '-]{2,100}$/u", $firstName) || !preg_match("/^[\p{L} '-]{2,100}$/u", $lastName)) {
    header('Location: ' . BASE_PATH . '/?route=register&error=' . urlencode('Ad veya soyad geçersiz görünüyor.'));
    exit;
}

// Parola kontrolü: en az 8 karakter (daha katı kurallar ekleyebilirsiniz)
if (strlen($password) < 8) {
    header('Location: ' . BASE_PATH . '/?route=register&error=' . urlencode('Şifre en az 8 karakter olmalı.'));
    exit;
}

if ($password !== $password2) {
    header('Location: ' . BASE_PATH . '/?route=register&error=' . urlencode('Şifreler eşleşmiyor.'));
    exit;
}

// E-posta zaten var mı kontrol et — sorgu başarısız olursa kaydet ve kullanıcıya genel bir mesaj göster
try {
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    if ($stmt->fetch()) {
        header('Location: ' . BASE_PATH . '/?route=register&error=' . urlencode('Bu e-posta zaten kullanılıyor.'));
        exit;
    }
} catch (PDOException $e) {
    // DB hatası olabilir (ör: tablo/alan yok) — hata kaydedilsin
    _log_register_error('SELECT users by email failed for ' . substr($email, 0, 64) . ' — ' . $e->getMessage());
    header('Location: ' . BASE_PATH . '/?route=register&error=' . urlencode('Kayıt sırasında sunucu hatası (DB kontrolü).'));
    exit;
}

// Kayıt: şifre hash'lenir, rol default client
$passwordHash = password_hash($password, PASSWORD_DEFAULT);
try {
    $stmt = $pdo->prepare('INSERT INTO users (first_name, last_name, email, password_hash, firm_name, role, is_active, created_at) VALUES (:first_name, :last_name, :email, :password_hash, :firm_name, :role, :is_active, NOW())');
    $stmt->execute([
        'first_name'    => $firstName,
        'last_name'     => $lastName,
        'email'         => $email,
        'password_hash' => $passwordHash,
        'firm_name'     => $firmName ?: null,
        'role'          => 'client', // kayıtta varsayılan rol
        'is_active'     => 1,
    ]);
} catch (PDOException $e) {
    // DB insert hatası — detay loglanır, kullanıcıya genel mesaj gösterilir
    $msg = $e->getMessage();
    _log_register_error('INSERT users failed for ' . substr($email, 0, 64) . ' — ' . $msg);

    // Eğer sütun eksikse (Unknown column) daha açıklayıcı yönerge yazalım
    if (strpos($msg, 'Unknown column') !== false || strpos($msg, '1054') !== false) {
        // Hangi sütunun eksik olduğunu loga ekleyelim
        _log_register_error('POSSIBLE MIGRATION NEEDED — eksik sütun tespit edildi. Lütfen db/migrations/20251130_add_password_hash_and_required_columns.sql dosyasını çalıştırın veya admin ile iletişime geçin.');
        header('Location: ' . BASE_PATH . '/?route=register&error=' . urlencode('Sunucuda eksik DB alanı var. Lütfen yöneticinize bildirin.'));
        exit;
    }

    header('Location: ' . BASE_PATH . '/?route=register&error=' . urlencode('Kayıt sırasında sunucu hatası (DB insert).'));
    exit;
}

// Başarılı kayıt → giriş sayfasına yönlendir, isteğe göre email doğrulama eklenebilir
header('Location: ' . BASE_PATH . '/?route=login&registered=1&success=1');
exit;
