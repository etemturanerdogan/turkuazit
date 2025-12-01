<?php
// ═══════════════════════════════════════════════════════════════════════════════
// DOSYA: app_config.php
// AÇIKLAMA: TurkuazIT Merkezi Yapılandırma Dosyası
// ═══════════════════════════════════════════════════════════════════════════════
// Bu dosya tüm uygulama genelinde kullanılan temel yapılandırmaları,
// veritabanı bağlantısını ve kimlik doğrulama yardımcı fonksiyonlarını içerir.
// 
// İÇERİK:
// 1. Temel Sabitler - BASE_PATH tanımı
// 2. Oturum Yönetimi - Güvenli session ayarları
// 3. Yardımcı Fonksiyonlar - app_functions.php dahil edilir
// 4. Veritabanı Bağlantısı - PDO ile MySQL bağlantısı
// 5. Kimlik Doğrulama - current_user(), is_admin(), require_login() vb.
// 
// KULLANIM:
// Tüm PHP dosyalarının başında 'require_once app_config.php' ile dahil edilir.
// ═══════════════════════════════════════════════════════════════════════════════

// ─────────────────────────────────────────────────────────────────────────────
// 1. TEMEL SABİTLER
// ─────────────────────────────────────────────────────────────────────────────
// BASE_PATH: Uygulamanın kök dizini.
// Lokal geliştirme: '/turkuazit' (http://localhost/turkuazit)
// Canlı sunucu (kök dizin): '' (http://turkuazit.com)
// 
// ÖNEMLİ: Canlıya alırken bu değeri güncelleyin!
define('BASE_PATH', '/turkuazit');

// ─────────────────────────────────────────────────────────────────────────────
// 2. GÜVENLİ OTURUM YÖNETİMİ (Session Hardening)
// ─────────────────────────────────────────────────────────────────────────────
// PHP oturum yönetimini güvenli biçimde yapılandırır.
// 
// GÜVENLİK ÖZELLİKLERİ:
// - httponly: true → JavaScript'in çerezlere erişimini engeller (XSS koruması)
// - samesite: 'Lax' → CSRF saldırılarına karşı koruma
// - secure: HTTPS bağlantısında true olur
// - lifetime: 0 → Tarayıcı kapatılınca oturum sona erer
if (session_status() === PHP_SESSION_NONE) {
    // HTTPS kontrolü
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

    // Oturum çerez parametrelerini ayarla
    session_set_cookie_params([
        'lifetime' => 0,           // Tarayıcı kapatılınca oturum biter
        'path' => '/',             // Tüm site için geçerli
        'domain' => '',            // Varsayılan domain
        'secure' => $secure,       // HTTPS'de güvenli çerez
        'httponly' => true,        // JavaScript erişimi engelle
        'samesite' => 'Lax'        // CSRF koruması
    ]);

    // Oturumu başlat
    session_start();
}

// ─────────────────────────────────────────────────────────────────────────────
// 3. YARDIMCI FONKSİYONLAR
// ─────────────────────────────────────────────────────────────────────────────
// app_functions.php dosyası genel yardımcı fonksiyonları içerir:
// - CSRF token yönetimi (csrf_token, csrf_check)
// - Flash mesajlar (set_flash, get_flash, render_messages)
// - Yönlendirme (redirect)
// - Rate limiting (rate_limit_check)
// - Güvenli input temizleme (sanitize)
require_once __DIR__ . '/app_functions.php';

// ─────────────────────────────────────────────────────────────────────────────
// 4. VERİTABANI BAĞLANTISI (PDO)
// ─────────────────────────────────────────────────────────────────────────────
// PDO (PHP Data Objects) kullanarak MySQL veritabanına bağlanır.
// 
// BAĞLANTI AYARLARI:
// - host: Veritabanı sunucusu (localhost veya IP)
// - port: MySQL portu (varsayılan 3306, Laragon için 3307 olabilir)
// - dbname: Veritabanı adı
// - charset: utf8mb4 (Türkçe ve emoji desteği)
// 
// PDO SEÇENEKLERİ:
// - ERRMODE_EXCEPTION: Hataları istisna olarak fırlatır
// - FETCH_ASSOC: Sonuçları ilgili dizi olarak döndürür
// 
// ÖNEMLİ: Canlıya alırken bu bilgileri güncelleyin!
try {
    // Veritabanı bağlantı bilgileri
    $dbHost = 'localhost:3307';  // Laragon için 3307, varsayılan 3306
    $dbName = 'turkuazit';       // Veritabanı adı
    $dbUser = 'root';            // Veritabanı kullanıcı adı
    $dbPass = '';                // Veritabanı şifresi

    // PDO bağlantısı oluştur
    $pdo = new PDO(
        "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,      // Hata yönetimi
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Varsayılan getirme modu
        ]
    );
} catch (PDOException $e) {
    // Bağlantı hatası durumunda kullanıcıya bilgi ver ve çık
    die('⚠️ Veritabanı bağlantı hatası: ' . $e->getMessage());
}

// ─────────────────────────────────────────────────────────────────────────────
// 5. KİMLİK DOĞRULAMA (Authentication) YARDIMCI FONKSİYONLARI
// ─────────────────────────────────────────────────────────────────────────────
// Bu fonksiyonlar kullanıcı kimlik doğrulaması ve yetkilendirme için kullanılır.
// Tüm uygulama genelinde ortak olarak erişilebilirler.

/**
 * Mevcut Kullanıcı Bilgilerini Döndür
 * ─────────────────────────────────────────────────────────────────────────────
 * Oturumda saklanan kullanıcı nesnesini döndürür.
 * Eğer first_name ve last_name varsa, full_name otomatik oluşturulur.
 * 
 * @return array|null Kullanıcı bilgileri veya null (giriş yapılmamışsa)
 * 
 * DÖNEN ALANLAR:
 * - id: Kullanıcı ID
 * - email: E-posta adresi
 * - first_name: Ad
 * - last_name: Soyad
 * - full_name: Tam ad (otomatik birleştirilir)
 * - role: Rol (admin, client)
 * - firma_id: Bağlı firma ID (nullable)
 */
function current_user()
{
    $u = $_SESSION['user'] ?? null;
    if ($u) {
        // full_name yoksa ad ve soyaddan oluştur
        if (empty($u['full_name']) && (!empty($u['first_name']) || !empty($u['last_name']))) {
            $u['full_name'] = trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''));
        }
    }
    return $u;
}

/**
 * Kullanıcı Giriş Yapmış mı Kontrol Et
 * ─────────────────────────────────────────────────────────────────────────────
 * @return bool Giriş yapılmışsa true, yapılmamışsa false
 */
function is_logged_in(): bool
{
    return isset($_SESSION['user']);
}

/**
 * Kullanıcı Admin mi Kontrol Et
 * ─────────────────────────────────────────────────────────────────────────────
 * Hem giriş yapmış olmalı hem de 'admin' rolüne sahip olmalı.
 * 
 * @return bool Admin ise true, değilse false
 */
function is_admin(): bool
{
    return is_logged_in() && ($_SESSION['user']['role'] ?? null) === 'admin';
}

/**
 * Giriş Zorunluluğu Kontrolü
 * ─────────────────────────────────────────────────────────────────────────────
 * Giriş yapmamış kullanıcıları login sayfasına yönlendirir.
 * Korumak istediğiniz sayfalarda bu fonksiyonu çağırın.
 * 
 * KULLANIM:
 * require_once 'app_config.php';
 * require_login(); // Giriş yapmamışsa buradan sonrası çalışmaz
 */
function require_login()
{
    if (!is_logged_in()) {
        header('Location: ' . BASE_PATH . '/?route=login');
        exit;
    }
}

/**
 * Admin Yetkisi Zorunluluğu Kontrolü
 * ─────────────────────────────────────────────────────────────────────────────
 * Admin olmayan kullanıcıları login sayfasına yönlendirir.
 * Sadece admin erişebilecek sayfalarda bu fonksiyonu çağırın.
 * 
 * KULLANIM:
 * require_once 'app_config.php';
 * require_admin(); // Admin değilse buradan sonrası çalışmaz
 */
function require_admin()
{
    if (!is_admin()) {
        header('Location: ' . BASE_PATH . '/?route=login');
        exit;
    }
}
    