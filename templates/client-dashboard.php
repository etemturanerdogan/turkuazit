<?php
require_login(); // admin de girer, client de
?>

<main class="section">
    <div class="container">
        <!--
            client-dashboard.php
            ---------------------
            Müşteri paneli ana sayfası — tüm client sayfalarında ortak kullanılacak büyük çerçeve (.client-panel) uygulandı.
            Burada sol menü (client) ve ana içerik aynı çerçeve içinde düzenlenir.
        -->

        <div class="client-panel">
            <div class="client-panel__header">
                <div class="title-area">
                    <span class="badge"><span class="badge__dot"></span> Müşteri Paneli</span>
                    <h1 class="section__title" style="margin-top:.5rem;">Müşteri Paneli</h1>
                    <p class="section__subtitle">Burada ileride arıza kayıtları, cihaz envanteri ve modül durumlarını göstereceğiz.</p>
                </div>

                <!-- Sağ üstte ileride kısa aksiyonlar / bildirimler olabilir -->
                <div style="display:flex; gap:8px; align-items:center;">
                    <a class="btn btn--ghost" href="<?php echo BASE_PATH; ?>/?route=client-envanter">Envanterim</a>
                    <a class="btn btn--ghost" href="<?php echo BASE_PATH; ?>/?route=client-zimmet">Zimmetlerim</a>
                </div>
            </div>

            <div class="client-panel__content">
                <div class="client-panel__sidebar">
                    <?php include __DIR__ . '/../partials/sidebar_client.php'; ?>
                </div>

                <div class="client-panel__main">
                    <?php
        // Dashboard özet verileri: firma envanteri sayısı, sizin üzerinizdeki zimmet sayısı
        $user = current_user();
        $firm = $user['firm_name'] ?? null;
        $inventoryCount = 0;
        $assignedCount = 0;
        try {
            if ($firm) {
                // Basit: firmalar tablosunda firma_adi eşleşen firma id'lerini bul ve envanter say
                $stmt = $pdo->prepare('SELECT COUNT(e.id) FROM envanter e JOIN firmalar f ON f.id = e.firma_id WHERE f.firma_adi = :firma');
                $stmt->execute(['firma' => $firm]);
                $inventoryCount = (int)$stmt->fetchColumn();
            }

            // Zimmet sayısı — kullanıcıyla eşleşme: user_id veya personel.email
            $stmt2 = $pdo->prepare("SELECT COUNT(*) FROM zimmetler WHERE (user_id = :uid) OR (personel_id IN (SELECT id FROM personeller WHERE email = :email))");
            $stmt2->execute(['uid' => $user['id'], 'email' => $user['email'] ?? '']);
            $assignedCount = (int)$stmt2->fetchColumn();
        } catch (PDOException $e) {
            // DB tablolarından herhangi biri eksikse burada sessizce 0 gösteririz — loglama geliştirmeye açık
            $inventoryCount = 0;
            $assignedCount = 0;
        }
        ?>

                    <div style="margin-top:1.5rem;" class="card">
            <div class="card__title">
                <!-- current_user() artık first_name / last_name ile uyumlu çalışır. full_name otomatik oluşturulur -->
                Merhaba, <?php echo htmlspecialchars(current_user()['full_name']); ?>
            </div>
            <div class="card__subtitle">
                <?php if (!empty(current_user()['firm_name'])): ?>
                    Firma: <?php echo htmlspecialchars(current_user()['firm_name']); ?>
                <?php else: ?>
                    Firma bilgisi daha sonra eklenecektir.
                <?php endif; ?>
            </div>

            <div style="display:flex; gap:12px; margin-top:1.25rem;">
                <div style="flex:1; display:flex; gap:12px;">
                    <div class="card" style="flex:1; padding:12px;">
                        <div style="font-weight:700;">Toplam Cihaz</div>
                        <div style="font-size:1.5rem; margin-top:.5rem;"><?php echo $inventoryCount; ?></div>
                        <div style="font-size:.8rem; color:#6B7280; margin-top:.5rem;">Sadece sizin firmanıza ait görünen cihazlar</div>
                    </div>

                    <div class="card" style="flex:1; padding:12px;">
                        <div style="font-weight:700;">Üzerinizdeki Zimmet</div>
                        <div style="font-size:1.5rem; margin-top:.5rem;"><?php echo $assignedCount; ?></div>
                        <div style="font-size:.8rem; color:#6B7280; margin-top:.5rem;">Size atanmış cihazlar</div>
                    </div>
                </div>

                <div style="width:260px; display:flex; flex-direction:column; gap:8px; justify-content:flex-start;">
                    <a class="btn btn--ghost" href="<?php echo BASE_PATH; ?>/?route=profile">Firma / Adres Bilgileri</a>
                    <a class="btn btn--ghost" href="<?php echo BASE_PATH; ?>/?route=client-envanter">Envanterim</a>
                    <a class="btn btn--ghost" href="<?php echo BASE_PATH; ?>/?route=client-zimmet">Zimmetlerim</a>
                </div>
            </div>

                </div> <!-- .client-panel__main -->
            </div> <!-- .client-panel__content -->
        </div> <!-- .client-panel -->

        </div>
    </div>
</main>
