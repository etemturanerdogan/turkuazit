<?php
// auth/profile_update.php
// Kullanıcı kendi profil / firma / adres bilgilerini güncelleme handler'ı
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_PATH . '/?route=profile');
    exit;
}

// CSRF kontrolü
$csrf = $_POST['csrf'] ?? '';
if (!csrf_check($csrf)) {
    header('Location: ' . BASE_PATH . '/?route=profile&error=' . urlencode('Sunucu doğrulama hatası.'));
    exit;
}

require_login();
$user = current_user();
$userId = (int)($_POST['user_id'] ?? 0);

// Güvenlik: user_id ile oturumdaki user id aynı olmalı
if ($userId !== $user['id']) {
    // Olası yetki yükseltme girişimi
    header('Location: ' . BASE_PATH . '/?route=profile&error=' . urlencode('Yetkisiz işlem.'));
    exit;
}

// Alanları oku ve basit validasyon yap
$companyName = trim($_POST['company_name'] ?? '');
$address1    = trim($_POST['address_line1'] ?? '');
$address2    = trim($_POST['address_line2'] ?? '');
$city        = trim($_POST['city'] ?? '');
$state       = trim($_POST['state'] ?? '');
$postal      = trim($_POST['postal_code'] ?? '');
$country     = trim($_POST['country'] ?? '');
$phone       = trim($_POST['phone'] ?? '');
$shipping    = trim($_POST['shipping_instructions'] ?? '');

// Basit sanity checks - çok katı olmasın ama saçma verileri engelle
if (mb_strlen($companyName) > 255 || mb_strlen($address1) > 255 || mb_strlen($address2) > 255) {
    header('Location: ' . BASE_PATH . '/?route=profile&error=' . urlencode('Girilen alanlar çok uzun.'));
    exit;
}

// Telefon için basit pattern (opsiyonel), sadece rakam, +, boşluk, parantez, tire
if ($phone !== '' && !preg_match('/^[0-9+()\-\s]{4,60}$/', $phone)) {
    header('Location: ' . BASE_PATH . '/?route=profile&error=' . urlencode('Telefon numarası geçersiz.'));
    exit;
}

try {
    // Var mı kontrol et
    $stmt = $pdo->prepare('SELECT id FROM user_profiles WHERE user_id = :uid LIMIT 1');
    $stmt->execute(['uid' => $userId]);
    $exists = (bool)$stmt->fetchColumn();

    if ($exists) {
        // Güncelle
        $stmt = $pdo->prepare('UPDATE user_profiles SET company_name = :company_name, address_line1 = :address1, address_line2 = :address2, city = :city, state = :state, postal_code = :postal, country = :country, phone = :phone, shipping_instructions = :shipping WHERE user_id = :uid');
        $stmt->execute([
            'company_name' => $companyName ?: null,
            'address1'     => $address1 ?: null,
            'address2'     => $address2 ?: null,
            'city'         => $city ?: null,
            'state'        => $state ?: null,
            'postal'       => $postal ?: null,
            'country'      => $country ?: null,
            'phone'        => $phone ?: null,
            'shipping'     => $shipping ?: null,
            'uid'          => $userId,
        ]);
    } else {
        // Insert
        $stmt = $pdo->prepare('INSERT INTO user_profiles (user_id, company_name, address_line1, address_line2, city, state, postal_code, country, phone, shipping_instructions) VALUES (:uid, :company_name, :address1, :address2, :city, :state, :postal, :country, :phone, :shipping)');
        $stmt->execute([
            'uid'          => $userId,
            'company_name' => $companyName ?: null,
            'address1'     => $address1 ?: null,
            'address2'     => $address2 ?: null,
            'city'         => $city ?: null,
            'state'        => $state ?: null,
            'postal'       => $postal ?: null,
            'country'      => $country ?: null,
            'phone'        => $phone ?: null,
            'shipping'     => $shipping ?: null,
        ]);
    }

    // Eğer kullanıcı tablosunda firm_name alanı varsa onu da güncelle (opsiyonel - backward compatibility)
    if ($companyName !== '') {
        $stmt = $pdo->prepare('UPDATE users SET firm_name = :firm, updated_at = NOW() WHERE id = :uid');
        $stmt->execute(['firm' => $companyName, 'uid' => $userId]);
    }

    // Session'ı güncelle (kullanıcı ekranında anlık gösterim için)
    $_SESSION['user']['firm_name'] = $companyName !== '' ? $companyName : ($_SESSION['user']['firm_name'] ?? null);

} catch (PDOException $e) {
    header('Location: ' . BASE_PATH . '/?route=profile&error=' . urlencode('Güncelleme sırasında sunucu hatası.'));
    exit;
}

header('Location: ' . BASE_PATH . '/?route=profile&success=1');
exit;
