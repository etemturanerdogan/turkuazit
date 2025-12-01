<?php
// templates/page_client_asset_assignment_list.php
// Oturum açmış kullanıcının üzerinde kayıtlı zimmetleri (eğer varsa) listeler.
require_once __DIR__ . '/../app_config.php';
require_login();

$user = current_user();
$userId = $user['id'];
$userEmail = $user['email'] ?? '';

$zimmetler = [];
try {
    // 1) Eğer zimmetler tablosunda user_id alanı varsa direkt çek
    $colCheck = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema = DATABASE() AND table_name = 'zimmetler' AND column_name = 'user_id'");
    $colCheck->execute();
    if ($colCheck->fetchColumn()) {
        $stmt = $pdo->prepare('SELECT z.*, e.urun_adi FROM zimmetler z LEFT JOIN envanter e ON e.id = z.envanter_id WHERE z.user_id = :uid ORDER BY z.zimmet_tarihi DESC LIMIT 200');
        $stmt->execute(['uid' => $userId]);
        $zimmetler = $stmt->fetchAll();
    } else {
        // 2) Alternatif: personel_id varsa, eşleşecek personeli email ile bulup personel.id kullan
        $colCheck2 = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema = DATABASE() AND table_name = 'zimmetler' AND column_name = 'personel_id'");
        $colCheck2->execute();
        if ($colCheck2->fetchColumn()) {
            // personeller tablosunda email alanının olup olmadığını kontrol edip join yapmaya çalış
            $stmt = $pdo->prepare('SELECT id FROM personeller WHERE email = :email LIMIT 1');
            $stmt->execute(['email' => $userEmail]);
            $pid = $stmt->fetchColumn();
            if ($pid) {
                $stmt = $pdo->prepare('SELECT z.*, e.urun_adi FROM zimmetler z LEFT JOIN envanter e ON e.id = z.envanter_id WHERE z.personel_id = :pid ORDER BY z.zimmet_tarihi DESC LIMIT 200');
                $stmt->execute(['pid' => $pid]);
                $zimmetler = $stmt->fetchAll();
            }
        }
    }
} catch (PDOException $e) {
    $zimmetler = [];
}

?>

<main class="section">
    <div class="container">
        <!--
            page_client_asset_assignment_list.php
            ------------------------
            Client panel çerçevesi içinde sidebar ve içerik düzeni kullanıldı. Sayfa eksik tablolara karşı defansif.
        -->

        <div class="client-panel">
            <div class="client-panel__header">
                <div class="title-area">
                    <span class="badge"><span class="badge__dot"></span> Zimmetlerim</span>
                    <h1 class="section__title" style="margin-top:.5rem;">Zimmetlerim</h1>
                    <p class="section__subtitle">Üzerinizde kayıtlı cihazlar ve zimmet geçmişi (varsa) burada listelenecektir.</p>
                </div>

                <div style="display:flex; gap:8px; align-items:center;">
                    <a class="btn btn--ghost" href="<?php echo BASE_PATH; ?>/?route=client-zimmet">Yenile</a>
                    <a class="btn btn--ghost" href="<?php echo BASE_PATH; ?>/?route=client-dashboard">Geri</a>
                </div>
            </div>

            <div class="client-panel__content">
                <div class="client-panel__sidebar">
                    <?php include __DIR__ . '/../partials/partial_sidebar_client.php'; ?>
                </div>

                <div class="client-panel__main">
                    <div style="margin-top:1rem;">
                        <div class="card">
                            <!-- Basit filtre / toolbar alanı (ileride JS veya server-side filtre ekleyin) -->
                            <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:8px;">
                                <div>
                                    <div class="card__title">Zimmetler</div>
                                </div>
                                <div style="display:flex; gap:8px; align-items:center;">
                                    <form method="get" action="" style="display:flex; gap:8px; align-items:center;">
                                        <input type="hidden" name="route" value="client-zimmet">
                                        <input name="q" placeholder="Envanter adı / açıklama ara" style="padding:.45rem .6rem; border-radius:10px; border:1px solid rgba(255,255,255,.04); background:transparent; color:var(--color-text-main);">
                                        <button class="btn btn--ghost" style="padding:.4rem .8rem;">Ara</button>
                                    </form>
                                    <a class="btn btn--ghost" href="#">Tarih aralığı</a>
                                </div>
                            </div>
                    <?php if (empty($zimmetler)): ?>
                        <div style="padding:1rem; color:#6B7280;">Sistemde sizinle eşleşen zimmet kaydı bulunamadı. Eğer cihaz zimmetlenmişse ve burada gözükmüyorsa lütfen firma yöneticiniz veya TurkuazIT ile iletişime geçin.</div>
                    <?php else: ?>
                        <table style="width:100%; border-collapse:collapse;">
                            <thead>
                                <tr style="text-align:left; border-bottom:1px solid #E5E7EB;">
                                    <th>Envanter</th>
                                    <th>Lokasyon</th>
                                    <th>Zimmet Tarihi</th>
                                    <th>İade Tarihi</th>
                                    <th>Açıklama</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($zimmetler as $z): ?>
                                    <tr style="border-bottom:1px solid #F3F4F6;">
                                        <td><?php echo htmlspecialchars($z['urun_adi'] ?? ($z['envanter_id'] ?? '—')); ?></td>
                                        <td><?php echo htmlspecialchars($z['lokasyon_id'] ?? '—'); ?></td>
                                        <td><?php echo htmlspecialchars($z['zimmet_tarihi'] ?? '—'); ?></td>
                                        <td><?php echo htmlspecialchars($z['iade_tarihi'] ?? '—'); ?></td>
                                        <td><?php echo htmlspecialchars($z['aciklama'] ?? ''); ?></td>
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
