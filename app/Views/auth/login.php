<?php
// niflix_project/app/Views/auth/login.php
// $error akan tersedia dari AuthController

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
    <meta name="description" content="Login page">
    <meta name="author" content="Kel 7">
    <title>Login</title>
    <link rel="stylesheet" href="/niflix_project/public/assets/css/global.css">
    <link rel="stylesheet" href="/niflix_project/public/assets/css/auth.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <h2>Login</h2>
        </div>
        <?php if (isset($error) && $error): ?>
            <div class="notification error"><?= escape_html($error) ?></div>
        <?php endif; ?>
        <form action="/niflix_project/public/auth/login" method="post">
            <div class="input-group">
                <label for="username">Username/Email:</label>
                <input type="text" id="username" class="input-field" name="username" placeholder="Email/Username" autocomplete="off" required>
                <i class='bx bxs-user'></i>
            </div>
            <div class="input-group">
                <label for="password">Password:</label>
                <input type="password" id="password" class="input-field" name="password" placeholder="Password" autocomplete="off" required>
                <i class='bx bxs-lock-alt'></i>
            </div>
            <div class="forgot-section">
                <section>
                    <input type="checkbox" id="remember_me">
                    <label for="remember_me">Remember me</label>
                </section>
                <section>
                    <a href="#">Forgot password?</a>
                </section>
            </div>
            <div class="submit-group">
                <button type="submit" class="submit-btn">Sign in</button>
            </div>
        </form>
        <div class="signup-link">
            <p>Don't have account? <a href="/niflix_project/public/auth/register">Register</a></p>
        </div>
    </div>
</body>
</html>