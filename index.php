<?php
// index.php

// route al
$route = isset($_GET['route']) ? $_GET['route'] : '';
$route = trim($route, "/");
if ($route === '') {
    $route = 'home';
}
$route = preg_replace('~[^a-zA-Z0-9_-]~', '', $route);

// route → template eşlemesi
$map = [
    'home'             => ['template' => 'page_public_home',             'title' => 'Ana Sayfa'],
    'moduller'         => ['template' => 'page_modules',          'title' => 'Modüller'],
    'iletisim'         => ['template' => 'page_contact',          'title' => 'İletişim'],
    'login'            => ['template' => 'page_auth_login',            'title' => 'Giriş Yap'],
    'register'         => ['template' => 'page_auth_register',         'title' => 'Kayıt Ol'],
    'admin-dashboard'  => ['template' => 'page_admin_dashboard',  'title' => 'Yönetim Paneli'],
    'admin-envanter-ekle' => ['template' => 'page_admin_inventory_create', 'title' => 'Envanter Ekle'],
    'admin-envanter-liste' => ['template' => 'page_admin_inventory_list', 'title' => 'Envanter Listesi'],
    'admin-envanter-edit' => ['template' => 'page_admin_inventory_edit', 'title' => 'Envanter Düzenle'],
    'admin-users' => ['template' => 'page_admin_user_list', 'title' => 'Kullanıcılar'],
    'admin-user-edit' => ['template' => 'page_admin_user_edit', 'title' => 'Kullanıcı Düzenle'],
    'client-dashboard' => ['template' => 'page_client_dashboard', 'title' => 'Müşteri Paneli'],
    // Client panel alt sayfaları
    'client-envanter'  => ['template' => 'page_client_inventory_list', 'title' => 'Envanterim'],
    'client-zimmet'    => ['template' => 'page_client_asset_assignment_list',  'title' => 'Zimmetlerim'],
    'profile'          => ['template' => 'page_profile',          'title' => 'Hesap / Firma Bilgileri'],
];

// geçerli mi?
if (isset($map[$route])) {
    $view      = $map[$route]['template'];
    $pageTitle = 'TurkuazIT – ' . $map[$route]['title'];
} else {
    $view      = null;
    $pageTitle = 'TurkuazIT – Sayfa Bulunamadı';
}

// ÜST BAR
require __DIR__ . '/partials/partial_header.php';

// İÇERİK
if ($view !== null) {
    $tpl = __DIR__ . '/templates/' . $view . '.php';
    if (is_file($tpl)) {
        require $tpl;
    } else {
        http_response_code(500);
        echo '<main class="section"><div class="container"><h1>Şablon Eksik</h1><p>' . $view . '.php bulunamadı.</p></div></main>';
    }
} else {
    http_response_code(404);
    echo '<main class="section"><div class="container"><h1>404</h1><p>Sayfa bulunamadı.</p></div></main>';
}

// ALT BAR
require __DIR__ . '/partials/partial_footer.php';
