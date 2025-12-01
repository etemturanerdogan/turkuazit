<?php
// templates/page_profile.php
// Kullanıcı kendi firma / adres / kargo bilgilerini burada görüntüler ve günceller.
require_once __DIR__ . '/../app_config.php';
require_login();

$user = current_user();
$userId = $user['id'];

// Mevcut profil bilgisini DB'den çek
$stmt = $pdo->prepare('SELECT * FROM user_profiles WHERE user_id = :uid LIMIT 1');
$stmt->execute(['uid' => $userId]);
$profile = $stmt->fetch() ?: [];

// Eğer users tablosunda firm_name varsa, formda göster (öncelik profile.company_name'e aittir)
$firmFromUser = $user['firm_name'] ?? '';

?>

<main class="section section--tight">
    <div class="container">
        <!--
            profile.php
            ----------
            Kullanıcı profil (firma / adres / kargo) bilgileri — client panel çerçevesi kullanılarak yeniden düzenlendi.
            Bu sayfa da sol menü ile birlikte .client-panel içinde görüntülenir, böylece tüm client sayfalarında görünüm tutarlı olur.
        -->

        <div class="client-panel">
            <div class="client-panel__header">
                <div class="title-area">
                    <span class="badge"><span class="badge__dot"></span> Hesap Bilgileri</span>
                    <h1 class="section__title" style="margin-top:.5rem;">Firma, adres ve kargo bilgileri</h1>
                    <p class="section__subtitle">Bu sayfadan firma ve teslimat adresinizi güncelleyebilirsiniz. Bilgiler doğruluk kontrolünden geçmelidir (saçma/uygunsuz veri kabul etmiyoruz).</p>
                </div>

                <div style="display:flex; gap:8px; align-items:center;">
                    <a class="btn btn--ghost" href="<?php echo BASE_PATH; ?>/?route=client-dashboard">Geri</a>
                </div>
            </div>

            <div class="client-panel__content">
                <div class="client-panel__sidebar">
                    <?php include __DIR__ . '/../partials/partial_sidebar_client.php'; ?>
                </div>

                <div class="client-panel__main">
                    <div class="card" style="margin-top:1.5rem;">
            <form method="post" action="<?php echo BASE_PATH; ?>/auth/auth_profile_update_action.php" style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">
                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($userId); ?>">

                <div style="grid-column:1 / span 2; display:flex; gap:8px;">
                    <div style="flex:1;">
                        <label>Ad</label>
                        <input type="text" name="first_name" required value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>">
                    </div>
                    <div style="flex:1;">
                        <label>Soyad</label>
                        <input type="text" name="last_name" required value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>">
                    </div>
                </div>

                <div>
                    <label>E-posta (değiştirmek için admin ile iletişime geçin)</label>
                    <input type="email" disabled value="<?php echo htmlspecialchars($user['email']); ?>">
                </div>

                <div>
                    <label>Firma adı</label>
                    <input type="text" name="company_name" value="<?php echo htmlspecialchars($profile['company_name'] ?? $firmFromUser); ?>" placeholder="Firma adı (opsiyonel)">
                </div>

                <div style="grid-column:1 / span 2;">
                    <label>Adres (Satır 1)</label>
                    <input type="text" name="address_line1" value="<?php echo htmlspecialchars($profile['address_line1'] ?? ''); ?>">
                </div>

                <div style="grid-column:1 / span 2;">
                    <label>Adres (Satır 2)</label>
                    <input type="text" name="address_line2" value="<?php echo htmlspecialchars($profile['address_line2'] ?? ''); ?>">
                </div>

                <div>
                    <label>Şehir</label>
                    <input type="text" name="city" value="<?php echo htmlspecialchars($profile['city'] ?? ''); ?>">
                </div>

                <div>
                    <label>İlçe / Bölge</label>
                    <input type="text" name="state" value="<?php echo htmlspecialchars($profile['state'] ?? ''); ?>">
                </div>

                <div>
                    <label>Posta / ZIP</label>
                    <input type="text" name="postal_code" value="<?php echo htmlspecialchars($profile['postal_code'] ?? ''); ?>">
                </div>

                <div>
                    <label>Ülke</label>
                    <input type="text" name="country" value="<?php echo htmlspecialchars($profile['country'] ?? ''); ?>">
                </div>

                <div style="grid-column:1 / span 2;">
                    <label>Telefon</label>
                    <input type="tel" name="phone" value="<?php echo htmlspecialchars($profile['phone'] ?? ''); ?>" placeholder="+90 5xx xxx xx xx">
                </div>

                <div style="grid-column:1 / span 2;">
                    <label>Kargo / teslimat notları</label>
                    <textarea name="shipping_instructions" rows="3"><?php echo htmlspecialchars($profile['shipping_instructions'] ?? ''); ?></textarea>
                </div>

                <div style="grid-column:1 / span 2; display:flex; gap:8px; justify-content:flex-end;">
                    <button type="submit" class="btn btn--ghost">Kaydet</button>
                </div>
            </form>
                    </div> <!-- .card -->
                </div> <!-- .client-panel__main -->
            </div> <!-- .client-panel__content -->
        </div> <!-- .client-panel -->
    </div> <!-- .container -->
</main>
