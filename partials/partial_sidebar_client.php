<?php
// partials/sidebar_client.php
// Müşteri paneli için sol menü (kendi envanteri, zimmetler, profil, talepler...)
require_once __DIR__ . '/../app_config.php';
?>

<aside class="sidebar" style="width:240px; padding:16px; background:rgba(10,14,20,0.45); border-radius:8px;">
    <div style="margin-bottom:1rem; font-weight:700;">Hesabım</div>
    <nav style="display:flex; flex-direction:column; gap:6px;">
        <!-- Not: linklerin tek tek route parametreleri ile eşleşmesine dikkat et. -->
        <a href="<?php echo BASE_PATH; ?>/?route=client-dashboard" class="nav__link">Gösterge Paneli</a>
        <a href="<?php echo BASE_PATH; ?>/?route=client-envanter" class="nav__link">Envanterim</a>
        <a href="<?php echo BASE_PATH; ?>/?route=client-zimmet" class="nav__link">Zimmetlerim</a>
        <a href="<?php echo BASE_PATH; ?>/?route=profile" class="nav__link">Firma / Adres Bilgileri</a>
        <a href="#" class="nav__link">Siparişler / Kargo</a>
        <a href="#" class="nav__link">Destek Talepleri</a>
    </nav>
</aside>
