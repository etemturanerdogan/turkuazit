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
