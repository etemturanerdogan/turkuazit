<?php
// templates/page_admin_inventory_list.php
require_once __DIR__ . '/../app_config.php';
require_admin();

$items = [];
try {
    $stmt = $pdo->query('SELECT e.id, e.urun_adi, e.marka, e.seri_no, e.barkod, e.demirbas_kodu, f.firma_adi, l.lokasyon_adi FROM envanter e LEFT JOIN firmalar f ON f.id = e.firma_id LEFT JOIN lokasyonlar l ON l.id = e.lokasyon_id ORDER BY e.id DESC LIMIT 500');
    $items = $stmt->fetchAll();
} catch (PDOException $e) {
    $items = [];
}

?>

<main class="section">
    <div class="container">
        <div style="display:flex; gap:16px;">
            <?php include __DIR__ . '/../partials/partial_sidebar_admin.php'; ?>

            <div style="flex:1;">
                <h1 class="section__title">Envanter Listesi</h1>
                <p class="section__subtitle">Tüm cihazların listesi. Buradan düzenleme, silme ve detaylara erişim sağlanır.</p>

                <div style="margin-top:12px;">
                    <div class="card">
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <div><strong>Envanter (son 500)</strong></div>
                            <div>
                                <a class="btn btn--ghost" href="<?php echo BASE_PATH; ?>/?route=admin-envanter-ekle">Yeni Envanter Ekle</a>
                            </div>
                        </div>

                        <?php if (empty($items)): ?>
                            <div style="padding:1rem; color:#6B7280;">Envanter veri yok veya DB tablosu eksik.</div>
                        <?php else: ?>
                            <table style="width:100%; border-collapse:collapse; margin-top:8px;">
                                <thead>
                                    <tr style="text-align:left; border-bottom:1px solid #E5E7EB;">
                                        <th>ID</th>
                                        <th>Firma</th>
                                        <th>Ürün</th>
                                        <th>Marka</th>
                                        <th>Seri No</th>
                                        <th>Barkod</th>
                                        <th>Demirbaş</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($items as $it): ?>
                                    <tr style="border-bottom:1px solid #F3F4F6;">
                                        <td><?php echo htmlspecialchars($it['id']); ?></td>
                                        <td><?php echo htmlspecialchars($it['firma_adi'] ?? '—'); ?></td>
                                        <td><?php echo htmlspecialchars($it['urun_adi'] ?? '—'); ?></td>
                                        <td><?php echo htmlspecialchars($it['marka'] ?? '—'); ?></td>
                                        <td><?php echo htmlspecialchars($it['seri_no'] ?? '—'); ?></td>
                                        <td><?php echo htmlspecialchars($it['barkod'] ?? '—'); ?></td>
                                        <td><?php echo htmlspecialchars($it['demirbas_kodu'] ?? '—'); ?></td>
                                        <td>
                                            <a class="btn btn--ghost" href="<?php echo BASE_PATH; ?>/?route=admin-envanter-edit&id=<?php echo $it['id']; ?>">Düzenle</a>
                                            <a class="btn btn--ghost" href="<?php echo BASE_PATH; ?>/admin/admin_inventory_delete_action.php?id=<?php echo $it['id']; ?>&csrf=<?php echo csrf_token(); ?>" onclick="return confirm('Bu envanteri silmek istediğinizden emin misiniz?');">Sil</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>

                    </div>
                </div>

            </div>
        </div>
    </div>
</main>
