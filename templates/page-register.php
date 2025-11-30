<?php
// templates/register.php
// Kayıt formu: form alanları İngilizce değişken adları kullanır (first_name, last_name, email, password)

$registerError = isset($_GET['error']) ? $_GET['error'] : null;
$success = isset($_GET['success']) && $_GET['success'] === '1';
?>

<main class="section section--tight">
    <div class="container" style="max-width:540px;">
        <span class="badge">
            <span class="badge__dot"></span>
            Kayıt Ol
        </span>

        <h1 class="section__title" style="margin-top:.75rem;">Yeni Hesap Oluştur</h1>
        <p class="section__subtitle">Ad ve soyad ayrı tutulur. Hesabınızı oluşturduktan sonra yönetici tarafından daha fazla yetki verilebilir.</p>

        <div class="card" style="margin-top:1.5rem;">
            <?php if ($registerError): ?>
                <div style="margin-bottom:.75rem; font-size:.8rem; color:#FCA5A5;">
                    <?php echo htmlspecialchars($registerError); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div style="margin-bottom:.75rem; font-size:.9rem; color:#86efac;">
                    Kayıt başarılı — lütfen giriş yapınız.
                </div>
            <?php endif; ?>

            <form method="post" action="<?php echo BASE_PATH; ?>/auth/register.php" style="display:flex; flex-direction:column; gap:.75rem;">
                <div style="display:flex; gap:.5rem;">
                    <div style="flex:1;">
                        <label style="font-size:.8rem; color:#9CA3AF;">Ad</label>
                        <input type="text" name="first_name" required class="coming-soon-input" placeholder="Ahmet">
                    </div>
                    <div style="flex:1;">
                        <label style="font-size:.8rem; color:#9CA3AF;">Soyad</label>
                        <input type="text" name="last_name" required class="coming-soon-input" placeholder="Yılmaz">
                    </div>
                </div>

                <div>
                    <label style="font-size:.8rem; color:#9CA3AF;">E-posta</label>
                    <input type="email" name="email" required class="coming-soon-input" placeholder="mail@orneksiteniz.com">
                </div>

                <div>
                    <label style="font-size:.8rem; color:#9CA3AF;">Şifre (en az 8 karakter)</label>
                    <input type="password" name="password" required class="coming-soon-input" placeholder="••••••••">
                </div>

                <div>
                    <label style="font-size:.8rem; color:#9CA3AF;">Şifre (tekrar)</label>
                    <input type="password" name="password_confirm" required class="coming-soon-input" placeholder="••••••••">
                </div>

                <div>
                    <label style="font-size:.8rem; color:#9CA3AF;">Firma (opsiyonel)</label>
                    <input type="text" name="firm_name" class="coming-soon-input" placeholder="Firma adı (varsa)">
                </div>

                <div style="display:flex; gap:.5rem; align-items:center;">
                    <input type="checkbox" name="accepted_terms" id="accepted_terms" required>
                    <label for="accepted_terms" style="font-size:.8rem; color:#6B7280;">Kullanım koşullarını ve gizlilik politikasını kabul ediyorum</label>
                </div>

                <button type="submit" class="btn btn--primary" style="margin-top:.5rem; width:100%; justify-content:center;">Kayıt Ol</button>
            </form>
        </div>
    </div>
</main>
