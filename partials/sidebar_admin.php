<?php
// partials/sidebar_admin.php — Yönetim paneli sol menüsü
require_once __DIR__ . '/../config.php';
require_admin(); // Bu menü sadece admin için görünür
?>

<aside class="sidebar" style="width:240px; padding:16px; background:rgba(10,14,20,0.45); border-radius:8px;">
    <div style="margin-bottom:1rem; font-weight:700;">Yönetim</div>
    <nav style="display:flex; flex-direction:column; gap:6px;">
        <a href="<?php echo BASE_PATH; ?>/?route=admin-dashboard" class="nav__link">Dashboard</a>
        <a href="<?php echo BASE_PATH; ?>/?route=admin-envanter-liste" class="nav__link">Envanter</a>
        <a href="<?php echo BASE_PATH; ?>/?route=admin-envanter-ekle" class="nav__link">Envanter Ekle</a>
        <a href="<?php echo BASE_PATH; ?>/?route=admin-firmalar" class="nav__link">Firmalar / Lokasyonlar</a>
        <a href="<?php echo BASE_PATH; ?>/?route=admin-kategoriler" class="nav__link">Kategoriler</a>
        <a href="<?php echo BASE_PATH; ?>/?route=admin-users" class="nav__link">Kullanıcılar / Roller</a>
    </nav>
</aside>
