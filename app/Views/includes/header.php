<?php
// niflix_project/app/Views/includes/header.php

$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '/') {
    $basePath = '';
} else {
    $basePath = rtrim($basePath, '/');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Niflix App' ?></title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/global.css">
    <?php

    // Membuat path saat ini
    $requestUri = $_SERVER['REQUEST_URI'];
    // Untuk hanya memgambalikan path dari url
    $path = parse_url($requestUri, PHP_URL_PATH);
    // Mengubah path menjadi array
    $pathSegments = explode('/', trim($path, '/'));

    $pageCss = '';
    // Periksa apakah string ada di array pathSegments
    if (in_array('dashboard', $pathSegments)) {
        $pageCss = 'dashboard.css';
    } elseif (in_array('articles', $pathSegments) || in_array('comment', $pathSegments)) {
        $pageCss = 'articles.css';
    } elseif (in_array('admin', $pathSegments)) {
        $pageCss = 'admin.css';
    } elseif (in_array('profile', $pathSegments)) {
        $pageCss = 'profile.css';
    } elseif (in_array('daftar_series', $pathSegments)) {
        $pageCss = 'daftar_film_series.css';
    } elseif (in_array('daftar_film', $pathSegments)) {
        $pageCss = 'daftar_film_series.css';
    } elseif (in_array('review_films', $pathSegments)) { // Keep existing
        $pageCss = 'review.css';
    } elseif (in_array('review_series', $pathSegments)) { // Keep existing
        $pageCss = 'review.css';
    } elseif (in_array('komentar_rating', $pathSegments)) { // New
        $pageCss = 'review.css'; // Reusing review.css, adjust if you want a new one
    }

    if ($pageCss) {
        echo '<link rel="stylesheet" href="' . $basePath . '/assets/css/' . $pageCss . '">';
    }
    ?>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <script>
        const BASE_URL = '<?= $basePath ?>';
    </script>
</head>
<body>
    <header>
        <button class="menu-toggle">☰</button>
        <nav class="nav-menu">
            <ul>
                <li><a href="<?= $basePath ?>/dashboard">Dashboard</a></li>
                <li><a href="<?= $basePath ?>/articles">Artikel</a></li>
                <?php if (Session::has('user') && Session::get('user')['is_admin'] == 1) : ?>
                    <li><a href="<?= $basePath ?>/admin">Kelola Akun</a></li>
                <?php endif; ?>
                <li><a href="<?= $basePath ?>/review_films">Review Film</a></li> <li><a href="<?= $basePath ?>/review_series">Review Series</a></li> <li><a href="<?= $basePath ?>/daftar_film">Daftar Film</a></li>
                <li><a href="<?= $basePath ?>/daftar_series">Daftar Series</a></li>
                <li><a href="<?= $basePath ?>/komentar_rating">Komentar & Rating</a></li> <?php if (Session::has('user')): ?>
                    <li><a href="<?= $basePath ?>/profile">Profile (<?= escape_html(Session::get('user')['fullname'] ?? Session::get('user')['username']) ?>)</a></li>
                    <li><a href="<?= $basePath ?>/auth/logout">Logout</a></li>
                <?php else: ?>
                    <li><a href="<?= $basePath ?>/auth/login">Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
