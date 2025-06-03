<?php
// niflix_project/app/Views/admin/manage_users.php
// $users akan tersedia dari AdminController
// $title akan tersedia dari AdminController
// $message dan $message_type akan tersedia dari AdminController (dari parameter URL)

// Pastikan APP_ROOT didefinisikan (dari index.php)
if (!defined('APP_ROOT')) {
    die('APP_ROOT not defined. Invalid entry point.');
}

// Muat helper functions jika belum ada (misalnya escape_html)
if (!function_exists('escape_html')) {
    require_once APP_ROOT . '/app/Core/Functions.php';
}

// Memuat header
require_once APP_ROOT . '/app/Views/includes/header.php';

// Pastikan base Path tersedia untuk tautan
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '/') {
    $basePath = '';
} else {
    $basePath = rtrim($basePath, '/');
}
?>

<main>
    <div class="admin-container">
        <h1>Kelola Akun Pengguna</h1>

        <?php if (isset($message) && $message): // Cek jika ada pesan dari parameter URL ?>
            <div class="notification <?= escape_html($message_type ?? '') ?>"><?= escape_html($message) ?></div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Nama Lengkap</th>
                    <th>Admin?</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= escape_html($user['id']) ?></td>
                        <td><?= escape_html($user['username']) ?></td>
                        <td><?= escape_html($user['email']) ?></td>
                        <td><?= escape_html($user['nama_lengkap']) ?></td>
                        <td><?= $user['is_admin'] == 1 ? 'Ya' : 'Tidak' ?></td>
                        <td class="action-links">
                            <a href="<?= $basePath ?>/admin/edit_user/<?= escape_html($user['id']) ?>">Edit</a>
                            <a href="<?= $basePath ?>/admin/delete/<?= escape_html($user['id']) ?>"
                            onclick="return confirm('Yakin hapus akun <?= escape_html($user['username']) ?> ini?')">Hapus</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">Tidak ada pengguna ditemukan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<?php
// Memuat footer
require_once APP_ROOT . '/app/Views/includes/footer.php';
?>