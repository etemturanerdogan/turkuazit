<?php
// templates/admin_envanter_edit.php
require_once __DIR__ . '/../config.php';
require_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    set_flash('error', 'Geçersiz envanter ID');
    redirect(BASE_PATH . '/?route=admin-envanter-liste');
}

$item = null; $firms = []; $locations = [];
try {
    $stmt = $pdo->prepare('SELECT * FROM envanter WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $item = $stmt->fetch();

    $firms = $pdo->query('SELECT id, firma_adi FROM firmalar ORDER BY firma_adi')->fetchAll();
    $locations = $pdo->query('SELECT id, lokasyon_adi, firma_id FROM lokasyonlar ORDER BY lokasyon_adi')->fetchAll();

    // kutu içerik
    $kit = $pdo->prepare('SELECT * FROM envanter_kutu_icerik WHERE envanter_id = :id');
    $kit->execute(['id' => $id]);
    $kitItems = $kit->fetchAll();

    // dosyalar
    $dosyalar = $pdo->prepare('SELECT * FROM envanter_dosyalar WHERE envanter_id = :id');
    $dosyalar->execute(['id' => $id]);
    $files = $dosyalar->fetchAll();

} catch (PDOException $e) {
    set_flash('error', 'Envanter yüklenemedi.');
    redirect(BASE_PATH . '/?route=admin-envanter-liste');
}

?>

<main class="section">
    <div class="container">
        <div style="display:flex; gap:16px;">
            <?php include __DIR__ . '/../partials/sidebar_admin.php'; ?>

            <div style="flex:1;">
                <h1 class="section__title">Envanter Düzenle — ID #<?php echo htmlspecialchars($id); ?></h1>
                <p class="section__subtitle">Bu sayfadan cihaz bilgilerini, kutu içeriğini ve dosyalarını yönetebilirsiniz.</p>

                <div style="margin-top:12px;">
                    <div class="card">
                        <?php echo render_messages(); ?>

                        <form method="post" action="<?php echo BASE_PATH; ?>/admin/envanter_update.php" enctype="multipart/form-data">
                            <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">

                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                                <div>
                                    <label>Firma</label>
                                    <select name="firma_id" required>
                                        <?php foreach ($firms as $f): ?>
                                            <option value="<?php echo $f['id']; ?>" <?php echo ($item['firma_id'] == $f['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($f['firma_adi']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div>
                                    <label>Lokasyon</label>
                                    <select name="lokasyon_id">
                                        <option value="">-- seçin --</option>
                                        <?php foreach ($locations as $l): ?>
                                            <option value="<?php echo $l['id']; ?>" <?php echo ($item['lokasyon_id'] == $l['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($l['lokasyon_adi']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div>
                                    <label>Ürün Adı</label>
                                    <input type="text" name="urun_adi" required value="<?php echo htmlspecialchars($item['urun_adi'] ?? ''); ?>">
                                </div>
                                <div>
                                    <label>Marka</label>
                                    <input type="text" name="marka" value="<?php echo htmlspecialchars($item['marka'] ?? ''); ?>">
                                </div>

                                <div>
                                    <label>Seri No</label>
                                    <input type="text" name="seri_no" value="<?php echo htmlspecialchars($item['seri_no'] ?? ''); ?>">
                                </div>
                                <div>
                                    <label>Barkod</label>
                                    <input type="text" name="barkod" value="<?php echo htmlspecialchars($item['barkod'] ?? ''); ?>">
                                </div>

                                <div style="grid-column:1 / span 2;">
                                    <label>Ürün Açıklama</label>
                                    <textarea name="urun_aciklama" rows="3"><?php echo htmlspecialchars($item['urun_aciklama'] ?? ''); ?></textarea>
                                </div>

                                <div style="grid-column:1 / span 2;">
                                    <label>Kutu İçeriği (mevcut satırlar)</label>
                                    <div style="margin-bottom:.5rem;">
                                        <?php if (empty($kitItems)): ?>
                                            <div style="color:#6B7280;">Kutu içeriği bulunmuyor.</div>
                                        <?php else: ?>
                                            <ul>
                                                <?php foreach ($kitItems as $k): ?>
                                                    <li><?php echo htmlspecialchars($k['bilesen_adi']); ?> (adet: <?php echo htmlspecialchars($k['adet']); ?>)</li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </div>

                                    <label>Kutu İçeriği — Yeni satırlar ekleyin</label>
                                    <textarea name="kutu_icerik" rows="3" placeholder="Her satıra yeni bileşen yazın"></textarea>
                                </div>

                                <div style="grid-column:1 / span 2;">
                                    <label>Dosya Yükle (yeni dosyalar)</label>
                                    <input type="file" name="files[]" multiple>
                                    <div style="margin-top:.5rem; font-size:.85rem; color:#6B7280;">Mevcut dosyalar:</div>
                                    <?php if (!empty($files)): ?>
                                        <ul>
                                            <?php foreach ($files as $f): ?>
                                                <li><?php echo htmlspecialchars($f['dosya_yolu']); ?> — <a href="<?php echo BASE_PATH . '/' . htmlspecialchars($f['dosya_yolu']); ?>" target="_blank">Görüntüle</a></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:12px;">
                                <button type="submit" class="btn btn--primary">Güncelle</button>
                                <a class="btn btn--ghost" href="<?php echo BASE_PATH; ?>/?route=admin-envanter-liste">İptal</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
