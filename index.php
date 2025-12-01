<?php
// ═══════════════════════════════════════════════════════════════════════════════
// DOSYA: index.php
// AÇIKLAMA: TurkuazIT Ana Yönlendirici (Router)
// ═══════════════════════════════════════════════════════════════════════════════
// Bu dosya tüm HTTP isteklerini karşılar ve uygun şablona yönlendirir.
// Tek giriş noktası (Single Entry Point) mimarisi kullanılır.
// 
// ÇALIŞMA MANTAĞI:
// 1. URL'den 'route' parametresi alınır (?route=login gibi)
// 2. Route güvenlik kontrolünden geçirilir (sadece alfanumerik + tire/alt çizgi)
// 3. Route haritasından ilgili şablon ve başlık bulunur
// 4. Header, içerik ve footer sırasıyla yüklenir
// 
// ROUTE TİPLERİ:
// - Public: home, moduller, iletisim (herkes erişebilir)
// - Auth: login, register (giriş yapmamış kullanıcılar için)
// - Admin: admin-dashboard (sadece yöneticiler)
// - Client: client-dashboard (giriş yapmış müşteriler)
// ═══════════════════════════════════════════════════════════════════════════════

// ─────────────────────────────────────────────────────────────────────────────
// ROUTE PARAMETRESİNİ AL VE TEMİZLE
// ─────────────────────────────────────────────────────────────────────────────
// URL'den route değeri alınır, temizlenir ve güvenlik kontrolü yapılır.
// Boş ise varsayılan olarak 'home' atanır.
$route = isset($_GET['route']) ? $_GET['route'] : '';
$route = trim($route, "/");
if ($route === '') {
    $route = 'home';
}
// Güvenlik: Sadece alfanumerik karakterler, tire ve alt çizgi izinli
$route = preg_replace('~[^a-zA-Z0-9_-]~', '', $route);

// ─────────────────────────────────────────────────────────────────────────────
// ROUTE → ŞABLON EŞLEMESİ
// ─────────────────────────────────────────────────────────────────────────────
// Her route için karşılık gelen şablon dosyası ve sayfa başlığı tanımlanır.
// Şablon dosyaları 'templates/' klasöründe bulunur.
// 
// YAPI:
// 'route-adi' => ['template' => 'şablon_dosyası', 'title' => 'Sayfa Başlığı']
// 
// NOT: Admin ve Client dashboard'lar SPA (Tek Sayfa Uygulama) yapısındadır.
// Tüm alt işlemler (envanter, kullanıcılar, profil vb.) tek sayfa içinde
// sekme geçişleri ile yönetilir - sayfa yenilenmesi olmaz.
// ─────────────────────────────────────────────────────────────────────────────
$map = [
    // ───────────────────────────────────────────────────────────────────────
    // PUBLIC SAYFALAR - Herkes erişebilir (giriş gerektirmez)
    // ───────────────────────────────────────────────────────────────────────
    'home'                          => ['template' => 'page_public_home',                'title' => 'Ana Sayfa'],
    'moduller'                      => ['template' => 'page_modules',                    'title' => 'Modüller'],
    'iletisim'                      => ['template' => 'page_contact',                    'title' => 'İletişim'],
    
    // ───────────────────────────────────────────────────────────────────────
    // KİMLİK DOĞRULAMA SAYFALARI - Giriş yapmamış kullanıcılar için
    // ───────────────────────────────────────────────────────────────────────
    'login'                         => ['template' => 'page_auth_login',                 'title' => 'Giriş Yap'],
    'register'                      => ['template' => 'page_auth_register',              'title' => 'Kayıt Ol'],
    
    // ───────────────────────────────────────────────────────────────────────
    // YÖNETİCİ PANELİ - Sadece admin rolündeki kullanıcılar erişebilir
    // SPA yapısı: Genel Bakış, Envanter, Kullanıcılar, Firmalar, Kategoriler
    // ───────────────────────────────────────────────────────────────────────
    'admin-dashboard'               => ['template' => 'page_admin_dashboard',            'title' => 'Yönetim Paneli'],
    
    // ───────────────────────────────────────────────────────────────────────
    // MÜŞTERİ PANELİ - Giriş yapmış tüm kullanıcılar erişebilir
    // SPA yapısı: Genel Bakış, Envanterim, Zimmetlerim, Hesap, Destek
    // ───────────────────────────────────────────────────────────────────────
    'client-dashboard'              => ['template' => 'page_client_dashboard',           'title' => 'Hesabım'],
];

// ─────────────────────────────────────────────────────────────────────────────
// ROUTE GEÇERLİLİK KONTROLÜ
// ─────────────────────────────────────────────────────────────────────────────
// Gelen route haritada var mı kontrol edilir.
// Varsa ilgili şablon ve başlık belirlenir, yoksa 404 hazırlanır.
if (isset($map[$route])) {
    $view      = $map[$route]['template'];
    $pageTitle = 'TurkuazIT – ' . $map[$route]['title'];
} else {
    $view      = null;
    $pageTitle = 'TurkuazIT – Sayfa Bulunamadı';
}

// ─────────────────────────────────────────────────────────────────────────────
// SAYFA YAPISINI OLUŞTUR
// ─────────────────────────────────────────────────────────────────────────────
// Sayfa üç parçadan oluşur: Header, İçerik, Footer
// Bu yapı tüm sayfalarda tutarlı bir görünüm sağlar.

// 1. ÜST KISIM (Header): Logo, navigasyon, kullanıcı menüsü
require __DIR__ . '/partials/partial_header.php';

// 2. ANA İÇERİK: Route'a göre belirlenen şablon dosyası
if ($view !== null) {
    $tpl = __DIR__ . '/templates/' . $view . '.php';
    if (is_file($tpl)) {
        // Şablon dosyası bulundu, yükle
        require $tpl;
    } else {
        // Şablon dosyası bulunamadı - 500 hatası
        http_response_code(500);
        echo '<main class="section"><div class="container">';
        echo '<h1>⚠️ Şablon Hatası</h1>';
        echo '<p>' . htmlspecialchars($view) . '.php dosyası bulunamadı.</p>';
        echo '</div></main>';
    }
} else {
    // Geçersiz route - 404 hatası
    http_response_code(404);
    echo '<main class="section"><div class="container">';
    echo '<h1>404 - Sayfa Bulunamadı</h1>';
    echo '<p>Aradığınız sayfa mevcut değil veya taşınmış olabilir.</p>';
    echo '<a href="' . BASE_PATH . '/?route=home" class="btn btn--primary">Ana Sayfaya Dön</a>';
    echo '</div></main>';
}

// 3. ALT KISIM (Footer): Telif hakkı, linkler
require __DIR__ . '/partials/partial_footer.php';
