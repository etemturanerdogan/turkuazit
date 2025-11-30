<?php
// admin/user_update.php
require_once __DIR__ . '/../config.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect(BASE_PATH . '/?route=admin-users');

$csrf = $_POST['csrf'] ?? '';
if (!csrf_check($csrf)) { set_flash('error','Geçersiz CSRF token'); redirect(BASE_PATH . '/?route=admin-users'); }

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) { set_flash('error','Geçersiz kullanıcı'); redirect(BASE_PATH . '/?route=admin-users'); }

$role = $_POST['role'] ?? 'client';
$allowed = ['admin','client','staff1','staff2','staff3'];
if (!in_array($role, $allowed, true)) { set_flash('error','Geçersiz rol'); redirect(BASE_PATH . '/?route=admin-user-edit&id=' . $id); }

$firma_id = isset($_POST['firma_id']) && $_POST['firma_id'] !== '' ? (int)$_POST['firma_id'] : null;
$is_active = (int)($_POST['is_active'] ?? 0);
$new_pass = trim($_POST['new_password'] ?? '');

try {
    if ($new_pass !== '') {
        $hash = password_hash($new_pass, PASSWORD_DEFAULT);
        $pdo->prepare('UPDATE users SET role = :role, firma_id = :firma, is_active = :active, password_hash = :hash WHERE id = :id')
            ->execute(['role'=>$role,'firma'=>$firma_id,'active'=>$is_active,'hash'=>$hash,'id'=>$id]);
    } else {
        $pdo->prepare('UPDATE users SET role = :role, firma_id = :firma, is_active = :active WHERE id = :id')
            ->execute(['role'=>$role,'firma'=>$firma_id,'active'=>$is_active,'id'=>$id]);
    }

    set_flash('success','Kullanıcı güncellendi.');
    redirect(BASE_PATH . '/?route=admin-users');

} catch (PDOException $e) {
    @file_put_contents(__DIR__ . '/../logs/admin_users_errors.log', date('Y-m-d H:i:s') . ' - ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
    set_flash('error','Güncelleme sırasında hata oluştu.');
    redirect(BASE_PATH . '/?route=admin-user-edit&id=' . $id);
}
