<?php
// ═══════════════════════════════════════════════════════════════════════════════
// DOSYA: partial_header.php
// AÇIKLAMA: Site Üst Kısım (Header) Şablonu
// ═══════════════════════════════════════════════════════════════════════════════
// Bu dosya tüm sayfalarda görüntülenen üst navigasyon çubuğunu içerir.
// index.php tarafından her sayfa yüklemesinde dahil edilir.
// 
// İÇERİK:
// 1. HTML Başlık Bölümü (DOCTYPE, head, meta, CSS, JS)
// 2. Site Header - Logo ve Marka
// 3. Ana Navigasyon - Sayfa linkleri
// 4. Kullanıcı Bölümü:
//    - Giriş yapmamış: Giriş/Kayıt butonları
//    - Giriş yapmış: Avatar + Dropdown menü
// 
// DROPDOWN MENÜ İÇERİĞİ:
// - Kullanıcı bilgileri (ad, e-posta, admin badge)
// - Dashboard linki (role göre admin veya client)
// - Hesap ayarları linki (client için)
// - Çıkış butonu
// ═══════════════════════════════════════════════════════════════════════════════
require_once __DIR__ . '/../app_config.php';

// ─────────────────────────────────────────────────────────────────────────────
// SAYFA BAŞLIĞI KONTROLÜ
// ─────────────────────────────────────────────────────────────────────────────
// $pageTitle değişkeni index.php'den gelir. Yoksa varsayılan başlık kullanılır.
if (!isset($pageTitle)) {
    $pageTitle = 'TurkuazIT – Modüler BT Hizmetleri';
}
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <!-- ═══════════════════════════════════════════════════════════════════════
         META BİLGİLERİ VE KAYNAKLAR
         ═══════════════════════════════════════════════════════════════════════ -->
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="TurkuazIT; uzaktan destek, ağ & güvenlik, envanter yönetimi ve saha operasyonları için modüler BT hizmetleri sunar.">

    <!-- CSS Dosyaları -->
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/style.css">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/assets/css/turkuazit-ui.css">
    
    <!-- JavaScript (defer: DOM hazır olunca çalışır) -->
    <script defer src="<?php echo BASE_PATH; ?>/assets/js/main.js"></script>
</head>

<body>
    <!-- ═══════════════════════════════════════════════════════════════════════
         SİTE HEADER (Üst Navigasyon Çubuğu)
         Sabit pozisyonlu, yarı saydam arka planlı, blur efektli.
         ═══════════════════════════════════════════════════════════════════════ -->
    <header class="site-header">
        <div class="container site-header__inner">
            
            <!-- ─────────────────────────────────────────────────────────────
                 LOGO VE MARKA
                 Ana sayfaya link verir.
                 ───────────────────────────────────────────────────────────── -->
            <a href="<?php echo BASE_PATH; ?>/?route=home" class="site-logo">
                <span class="site-logo__mark"></span>
                <span>TURKUAZIT</span>
            </a>

            <!-- ─────────────────────────────────────────────────────────────
                 ANA NAVİGASYON LİNKLERİ
                 Tüm kullanıcılar için görünür public sayfalar.
                 ───────────────────────────────────────────────────────────── -->
            <nav class="nav">
                <a href="<?php echo BASE_PATH; ?>/?route=home" class="nav__link">Ana Sayfa</a>
                <a href="<?php echo BASE_PATH; ?>/?route=moduller" class="nav__link">Modüller</a>
                <a href="<?php echo BASE_PATH; ?>/?route=iletisim" class="nav__link">İletişim</a>
            </nav>

            <!-- ─────────────────────────────────────────────────────────────
                 KULLANICI BÖLÜMÜ
                 Giriş durumuna göre farklı içerik gösterilir.
                 ───────────────────────────────────────────────────────────── -->
            <div class="site-header__actions">
                <?php if (is_logged_in()): ?>
                    <!-- ═══════════════════════════════════════════════════════
                         GİRİŞ YAPMIŞ KULLANICI
                         Dashboard butonu + Avatar dropdown menüsü
                         ═══════════════════════════════════════════════════════ -->
                    
                    <!-- Dashboard Butonu (role göre farklı) -->
                    <?php if (is_admin()): ?>
                        <a href="<?php echo BASE_PATH; ?>/?route=admin-dashboard" class="btn btn--primary">
                            <span>🛡️</span> Yönetim Paneli
                        </a>
                    <?php else: ?>
                        <a href="<?php echo BASE_PATH; ?>/?route=client-dashboard" class="btn btn--primary">
                            <span>👤</span> Hesabım
                        </a>
                    <?php endif; ?>
                    
                    <!-- Kullanıcı Dropdown Menüsü -->
                    <div class="site-header__user-menu">
                        <!-- Dropdown Tetikleyici Buton -->
                        <button class="site-header__user-btn" id="userMenuBtn">
                            <span class="site-header__user-avatar">
                                <?php echo strtoupper(substr(current_user()['first_name'], 0, 1)); ?>
                            </span>
                            <span class="site-header__user-name"><?php echo htmlspecialchars(current_user()['first_name']); ?></span>
                            <span class="site-header__user-arrow">▼</span>
                        </button>
                        
                        <!-- Dropdown Menü İçeriği -->
                        <div class="site-header__dropdown" id="userDropdown">
                            <!-- Kullanıcı Bilgileri -->
                            <div class="site-header__dropdown-header">
                                <strong><?php echo htmlspecialchars(current_user()['full_name']); ?></strong>
                                <span><?php echo htmlspecialchars(current_user()['email']); ?></span>
                                <?php if (is_admin()): ?>
                                    <span class="site-header__dropdown-badge">Admin</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="site-header__dropdown-divider"></div>
                            
                            <!-- Menü Öğeleri (role göre farklı) -->
                            <?php if (is_admin()): ?>
                                <a href="<?php echo BASE_PATH; ?>/?route=admin-dashboard" class="site-header__dropdown-item">
                                    <span>🛡️</span> Yönetim Paneli
                                </a>
                            <?php else: ?>
                                <a href="<?php echo BASE_PATH; ?>/?route=client-dashboard" class="site-header__dropdown-item">
                                    <span>📊</span> Dashboard
                                </a>
                                <a href="<?php echo BASE_PATH; ?>/?route=client-dashboard#account" class="site-header__dropdown-item">
                                    <span>⚙️</span> Hesap Ayarları
                                </a>
                            <?php endif; ?>
                            
                            <div class="site-header__dropdown-divider"></div>
                            
                            <!-- Çıkış Butonu -->
                            <a href="<?php echo BASE_PATH; ?>/auth/auth_logout_action.php" class="site-header__dropdown-item site-header__dropdown-item--danger">
                                <span>🚪</span> Çıkış Yap
                            </a>
                        </div>
                    </div>
                    
                <?php else: ?>
                    <!-- ═══════════════════════════════════════════════════════
                         GİRİŞ YAPMAMIŞ ZİYARETÇİ
                         Giriş, Kayıt ve Teklif Al butonları
                         ═══════════════════════════════════════════════════════ -->
                    <a href="<?php echo BASE_PATH; ?>/?route=login" class="btn btn--ghost">Giriş Yap</a>
                    <a href="<?php echo BASE_PATH; ?>/?route=register" class="btn btn--ghost">Kayıt Ol</a>
                    <a href="<?php echo BASE_PATH; ?>/?route=iletisim" class="btn btn--primary">Teklif Al</a>
                <?php endif; ?>
            </div>
        </div>
    </header>
