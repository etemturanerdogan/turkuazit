<?php
// ═══════════════════════════════════════════════════════════════════════════════
// DOSYA: app_functions.php
// AÇIKLAMA: TurkuazIT Genel Yardımcı Fonksiyonlar
// ═══════════════════════════════════════════════════════════════════════════════
// Bu dosya uygulama genelinde kullanılan yardımcı fonksiyonları içerir.
// app_config.php tarafından dahil edilir, ayrıca dahil etmeye gerek yoktur.
// 
// İÇERİK:
// 1. Flash Mesaj Sistemi - Tek seferlik kullanıcı bildirimleri
// 2. Yönlendirme - Güvenli sayfa yönlendirmesi
// 3. CSRF Koruması - Form güvenliği için token yönetimi
// 4. Rate Limiting - İstek sınırlama (brute-force koruması)
// 5. Input Temizleme - XSS koruması için sanitizasyon
// 6. Mesaj Görüntüleme - Flash mesajları HTML olarak render etme
// ═══════════════════════════════════════════════════════════════════════════════

// Oturum başlatılmamışsa başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ─────────────────────────────────────────────────────────────────────────────
// 1. FLASH MESAJ SİSTEMİ
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Flash Mesaj Kaydet
 */
function set_flash(string $type, string $message)
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Flash Mesaj Al ve Temizle
 */
function get_flash()
{
    $m = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $m;
}

// ─────────────────────────────────────────────────────────────────────────────
// 2. GÜVENLİ YÖNLENDİRME
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Güvenli Yönlendirme
 */
function redirect(string $url)
{
    header('Location: ' . $url);
    exit;
}

// ─────────────────────────────────────────────────────────────────────────────
// 3. CSRF KORUMASI
// ─────────────────────────────────────────────────────────────────────────────

/**
 * CSRF Token Oluştur veya Mevcut Olanı Döndür
 */
function csrf_token()
{
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(24));
    }
    return $_SESSION['_csrf_token'];
}

/**
 * CSRF Token Doğrula
 */
function csrf_check($token): bool
{
    if (empty($token) || empty($_SESSION['_csrf_token'])) return false;
    return hash_equals($_SESSION['_csrf_token'], (string)$token);
}

// ─────────────────────────────────────────────────────────────────────────────
// 4. RATE LIMITING
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Rate Limit Kontrolü
 */
function rate_limit_check(string $key, int $limit = 5, int $ttl = 300): bool
{
    $k = '_rl_' . $key;
    $now = time();
    $data = $_SESSION[$k] ?? ['count' => 0, 'first' => $now];

    if ($now - $data['first'] > $ttl) {
        $data = ['count' => 0, 'first' => $now];
    }

    $data['count']++;
    $_SESSION[$k] = $data;

    return $data['count'] <= $limit;
}

// ─────────────────────────────────────────────────────────────────────────────
// 5. INPUT TEMİZLEME
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Güvenli Input Temizleme
 */
function sanitize(string $v): string
{
    return trim(htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
}

// ─────────────────────────────────────────────────────────────────────────────
// 6. FLASH MESAJ GÖRÜNTÜLEME
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Flash Mesajları HTML Olarak Render Et
 */
function render_messages()
{
    $m = get_flash();
    if (!$m) return '';

    $type = $m['type'] ?? 'info';
    $msg  = htmlspecialchars($m['message']);

    $color = 'color:#6B7280;';
    if ($type === 'success') $color = 'color:#86efac;';
    if ($type === 'error')   $color = 'color:#FCA5A5;';
    if ($type === 'warning') $color = 'color:#FCD34D;';

    return '<div style="margin-bottom:.75rem; font-size:.9rem; ' . $color . '">' . $msg . '</div>';
}
