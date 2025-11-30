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
    'home'             => ['template' => 'home',             'title' => 'Ana Sayfa'],
    'moduller'         => ['template' => 'modules',          'title' => 'Modüller'],
    'iletisim'         => ['template' => 'contact',          'title' => 'İletişim'],
    'login'            => ['template' => 'page-login',            'title' => 'Giriş Yap'],
    'register'         => ['template' => 'page-register',         'title' => 'Kayıt Ol'],
    'admin-dashboard'  => ['template' => 'admin-dashboard',  'title' => 'Yönetim Paneli'],
    'client-dashboard' => ['template' => 'client-dashboard', 'title' => 'Müşteri Paneli'],
    'profile'          => ['template' => 'profile',          'title' => 'Hesap / Firma Bilgileri'],
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
require __DIR__ . '/partials/header.php';

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
require __DIR__ . '/partials/footer.php';
