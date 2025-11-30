<?php
// templates/admin_envanter_ekle.php
require_once __DIR__ . '/../config.php';
require_admin();

$firms = [];
$locations = [];
try {
    $firms = $pdo->query('SELECT id, firma_adi FROM firmalar ORDER BY firma_adi')->fetchAll();
    $locations = $pdo->query('SELECT id, lokasyon_adi, firma_id FROM lokasyonlar ORDER BY lokasyon_adi')->fetchAll();
} catch (PDOException $e) {
    // tablolar yoksa boş bırak
}

?>

<main class="section">
    <div class="container">
        <div style="display:flex; gap:16px;">
            <?php include __DIR__ . '/../partials/sidebar_admin.php'; ?>

            <div style="flex:1;">
                <h1 class="section__title">Yeni Envanter Ekle</h1>
                <p class="section__subtitle">Yeni cihaz kaydı ekleyin. Dosyalar ve kutu içeriği eklemek için formu doldurun.</p>

                <div style="margin-top:12px;">
                    <div class="card">
                        <?php echo render_messages(); ?>

                        <form method="post" action="<?php echo BASE_PATH; ?>/admin/envanter_save.php" enctype="multipart/form-data">
                            <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">

                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                                <div>
                                    <label>Firma (zorunlu)</label>
                                    <select name="firma_id" required>
                                        <option value="">-- Firma seçin --</option>
                                        <?php foreach ($firms as $f): ?>
                                            <option value="<?php echo $f['id']; ?>"><?php echo htmlspecialchars($f['firma_adi']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div>
                                    <label>Lokasyon (opsiyonel)</label>
                                    <select name="lokasyon_id">
                                        <option value="">-- Lokasyon seçin --</option>
                                        <?php foreach ($locations as $l): ?>
                                            <option value="<?php echo $l['id']; ?>"><?php echo htmlspecialchars($l['lokasyon_adi']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div>
                                    <label>Ürün Adı</label>
                                    <input type="text" name="urun_adi" required>
                                </div>

                                <div>
                                    <label>Marka</label>
                                    <input type="text" name="marka">
                                </div>

                                <div>
                                    <label>Seri No</label>
                                    <input type="text" name="seri_no">
                                </div>

                                <div>
                                    <label>Garanti Süresi</label>
                                    <input type="text" name="garanti_suresi">
                                </div>

                                <div style="grid-column:1 / span 2;">
                                    <label>Ürün Açıklaması</label>
                                    <textarea name="urun_aciklama" rows="3"></textarea>
                                </div>

                                <div>
                                    <label>Takip Tipi</label>
                                    <select name="takip_tipi">
                                        <option value="demirbas">Demirbaş</option>
                                        <option value="stok">Stok</option>
                                        <option value="tuketim">Tüketim</option>
                                    </select>
                                </div>

                                <div>
                                    <label>Birim</label>
                                    <input type="text" name="birim" placeholder="adet">
                                </div>

                                <div>
                                    <label>Barkod (opsiyonel)</label>
                                    <input type="text" name="barkod">
                                </div>

                                <div>
                                    <label>Demirbaş Kodu (opsiyonel)</label>
                                    <input type="text" name="demirbas_kodu">
                                </div>

                                <div style="grid-column:1 / span 2;">
                                    <label>Kutu İçeriği (bileşenleri alt alta yazın, her birini ayrı satırda)</label>
                                    <textarea name="kutu_icerik" placeholder="kablo, adaptör, kullanım kılavuzu" rows="3"></textarea>
                                </div>

                                <div style="grid-column:1 / span 2;">
                                    <label>Dosyalar / Fotoğraflar (çoklu)</label>
                                    <input type="file" name="files[]" multiple accept="image/*,application/pdf">
                                    <div style="font-size:.8rem; color:var(--color-text-muted); margin-top:.4rem;">Fotoğraf ve PDF yükleyebilirsiniz. Maks dosya boyutu sunucu konfigürasyonuna bağlıdır.</div>
                                </div>

                            </div>

                            <div style="display:flex; gap:8px; justify-content:flex-end; margin-top:12px;">
                                <button type="submit" class="btn btn--primary">Kaydet</button>
                                <a class="btn btn--ghost" href="<?php echo BASE_PATH; ?>/?route=admin-envanter-liste">İptal</a>
                            </div>
                        </form>

                    </div>
                </div>

            </div>
        </div>
    </div>
</main>
