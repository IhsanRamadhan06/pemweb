<?php
// niflix_project/app/Views/profile.php
// $currentUser, $message, $message_type, $error, $title akan tersedia dari ProfileController

require_once APP_ROOT . '/app/Views/includes/header.php'; // UBAH BARIS INI

// Pastikan base Path tersedia untuk gambar dan tautan
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '/') {
    $basePath = '';
} else {
    $basePath = rtrim($basePath, '/');
}

// Path lengkap ke foto profil
$profilePhotoUrl = $basePath . '/uploads/profile_photos/' . escape_html($currentUser['foto_pengguna'] ?? 'default.png');
// Jika default.png ada di folder public/assets/img/
if (strpos($profilePhotoUrl, 'default.png') !== false && !file_exists(PUBLIC_PATH . '/uploads/profile_photos/default.png')) {
    $profilePhotoUrl = $basePath . '/assets/img/default.png'; // Asumsi default.png ada di assets/img
}

?>

<main>
    <div class="profile-container">
        <div class="profile-header">
            <h1>My Profile</h1>
        </div>
        <div id="profile-notification">
            <?php if (isset($message) && $message): ?>
                <div class="notification <?= escape_html($message_type) ?>"><?= escape_html($message) ?></div>
            <?php endif; ?>
            <?php if (isset($error) && $error): // Error dari validasi PHP awal (sebelum AJAX) ?>
                <div class="notification error"><?= escape_html($error) ?></div>
            <?php endif; ?>
        </div>

        <form class="profile-content" method="POST" enctype="multipart/form-data">
            <div class="profile-photo-section">
                <img src="<?= $profilePhotoUrl ?>"
                    alt="Profile Photo" class="profile-photo">

                <div class="photo-upload">
                    <label for="profile_photo">
                        Change Profile Photo
                    </label>
                    <input type="file" id="profile_photo" name="profile_photo" accept="image/*">
                </div>
            </div>

            <div class="profile-info-section">
                <div class="profile-info">
                    <div class="input-row"> <label for="username">Username</label>
                        <input type="text" id="username" name="username"
                            value="<?= escape_html($currentUser['username']) ?>" required>
                    </div>

                    <div class="input-row"> <label for="fullname">Full Name</label>
                        <input type="text" id="fullname" name="fullname"
                            value="<?= escape_html($currentUser['nama_lengkap']) ?>">
                    </div>

                    <div class="input-row"> <label for="email">Email</label>
                        <input type="email" id="email" name="email"
                            value="<?= escape_html($currentUser['email']) ?>" required>
                    </div>
                </div>

                <div class="password-section">
                    <h3>Change Password</h3>

                    <div class="input-row"> <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password">
                    </div>

                    <div class="input-row"> <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password">
                    </div>

                    <div class="input-row"> <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password">
                    </div>
                </div>

                <button type="submit" class="btn-update">Update Profile</button>
            </div>
        </form>
    </div>
</main>

<?php
require_once APP_ROOT . '/app/Views/includes/footer.php';
?>