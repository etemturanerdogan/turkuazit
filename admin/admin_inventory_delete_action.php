<?php
// admin/admin_inventory_delete_action.php
require_once __DIR__ . '/../app_config.php';
require_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$csrf = $_GET['csrf'] ?? '';

if ($id <= 0 || !csrf_check($csrf)) {
    set_flash('error', 'Geçersiz istek.');
    redirect(BASE_PATH . '/?route=admin-envanter-liste');
}

try {
    // Dosyaları sil (fs) ve envanter_dosyalar tablosundan sil
    $stmt = $pdo->prepare('SELECT dosya_yolu FROM envanter_dosyalar WHERE envanter_id = :id');
    $stmt->execute(['id' => $id]);
    $files = $stmt->fetchAll();
    foreach ($files as $f) {
        $path = __DIR__ . '/../' . $f['dosya_yolu'];
        if (is_file($path)) @unlink($path);
    }

    // DB key cleanup
    $pdo->prepare('DELETE FROM envanter_dosyalar WHERE envanter_id = :id')->execute(['id' => $id]);
    $pdo->prepare('DELETE FROM envanter_kutu_icerik WHERE envanter_id = :id')->execute(['id' => $id]);
    $pdo->prepare('DELETE FROM envanter WHERE id = :id')->execute(['id' => $id]);

    set_flash('success', 'Envanter ve ilgili veriler silindi.');
    redirect(BASE_PATH . '/?route=admin-envanter-liste');

} catch (PDOException $e) {
    @file_put_contents(__DIR__ . '/../logs/admin_inventory_errors.log', date('Y-m-d H:i:s') . ' - ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
    set_flash('error', 'Silme sırasında hata oluştu.');
    redirect(BASE_PATH . '/?route=admin-envanter-liste');
}
