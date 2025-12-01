<?php
// admin/admin_inventory_update_action.php
require_once __DIR__ . '/../app_config.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect(BASE_PATH . '/?route=admin-envanter-liste');

$csrf = $_POST['csrf'] ?? '';
if (!csrf_check($csrf)) {
    set_flash('error', 'Geçersiz CSRF token');
    redirect(BASE_PATH . '/?route=admin-envanter-liste');
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    set_flash('error', 'Geçersiz id');
    redirect(BASE_PATH . '/?route=admin-envanter-liste');
}

// sanitize
$firma_id = isset($_POST['firma_id']) ? (int)$_POST['firma_id'] : null;
$lokasyon_id = isset($_POST['lokasyon_id']) && $_POST['lokasyon_id'] !== '' ? (int)$_POST['lokasyon_id'] : null;
$urun_adi = sanitize($_POST['urun_adi'] ?? '');
$marka = sanitize($_POST['marka'] ?? '');
$seri_no = sanitize($_POST['seri_no'] ?? '');
$barkod = sanitize($_POST['barkod'] ?? '');
$urun_aciklama = sanitize($_POST['urun_aciklama'] ?? '');
$kutu_icerik = trim($_POST['kutu_icerik'] ?? '');

try {
    $stmt = $pdo->prepare('UPDATE envanter SET firma_id = :firma_id, lokasyon_id = :lokasyon_id, urun_adi = :urun_adi, marka = :marka, seri_no = :seri_no, barkod = :barkod, urun_aciklama = :acik WHERE id = :id');
    $stmt->execute(['firma_id' => $firma_id, 'lokasyon_id' => $lokasyon_id, 'urun_adi' => $urun_adi, 'marka' => $marka, 'seri_no' => $seri_no, 'barkod' => $barkod, 'acik' => $urun_aciklama, 'id' => $id]);

    // Yeni kutu içerikleri eklensin
    if ($kutu_icerik !== '') {
        $lines = preg_split('/\r?\n/', $kutu_icerik);
        $ins = $pdo->prepare('INSERT INTO envanter_kutu_icerik (envanter_id, bilesen_adi, adet, seri_no, notlar) VALUES (:eid, :bilesen, :adet, :seri, :notlar)');
        foreach ($lines as $l) {
            $name = trim($l);
            if ($name === '') continue;
            $ins->execute(['eid' => $id, 'bilesen' => $name, 'adet' => 1, 'seri' => null, 'notlar' => null]);
        }
    }

    // Dosya yükleme
    if (!empty($_FILES['files']) && is_array($_FILES['files']['name'])) {
        $uploadDir = __DIR__ . '/../uploads/envanter/' . $id;
        if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);
        foreach ($_FILES['files']['name'] as $i => $origName) {
            $tmp = $_FILES['files']['tmp_name'][$i] ?? null;
            $err = $_FILES['files']['error'][$i] ?? 1;
            if ($tmp && $err === UPLOAD_ERR_OK) {
                $ext = pathinfo($origName, PATHINFO_EXTENSION);
                $target = $uploadDir . '/' . time() . '_' . bin2hex(random_bytes(6)) . ($ext ? '.' . $ext : '');
                if (@move_uploaded_file($tmp, $target)) {
                    $stmt2 = $pdo->prepare('INSERT INTO envanter_dosyalar (envanter_id, dosya_yolu, dosya_tipi, aciklama, yukleme_tarihi) VALUES (:eid, :path, :mime, NULL, NOW())');
                    $stmt2->execute(['eid' => $id, 'path' => str_replace(__DIR__ . '/../', '', $target), 'mime' => mime_content_type($target)]);
                }
            }
        }
    }

    set_flash('success', 'Envanter güncellendi.');
    redirect(BASE_PATH . '/?route=admin-envanter-edit&id=' . $id);

} catch (PDOException $e) {
    @file_put_contents(__DIR__ . '/../logs/admin_inventory_errors.log', date('Y-m-d H:i:s') . ' - ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
    set_flash('error', 'Envanter güncelleme sırasında hata oluştu.');
    redirect(BASE_PATH . '/?route=admin-envanter-edit&id=' . $id);
}
