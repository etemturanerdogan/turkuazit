<?php
// app_functions.php — Proje genel yardımcı fonksiyonları
// NOT: Bu dosya `app_config.php` ile aynı dizinde kullanılır. Burada CSRF, flash mesajlar, redirect, basit rate-limit helper vb bulunur.

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

/**
 * Flash mesaj ekle / al
 * Kullanım: set_flash('success', 'Kaydedildi'); sonra get_flash() ile göster.
 */
function set_flash(string $type, string $message)
{
	$_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash()
{
	$m = $_SESSION['flash'] ?? null;
	unset($_SESSION['flash']);
	return $m;
}

/**
 * Basit güvenli redirect
 */
function redirect(string $url)
{
	header('Location: ' . $url);
	exit;
}

/**
 * CSRF token yönetimi
 */
function csrf_token()
{
	if (empty($_SESSION['_csrf_token'])) {
		$_SESSION['_csrf_token'] = bin2hex(random_bytes(24));
	}
	return $_SESSION['_csrf_token'];
}

function csrf_check($token): bool
{
	if (empty($token) || empty($_SESSION['_csrf_token'])) return false;
	return hash_equals($_SESSION['_csrf_token'], (string)$token);
}

/**
 * Basit rate limiter (session-based)
 * - key: benzersiz anahtar
 * - limit: kaç istek izlenecek
 * - ttl: saniye cinsinden süre
 */
function rate_limit_check(string $key, int $limit = 5, int $ttl = 300): bool
{
	$k = '_rl_' . $key;
	$now = time();
	$data = $_SESSION[$k] ?? ['count' => 0, 'first' => $now];

	if ($now - $data['first'] > $ttl) {
		// reset
		$data = ['count' => 0, 'first' => $now];
	}

	$data['count']++;
	$_SESSION[$k] = $data;

	return $data['count'] <= $limit;
}

/**
 * Simple input sanitization helper
 */
function sanitize(string $v): string
{
	return trim(htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
}

/**
 * Kullanıcıya bir sayfa üstünde küçük uyarı/başarı mesajı gösterimi
 */
function render_messages()
{
	$m = get_flash();
	if (!$m) return '';
	$type = $m['type'] ?? 'info';
	$msg  = htmlspecialchars($m['message']);
	$color = 'color:#6B7280;';
	if ($type === 'success') $color = 'color:#86efac;';
	if ($type === 'error') $color = 'color:#FCA5A5;';
	return '<div style="margin-bottom:.75rem; font-size:.9rem; ' . $color . '">' . $msg . '</div>';
}

// EOF

