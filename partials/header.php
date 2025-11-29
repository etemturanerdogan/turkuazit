<?php
require_once __DIR__ . '/../config.php';

// $pageTitle index.php'den geliyor, yoksa default verelim:
if (!isset($pageTitle)) {
    $pageTitle = 'TurkuazIT – Modüler BT Hizmetleri';
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="description" content="TurkuazIT; uzaktan destek, ağ & güvenlik, envanter yönetimi ve saha operasyonları için modüler BT hizmetleri sunar.">

    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/style.css">
    <script defer src="<?php echo BASE_PATH; ?>/assets/js/main.js"></script>
</head>
<body>

<header class="site-header">
    <div class="container site-header__inner">
        <a href="<?php echo BASE_PATH; ?>/?route=home" class="site-logo">
            <span class="site-logo__mark"></span>
            <span>TURKUAZIT</span>
        </a>

        <nav class="nav">
            <a href="<?php echo BASE_PATH; ?>/?route=home" class="nav__link">Ana Sayfa</a>
            <a href="<?php echo BASE_PATH; ?>/?route=moduller" class="nav__link">Modüller</a>
            <a href="<?php echo BASE_PATH; ?>/?route=iletisim" class="nav__link">İletişim</a>
        </nav>

        <div style="display:flex; gap:0.5rem; align-items:center;">
            <?php if (is_logged_in()): ?>
                <span style="font-size:0.8rem; color:#9CA3AF;">
                    <?php echo htmlspecialchars(current_user()['full_name']); ?>
                    <?php if (is_admin()) echo ' · Admin'; ?>
                </span>
                <a href="<?php echo BASE_PATH; ?>/auth/logout.php" class="btn btn--ghost">Çıkış</a>
            <?php else: ?>
                <a href="<?php echo BASE_PATH; ?>/?route=login" class="btn btn--ghost">Giriş Yap</a>
                <a href="<?php echo BASE_PATH; ?>/?route=iletisim" class="btn btn--primary">Teklif Al</a>
            <?php endif; ?>
        </div>
    </div>
</header>