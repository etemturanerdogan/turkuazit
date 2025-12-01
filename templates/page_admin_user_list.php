<?php
// templates/page_admin_user_list.php
require_once __DIR__ . '/../app_config.php';
require_admin();

$users = [];
try {
    $users = $pdo->query('SELECT u.id, u.first_name, u.last_name, u.email, u.role, u.is_active, u.firma_id, f.firma_adi FROM users u LEFT JOIN firmalar f ON f.id = u.firma_id ORDER BY u.id DESC LIMIT 500')->fetchAll();
} catch (PDOException $e) {
    $users = [];
}

?>

<main class="section">
    <div class="container">
        <div style="display:flex; gap:16px;">
            <?php include __DIR__ . '/../partials/partial_sidebar_admin.php'; ?>

            <div style="flex:1;">
                <h1 class="section__title">Kullanıcılar ve Roller</h1>
                <p class="section__subtitle">Buradan kullanıcılara roller verip firma ataması yapabilir, aktif/pasif durumu değiştirebilirsiniz.</p>

                <div style="margin-top:12px;">
                    <div class="card">
                        <?php echo render_messages(); ?>

                        <table style="width:100%; border-collapse:collapse; margin-top:8px;">
                            <thead>
                                <tr style="text-align:left; border-bottom:1px solid #E5E7EB;">
                                    <th>ID</th>
                                    <th>Ad Soyad</th>
                                    <th>E-posta</th>
                                    <th>Firma</th>
                                    <th>Rol</th>
                                    <th>Durum</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $u): ?>
                                    <tr style="border-bottom:1px solid #F3F4F6;">
                                        <td><?php echo htmlspecialchars($u['id']); ?></td>
                                        <td><?php echo htmlspecialchars(trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''))); ?></td>
                                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                                        <td><?php echo htmlspecialchars($u['firma_adi'] ?? '—'); ?></td>
                                        <td><?php echo htmlspecialchars($u['role']); ?></td>
                                        <td><?php echo $u['is_active'] ? 'Aktif' : 'Pasif'; ?></td>
                                        <td>
                                            <a class="btn btn--ghost" href="<?php echo BASE_PATH; ?>/?route=admin-user-edit&id=<?php echo $u['id']; ?>">Düzenle</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
