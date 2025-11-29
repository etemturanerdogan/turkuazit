<?php
// templates/login.php
$loginError = isset($_GET['error']) && $_GET['error'] === '1';
?>

<main class="section section--tight">
    <div class="container" style="max-width:420px;">
        <span class="badge">
            <span class="badge__dot"></span>
            Giriş Yap
        </span>

        <h1 class="section__title" style="margin-top:.75rem;">TurkuazIT Panel Girişi</h1>
        <p class="section__subtitle">
            Admin veya müşteri hesabınızla giriş yapabilirsiniz. Henüz hesabınız yoksa
            TurkuazIT ekibi ile iletişime geçin.
        </p>

        <div class="card" style="margin-top:1.5rem;">
            <?php if ($loginError): ?>
                <div style="margin-bottom:.75rem; font-size:.8rem; color:#FCA5A5;">
                    E-posta veya şifre hatalı, ya da hesabınız pasif.
                </div>
            <?php endif; ?>

            <form method="post" action="<?php echo BASE_PATH; ?>/auth/login.php" style="display:flex; flex-direction:column; gap:.75rem;">
                <div>
                    <label style="font-size:.8rem; color:#9CA3AF;">E-posta</label>
                    <input
                        type="email"
                        name="email"
                        required
                        class="coming-soon-input"
                        style="width:100%; margin-top:4px;"
                        placeholder="admin@turkuazit.com"
                    >
                </div>

                <div>
                    <label style="font-size:.8rem; color:#9CA3AF;">Şifre</label>
                    <input
                        type="password"
                        name="password"
                        required
                        class="coming-soon-input"
                        style="width:100%; margin-top:4px;"
                        placeholder="••••••••"
                    >
                </div>

                <button type="submit" class="btn btn--primary" style="margin-top:.5rem; width:100%; justify-content:center;">
                    Giriş Yap
                </button>
            </form>
        </div>
    </div>
</main>
