<?php
// admin/admin_inventory_create_action.php
// Admin tarafından gelen envanter ekleme formunu işler.
require_once __DIR__ . '/../app_config.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_PATH . '/?route=admin-envanter-ekle');
}

// CSRF kontrolü
$csrf = $_POST['csrf'] ?? '';
if (!csrf_check($csrf)) {
    set_flash('error', 'Geçersiz CSRF token.');
    redirect(BASE_PATH . '/?route=admin-envanter-ekle');
}

// Basit validasyon & sanitize
$firma_id = isset($_POST['firma_id']) ? (int)$_POST['firma_id'] : null;
$lokasyon_id = isset($_POST['lokasyon_id']) && $_POST['lokasyon_id'] !== '' ? (int)$_POST['lokasyon_id'] : null;
$urun_adi = sanitize($_POST['urun_adi'] ?? '');
$marka = sanitize($_POST['marka'] ?? '');
$seri_no = sanitize($_POST['seri_no'] ?? '');
$garanti = sanitize($_POST['garanti_suresi'] ?? '');
$urun_aciklama = sanitize($_POST['urun_aciklama'] ?? '');
$takip_tipi = sanitize($_POST['takip_tipi'] ?? 'demirbas');
$birim = sanitize($_POST['birim'] ?? '');
$barkod = sanitize($_POST['barkod'] ?? '');
$demirbas = sanitize($_POST['demirbas_kodu'] ?? '');
$kutu_icerik = trim($_POST['kutu_icerik'] ?? '');

if ($firma_id === null || $urun_adi === '') {
    set_flash('error', 'Firma ve ürün adı zorunludur.');
    redirect(BASE_PATH . '/?route=admin-envanter-ekle');
}

try {
    $stmt = $pdo->prepare('INSERT INTO envanter (firma_id, lokasyon_id, urun_adi, urun_aciklama, marka, seri_no, garanti_suresi, takip_tipi, birim, barkod, demirbas_kodu, olusturma_tarihi) VALUES (:firma_id, :lokasyon_id, :urun_adi, :urun_aciklama, :marka, :seri_no, :garanti, :takip, :birim, :barkod, :demirbas, NOW())');
    $stmt->execute([
        'firma_id' => $firma_id,
        'lokasyon_id' => $lokasyon_id,
        'urun_adi' => $urun_adi,
        'urun_aciklama' => $urun_aciklama ?: null,
        'marka' => $marka ?: null,
        'seri_no' => $seri_no ?: null,
        'garanti' => $garanti ?: null,
        'takip' => $takip_tipi,
        'birim' => $birim ?: null,
        'barkod' => $barkod ?: null,
        'demirbas' => $demirbas ?: null,
    ]);

    $envanter_id = (int)$pdo->lastInsertId();

    // Kutu içerikleri varsa ekle
    if ($kutu_icerik !== '') {
        $lines = preg_split("/\r?\n/", $kutu_icerik);
        $ins = $pdo->prepare('INSERT INTO envanter_kutu_icerik (envanter_id, bilesen_adi, adet, seri_no, notlar) VALUES (:eid, :bilesen, :adet, :seri, :notlar)');
        foreach ($lines as $l) {
            $name = trim($l);
            if ($name === '') continue;
            $ins->execute(['eid' => $envanter_id, 'bilesen' => $name, 'adet' => 1, 'seri' => null, 'notlar' => null]);
        }
    }

    // Dosyalar yükleniyorsa kaydet
    if (!empty($_FILES['files']) && is_array($_FILES['files']['name'])) {
        $uploadDir = __DIR__ . '/../uploads/envanter/' . $envanter_id;
        if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);
        foreach ($_FILES['files']['name'] as $i => $origName) {
            $tmp = $_FILES['files']['tmp_name'][$i] ?? null;
            $err = $_FILES['files']['error'][$i] ?? 1;
            if ($tmp && $err === UPLOAD_ERR_OK) {
                $ext = pathinfo($origName, PATHINFO_EXTENSION);
                $target = $uploadDir . '/' . time() . '_' . bin2hex(random_bytes(6)) . ($ext ? '.' . $ext : '');
                if (@move_uploaded_file($tmp, $target)) {
                    // veritabanına ekle
                    $stmt2 = $pdo->prepare('INSERT INTO envanter_dosyalar (envanter_id, dosya_yolu, dosya_tipi, aciklama, yukleme_tarihi) VALUES (:eid, :path, :mime, NULL, NOW())');
                    $stmt2->execute(['eid' => $envanter_id, 'path' => str_replace(__DIR__ . '/../', '', $target), 'mime' => mime_content_type($target)]);
                }
            }
        }
    }

    set_flash('success', 'Envanter başarıyla kaydedildi.');
    redirect(BASE_PATH . '/?route=admin-envanter-liste');

} catch (PDOException $e) {
    // Hata durumunda logla
    @file_put_contents(__DIR__ . '/../logs/admin_inventory_errors.log', date('Y-m-d H:i:s') . ' - ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
    set_flash('error', 'Envanter kaydı sırasında hata oluştu.');
    redirect(BASE_PATH . '/?route=admin-envanter-ekle');
}
