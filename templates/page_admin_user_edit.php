<?php
// templates/page_admin_user_edit.php
require_once __DIR__ . '/../app_config.php';
require_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) redirect(BASE_PATH . '/?route=admin-users');

try {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $user = $stmt->fetch();

    $firms = $pdo->query('SELECT id, firma_adi FROM firmalar ORDER BY firma_adi')->fetchAll();
} catch (PDOException $e) {
    set_flash('error', 'Kullanıcı yüklenemedi.');
    redirect(BASE_PATH . '/?route=admin-users');
}

?>

<main class="section">
    <div class="container">
        <div style="display:flex; gap:16px;">
            <?php include __DIR__ . '/../partials/partial_sidebar_admin.php'; ?>

            <div style="flex:1;">
                <h1 class="section__title">Kullanıcı Düzenle — ID #<?php echo $id; ?></h1>
                <p class="section__subtitle">Kullanıcının rolünü, firma atamasını ve aktif/pasif durumunu buradan düzenleyebilirsiniz.</p>

                <div style="margin-top:12px;">
                    <div class="card">
                        <?php echo render_messages(); ?>

                        <form method="post" action="<?php echo BASE_PATH; ?>/admin/admin_user_update_action.php">
                            <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">

                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                                <div>
                                    <label>Rol</label>
                                    <select name="role" required>
                                        <?php $roles = ['admin','client','staff1','staff2','staff3'];
                                        foreach ($roles as $r): ?>
                                            <option value="<?php echo $r; ?>" <?php echo ($user['role'] === $r) ? 'selected' : ''; ?>><?php echo strtoupper($r); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div>
                                    <label>Firma Ataması (opsiyonel)</label>
                                    <select name="firma_id">
                                        <option value="">-- Firma atama --</option>
                                        <?php foreach ($firms as $f): ?>
                                            <option value="<?php echo $f['id']; ?>" <?php echo ($user['firma_id'] == $f['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($f['firma_adi']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div>
                                    <label>Aktif</label>
                                    <select name="is_active">
                                        <option value="1" <?php echo ($user['is_active']) ? 'selected' : ''; ?>>Aktif</option>
                                        <option value="0" <?php echo (!$user['is_active']) ? 'selected' : ''; ?>>Pasif</option>
                                    </select>
                                </div>

                                <div>
                                    <label>Parola Sıfırla (opsiyonel)</label>
                                    <input type="password" name="new_password" placeholder="Yeni parola (boş bırakabilirsiniz)">
                                </div>

                            </div>

                            <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:12px;">
                                <button type="submit" class="btn btn--primary">Güncelle</button>
                                <a class="btn btn--ghost" href="<?php echo BASE_PATH; ?>/?route=admin-users">İptal</a>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
