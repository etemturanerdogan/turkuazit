<?php
// templates/page_admin_dashboard.php
// Yönetim paneli ana sayfası — kısa özet ve kısayollar
require_once __DIR__ . '/../app_config.php';
require_admin();

// Özet sayısı (defansif sorgular)
$stats = [];
try {
    $stats['users'] = (int)($pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() ?: 0);
    $stats['envanter'] = (int)($pdo->query('SELECT COUNT(*) FROM envanter')->fetchColumn() ?: 0);
    $stats['firmalar'] = (int)($pdo->query('SELECT COUNT(*) FROM firmalar')->fetchColumn() ?: 0);
} catch (PDOException $e) {
    $stats = ['users' => 0, 'envanter' => 0, 'firmalar' => 0];
}
?>

<main class="section">
    <div class="container">
        <div style="display:flex; gap:16px;">
            <?php include __DIR__ . '/../partials/partial_sidebar_admin.php'; ?>

            <div style="flex:1;">
                <h1 class="section__title">Yönetim Paneli</h1>
                <p class="section__subtitle">Sistem genel özetleri ve hızlı erişimler. Tıklayarak detay sayfalara gidebilirsiniz.</p>

                <div style="margin-top:16px; display:flex; gap:12px; align-items:stretch;">
                    <div class="card" style="flex:1;">
                        <div class="card__title">Toplam Kullanıcı</div>
                        <div style="font-size:1.5rem; margin-top:.5rem; font-weight:700;"><?php echo $stats['users']; ?></div>
                    </div>

                    <div class="card" style="flex:1;">
                        <div class="card__title">Toplam Envanter</div>
                        <div style="font-size:1.5rem; margin-top:.5rem; font-weight:700;"><?php echo $stats['envanter']; ?></div>
                    </div>

                    <div class="card" style="flex:1;">
                        <div class="card__title">Firmalar</div>
                        <div style="font-size:1.5rem; margin-top:.5rem; font-weight:700;"><?php echo $stats['firmalar']; ?></div>
                    </div>
                </div>

                <div style="margin-top:16px; display:flex; gap:12px; align-items:stretch;">
                    <a class="btn btn--primary" href="<?php echo BASE_PATH; ?>/?route=admin-envanter-ekle">Yeni Envanter Ekle</a>
                    <a class="btn btn--ghost" href="<?php echo BASE_PATH; ?>/?route=admin-envanter-liste">Envanter Listesi</a>
                    <a class="btn btn--ghost" href="<?php echo BASE_PATH; ?>/?route=admin-users">Kullanıcılar ve Roller</a>
                </div>
            </div>
        </div>
    </div>
</main>
<?php
require_admin();
?>

<main class="section">
    <div class="container">
        <h1 class="section__title">Yönetim Paneli</h1>
        <p class="section__subtitle">
            Burada ileride modüller, müşteriler, sözleşmeler ve loglar için yönetim ekranları açacağız.
        </p>

        <div style="margin-top:1.5rem;" class="card">
            <div class="card__title">Hoş geldin, <?php echo htmlspecialchars(current_user()['full_name']); ?></div>
            <div class="card__subtitle">
                Rolün: Admin. Bu panel yalnızca TurkuazIT ekibi içindir.
            </div>
        </div>
    </div>
</main>
