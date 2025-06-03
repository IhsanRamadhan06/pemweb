<?php
// niflix_project/app/Views/admin/edit_user.php
// $user, $message, $message_type, $error, $title akan tersedia dari AdminController

// Memuat header
require_once APP_ROOT . '/app/Views/includes/header.php';

// Pastikan base Path tersedia untuk tautan dan gambar
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '/') {
    $basePath = '';
} else {
    $basePath = rtrim($basePath, '/');
}

// Path lengkap ke foto profil pengguna yang diedit
$profilePhotoUrl = $basePath . '/uploads/profile_photos/' . escape_html($user['foto_pengguna'] ?? 'default.png');
// Jika default.png tidak ada di uploads/profile_photos, coba di assets/img
if (strpos($profilePhotoUrl, 'default.png') !== false && !file_exists(PUBLIC_PATH . '/uploads/profile_photos/default.png')) {
    $profilePhotoUrl = $basePath . '/assets/img/default.png';
}

?>

<main>
    <div class="form-container admin-edit-user-container">
        <h1><?= escape_html($title) ?>: <?= escape_html($user['username']) ?></h1>

        <?php if (isset($message) && $message): ?>
            <div class="notification <?= escape_html($message_type ?? '') ?>"><?= escape_html($message) ?></div>
        <?php endif; ?>

        <?php if (isset($error) && $error): ?>
            <div class="notification error"><?= escape_html($error) ?></div>
        <?php endif; ?>

        <?php if ($user): ?>
            <form action="<?= $basePath ?>/admin/edit_user/<?= escape_html($user['id']) ?>" method="POST" enctype="multipart/form-data">
                <div class="profile-photo-section">
                    <img src="<?= $profilePhotoUrl ?>" alt="Profile Photo" class="profile-photo">
                    <div class="photo-upload">
                        <label for="profile_photo">Ubah Foto Profil</label>
                        <input type="file" id="profile_photo" name="profile_photo" accept="image/*">
                    </div>
                </div>

                <div class="profile-info-section">
                    <div class="input-group">
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" value="<?= escape_html($user['username']) ?>" required>
                    </div>

                    <div class="input-group">
                        <label for="fullname">Nama Lengkap:</label>
                        <input type="text" id="fullname" name="fullname" value="<?= escape_html($user['nama_lengkap']) ?>">
                    </div>

                    <div class="input-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" value="<?= escape_html($user['email']) ?>" required>
                    </div>

                    <div class="input-group">
                        <label for="is_admin">Status Admin:</label>
                        <select id="is_admin" name="is_admin">
                            <option value="0" <?= $user['is_admin'] == 0 ? 'selected' : '' ?>>Tidak</option>
                            <option value="1" <?= $user['is_admin'] == 1 ? 'selected' : '' ?>>Ya</option>
                        </select>
                    </div>

                    <div class="password-section">
                        <h3>Ubah Password (kosongkan jika tidak ingin diubah)</h3>
                        <div class="input-group">
                            <label for="new_password">Password Baru:</label>
                            <input type="password" id="new_password" name="new_password">
                        </div>
                        <div class="input-group">
                            <label for="confirm_password">Konfirmasi Password Baru:</label>
                            <input type="password" id="confirm_password" name="confirm_password">
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-update">Perbarui Pengguna</button>
                        <a href="<?= $basePath ?>/admin" class="btn btn-cancel">Batal</a>
                    </div>
                </div>
            </form>
        <?php else: ?>
            <p>Pengguna tidak ditemukan.</p>
        <?php endif; ?>
    </div>
</main>

<?php
// Memuat footer
require_once APP_ROOT . '/app/Views/includes/footer.php';
?>