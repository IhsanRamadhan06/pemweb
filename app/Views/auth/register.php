<?php
// niflix_project/app/Views/auth/register.php
// $message dan $message_type akan tersedia dari AuthController

// Pastikan escape_html() tersedia
if (!function_exists('escape_html')) {
    require_once APP_ROOT . '/app/Core/Functions.php';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Halaman Pendaftaran">
    <meta name="author" content="Kel 7">
    <title>Register</title>
    <link rel="stylesheet" href="/niflix_project/public/assets/css/global.css">
    <link rel="stylesheet" href="/niflix_project/public/assets/css/auth.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <h2>Daftar</h2>
        </div>
        <?php if (isset($message) && $message): ?>
            <div class="notification <?= escape_html($message_type) ?>"><?= escape_html($message) ?></div>
        <?php endif; ?>
        <form action="/niflix_project/public/auth/register" method="post">
            <div class="input-group">
                <label for="fullname">Nama Lengkap:</label>
                <input type="text" id="fullname" name="fullname" class="input-field" placeholder="Nama Lengkap" required>
                <i class='bx bx-user'></i>
            </div>
            <div class="input-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" class="input-field" placeholder="Email" required>
                <i class='bx bx-envelope'></i>
            </div>
            <div class="input-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" class="input-field" placeholder="Username" required>
                <i class='bx bx-user-circle'></i>
            </div>
            <div class="input-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" class="input-field" placeholder="Password" required>
                <i class='bx bx-lock'></i>
            </div>
            <div class="input-group">
                <label for="confirm-password">Konfirmasi Password:</label>
                <input type="password" id="confirm-password" name="confirm-password" class="input-field" placeholder="Konfirmasi Password" required>
                <i class='bx bx-lock'></i>
            </div>
            <div class="submit-group">
                <button type="submit" class="submit-btn">Daftar</button>
            </div>
        </form>
        <div class="signup-link">
            <p>Sudah punya akun? <a href="/niflix_project/public/auth/login">Login di sini</a></p>
        </div>
    </div>
</body>
</html>