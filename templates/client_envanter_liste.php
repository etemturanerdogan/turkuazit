<?php
// templates/client_envanter_liste.php
// Müşterinin kendi envanter listesini gösterir.
require_once __DIR__ . '/../config.php';
require_login();

$user = current_user();
$firm = $user['firma_id'] ?? $user['firm_name'] ?? '';

// Güvenli DB sorgulaması — tablo yoksa kullanıcıya anlamlı mesaj göster.
$items = [];
try {
    // 1) Eğer envanter tablosu yoksa PDO exception fırlatılacaktır
    // 2) Örnek: firmaya ait ürünleri çek
    if (!empty($firm)) {
        if (is_numeric($firm)) {
            // firma_id ile doğrudan eşle
            $stmt = $pdo->prepare('SELECT id, firma_id, lokasyon_id, urun_adi, marka, seri_no, barkod, demirbas_kodu, takip_tipi FROM envanter WHERE firma_id = :fid LIMIT 200');
            $stmt->execute(['fid' => (int)$firm]);
            $items = $stmt->fetchAll();
        } else {
            // eski yapıda firma adı tutuluyorsa isim ile eşle
            $stmt = $pdo->prepare('SELECT id, firma_id, lokasyon_id, urun_adi, marka, seri_no, barkod, demirbas_kodu, takip_tipi FROM envanter WHERE firma_id IS NOT NULL AND firma_id IN (SELECT id FROM firmalar WHERE firma_adi = :firma) LIMIT 200');
            $stmt->execute(['firma' => $firm]);
            $items = $stmt->fetchAll();
        }
    }
} catch (PDOException $e) {
    // Eğer yukarıdaki sorgu hatası alırsa, bir diğer yaklaşım: doğrudan envanter tablosu varsa tüm kayıtları çek (geliştirmeye açık)
    $items = [];
}

?>

<main class="section">
    <div class="container">
        <!--
            client_envanter_liste.php
            -------------------------
            Tüm client sayfalarında kullanılan geniş çerçeve (.client-panel) uygulandı.
            Sidebar ve içerik aynı panel içinde düzenlenir — bu sayede görünüm projede tutarlı olur.
        -->

        <div class="client-panel">
            <div class="client-panel__header">
                <div class="title-area">
                    <span class="badge"><span class="badge__dot"></span> Envanterim</span>
                    <h1 class="section__title" style="margin-top:.5rem;">Envanterim</h1>
                    <p class="section__subtitle">Firmana ait cihazların bir özetini burada görebilirsin. İleri düzey filtre ve detay sayfaları ileride eklenecek.</p>
                </div>

                <div style="display:flex; gap:8px; align-items:center;">
                    <a class="btn btn--ghost" href="<?php echo BASE_PATH; ?>/?route=client-envanter">Yenile</a>
                    <a class="btn btn--ghost" href="<?php echo BASE_PATH; ?>/?route=client-dashboard">Geri</a>
                </div>
            </div>

            <div class="client-panel__content">
                <div class="client-panel__sidebar">
                    <?php include __DIR__ . '/../partials/sidebar_client.php'; ?>
                </div>

                <div class="client-panel__main">
                    <div style="margin-top:1rem;">
                <div class="card">
                    <div style="display:flex; align-items:center; justify-content:space-between; gap:12px;">
                        <div>
                            <div class="card__title">Cihazlar</div>
                            <div class="card__subtitle">Toplam: <?php echo count($items); ?> (maks 200 gösteriliyor)</div>
                        </div>

                        <!--
                            Basit toolbar: arama / filtre / csv export
                            Not: bu örnek arama sunucu tarafında henüz işlenmiyor; ileride GET parametreleriyle filtreleme eklenebilir.
                        -->
                        <div style="display:flex; gap:8px; align-items:center;">
                            <form method="get" action="" style="display:flex; gap:8px; align-items:center;">
                                <input type="hidden" name="route" value="client-envanter">
                                <input name="q" placeholder="Cihaz adı / seri no ara" style="padding:.45rem .6rem; border-radius:10px; border:1px solid rgba(255,255,255,.04); background:transparent; color:var(--color-text-main);">
                                <button class="btn btn--ghost" style="padding:.4rem .8rem;">Ara</button>
                            </form>
                            <a class="btn btn--ghost" href="#">CSV Export</a>
                        </div>
                    </div>
                    

                    <?php if (empty($items)): ?>
                        <div style="padding:1rem; color:#6B7280;">Henüz envanter kaydınız bulunamadı veya veritabanı sorgusu başarısız oldu. Eğer firma bilgileriniz doğruysa, admin panelinden envanter eklenmesini talep edin.</div>
                    <?php else: ?>
                        <table style="width:100%; border-collapse:collapse; margin-top:8px;">
                            <thead>
                                <tr style="text-align:left; border-bottom:1px solid #E5E7EB;">
                                    <th>ID</th>
                                    <th>Ürün</th>
                                    <th>Marka</th>
                                    <th>Seri No</th>
                                    <th>Barkod</th>
                                    <th>Demirbaş</th>
                                    <th>Takip</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $it): ?>
                                    <tr style="border-bottom:1px solid #F3F4F6;">
                                        <td><?php echo htmlspecialchars($it['id']); ?></td>
                                        <td><?php echo htmlspecialchars($it['urun_adi'] ?? '—'); ?></td>
                                        <td><?php echo htmlspecialchars($it['marka'] ?? '—'); ?></td>
                                        <td><?php echo htmlspecialchars($it['seri_no'] ?? '—'); ?></td>
                                        <td><?php echo htmlspecialchars($it['barkod'] ?? '—'); ?></td>
                                        <td><?php echo htmlspecialchars($it['demirbas_kodu'] ?? '—'); ?></td>
                                        <td><?php echo htmlspecialchars($it['takip_tipi'] ?? '—'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>

                </div>
                    </div> <!-- margin-top -->
                </div> <!-- .client-panel__main -->
            </div> <!-- .client-panel__content -->
        </div> <!-- .client-panel -->
    </div> <!-- .container -->
</main>
