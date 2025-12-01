<?php
// templates/page_admin_dashboard.php
// ═══════════════════════════════════════════════════════════════════════════════
// YÖNETİM PANELİ - TEK SAYFA UYGULAMA (SPA)
// ═══════════════════════════════════════════════════════════════════════════════
// Bu dosya yönetim panelinin tüm işlevlerini tek sayfada toplar.
// Sayfa yenilenmeden panel geçişleri ile SPA benzeri deneyim sunar.
// 
// ANA PANELLER:
// 1. Genel Bakış - İstatistikler ve özet bilgiler
// 2. Envanter Listesi - Tüm envanter öğelerini görüntüle
// 3. Envanter Ekle - Yeni envanter oluşturma formu
// 4. Kullanıcılar - Kullanıcı listesi (alt panel) ve kullanıcı ekle (alt panel)
// 5. Firmalar - Firma listesi (alt panel) ve firma ekle (alt panel)
// 6. Kategoriler - Kategori listesi (alt panel) ve kategori ekle (alt panel)
// 
// ALT PANEL SİSTEMİ:
// - Kullanıcılar, Firmalar, Kategoriler ana panelleri içinde
// - Liste ve Ekle alt sekmeleri bulunur
// - Alt sekmeler arasında sayfa yenilenmeden geçiş yapılır
// 
// TEKNİK ÖZELLİKLER:
// - CSS animasyonları ile yumuşak panel geçişleri
// - JavaScript ile dinamik içerik yönetimi
// - Alt panel sistemi ile iç içe sekme yapısı
// - AJAX form gönderimi (opsiyonel)
// - Responsive tasarım
// ═══════════════════════════════════════════════════════════════════════════════
require_once __DIR__ . '/../app_config.php';
require_admin();

// ─────────────────────────────────────────────────────────────────────────────
// VERİTABANI VERİLERİNİ ÇEK
// ─────────────────────────────────────────────────────────────────────────────

// ─────────────────────────────────────────────────────────────────────────────
// 1. İSTATİSTİKLER VE TREND VERİLERİ
// ─────────────────────────────────────────────────────────────────────────────
// Ana istatistikler + geçen haftaya kıyasla değişim yüzdesi hesaplanır.
// Trend verileri (son 7 gün) sparkline grafikleri için kullanılır.
$stats = [];
$trends = [];
try {
    // Mevcut toplam sayılar
    $stats['users'] = (int)($pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() ?: 0);
    $stats['envanter'] = (int)($pdo->query('SELECT COUNT(*) FROM envanter')->fetchColumn() ?: 0);
    $stats['firmalar'] = (int)($pdo->query('SELECT COUNT(*) FROM firmalar')->fetchColumn() ?: 0);

    // Aktif kullanıcı sayısı (is_active = 1)
    $stats['active_users'] = (int)($pdo->query('SELECT COUNT(*) FROM users WHERE is_active = 1')->fetchColumn() ?: 0);

    // Geçen hafta ile kıyaslama için tarih hesaplama
    $oneWeekAgo = date('Y-m-d H:i:s', strtotime('-7 days'));

    // Geçen haftaki kullanıcı sayısı (created_at varsa)
    try {
        $lastWeekUsers = (int)($pdo->query("SELECT COUNT(*) FROM users WHERE created_at < '$oneWeekAgo'")->fetchColumn() ?: 0);
        $trends['users_change'] = $lastWeekUsers > 0 ? round((($stats['users'] - $lastWeekUsers) / $lastWeekUsers) * 100, 1) : 0;
        $trends['users_new'] = $stats['users'] - $lastWeekUsers;
    } catch (PDOException $e) {
        $trends['users_change'] = 0;
        $trends['users_new'] = 0;
    }

    // Geçen haftaki envanter sayısı
    try {
        $lastWeekEnvanter = (int)($pdo->query("SELECT COUNT(*) FROM envanter WHERE created_at < '$oneWeekAgo'")->fetchColumn() ?: 0);
        $trends['envanter_change'] = $lastWeekEnvanter > 0 ? round((($stats['envanter'] - $lastWeekEnvanter) / $lastWeekEnvanter) * 100, 1) : 0;
        $trends['envanter_new'] = $stats['envanter'] - $lastWeekEnvanter;
    } catch (PDOException $e) {
        $trends['envanter_change'] = 0;
        $trends['envanter_new'] = 0;
    }

    // Firma trend verisi
    $trends['firmalar_change'] = 0;
    $trends['firmalar_new'] = 0;

    // Son 7 günlük kayıt sayıları (sparkline için)
    $trends['users_sparkline'] = [];
    $trends['envanter_sparkline'] = [];
    for ($i = 6; $i >= 0; $i--) {
        $dayStart = date('Y-m-d 00:00:00', strtotime("-$i days"));
        $dayEnd = date('Y-m-d 23:59:59', strtotime("-$i days"));
        try {
            $trends['users_sparkline'][] = (int)($pdo->query("SELECT COUNT(*) FROM users WHERE created_at BETWEEN '$dayStart' AND '$dayEnd'")->fetchColumn() ?: 0);
            $trends['envanter_sparkline'][] = (int)($pdo->query("SELECT COUNT(*) FROM envanter WHERE created_at BETWEEN '$dayStart' AND '$dayEnd'")->fetchColumn() ?: 0);
        } catch (PDOException $e) {
            $trends['users_sparkline'][] = 0;
            $trends['envanter_sparkline'][] = 0;
        }
    }
    $trends['firmalar_sparkline'] = [0, 0, 0, 0, 0, 0, 0]; // Firma için varsayılan

} catch (PDOException $e) {
    $stats = ['users' => 0, 'envanter' => 0, 'firmalar' => 0, 'active_users' => 0];
    $trends = [
        'users_change' => 0,
        'users_new' => 0,
        'users_sparkline' => [0, 0, 0, 0, 0, 0, 0],
        'envanter_change' => 0,
        'envanter_new' => 0,
        'envanter_sparkline' => [0, 0, 0, 0, 0, 0, 0],
        'firmalar_change' => 0,
        'firmalar_new' => 0,
        'firmalar_sparkline' => [0, 0, 0, 0, 0, 0, 0]
    ];
}

// ─────────────────────────────────────────────────────────────────────────────
// 2. SON AKTİVİTELER (Activity Log)
// ─────────────────────────────────────────────────────────────────────────────
// Sistemdeki son 10 aktivite kaydını çeker.
// Not: activity_log tablosu yoksa simüle edilmiş veriler kullanılır.
$recentActivities = [];
try {
    // Önce activity_log tablosunun varlığını kontrol et
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'activity_log'")->fetchColumn();
    if ($tableCheck) {
        $stmt = $pdo->query("SELECT al.*, u.first_name, u.last_name 
                             FROM activity_log al 
                             LEFT JOIN users u ON u.id = al.user_id 
                             ORDER BY al.created_at DESC LIMIT 10");
        $recentActivities = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    // Tablo yoksa boş bırak
}

// Aktivite tablosu yoksa simüle edilmiş veriler oluştur
if (empty($recentActivities)) {
    $recentActivities = [
        ['type' => 'user_login', 'description' => 'Sisteme giriş yapıldı', 'user_name' => current_user()['full_name'], 'created_at' => date('Y-m-d H:i:s'), 'icon' => '🔐', 'color' => 'success'],
        ['type' => 'inventory_add', 'description' => 'Yeni envanter eklendi', 'user_name' => 'Sistem', 'created_at' => date('Y-m-d H:i:s', strtotime('-1 hour')), 'icon' => '📦', 'color' => 'primary'],
        ['type' => 'user_register', 'description' => 'Yeni kullanıcı kaydı', 'user_name' => 'Sistem', 'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours')), 'icon' => '👤', 'color' => 'info'],
        ['type' => 'inventory_update', 'description' => 'Envanter güncellendi', 'user_name' => 'Admin', 'created_at' => date('Y-m-d H:i:s', strtotime('-3 hours')), 'icon' => '✏️', 'color' => 'warning'],
        ['type' => 'company_add', 'description' => 'Yeni firma eklendi', 'user_name' => 'Admin', 'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')), 'icon' => '🏢', 'color' => 'purple'],
    ];
}

// ─────────────────────────────────────────────────────────────────────────────
// 3. BEKLEYEN GÖREVLER / UYARILAR
// ─────────────────────────────────────────────────────────────────────────────
// Dikkat gerektiren öğeleri hesapla
$pendingTasks = [];

// Onay bekleyen kullanıcılar (is_active = 0 olanlar)
try {
    $pendingUsers = (int)($pdo->query('SELECT COUNT(*) FROM users WHERE is_active = 0')->fetchColumn() ?: 0);
    if ($pendingUsers > 0) {
        $pendingTasks[] = [
            'type' => 'pending_users',
            'title' => 'Onay Bekleyen Kullanıcılar',
            'count' => $pendingUsers,
            'icon' => '👤',
            'color' => 'warning',
            'action' => 'kullanicilar'
        ];
    }
} catch (PDOException $e) {
}

// Firma ataması yapılmamış kullanıcılar
try {
    $unassignedUsers = (int)($pdo->query('SELECT COUNT(*) FROM users WHERE firma_id IS NULL')->fetchColumn() ?: 0);
    if ($unassignedUsers > 0) {
        $pendingTasks[] = [
            'type' => 'unassigned_users',
            'title' => 'Firma Ataması Beklenen Kullanıcılar',
            'count' => $unassignedUsers,
            'icon' => '🔗',
            'color' => 'info',
            'action' => 'kullanicilar'
        ];
    }
} catch (PDOException $e) {
}

// Lokasyonu olmayan envanterler
try {
    $noLocationInventory = (int)($pdo->query('SELECT COUNT(*) FROM envanter WHERE lokasyon_id IS NULL')->fetchColumn() ?: 0);
    if ($noLocationInventory > 0) {
        $pendingTasks[] = [
            'type' => 'no_location',
            'title' => 'Lokasyonsuz Envanterler',
            'count' => $noLocationInventory,
            'icon' => '📍',
            'color' => 'danger',
            'action' => 'envanter'
        ];
    }
} catch (PDOException $e) {
}

// ─────────────────────────────────────────────────────────────────────────────
// 4. SİSTEM DURUMU
// ─────────────────────────────────────────────────────────────────────────────
$systemStatus = [
    'database' => ['status' => 'online', 'label' => 'Veritabanı Bağlantısı', 'icon' => '🗄️'],
    'storage' => ['status' => 'normal', 'label' => 'Depolama Alanı', 'icon' => '💾', 'usage' => '45%'],
    'sessions' => ['status' => 'online', 'label' => 'Aktif Oturumlar', 'icon' => '🔌', 'count' => 1]
];

// Veritabanı bağlantı kontrolü
try {
    $pdo->query('SELECT 1');
    $systemStatus['database']['status'] = 'online';
} catch (PDOException $e) {
    $systemStatus['database']['status'] = 'error';
}

// ─────────────────────────────────────────────────────────────────────────────
// 5. KULLANICI SON GİRİŞ BİLGİSİ
// ─────────────────────────────────────────────────────────────────────────────
$lastLogin = null;
try {
    $userId = current_user()['id'];
    // last_login_at sütunu varsa kullan
    $stmt = $pdo->prepare("SELECT last_login_at FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $lastLogin = $stmt->fetchColumn();
} catch (PDOException $e) {
    // Sütun yoksa null kalır
}

// Envanter listesi
$envanterItems = [];
try {
    $stmt = $pdo->query('SELECT e.id, e.urun_adi, e.marka, e.seri_no, e.barkod, e.demirbas_kodu, f.firma_adi, l.lokasyon_adi 
                         FROM envanter e 
                         LEFT JOIN firmalar f ON f.id = e.firma_id 
                         LEFT JOIN lokasyonlar l ON l.id = e.lokasyon_id 
                         ORDER BY e.id DESC LIMIT 500');
    $envanterItems = $stmt->fetchAll();
} catch (PDOException $e) {
    $envanterItems = [];
}

// Kullanıcı listesi
$users = [];
try {
    $users = $pdo->query('SELECT u.id, u.first_name, u.last_name, u.email, u.role, u.is_active, u.firma_id, f.firma_adi 
                          FROM users u 
                          LEFT JOIN firmalar f ON f.id = u.firma_id 
                          ORDER BY u.id DESC LIMIT 500')->fetchAll();
} catch (PDOException $e) {
    $users = [];
}

// Firma ve lokasyon verileri (form için)
$firms = [];
$locations = [];
try {
    $firms = $pdo->query('SELECT id, firma_adi FROM firmalar ORDER BY firma_adi')->fetchAll();
    $locations = $pdo->query('SELECT id, lokasyon_adi, firma_id FROM lokasyonlar ORDER BY lokasyon_adi')->fetchAll();
} catch (PDOException $e) {
    // tablolar yoksa boş bırak
}

// ─────────────────────────────────────────────────────────────────────────────
// SEKME YAPILANDIRMASI
// ─────────────────────────────────────────────────────────────────────────────
// Her sekme bir panele karşılık gelir. Bu dizi hem menü hem de panel
// oluşturmak için kullanılır.
$tabs = [
    [
        'id' => 'overview',
        'icon' => '🏠',
        'label' => 'Genel Bakış',
        'desc' => 'İstatistikler ve sistem özeti'
    ],
    [
        'id' => 'envanter',
        'icon' => '📦',
        'label' => 'Envanter',
        'desc' => 'Envanter listesi ve yönetimi'
    ],
    [
        'id' => 'kullanicilar',
        'icon' => '👥',
        'label' => 'Kullanıcılar',
        'desc' => 'Kullanıcı ve rol yönetimi'
    ],
    [
        'id' => 'firmalar',
        'icon' => '🏢',
        'label' => 'Firmalar',
        'desc' => 'Firma ve lokasyon yönetimi'
    ],
    [
        'id' => 'kategoriler',
        'icon' => '🏷️',
        'label' => 'Kategoriler',
        'desc' => 'Envanter kategorileri'
    ],
];
?>

<main class="section">
    <div class="container">
        <!-- ═══════════════════════════════════════════════════════════════════
             ANA PANEL KAPSAYICI
             Sidebar olmadan tam genişlik kullanır.
             ═══════════════════════════════════════════════════════════════════ -->
        <div class="panel-shell panel-shell--full-width">

            <div class="panel-main">
                <!-- ─────────────────────────────────────────────────────────────
                     PANEL BAŞLIĞI - MODERN TASARIM
                     Gradient arka plan, ikon ve sekme navigasyonu içerir.
                     ───────────────────────────────────────────────────────────── -->
                <header class="dashboard-header">
                    <!-- Üst Kısım: Logo, Başlık ve Kullanıcı Bilgisi -->
                    <div class="dashboard-header__top">
                        <div class="dashboard-header__brand">
                            <div class="dashboard-header__logo">
                                <span class="dashboard-header__logo-icon">🛡️</span>
                            </div>
                            <div class="dashboard-header__titles">
                                <div class="dashboard-header__badge">
                                    <span class="dashboard-header__badge-dot"></span>
                                    <span>Yönetim Modu</span>
                                </div>
                                <h1 class="dashboard-header__title">TurkuazIT Yönetim Paneli</h1>
                                <p class="dashboard-header__subtitle">Envanter, kullanıcı ve firma yönetimini tek noktadan gerçekleştirin</p>
                            </div>
                        </div>
                        <div class="dashboard-header__user">
                            <div class="dashboard-header__user-info">
                                <span class="dashboard-header__user-name"><?php echo htmlspecialchars(current_user()['full_name']); ?></span>
                                <span class="dashboard-header__user-role"><?php echo ucfirst(current_user()['role']); ?></span>
                            </div>
                            <div class="dashboard-header__user-avatar">
                                <span><?php echo strtoupper(substr(current_user()['first_name'], 0, 1) . substr(current_user()['last_name'], 0, 1)); ?></span>
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
                                    <?php if ($tab['id'] === 'kullanicilar' && isset($pendingTasks)): ?>
                                        <?php
                                        $pendingUsersCount = 0;
                                        foreach ($pendingTasks as $task) {
                                            if ($task['type'] === 'pending_users') $pendingUsersCount = $task['count'];
                                        }
                                        if ($pendingUsersCount > 0): ?>
                                            <span class="dashboard-nav__tab-badge"><?php echo $pendingUsersCount; ?></span>
                                        <?php endif; ?>
                                    <?php endif; ?>
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
                     Tüm içerik panelleri bu alan içinde yer alır.
                     Animasyonlar CSS ile, geçişler JavaScript ile yönetilir.
                     ═══════════════════════════════════════════════════════════════ -->
                <div class="sliding-panel-container">
                    <div class="sliding-panel-wrapper">

                        <!-- ═══════════════════════════════════════════════════════
                             PANEL 1: GENEL BAKIŞ (OVERVIEW) - KOMUTA MERKEZİ
                             Yöneticiler için kapsamlı bir kontrol paneli.
                             - Global arama çubuğu
                             - Kişiselleştirilmiş karşılama (günün saatine göre)
                             - Canlı tarih/saat gösterimi
                             - Trend göstergeli istatistik kartları (sparkline)
                             - Son aktiviteler zaman çizelgesi
                             - Hızlı işlemler grid'i
                             - Sistem durumu göstergeleri
                             - Bekleyen görevler/uyarılar
                             ═══════════════════════════════════════════════════════ -->
                        <section class="sliding-panel is-active" data-panel-id="overview">
                            <?php echo render_messages(); ?>

                            <?php
                            // Günün saatine göre karşılama mesajı
                            $hour = (int)date('H');
                            if ($hour >= 5 && $hour < 12) {
                                $greeting = 'Günaydın';
                                $greetingIcon = '🌅';
                                $greetingMessage = 'Yeni bir güne harika bir başlangıç yapın!';
                            } elseif ($hour >= 12 && $hour < 17) {
                                $greeting = 'İyi günler';
                                $greetingIcon = '☀️';
                                $greetingMessage = 'Günün geri kalanı verimli geçsin!';
                            } elseif ($hour >= 17 && $hour < 21) {
                                $greeting = 'İyi akşamlar';
                                $greetingIcon = '🌆';
                                $greetingMessage = 'Akşam üstü işlerini tamamlayın.';
                            } else {
                                $greeting = 'İyi geceler';
                                $greetingIcon = '🌙';
                                $greetingMessage = 'Gece vardiyasında başarılar!';
                            }
                            ?>

                            <!-- ─────────────────────────────────────────────────────
                                 1. GLOBAL ARAMA ÇUBUĞU
                                 Envanter, kullanıcı ve firmalar arasında hızlı arama.
                                 Klavye kısayolu: / tuşu
                                 ───────────────────────────────────────────────────── -->
                            <div class="overview-search" role="search">
                                <div class="overview-search__container">
                                    <span class="overview-search__icon">🔍</span>
                                    <input type="text"
                                        class="overview-search__input"
                                        id="globalSearchInput"
                                        placeholder="Envanter, kullanıcı veya firma ara..."
                                        autocomplete="off"
                                        aria-label="Global arama">
                                    <kbd class="overview-search__shortcut" title="Aramak için / tuşuna basın">/</kbd>
                                </div>
                                <div class="overview-search__results" id="globalSearchResults" hidden></div>
                            </div>

                            <!-- Ana Dashboard Grid -->
                            <div class="overview-grid">

                                <!-- ─────────────────────────────────────────────────────
                                     2. KARŞILAMA BÖLÜMÜ (Welcome Section)
                                     Kişiselleştirilmiş karşılama, tarih/saat ve son giriş.
                                     ───────────────────────────────────────────────────── -->
                                <div class="panel-card overview-welcome-card">
                                    <div class="overview-welcome">
                                        <div class="overview-welcome__left">
                                            <div class="overview-welcome__avatar">
                                                <span class="overview-welcome__avatar-icon"><?php echo $greetingIcon; ?></span>
                                            </div>
                                            <div class="overview-welcome__info">
                                                <h2 class="overview-welcome__title"><?php echo $greeting; ?>, <?php echo htmlspecialchars(current_user()['full_name']); ?>!</h2>
                                                <p class="overview-welcome__message"><?php echo $greetingMessage; ?></p>
                                                <div class="overview-welcome__meta">
                                                    <span class="role-badge role-badge--<?php echo current_user()['role']; ?>">
                                                        <?php echo strtoupper(current_user()['role']); ?>
                                                    </span>
                                                    <?php if ($lastLogin): ?>
                                                        <span class="overview-welcome__last-login">
                                                            <span class="overview-welcome__last-login-icon">🕐</span>
                                                            Son giriş: <?php echo date('d.m.Y H:i', strtotime($lastLogin)); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="overview-welcome__right">
                                            <div class="overview-datetime">
                                                <div class="overview-datetime__time" id="liveTime"><?php echo date('H:i'); ?></div>
                                                <div class="overview-datetime__date" id="liveDate"><?php echo strftime('%d %B %Y, %A'); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- ─────────────────────────────────────────────────────
                                     3. İSTATİSTİK KARTLARI (Key Metrics)
                                     Trend göstergeleri ve sparkline grafikleri ile.
                                     ───────────────────────────────────────────────────── -->
                                <div class="overview-metrics">
                                    <!-- Kullanıcı Metrik Kartı -->
                                    <article class="metric-card metric-card--users" data-goto-panel="kullanicilar" role="button" tabindex="0" aria-label="Kullanıcıları yönet">
                                        <div class="metric-card__header">
                                            <div class="metric-card__icon">
                                                <span>👥</span>
                                            </div>
                                            <div class="metric-card__trend metric-card__trend--<?php echo $trends['users_change'] >= 0 ? 'up' : 'down'; ?>">
                                                <span class="metric-card__trend-icon"><?php echo $trends['users_change'] >= 0 ? '↑' : '↓'; ?></span>
                                                <span class="metric-card__trend-value"><?php echo abs($trends['users_change']); ?>%</span>
                                            </div>
                                        </div>
                                        <div class="metric-card__body">
                                            <div class="metric-card__value"><?php echo number_format($stats['users']); ?></div>
                                            <div class="metric-card__label">Toplam Kullanıcı</div>
                                            <div class="metric-card__sublabel">
                                                <span class="metric-card__active"><?php echo $stats['active_users']; ?> aktif</span>
                                                <?php if ($trends['users_new'] > 0): ?>
                                                    <span class="metric-card__new">+<?php echo $trends['users_new']; ?> bu hafta</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="metric-card__sparkline" data-values="<?php echo implode(',', $trends['users_sparkline']); ?>">
                                            <svg class="sparkline sparkline--users" viewBox="0 0 100 30" preserveAspectRatio="none"></svg>
                                        </div>
                                        <div class="metric-card__footer">
                                            <span>Kullanıcıları Yönet</span>
                                            <span class="metric-card__arrow">→</span>
                                        </div>
                                    </article>

                                    <!-- Envanter Metrik Kartı -->
                                    <article class="metric-card metric-card--inventory" data-goto-panel="envanter" role="button" tabindex="0" aria-label="Envanteri görüntüle">
                                        <div class="metric-card__header">
                                            <div class="metric-card__icon">
                                                <span>📦</span>
                                            </div>
                                            <div class="metric-card__trend metric-card__trend--<?php echo $trends['envanter_change'] >= 0 ? 'up' : 'down'; ?>">
                                                <span class="metric-card__trend-icon"><?php echo $trends['envanter_change'] >= 0 ? '↑' : '↓'; ?></span>
                                                <span class="metric-card__trend-value"><?php echo abs($trends['envanter_change']); ?>%</span>
                                            </div>
                                        </div>
                                        <div class="metric-card__body">
                                            <div class="metric-card__value"><?php echo number_format($stats['envanter']); ?></div>
                                            <div class="metric-card__label">Toplam Envanter</div>
                                            <div class="metric-card__sublabel">
                                                <span class="metric-card__category">Cihaz ve demirbaşlar</span>
                                                <?php if ($trends['envanter_new'] > 0): ?>
                                                    <span class="metric-card__new">+<?php echo $trends['envanter_new']; ?> bu hafta</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="metric-card__sparkline" data-values="<?php echo implode(',', $trends['envanter_sparkline']); ?>">
                                            <svg class="sparkline sparkline--inventory" viewBox="0 0 100 30" preserveAspectRatio="none"></svg>
                                        </div>
                                        <div class="metric-card__footer">
                                            <span>Envanteri Görüntüle</span>
                                            <span class="metric-card__arrow">→</span>
                                        </div>
                                    </article>

                                    <!-- Firma Metrik Kartı -->
                                    <article class="metric-card metric-card--companies" data-goto-panel="firmalar" role="button" tabindex="0" aria-label="Firmaları yönet">
                                        <div class="metric-card__header">
                                            <div class="metric-card__icon">
                                                <span>🏢</span>
                                            </div>
                                            <div class="metric-card__trend metric-card__trend--stable">
                                                <span class="metric-card__trend-icon">→</span>
                                                <span class="metric-card__trend-value">Sabit</span>
                                            </div>
                                        </div>
                                        <div class="metric-card__body">
                                            <div class="metric-card__value"><?php echo number_format($stats['firmalar']); ?></div>
                                            <div class="metric-card__label">Firmalar</div>
                                            <div class="metric-card__sublabel">
                                                <span class="metric-card__category">Kayıtlı firmalar</span>
                                            </div>
                                        </div>
                                        <div class="metric-card__sparkline" data-values="<?php echo implode(',', $trends['firmalar_sparkline']); ?>">
                                            <svg class="sparkline sparkline--companies" viewBox="0 0 100 30" preserveAspectRatio="none"></svg>
                                        </div>
                                        <div class="metric-card__footer">
                                            <span>Firmaları Yönet</span>
                                            <span class="metric-card__arrow">→</span>
                                        </div>
                                    </article>
                                </div>

                                <!-- ─────────────────────────────────────────────────────
                                     4. SON AKTİVİTELER VE HIZLI İŞLEMLER GRID
                                     İki sütunlu layout: Aktiviteler (sol), Hızlı İşlemler (sağ)
                                     ───────────────────────────────────────────────────── -->
                                <div class="overview-content-grid">

                                    <!-- Sol Sütun: Son Aktiviteler -->
                                    <div class="panel-card overview-activity-card">
                                        <div class="overview-card-header">
                                            <h3 class="overview-card-header__title">
                                                <span class="overview-card-header__icon">📋</span>
                                                Son Aktiviteler
                                            </h3>
                                            <a href="#" class="overview-card-header__link">Tümünü Gör →</a>
                                        </div>
                                        <div class="activity-timeline">
                                            <?php foreach ($recentActivities as $activity): ?>
                                                <?php
                                                // Aktivite tipi için renk ve ikon belirleme
                                                $activityIcon = $activity['icon'] ?? '📌';
                                                $activityColor = $activity['color'] ?? 'default';
                                                $userName = $activity['user_name'] ?? ($activity['first_name'] ?? 'Sistem') . ' ' . ($activity['last_name'] ?? '');
                                                $timeAgo = isset($activity['created_at']) ? $activity['created_at'] : '';
                                                ?>
                                                <div class="activity-item activity-item--<?php echo $activityColor; ?>">
                                                    <div class="activity-item__icon"><?php echo $activityIcon; ?></div>
                                                    <div class="activity-item__content">
                                                        <div class="activity-item__text"><?php echo htmlspecialchars($activity['description']); ?></div>
                                                        <div class="activity-item__meta">
                                                            <span class="activity-item__user"><?php echo htmlspecialchars(trim($userName)); ?></span>
                                                            <span class="activity-item__time" data-time="<?php echo $timeAgo; ?>">
                                                                <?php
                                                                if ($timeAgo) {
                                                                    $diff = time() - strtotime($timeAgo);
                                                                    if ($diff < 60) echo 'Az önce';
                                                                    elseif ($diff < 3600) echo floor($diff / 60) . ' dk önce';
                                                                    elseif ($diff < 86400) echo floor($diff / 3600) . ' saat önce';
                                                                    else echo floor($diff / 86400) . ' gün önce';
                                                                }
                                                                ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>

                                            <?php if (empty($recentActivities)): ?>
                                                <div class="activity-empty">
                                                    <span class="activity-empty__icon">📭</span>
                                                    <p>Henüz aktivite kaydı bulunmuyor.</p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Sağ Sütun: Hızlı İşlemler Grid -->
                                    <div class="panel-card overview-actions-card">
                                        <div class="overview-card-header">
                                            <h3 class="overview-card-header__title">
                                                <span class="overview-card-header__icon">⚡</span>
                                                Hızlı İşlemler
                                            </h3>
                                        </div>
                                        <div class="quick-actions-grid">
                                            <!-- Yeni Envanter Ekle -->
                                            <button class="action-card" data-goto-panel="envanter" data-sub-panel-goto="inventory-add" data-sub-panel-group="inventory">
                                                <div class="action-card__icon action-card__icon--primary">📦</div>
                                                <div class="action-card__content">
                                                    <div class="action-card__title">Yeni Envanter</div>
                                                    <div class="action-card__desc">Cihaz veya demirbaş ekle</div>
                                                </div>
                                            </button>

                                            <!-- Yeni Kullanıcı Ekle -->
                                            <button class="action-card" data-goto-panel="kullanicilar" data-sub-panel-goto="users-add" data-sub-panel-group="users">
                                                <div class="action-card__icon action-card__icon--success">👤</div>
                                                <div class="action-card__content">
                                                    <div class="action-card__title">Yeni Kullanıcı</div>
                                                    <div class="action-card__desc">Kullanıcı hesabı oluştur</div>
                                                </div>
                                            </button>

                                            <!-- Yeni Firma Ekle -->
                                            <button class="action-card" data-goto-panel="firmalar" data-sub-panel-goto="companies-add" data-sub-panel-group="companies">
                                                <div class="action-card__icon action-card__icon--info">🏢</div>
                                                <div class="action-card__content">
                                                    <div class="action-card__title">Yeni Firma</div>
                                                    <div class="action-card__desc">Firma kaydı oluştur</div>
                                                </div>
                                            </button>

                                            <!-- Rapor Oluştur -->
                                            <button class="action-card" data-action="generate-report">
                                                <div class="action-card__icon action-card__icon--warning">📊</div>
                                                <div class="action-card__content">
                                                    <div class="action-card__title">Rapor Oluştur</div>
                                                    <div class="action-card__desc">Sistem raporları</div>
                                                </div>
                                            </button>

                                            <!-- Sistem Ayarları -->
                                            <button class="action-card" data-action="settings">
                                                <div class="action-card__icon action-card__icon--purple">⚙️</div>
                                                <div class="action-card__content">
                                                    <div class="action-card__title">Ayarlar</div>
                                                    <div class="action-card__desc">Sistem yapılandırması</div>
                                                </div>
                                            </button>

                                            <!-- Bildirimler -->
                                            <button class="action-card" data-action="notifications">
                                                <div class="action-card__icon action-card__icon--danger">🔔</div>
                                                <div class="action-card__content">
                                                    <div class="action-card__title">Bildirimler</div>
                                                    <div class="action-card__desc">Sistem bildirimleri</div>
                                                </div>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- ─────────────────────────────────────────────────────
                                     5. SİSTEM DURUMU VE BEKLEYEN GÖREVLER
                                     İki sütunlu layout: Sistem Durumu (sol), Uyarılar (sağ)
                                     ───────────────────────────────────────────────────── -->
                                <div class="overview-status-grid">

                                    <!-- Sistem Durumu -->
                                    <div class="panel-card overview-system-card">
                                        <div class="overview-card-header">
                                            <h3 class="overview-card-header__title">
                                                <span class="overview-card-header__icon">🖥️</span>
                                                Sistem Durumu
                                            </h3>
                                        </div>
                                        <div class="system-status-list">
                                            <?php foreach ($systemStatus as $key => $status): ?>
                                                <div class="system-status-item">
                                                    <div class="system-status-item__left">
                                                        <span class="system-status-item__icon"><?php echo $status['icon']; ?></span>
                                                        <span class="system-status-item__label"><?php echo $status['label']; ?></span>
                                                    </div>
                                                    <div class="system-status-item__right">
                                                        <?php if (isset($status['usage'])): ?>
                                                            <span class="system-status-item__usage"><?php echo $status['usage']; ?></span>
                                                        <?php endif; ?>
                                                        <?php if (isset($status['count'])): ?>
                                                            <span class="system-status-item__count"><?php echo $status['count']; ?></span>
                                                        <?php endif; ?>
                                                        <span class="system-status-item__indicator system-status-item__indicator--<?php echo $status['status']; ?>"></span>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>

                                    <!-- Bekleyen Görevler / Uyarılar -->
                                    <div class="panel-card overview-alerts-card">
                                        <div class="overview-card-header">
                                            <h3 class="overview-card-header__title">
                                                <span class="overview-card-header__icon">⚠️</span>
                                                Dikkat Gerektiren
                                                <?php if (!empty($pendingTasks)): ?>
                                                    <span class="overview-card-header__badge"><?php echo count($pendingTasks); ?></span>
                                                <?php endif; ?>
                                            </h3>
                                        </div>
                                        <div class="pending-tasks-list">
                                            <?php if (!empty($pendingTasks)): ?>
                                                <?php foreach ($pendingTasks as $task): ?>
                                                    <div class="pending-task pending-task--<?php echo $task['color']; ?>"
                                                        data-goto-panel="<?php echo $task['action']; ?>"
                                                        role="button"
                                                        tabindex="0">
                                                        <div class="pending-task__icon"><?php echo $task['icon']; ?></div>
                                                        <div class="pending-task__content">
                                                            <div class="pending-task__title"><?php echo $task['title']; ?></div>
                                                        </div>
                                                        <div class="pending-task__count"><?php echo $task['count']; ?></div>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <div class="pending-tasks-empty">
                                                    <span class="pending-tasks-empty__icon">✅</span>
                                                    <p>Tüm görevler tamamlandı!</p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- ─────────────────────────────────────────────────────
                                     6. İPUÇLARI BÖLÜMÜ
                                     Kullanıcıya yardımcı bilgiler.
                                     ───────────────────────────────────────────────────── -->
                                <div class="panel-card overview-tips-card">
                                    <div class="overview-tips">
                                        <div class="overview-tips__icon">💡</div>
                                        <div class="overview-tips__content">
                                            <h4 class="overview-tips__title">İpucu</h4>
                                            <p class="overview-tips__text">Arama çubuğuna odaklanmak için <kbd>/</kbd> tuşuna basabilirsiniz. İstatistik kartlarına tıklayarak ilgili bölüme hızlıca geçebilirsiniz.</p>
                                        </div>
                                        <button class="overview-tips__close" aria-label="İpucunu kapat">×</button>
                                    </div>
                                </div>

                            </div><!-- /.overview-grid -->
                        </section>

                        <!-- ═══════════════════════════════════════════════════════
                             PANEL 2: ENVANTER YÖNETİMİ
                             Alt sekmeli yapı: Envanter Listesi ve Envanter Ekle.
                             JavaScript ile alt panel geçişleri yönetilir.
                             ═══════════════════════════════════════════════════════ -->
                        <section class="sliding-panel" data-panel-id="envanter">
                            <div class="panel-card">
                                <!-- ─────────────────────────────────────────────────────
                                     Alt Sekme Başlığı ve Butonları
                                     Envanter yönetimi için iki alt sekme sunar.
                                     ───────────────────────────────────────────────────── -->
                                <div class="sub-panel-header">
                                    <div class="sub-panel-header__info">
                                        <h3 class="sub-panel-header__title">📦 Envanter Yönetimi</h3>
                                        <p class="sub-panel-header__desc">Envanter kayıtlarını görüntüleyin, düzenleyin ve yeni kayıtlar ekleyin.</p>
                                    </div>
                                    <div class="sub-panel-tabs" data-sub-panel-group="inventory">
                                        <button class="sub-panel-tab is-active" data-sub-panel="inventory-list">
                                            <span class="sub-panel-tab__icon">📋</span>
                                            <span class="sub-panel-tab__label">Envanter Listesi</span>
                                        </button>
                                        <button class="sub-panel-tab" data-sub-panel="inventory-add">
                                            <span class="sub-panel-tab__icon">➕</span>
                                            <span class="sub-panel-tab__label">Envanter Ekle</span>
                                        </button>
                                    </div>
                                </div>

                                <!-- ─────────────────────────────────────────────────────
                                     Alt Panel: Envanter Listesi
                                     Tüm envanter öğelerinin tablo görünümü.
                                     ───────────────────────────────────────────────────── -->
                                <div class="sub-panel is-active" data-sub-panel-id="inventory-list">
                                    <div class="panel-toolbar panel-toolbar--compact">
                                        <div class="panel-toolbar__meta">
                                            <small>Toplam <?php echo count($envanterItems); ?> kayıt (son 500)</small>
                                        </div>
                                    </div>

                                    <?php if (empty($envanterItems)): ?>
                                        <div class="panel-empty">
                                            <div class="panel-empty__icon">📦</div>
                                            <div class="panel-empty__text">Henüz envanter kaydı bulunmuyor.</div>
                                            <button class="btn btn--primary" data-sub-panel-goto="inventory-add" data-sub-panel-group="inventory">İlk Envanteri Ekle</button>
                                        </div>
                                    <?php else: ?>
                                        <div class="table-scroll">
                                            <table class="data-table">
                                                <thead>
                                                    <tr>
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
                                                    <?php foreach ($envanterItems as $it): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($it['id']); ?></td>
                                                            <td><?php echo htmlspecialchars($it['firma_adi'] ?? '—'); ?></td>
                                                            <td><?php echo htmlspecialchars($it['urun_adi'] ?? '—'); ?></td>
                                                            <td><?php echo htmlspecialchars($it['marka'] ?? '—'); ?></td>
                                                            <td><?php echo htmlspecialchars($it['seri_no'] ?? '—'); ?></td>
                                                            <td><?php echo htmlspecialchars($it['barkod'] ?? '—'); ?></td>
                                                            <td><?php echo htmlspecialchars($it['demirbas_kodu'] ?? '—'); ?></td>
                                                            <td>
                                                                <span class="data-table__actions">
                                                                    <a class="btn btn--ghost btn--sm" href="<?php echo BASE_PATH; ?>/?route=admin-envanter-edit&id=<?php echo $it['id']; ?>">Düzenle</a>
                                                                    <a class="btn btn--ghost btn--sm btn--danger"
                                                                        href="<?php echo BASE_PATH; ?>/admin/admin_inventory_delete_action.php?id=<?php echo $it['id']; ?>&csrf=<?php echo csrf_token(); ?>"
                                                                        onclick="return confirm('Bu envanteri silmek istediğinizden emin misiniz?');">Sil</a>
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- ─────────────────────────────────────────────────────
                                     Alt Panel: Envanter Ekle Formu
                                     Yeni envanter kayıt formu.
                                     ───────────────────────────────────────────────────── -->
                                <div class="sub-panel" data-sub-panel-id="inventory-add">
                                    <form method="post" action="<?php echo BASE_PATH; ?>/admin/admin_inventory_create_action.php" enctype="multipart/form-data" class="admin-form">
                                        <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">

                                        <div class="form-grid form-grid--two">
                                            <!-- Firma Seçimi -->
                                            <div class="form-field">
                                                <label>Firma <span class="required">*</span></label>
                                                <select name="firma_id" required>
                                                    <option value="">-- Firma seçin --</option>
                                                    <?php foreach ($firms as $f): ?>
                                                        <option value="<?php echo $f['id']; ?>"><?php echo htmlspecialchars($f['firma_adi']); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <!-- Lokasyon Seçimi -->
                                            <div class="form-field">
                                                <label>Lokasyon</label>
                                                <select name="lokasyon_id">
                                                    <option value="">-- Lokasyon seçin --</option>
                                                    <?php foreach ($locations as $l): ?>
                                                        <option value="<?php echo $l['id']; ?>"><?php echo htmlspecialchars($l['lokasyon_adi']); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <!-- Ürün Adı -->
                                            <div class="form-field">
                                                <label>Ürün Adı <span class="required">*</span></label>
                                                <input type="text" name="urun_adi" required placeholder="Örn: Dell Latitude 5520">
                                            </div>

                                            <!-- Marka -->
                                            <div class="form-field">
                                                <label>Marka</label>
                                                <input type="text" name="marka" placeholder="Örn: Dell, HP, Lenovo">
                                            </div>

                                            <!-- Seri No -->
                                            <div class="form-field">
                                                <label>Seri No</label>
                                                <input type="text" name="seri_no" placeholder="Cihaz seri numarası">
                                            </div>

                                            <!-- Garanti Süresi -->
                                            <div class="form-field">
                                                <label>Garanti Süresi</label>
                                                <input type="text" name="garanti_suresi" placeholder="Örn: 2 yıl">
                                            </div>

                                            <!-- Ürün Açıklaması -->
                                            <div class="form-field form-grid__wide">
                                                <label>Ürün Açıklaması</label>
                                                <textarea name="urun_aciklama" rows="3" placeholder="Ürün hakkında detaylı bilgi..."></textarea>
                                            </div>

                                            <!-- Takip Tipi -->
                                            <div class="form-field">
                                                <label>Takip Tipi</label>
                                                <select name="takip_tipi">
                                                    <option value="demirbas">Demirbaş</option>
                                                    <option value="stok">Stok</option>
                                                    <option value="tuketim">Tüketim</option>
                                                </select>
                                            </div>

                                            <!-- Birim -->
                                            <div class="form-field">
                                                <label>Birim</label>
                                                <input type="text" name="birim" placeholder="adet" value="adet">
                                            </div>

                                            <!-- Barkod -->
                                            <div class="form-field">
                                                <label>Barkod</label>
                                                <input type="text" name="barkod" placeholder="Barkod numarası">
                                            </div>

                                            <!-- Demirbaş Kodu -->
                                            <div class="form-field">
                                                <label>Demirbaş Kodu</label>
                                                <input type="text" name="demirbas_kodu" placeholder="Demirbaş kodu">
                                            </div>

                                            <!-- Kutu İçeriği -->
                                            <div class="form-field form-grid__wide">
                                                <label>Kutu İçeriği</label>
                                                <textarea name="kutu_icerik" placeholder="kablo, adaptör, kullanım kılavuzu" rows="2"></textarea>
                                            </div>

                                            <!-- Dosya Yükleme -->
                                            <div class="form-field form-grid__wide">
                                                <label>Dosyalar / Fotoğraflar</label>
                                                <input type="file" name="files[]" multiple accept="image/*,application/pdf">
                                                <small class="form-help">Fotoğraf ve PDF yükleyebilirsiniz. Çoklu seçim yapabilirsiniz.</small>
                                            </div>
                                        </div>

                                        <div class="form-actions">
                                            <button type="button" class="btn btn--ghost" data-sub-panel-goto="inventory-list" data-sub-panel-group="inventory">İptal</button>
                                            <button type="submit" class="btn btn--primary">💾 Kaydet</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </section>

                        <!-- ═══════════════════════════════════════════════════════
                             PANEL 4: KULLANICILAR
                             Alt sekmeli yapı: Kullanıcı Listesi ve Kullanıcı Ekle.
                        <!-- ═══════════════════════════════════════════════════════
                             PANEL 3: KULLANICILAR
                             Alt sekmeli yapı: Kullanıcı Listesi ve Kullanıcı Ekle.
                             JavaScript ile alt panel geçişleri yönetilir.
                             ═══════════════════════════════════════════════════════ -->
                        <section class="sliding-panel" data-panel-id="kullanicilar">
                            <div class="panel-card">
                                <!-- ─────────────────────────────────────────────────────
                                     Alt Sekme Başlığı ve Butonları
                                     Kullanıcı yönetimi için iki alt sekme sunar.
                                     ───────────────────────────────────────────────────── -->
                                <div class="sub-panel-header">
                                    <div class="sub-panel-header__info">
                                        <h3 class="sub-panel-header__title">👥 Kullanıcı Yönetimi</h3>
                                        <p class="sub-panel-header__desc">Sistemdeki kullanıcıları yönetin ve yeni kullanıcılar ekleyin.</p>
                                    </div>
                                    <div class="sub-panel-tabs" data-sub-panel-group="users">
                                        <button class="sub-panel-tab is-active" data-sub-panel="user-list">
                                            <span class="sub-panel-tab__icon">📋</span>
                                            <span class="sub-panel-tab__label">Kullanıcı Listesi</span>
                                        </button>
                                        <button class="sub-panel-tab" data-sub-panel="user-add">
                                            <span class="sub-panel-tab__icon">➕</span>
                                            <span class="sub-panel-tab__label">Kullanıcı Ekle</span>
                                        </button>
                                    </div>
                                </div>

                                <!-- ─────────────────────────────────────────────────────
                                     Alt Panel: Kullanıcı Listesi
                                     Mevcut kullanıcıların tablo görünümü.
                                     ───────────────────────────────────────────────────── -->
                                <div class="sub-panel is-active" data-sub-panel-id="user-list">
                                    <div class="panel-toolbar panel-toolbar--compact">
                                        <div class="panel-toolbar__meta">
                                            <small>Toplam <?php echo count($users); ?> kullanıcı</small>
                                        </div>
                                    </div>

                                    <?php if (empty($users)): ?>
                                        <div class="panel-empty">
                                            <div class="panel-empty__icon">👥</div>
                                            <div class="panel-empty__text">Henüz kullanıcı bulunmuyor.</div>
                                            <button class="btn btn--primary" data-sub-panel-goto="user-add" data-sub-panel-group="users">İlk Kullanıcıyı Ekle</button>
                                        </div>
                                    <?php else: ?>
                                        <div class="table-scroll">
                                            <table class="data-table">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Ad Soyad</th>
                                                        <th>E-posta</th>
                                                        <th>Firma</th>
                                                        <th>Rol</th>
                                                        <th>Durum</th>
                                                        <th>İşlemler</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($users as $u): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($u['id']); ?></td>
                                                            <td><?php echo htmlspecialchars(trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''))); ?></td>
                                                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                                                            <td><?php echo htmlspecialchars($u['firma_adi'] ?? '—'); ?></td>
                                                            <td><span class="role-badge role-badge--<?php echo $u['role']; ?>"><?php echo strtoupper($u['role']); ?></span></td>
                                                            <td>
                                                                <?php if ($u['is_active']): ?>
                                                                    <span class="status-badge status-badge--active">Aktif</span>
                                                                <?php else: ?>
                                                                    <span class="status-badge status-badge--inactive">Pasif</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <span class="data-table__actions">
                                                                    <a class="btn btn--ghost btn--sm" href="<?php echo BASE_PATH; ?>/?route=admin-user-edit&id=<?php echo $u['id']; ?>">Düzenle</a>
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- ─────────────────────────────────────────────────────
                                     Alt Panel: Kullanıcı Ekle Formu
                                     Yeni kullanıcı kayıt formu.
                                     ───────────────────────────────────────────────────── -->
                                <div class="sub-panel" data-sub-panel-id="user-add">
                                    <form method="post" action="<?php echo BASE_PATH; ?>/auth/auth_register_action.php" class="admin-form">
                                        <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">
                                        <input type="hidden" name="admin_create" value="1">

                                        <div class="form-grid form-grid--two">
                                            <!-- Ad -->
                                            <div class="form-field">
                                                <label>Ad <span class="required">*</span></label>
                                                <input type="text" name="first_name" required placeholder="Kullanıcının adı">
                                            </div>

                                            <!-- Soyad -->
                                            <div class="form-field">
                                                <label>Soyad <span class="required">*</span></label>
                                                <input type="text" name="last_name" required placeholder="Kullanıcının soyadı">
                                            </div>

                                            <!-- E-posta -->
                                            <div class="form-field">
                                                <label>E-posta <span class="required">*</span></label>
                                                <input type="email" name="email" required placeholder="ornek@email.com">
                                            </div>

                                            <!-- Telefon -->
                                            <div class="form-field">
                                                <label>Telefon</label>
                                                <input type="tel" name="phone" placeholder="+90 5XX XXX XX XX">
                                            </div>

                                            <!-- Şifre -->
                                            <div class="form-field">
                                                <label>Şifre <span class="required">*</span></label>
                                                <input type="password" name="password" required minlength="6" placeholder="En az 6 karakter">
                                            </div>

                                            <!-- Şifre Onay -->
                                            <div class="form-field">
                                                <label>Şifre Onay <span class="required">*</span></label>
                                                <input type="password" name="password_confirm" required minlength="6" placeholder="Şifreyi tekrar girin">
                                            </div>

                                            <!-- Firma Seçimi -->
                                            <div class="form-field">
                                                <label>Firma</label>
                                                <select name="firma_id">
                                                    <option value="">-- Firma seçin --</option>
                                                    <?php foreach ($firms as $f): ?>
                                                        <option value="<?php echo $f['id']; ?>"><?php echo htmlspecialchars($f['firma_adi']); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <!-- Rol Seçimi -->
                                            <div class="form-field">
                                                <label>Rol <span class="required">*</span></label>
                                                <select name="role" required>
                                                    <option value="client">Client (Müşteri)</option>
                                                    <option value="admin">Admin (Yönetici)</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-actions">
                                            <button type="button" class="btn btn--ghost" data-sub-panel-goto="user-list" data-sub-panel-group="users">İptal</button>
                                            <button type="submit" class="btn btn--primary">💾 Kullanıcı Kaydet</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </section>

                        <!-- ═══════════════════════════════════════════════════════
                             PANEL 4: FİRMALAR
                             Alt sekmeli yapı: Firma Listesi ve Firma Ekle.
                             Lokasyon yönetimi de bu panelde yapılacak.
                             ═══════════════════════════════════════════════════════ -->
                        <section class="sliding-panel" data-panel-id="firmalar">
                            <div class="panel-card">
                                <!-- ─────────────────────────────────────────────────────
                                     Alt Sekme Başlığı ve Butonları
                                     Firma yönetimi için iki alt sekme sunar.
                                     ───────────────────────────────────────────────────── -->
                                <div class="sub-panel-header">
                                    <div class="sub-panel-header__info">
                                        <h3 class="sub-panel-header__title">🏢 Firma Yönetimi</h3>
                                        <p class="sub-panel-header__desc">Firmaları ve lokasyonlarını yönetin.</p>
                                    </div>
                                    <div class="sub-panel-tabs" data-sub-panel-group="companies">
                                        <button class="sub-panel-tab is-active" data-sub-panel="company-list">
                                            <span class="sub-panel-tab__icon">📋</span>
                                            <span class="sub-panel-tab__label">Firma Listesi</span>
                                        </button>
                                        <button class="sub-panel-tab" data-sub-panel="company-add">
                                            <span class="sub-panel-tab__icon">➕</span>
                                            <span class="sub-panel-tab__label">Firma Ekle</span>
                                        </button>
                                    </div>
                                </div>

                                <!-- ─────────────────────────────────────────────────────
                                     Alt Panel: Firma Listesi
                                     Mevcut firmaların kart görünümü ve tablosu.
                                     ───────────────────────────────────────────────────── -->
                                <div class="sub-panel is-active" data-sub-panel-id="company-list">
                                    <div class="panel-toolbar panel-toolbar--compact">
                                        <div class="panel-toolbar__meta">
                                            <small>Toplam <?php echo count($firms); ?> firma</small>
                                        </div>
                                    </div>

                                    <?php if (empty($firms)): ?>
                                        <div class="panel-empty">
                                            <div class="panel-empty__icon">🏢</div>
                                            <div class="panel-empty__text">Henüz firma bulunmuyor.</div>
                                            <button class="btn btn--primary" data-sub-panel-goto="company-add" data-sub-panel-group="companies">İlk Firmayı Ekle</button>
                                        </div>
                                    <?php else: ?>
                                        <div class="nav-cards-grid">
                                            <?php foreach ($firms as $f): ?>
                                                <div class="nav-card nav-card--compact">
                                                    <span class="nav-card__icon">🏢</span>
                                                    <div class="nav-card__content">
                                                        <span class="nav-card__label"><?php echo htmlspecialchars($f['firma_adi']); ?></span>
                                                        <span class="nav-card__desc">ID: <?php echo $f['id']; ?></span>
                                                    </div>
                                                    <div class="nav-card__actions">
                                                        <button class="btn btn--ghost btn--sm">Düzenle</button>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- ─────────────────────────────────────────────────────
                                     Alt Panel: Firma Ekle Formu
                                     Yeni firma kayıt formu.
                                     ───────────────────────────────────────────────────── -->
                                <div class="sub-panel" data-sub-panel-id="company-add">
                                    <form method="post" action="<?php echo BASE_PATH; ?>/admin/admin_company_create_action.php" class="admin-form">
                                        <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">

                                        <div class="form-grid form-grid--two">
                                            <!-- Firma Adı -->
                                            <div class="form-field form-grid__wide">
                                                <label>Firma Adı <span class="required">*</span></label>
                                                <input type="text" name="firma_adi" required placeholder="Firma veya şirket adı">
                                            </div>

                                            <!-- Vergi No -->
                                            <div class="form-field">
                                                <label>Vergi Numarası</label>
                                                <input type="text" name="vergi_no" placeholder="Vergi numarası">
                                            </div>

                                            <!-- Vergi Dairesi -->
                                            <div class="form-field">
                                                <label>Vergi Dairesi</label>
                                                <input type="text" name="vergi_dairesi" placeholder="Vergi dairesi adı">
                                            </div>

                                            <!-- Telefon -->
                                            <div class="form-field">
                                                <label>Telefon</label>
                                                <input type="tel" name="telefon" placeholder="+90 XXX XXX XX XX">
                                            </div>

                                            <!-- E-posta -->
                                            <div class="form-field">
                                                <label>E-posta</label>
                                                <input type="email" name="email" placeholder="firma@email.com">
                                            </div>

                                            <!-- Adres -->
                                            <div class="form-field form-grid__wide">
                                                <label>Adres</label>
                                                <textarea name="adres" rows="2" placeholder="Firma adresi"></textarea>
                                            </div>

                                            <!-- Notlar -->
                                            <div class="form-field form-grid__wide">
                                                <label>Notlar</label>
                                                <textarea name="notlar" rows="2" placeholder="Ek notlar..."></textarea>
                                            </div>
                                        </div>

                                        <div class="form-actions">
                                            <button type="button" class="btn btn--ghost" data-sub-panel-goto="company-list" data-sub-panel-group="companies">İptal</button>
                                            <button type="submit" class="btn btn--primary">💾 Firma Kaydet</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </section>

                        <!-- ═══════════════════════════════════════════════════════
                             PANEL 5: KATEGORİLER
                             Alt sekmeli yapı: Kategori Listesi ve Kategori Ekle.
                             Envanter kategorileri bu panelde yönetilir.
                             ═══════════════════════════════════════════════════════ -->
                        <section class="sliding-panel" data-panel-id="kategoriler">
                            <div class="panel-card">
                                <!-- ─────────────────────────────────────────────────────
                                     Alt Sekme Başlığı ve Butonları
                                     Kategori yönetimi için iki alt sekme sunar.
                                     ───────────────────────────────────────────────────── -->
                                <div class="sub-panel-header">
                                    <div class="sub-panel-header__info">
                                        <h3 class="sub-panel-header__title">🏷️ Kategori Yönetimi</h3>
                                        <p class="sub-panel-header__desc">Envanter kategorilerini düzenleyin ve yeni kategoriler ekleyin.</p>
                                    </div>
                                    <div class="sub-panel-tabs" data-sub-panel-group="categories">
                                        <button class="sub-panel-tab is-active" data-sub-panel="category-list">
                                            <span class="sub-panel-tab__icon">📋</span>
                                            <span class="sub-panel-tab__label">Kategori Listesi</span>
                                        </button>
                                        <button class="sub-panel-tab" data-sub-panel="category-add">
                                            <span class="sub-panel-tab__icon">➕</span>
                                            <span class="sub-panel-tab__label">Kategori Ekle</span>
                                        </button>
                                    </div>
                                </div>

                                <!-- ─────────────────────────────────────────────────────
                                     Alt Panel: Kategori Listesi
                                     Mevcut kategorilerin kart/tablo görünümü.
                                     ───────────────────────────────────────────────────── -->
                                <div class="sub-panel is-active" data-sub-panel-id="category-list">
                                    <div class="panel-toolbar panel-toolbar--compact">
                                        <div class="panel-toolbar__meta">
                                            <small>Kategoriler</small>
                                        </div>
                                    </div>

                                    <div class="panel-empty">
                                        <div class="panel-empty__icon">🏷️</div>
                                        <div class="panel-empty__text">Henüz kategori bulunmuyor.</div>
                                        <button class="btn btn--primary" data-sub-panel-goto="category-add" data-sub-panel-group="categories">İlk Kategoriyi Ekle</button>
                                    </div>
                                </div>

                                <!-- ─────────────────────────────────────────────────────
                                     Alt Panel: Kategori Ekle Formu
                                     Yeni kategori kayıt formu.
                                     ───────────────────────────────────────────────────── -->
                                <div class="sub-panel" data-sub-panel-id="category-add">
                                    <form method="post" action="<?php echo BASE_PATH; ?>/admin/admin_category_create_action.php" class="admin-form">
                                        <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">

                                        <div class="form-grid form-grid--two">
                                            <!-- Kategori Adı -->
                                            <div class="form-field">
                                                <label>Kategori Adı <span class="required">*</span></label>
                                                <input type="text" name="kategori_adi" required placeholder="Örn: Bilgisayar, Yazıcı, Ağ Cihazı">
                                            </div>

                                            <!-- Üst Kategori -->
                                            <div class="form-field">
                                                <label>Üst Kategori</label>
                                                <select name="parent_id">
                                                    <option value="">-- Ana Kategori --</option>
                                                    <!-- Kategoriler veritabanından çekilecek -->
                                                </select>
                                                <small class="form-help">Boş bırakılırsa ana kategori olarak eklenir.</small>
                                            </div>

                                            <!-- İkon -->
                                            <div class="form-field">
                                                <label>İkon (Emoji)</label>
                                                <input type="text" name="icon" placeholder="📦" maxlength="4">
                                                <small class="form-help">Kategoriyi temsil eden bir emoji seçin.</small>
                                            </div>

                                            <!-- Renk -->
                                            <div class="form-field">
                                                <label>Renk</label>
                                                <input type="color" name="color" value="#0ea5e9">
                                            </div>

                                            <!-- Açıklama -->
                                            <div class="form-field form-grid__wide">
                                                <label>Açıklama</label>
                                                <textarea name="aciklama" rows="2" placeholder="Kategori hakkında kısa açıklama..."></textarea>
                                            </div>
                                        </div>

                                        <div class="form-actions">
                                            <button type="button" class="btn btn--ghost" data-sub-panel-goto="category-list" data-sub-panel-group="categories">İptal</button>
                                            <button type="submit" class="btn btn--primary">💾 Kategori Kaydet</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </section>

                    </div>
                </div>
                <!-- Kayan Panel Kapsayıcısı Sonu -->

            </div>
        </div>
    </div>
</main>