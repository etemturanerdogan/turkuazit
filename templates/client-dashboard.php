<?php
require_login(); // admin de girer, client de
?>

<main class="section">
    <div class="container">
        <h1 class="section__title">Müşteri Paneli</h1>
        <p class="section__subtitle">
            Burada ileride arıza kayıtları, cihaz envanteri ve modül durumlarını göstereceğiz.
        </p>

        <div style="margin-top:1.5rem;" class="card">
            <div class="card__title">
                Merhaba, <?php echo htmlspecialchars(current_user()['full_name']); ?>
            </div>
            <div class="card__subtitle">
                <?php if (!empty(current_user()['firm_name'])): ?>
                    Firma: <?php echo htmlspecialchars(current_user()['firm_name']); ?>
                <?php else: ?>
                    Firma bilgisi daha sonra eklenecektir.
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>
