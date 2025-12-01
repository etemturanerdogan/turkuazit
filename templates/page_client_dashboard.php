<?php
// templates/page_client_dashboard.php
// ═══════════════════════════════════════════════════════════════════════════════
// MÜŞTERİ PANELİ - TEK SAYFA UYGULAMA (SPA)
// ═══════════════════════════════════════════════════════════════════════════════
// Bu dosya müşteri panelinin tüm işlevlerini tek sayfada toplar.
// Sayfa yenilenmeden panel geçişleri ile SPA benzeri deneyim sunar.
// 
// ANA PANELLER:
// 1. Genel Bakış - Özet bilgiler ve hızlı erişim
// 2. Envanterim - Firmaya ait cihazlar
// 3. Zimmetlerim - Kullanıcıya atanmış cihazlar
// 4. Hesap Ayarları - Profil ve güvenlik ayarları
// 5. Destek - Destek talepleri ve iletişim
// 
// TEKNİK ÖZELLİKLER:
// - CSS animasyonları ile yumuşak panel geçişleri
// - JavaScript ile dinamik içerik yönetimi
// - Responsive tasarım
// ═══════════════════════════════════════════════════════════════════════════════
require_once __DIR__ . '/../app_config.php';
require_login();

// ─────────────────────────────────────────────────────────────────────────────
// KULLANICI VE FİRMA BİLGİLERİ
// ─────────────────────────────────────────────────────────────────────────────
$user = current_user();
$userId = $user['id'];
$userEmail = $user['email'] ?? '';
$firmaId = $user['firma_id'] ?? null;
$firmaAdi = $user['firm_name'] ?? null;

// ─────────────────────────────────────────────────────────────────────────────
// PROFİL BİLGİLERİ (user_profiles tablosundan)
// ─────────────────────────────────────────────────────────────────────────────
$profile = [];
try {
    $stmt = $pdo->prepare('SELECT * FROM user_profiles WHERE user_id = :uid LIMIT 1');
    $stmt->execute(['uid' => $userId]);
    $profile = $stmt->fetch() ?: [];
} catch (PDOException $e) {
    $profile = [];
}
$companyDisplay = $profile['company_name'] ?? $firmaAdi ?? '';

// ─────────────────────────────────────────────────────────────────────────────
// İSTATİSTİKLER
// ─────────────────────────────────────────────────────────────────────────────
$stats = [
    'inventory' => 0,
    'assigned' => 0,
    'tickets' => 0
];

try {
    // Firma envanteri sayısı
    if ($firmaId) {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM envanter WHERE firma_id = ?');
        $stmt->execute([$firmaId]);
        $stats['inventory'] = (int)$stmt->fetchColumn();
    } elseif ($firmaAdi) {
        $stmt = $pdo->prepare('SELECT COUNT(e.id) FROM envanter e JOIN firmalar f ON f.id = e.firma_id WHERE f.firma_adi = ?');
        $stmt->execute([$firmaAdi]);
        $stats['inventory'] = (int)$stmt->fetchColumn();
    }
    
    // Kullanıcıya atanmış zimmet sayısı
    try {
        $colCheck = $pdo->query("SHOW COLUMNS FROM zimmetler LIKE 'user_id'")->fetchColumn();
        if ($colCheck) {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM zimmetler WHERE user_id = ?');
            $stmt->execute([$userId]);
            $stats['assigned'] = (int)$stmt->fetchColumn();
        }
    } catch (PDOException $e) {}
    
} catch (PDOException $e) {
    // Hata durumunda varsayılan değerler kalır
}

// ─────────────────────────────────────────────────────────────────────────────
// ENVANTER LİSTESİ
// ─────────────────────────────────────────────────────────────────────────────
$inventoryItems = [];
try {
    if ($firmaId) {
        $stmt = $pdo->prepare('SELECT id, urun_adi, marka, seri_no, barkod, demirbas_kodu, takip_tipi FROM envanter WHERE firma_id = ? ORDER BY id DESC LIMIT 200');
        $stmt->execute([$firmaId]);
        $inventoryItems = $stmt->fetchAll();
    } elseif ($firmaAdi) {
        $stmt = $pdo->prepare('SELECT e.id, e.urun_adi, e.marka, e.seri_no, e.barkod, e.demirbas_kodu, e.takip_tipi FROM envanter e JOIN firmalar f ON f.id = e.firma_id WHERE f.firma_adi = ? ORDER BY e.id DESC LIMIT 200');
        $stmt->execute([$firmaAdi]);
        $inventoryItems = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    $inventoryItems = [];
}

// ─────────────────────────────────────────────────────────────────────────────
// ZİMMET LİSTESİ
// ─────────────────────────────────────────────────────────────────────────────
$assignedItems = [];
try {
    $colCheck = $pdo->query("SHOW COLUMNS FROM zimmetler LIKE 'user_id'")->fetchColumn();
    if ($colCheck) {
        $stmt = $pdo->prepare('SELECT z.*, e.urun_adi, e.marka, e.seri_no FROM zimmetler z LEFT JOIN envanter e ON e.id = z.envanter_id WHERE z.user_id = ? ORDER BY z.zimmet_tarihi DESC LIMIT 200');
        $stmt->execute([$userId]);
        $assignedItems = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    $assignedItems = [];
}

// ─────────────────────────────────────────────────────────────────────────────
// SEKME YAPILANDIRMASI
// ─────────────────────────────────────────────────────────────────────────────
$tabs = [
    [
        'id' => 'overview',
        'icon' => '🏠',
        'label' => 'Genel Bakış',
        'desc' => 'Özet bilgiler ve hızlı erişim'
    ],
    [
        'id' => 'inventory',
        'icon' => '📦',
        'label' => 'Envanterim',
        'desc' => 'Firmaya ait cihazlar'
    ],
    [
        'id' => 'assigned',
        'icon' => '📋',
        'label' => 'Zimmetlerim',
        'desc' => 'Size atanmış cihazlar'
    ],
    [
        'id' => 'account',
        'icon' => '⚙️',
        'label' => 'Hesap',
        'desc' => 'Profil ve güvenlik ayarları'
    ],
    [
        'id' => 'support',
        'icon' => '💬',
        'label' => 'Destek',
        'desc' => 'Yardım ve iletişim'
    ],
];

// ─────────────────────────────────────────────────────────────────────────────
// GÜNÜN SAATİNE GÖRE KARŞILAMA
// ─────────────────────────────────────────────────────────────────────────────
$hour = (int)date('H');
if ($hour >= 5 && $hour < 12) {
    $greeting = 'Günaydın';
    $greetingIcon = '🌅';
} elseif ($hour >= 12 && $hour < 17) {
    $greeting = 'İyi günler';
    $greetingIcon = '☀️';
} elseif ($hour >= 17 && $hour < 21) {
    $greeting = 'İyi akşamlar';
    $greetingIcon = '🌆';
} else {
    $greeting = 'İyi geceler';
    $greetingIcon = '🌙';
}
?>

<main class="section">
    <div class="container">
        <!-- ═══════════════════════════════════════════════════════════════════
             ANA PANEL KAPSAYICI
             ═══════════════════════════════════════════════════════════════════ -->
        <div class="panel-shell panel-shell--full-width">
            <div class="panel-main">
                
                <!-- ─────────────────────────────────────────────────────────────
                     PANEL BAŞLIĞI - MODERN TASARIM
                     ───────────────────────────────────────────────────────────── -->
                <header class="dashboard-header dashboard-header--client">
                    <!-- Üst Kısım: Logo, Başlık ve Kullanıcı Bilgisi -->
                    <div class="dashboard-header__top">
                        <div class="dashboard-header__brand">
                            <div class="dashboard-header__logo dashboard-header__logo--client">
                                <span class="dashboard-header__logo-icon">👤</span>
                            </div>
                            <div class="dashboard-header__titles">
                                <div class="dashboard-header__badge dashboard-header__badge--client">
                                    <span class="dashboard-header__badge-dot"></span>
                                    <span>Müşteri Paneli</span>
                                </div>
                                <h1 class="dashboard-header__title">Hesabım</h1>
                                <p class="dashboard-header__subtitle">Envanter, zimmet ve hesap işlemlerinizi tek noktadan yönetin</p>
                            </div>
                        </div>
                        <div class="dashboard-header__user">
                            <div class="dashboard-header__user-info">
                                <span class="dashboard-header__user-name"><?php echo htmlspecialchars($user['full_name']); ?></span>
                                <span class="dashboard-header__user-role"><?php echo $firmaAdi ? htmlspecialchars($firmaAdi) : 'Müşteri'; ?></span>
                            </div>
                            <div class="dashboard-header__user-avatar dashboard-header__user-avatar--client">
                                <span><?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Alt Kısım: Navigasyon Sekmeleri -->
                    <nav class="dashboard-nav" aria-label="Ana navigasyon">
                        <div class="dashboard-nav__tabs">
                            <?php foreach ($tabs as $index => $tab): ?>
                                <button class="dashboard-nav__tab <?php echo $index === 0 ? 'is-active' : ''; ?> panel-tab-btn"
                                    data-panel="<?php echo $tab['id']; ?>"
                                    title="<?php echo htmlspecialchars($tab['desc']); ?>"
                                    aria-selected="<?php echo $index === 0 ? 'true' : 'false'; ?>">
                                    <span class="dashboard-nav__tab-icon"><?php echo $tab['icon']; ?></span>
                                    <span class="dashboard-nav__tab-label"><?php echo htmlspecialchars($tab['label']); ?></span>
                                </button>
                            <?php endforeach; ?>
                        </div>
                        <div class="dashboard-nav__actions">
                            <button class="dashboard-nav__action" title="Sayfayı Yenile" onclick="location.reload()">
                                <span>🔄</span>
                            </button>
                            <a href="<?php echo BASE_PATH; ?>/?route=home" class="dashboard-nav__action" title="Ana Sayfaya Git">
                                <span>🏠</span>
                            </a>
                            <a href="<?php echo BASE_PATH; ?>/auth/auth_logout_action.php" class="dashboard-nav__action dashboard-nav__action--logout" title="Çıkış Yap">
                                <span>🚪</span>
                            </a>
                        </div>
                    </nav>
                </header>

                <!-- ═══════════════════════════════════════════════════════════════
                     KAYAN PANEL KAPSAYICISI
                     ═══════════════════════════════════════════════════════════════ -->
                <div class="sliding-panel-container">
                    <div class="sliding-panel-wrapper">

                        <!-- ═══════════════════════════════════════════════════════
                             PANEL 1: GENEL BAKIŞ (OVERVIEW)
                             ═══════════════════════════════════════════════════════ -->
                        <section class="sliding-panel is-active" data-panel-id="overview">
                            <?php echo render_messages(); ?>

                            <div class="overview-grid">
                                
                                <!-- Karşılama Kartı -->
                                <div class="panel-card overview-welcome-card">
                                    <div class="overview-welcome">
                                        <div class="overview-welcome__left">
                                            <div class="overview-welcome__avatar">
                                                <span class="overview-welcome__avatar-icon"><?php echo $greetingIcon; ?></span>
                                            </div>
                                            <div class="overview-welcome__info">
                                                <h2 class="overview-welcome__title"><?php echo $greeting; ?>, <?php echo htmlspecialchars($user['first_name']); ?>!</h2>
                                                <p class="overview-welcome__message">Hesabınıza hoş geldiniz. Aşağıdan hızlıca işlemlerinize ulaşabilirsiniz.</p>
                                                <div class="overview-welcome__meta">
                                                    <?php if ($firmaAdi): ?>
                                                        <span class="overview-welcome__company">
                                                            <span>🏢</span> <?php echo htmlspecialchars($firmaAdi); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="overview-welcome__right">
                                            <div class="overview-datetime">
                                                <div class="overview-datetime__time" id="liveTime"><?php echo date('H:i'); ?></div>
                                                <div class="overview-datetime__date" id="liveDate"><?php 
                                                    $months = ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];
                                                    $days = ['Pazar', 'Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi'];
                                                    echo date('d') . ' ' . $months[date('n')-1] . ' ' . date('Y') . ', ' . $days[date('w')];
                                                ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- İstatistik Kartları -->
                                <div class="overview-metrics overview-metrics--client">
                                    <!-- Envanter -->
                                    <article class="metric-card metric-card--inventory" data-goto-panel="inventory" role="button" tabindex="0">
                                        <div class="metric-card__header">
                                            <div class="metric-card__icon"><span>📦</span></div>
                                        </div>
                                        <div class="metric-card__body">
                                            <div class="metric-card__value"><?php echo number_format($stats['inventory']); ?></div>
                                            <div class="metric-card__label">Firma Envanteri</div>
                                            <div class="metric-card__sublabel">Kayıtlı cihazlar</div>
                                        </div>
                                        <div class="metric-card__footer">
                                            <span>Envanteri Görüntüle</span>
                                            <span class="metric-card__arrow">→</span>
                                        </div>
                                    </article>

                                    <!-- Zimmetler -->
                                    <article class="metric-card metric-card--assigned" data-goto-panel="assigned" role="button" tabindex="0">
                                        <div class="metric-card__header">
                                            <div class="metric-card__icon"><span>📋</span></div>
                                        </div>
                                        <div class="metric-card__body">
                                            <div class="metric-card__value"><?php echo number_format($stats['assigned']); ?></div>
                                            <div class="metric-card__label">Zimmetlerim</div>
                                            <div class="metric-card__sublabel">Size atanmış cihazlar</div>
                                        </div>
                                        <div class="metric-card__footer">
                                            <span>Zimmetleri Görüntüle</span>
                                            <span class="metric-card__arrow">→</span>
                                        </div>
                                    </article>

                                    <!-- Destek -->
                                    <article class="metric-card metric-card--support" data-goto-panel="support" role="button" tabindex="0">
                                        <div class="metric-card__header">
                                            <div class="metric-card__icon"><span>💬</span></div>
                                        </div>
                                        <div class="metric-card__body">
                                            <div class="metric-card__value"><?php echo $stats['tickets']; ?></div>
                                            <div class="metric-card__label">Destek Talepleri</div>
                                            <div class="metric-card__sublabel">Açık talepler</div>
                                        </div>
                                        <div class="metric-card__footer">
                                            <span>Destek Merkezi</span>
                                            <span class="metric-card__arrow">→</span>
                                        </div>
                                    </article>
                                </div>

                                <!-- Hızlı İşlemler -->
                                <div class="panel-card overview-actions-card">
                                    <div class="overview-card-header">
                                        <h3 class="overview-card-header__title">
                                            <span class="overview-card-header__icon">⚡</span>
                                            Hızlı İşlemler
                                        </h3>
                                    </div>
                                    <div class="quick-actions-grid quick-actions-grid--compact">
                                        <button class="action-card" data-goto-panel="inventory">
                                            <div class="action-card__icon action-card__icon--primary">📦</div>
                                            <div class="action-card__content">
                                                <div class="action-card__title">Envanterim</div>
                                                <div class="action-card__desc">Firma cihazlarını görüntüle</div>
                                            </div>
                                        </button>
                                        
                                        <button class="action-card" data-goto-panel="assigned">
                                            <div class="action-card__icon action-card__icon--success">📋</div>
                                            <div class="action-card__content">
                                                <div class="action-card__title">Zimmetlerim</div>
                                                <div class="action-card__desc">Atanmış cihazları gör</div>
                                            </div>
                                        </button>
                                        
                                        <button class="action-card" data-goto-panel="account">
                                            <div class="action-card__icon action-card__icon--purple">⚙️</div>
                                            <div class="action-card__content">
                                                <div class="action-card__title">Hesap Ayarları</div>
                                                <div class="action-card__desc">Profil ve güvenlik</div>
                                            </div>
                                        </button>
                                        
                                        <button class="action-card" data-goto-panel="support">
                                            <div class="action-card__icon action-card__icon--info">💬</div>
                                            <div class="action-card__content">
                                                <div class="action-card__title">Destek Talebi</div>
                                                <div class="action-card__desc">Yardım ve iletişim</div>
                                            </div>
                                        </button>
                                    </div>
                                </div>

                                <!-- Bilgilendirme -->
                                <div class="panel-card overview-tips-card">
                                    <div class="overview-tips">
                                        <div class="overview-tips__icon">💡</div>
                                        <div class="overview-tips__content">
                                            <h4 class="overview-tips__title">Bilgi</h4>
                                            <p class="overview-tips__text">Yukarıdaki sekmelere tıklayarak farklı bölümlere geçebilirsiniz. Herhangi bir sorunuz varsa Destek bölümünden bize ulaşabilirsiniz.</p>
                                        </div>
                                        <button class="overview-tips__close" aria-label="Kapat">×</button>
                                    </div>
                                </div>

                            </div>
                        </section>

                        <!-- ═══════════════════════════════════════════════════════
                             PANEL 2: ENVANTERİM
                             ═══════════════════════════════════════════════════════ -->
                        <section class="sliding-panel" data-panel-id="inventory">
                            <div class="panel-card">
                                <div class="sub-panel-header">
                                    <div class="sub-panel-header__info">
                                        <h3 class="sub-panel-header__title">📦 Envanterim</h3>
                                        <p class="sub-panel-header__desc">Firmanıza ait kayıtlı cihazların listesi. Toplam: <?php echo count($inventoryItems); ?> kayıt</p>
                                    </div>
                                </div>

                                <?php if (empty($inventoryItems)): ?>
                                    <div class="panel-empty-state">
                                        <div class="panel-empty-state__icon">📭</div>
                                        <h4 class="panel-empty-state__title">Envanter Bulunamadı</h4>
                                        <p class="panel-empty-state__text">Firmanıza ait envanter kaydı henüz bulunmuyor veya firma bilgileriniz eksik.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="table-scroll">
                                        <table class="data-table">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Ürün Adı</th>
                                                    <th>Marka</th>
                                                    <th>Seri No</th>
                                                    <th>Barkod</th>
                                                    <th>Demirbaş Kodu</th>
                                                    <th>Takip</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($inventoryItems as $item): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($item['id']); ?></td>
                                                        <td><?php echo htmlspecialchars($item['urun_adi'] ?? '—'); ?></td>
                                                        <td><?php echo htmlspecialchars($item['marka'] ?? '—'); ?></td>
                                                        <td><code><?php echo htmlspecialchars($item['seri_no'] ?? '—'); ?></code></td>
                                                        <td><?php echo htmlspecialchars($item['barkod'] ?? '—'); ?></td>
                                                        <td><?php echo htmlspecialchars($item['demirbas_kodu'] ?? '—'); ?></td>
                                                        <td>
                                                            <span class="status-badge status-badge--<?php echo ($item['takip_tipi'] ?? '') === 'aktif' ? 'success' : 'default'; ?>">
                                                                <?php echo htmlspecialchars($item['takip_tipi'] ?? '—'); ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </section>

                        <!-- ═══════════════════════════════════════════════════════
                             PANEL 3: ZİMMETLERİM
                             ═══════════════════════════════════════════════════════ -->
                        <section class="sliding-panel" data-panel-id="assigned">
                            <div class="panel-card">
                                <div class="sub-panel-header">
                                    <div class="sub-panel-header__info">
                                        <h3 class="sub-panel-header__title">📋 Zimmetlerim</h3>
                                        <p class="sub-panel-header__desc">Size atanmış cihazların listesi. Toplam: <?php echo count($assignedItems); ?> kayıt</p>
                                    </div>
                                </div>

                                <?php if (empty($assignedItems)): ?>
                                    <div class="panel-empty-state">
                                        <div class="panel-empty-state__icon">📭</div>
                                        <h4 class="panel-empty-state__title">Zimmet Bulunamadı</h4>
                                        <p class="panel-empty-state__text">Üzerinize kayıtlı zimmet bulunmuyor. Eğer bir cihaz zimmetlenmişse ve burada görünmüyorsa lütfen yöneticinizle iletişime geçin.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="table-scroll">
                                        <table class="data-table">
                                            <thead>
                                                <tr>
                                                    <th>Cihaz</th>
                                                    <th>Marka</th>
                                                    <th>Seri No</th>
                                                    <th>Zimmet Tarihi</th>
                                                    <th>İade Tarihi</th>
                                                    <th>Durum</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($assignedItems as $item): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($item['urun_adi'] ?? '—'); ?></td>
                                                        <td><?php echo htmlspecialchars($item['marka'] ?? '—'); ?></td>
                                                        <td><code><?php echo htmlspecialchars($item['seri_no'] ?? '—'); ?></code></td>
                                                        <td><?php echo htmlspecialchars($item['zimmet_tarihi'] ?? '—'); ?></td>
                                                        <td><?php echo htmlspecialchars($item['iade_tarihi'] ?? '—'); ?></td>
                                                        <td>
                                                            <?php 
                                                            $iadeTarihi = $item['iade_tarihi'] ?? null;
                                                            $durum = $iadeTarihi ? 'İade Edildi' : 'Zimmetli';
                                                            $durumClass = $iadeTarihi ? 'default' : 'success';
                                                            ?>
                                                            <span class="status-badge status-badge--<?php echo $durumClass; ?>">
                                                                <?php echo $durum; ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </section>

                        <!-- ═══════════════════════════════════════════════════════
                             PANEL 4: HESAP AYARLARI
                             ═══════════════════════════════════════════════════════ -->
                        <section class="sliding-panel" data-panel-id="account">
                            <div class="panel-card">
                                <div class="sub-panel-header">
                                    <div class="sub-panel-header__info">
                                        <h3 class="sub-panel-header__title">⚙️ Hesap Ayarları</h3>
                                        <p class="sub-panel-header__desc">Profil, adres ve iletişim bilgilerinizi güncelleyin</p>
                                    </div>
                                </div>

                                <!-- Hesap Özeti -->
                                <div class="account-summary">
                                    <div class="account-summary__avatar">
                                        <span><?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?></span>
                                    </div>
                                    <div class="account-summary__info">
                                        <h4 class="account-summary__name"><?php echo htmlspecialchars($user['full_name']); ?></h4>
                                        <p class="account-summary__email"><?php echo htmlspecialchars($user['email']); ?></p>
                                        <div class="account-summary__badges">
                                            <span class="status-badge status-badge--info"><?php echo ucfirst($user['role'] ?? 'client'); ?></span>
                                            <?php if ($companyDisplay): ?>
                                                <span class="status-badge status-badge--default">🏢 <?php echo htmlspecialchars($companyDisplay); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Profil Düzenleme Formu -->
                                <form method="post" action="<?php echo BASE_PATH; ?>/auth/auth_profile_update_action.php" class="account-form">
                                    <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">
                                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($userId); ?>">

                                    <!-- Kullanıcı Bilgileri -->
                                    <div class="account-section">
                                        <h4 class="account-section__title">
                                            <span class="account-section__icon">👤</span>
                                            Kullanıcı Bilgileri
                                        </h4>
                                        <p class="account-section__desc">Hesabınıza ait temel kimlik bilgileri.</p>
                                        <div class="form-grid form-grid--two">
                                            <div class="form-field">
                                                <label>Ad</label>
                                                <input type="text" name="first_name" required value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>">
                                            </div>
                                            <div class="form-field">
                                                <label>Soyad</label>
                                                <input type="text" name="last_name" required value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>">
                                            </div>
                                            <div class="form-field">
                                                <label>E-posta</label>
                                                <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                                                <small class="form-help">E-posta değişikliği için lütfen admin ile iletişime geçin.</small>
                                            </div>
                                            <div class="form-field">
                                                <label>Firma Adı</label>
                                                <input type="text" name="company_name" value="<?php echo htmlspecialchars($companyDisplay); ?>" placeholder="Firma adı (opsiyonel)">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Adres Bilgileri -->
                                    <div class="account-section">
                                        <h4 class="account-section__title">
                                            <span class="account-section__icon">📍</span>
                                            Adres Bilgileri
                                        </h4>
                                        <p class="account-section__desc">Teslimat ve fatura adresiniz.</p>
                                        <div class="form-grid form-grid--two">
                                            <div class="form-field form-grid__wide">
                                                <label>Adres (Satır 1)</label>
                                                <input type="text" name="address_line1" value="<?php echo htmlspecialchars($profile['address_line1'] ?? ''); ?>" placeholder="Sokak, Cadde, Bina No">
                                            </div>
                                            <div class="form-field form-grid__wide">
                                                <label>Adres (Satır 2)</label>
                                                <input type="text" name="address_line2" value="<?php echo htmlspecialchars($profile['address_line2'] ?? ''); ?>" placeholder="Mahalle, Daire No (opsiyonel)">
                                            </div>
                                            <div class="form-field">
                                                <label>Şehir</label>
                                                <input type="text" name="city" value="<?php echo htmlspecialchars($profile['city'] ?? ''); ?>" placeholder="İstanbul">
                                            </div>
                                            <div class="form-field">
                                                <label>İlçe / Bölge</label>
                                                <input type="text" name="state" value="<?php echo htmlspecialchars($profile['state'] ?? ''); ?>" placeholder="Kadıköy">
                                            </div>
                                            <div class="form-field">
                                                <label>Posta Kodu</label>
                                                <input type="text" name="postal_code" value="<?php echo htmlspecialchars($profile['postal_code'] ?? ''); ?>" placeholder="34700">
                                            </div>
                                            <div class="form-field">
                                                <label>Ülke</label>
                                                <input type="text" name="country" value="<?php echo htmlspecialchars($profile['country'] ?? ''); ?>" placeholder="Türkiye">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- İletişim ve Notlar -->
                                    <div class="account-section">
                                        <h4 class="account-section__title">
                                            <span class="account-section__icon">📞</span>
                                            İletişim ve Notlar
                                        </h4>
                                        <p class="account-section__desc">Teslimat için ek açıklamalar ve iletişim bilgileriniz.</p>
                                        <div class="form-grid form-grid--two">
                                            <div class="form-field form-grid__wide">
                                                <label>Telefon</label>
                                                <input type="tel" name="phone" value="<?php echo htmlspecialchars($profile['phone'] ?? ''); ?>" placeholder="+90 5xx xxx xx xx">
                                            </div>
                                            <div class="form-field form-grid__wide">
                                                <label>Kargo / Teslimat Notları</label>
                                                <textarea name="shipping_instructions" rows="3" placeholder="Kapıda teslim, zil numarası, vb."><?php echo htmlspecialchars($profile['shipping_instructions'] ?? ''); ?></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Güvenlik -->
                                    <div class="account-section">
                                        <h4 class="account-section__title">
                                            <span class="account-section__icon">🔐</span>
                                            Güvenlik
                                        </h4>
                                        <p class="account-section__desc">Hesap güvenlik bilgileriniz.</p>
                                        <div class="account-info-grid">
                                            <div class="account-info-item">
                                                <div class="account-info-item__label">Şifre</div>
                                                <div class="account-info-item__value">••••••••</div>
                                            </div>
                                            <div class="account-info-item">
                                                <div class="account-info-item__label">Son Giriş</div>
                                                <div class="account-info-item__value"><?php echo date('d.m.Y H:i'); ?></div>
                                            </div>
                                        </div>
                                        <div class="account-section__hint">
                                            <span>💡</span>
                                            <span>Şifre değişikliği için ayrı bir güvenlik formu kullanılacaktır.</span>
                                        </div>
                                    </div>

                                    <!-- Form Butonları -->
                                    <div class="account-form__actions">
                                        <button type="reset" class="btn btn--ghost">Değişiklikleri Sıfırla</button>
                                        <button type="submit" class="btn btn--primary">💾 Değişiklikleri Kaydet</button>
                                    </div>
                                </form>
                            </div>
                        </section>

                        <!-- ═══════════════════════════════════════════════════════
                             PANEL 5: DESTEK
                             ═══════════════════════════════════════════════════════ -->
                        <section class="sliding-panel" data-panel-id="support">
                            <div class="panel-card">
                                <div class="sub-panel-header">
                                    <div class="sub-panel-header__info">
                                        <h3 class="sub-panel-header__title">💬 Destek Merkezi</h3>
                                        <p class="sub-panel-header__desc">Yardım ve iletişim seçenekleri</p>
                                    </div>
                                </div>

                                <div class="support-grid">
                                    <!-- Destek Kartları -->
                                    <div class="support-card">
                                        <div class="support-card__icon">📞</div>
                                        <h4 class="support-card__title">Telefon Desteği</h4>
                                        <p class="support-card__desc">Hafta içi 09:00 - 18:00 arası</p>
                                        <a href="tel:+902121234567" class="btn btn--ghost btn--sm">Ara</a>
                                    </div>

                                    <div class="support-card">
                                        <div class="support-card__icon">📧</div>
                                        <h4 class="support-card__title">E-posta Desteği</h4>
                                        <p class="support-card__desc">24 saat içinde yanıt</p>
                                        <a href="mailto:destek@turkuazit.com" class="btn btn--ghost btn--sm">E-posta Gönder</a>
                                    </div>

                                    <div class="support-card">
                                        <div class="support-card__icon">📝</div>
                                        <h4 class="support-card__title">Destek Talebi</h4>
                                        <p class="support-card__desc">Detaylı destek formu</p>
                                        <button class="btn btn--ghost btn--sm" disabled>Yakında</button>
                                    </div>

                                    <div class="support-card">
                                        <div class="support-card__icon">📚</div>
                                        <h4 class="support-card__title">Yardım Merkezi</h4>
                                        <p class="support-card__desc">SSS ve kullanım kılavuzları</p>
                                        <button class="btn btn--ghost btn--sm" disabled>Yakında</button>
                                    </div>
                                </div>

                                <!-- İletişim Bilgileri -->
                                <div class="support-contact">
                                    <h4 class="support-contact__title">İletişim Bilgileri</h4>
                                    <div class="support-contact__grid">
                                        <div class="support-contact__item">
                                            <span class="support-contact__icon">🏢</span>
                                            <span>TurkuazIT Bilişim Hizmetleri</span>
                                        </div>
                                        <div class="support-contact__item">
                                            <span class="support-contact__icon">📍</span>
                                            <span>İstanbul, Türkiye</span>
                                        </div>
                                        <div class="support-contact__item">
                                            <span class="support-contact__icon">📧</span>
                                            <span>info@turkuazit.com</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>

                    </div>
                </div>

            </div>
        </div>
    </div>
</main>