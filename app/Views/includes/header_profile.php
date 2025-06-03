<?php
// niflix_project/app/Views/includes/header_profile.php

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
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/style.css"> <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
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
                <li><a href="<?= $basePath ?>/review_film">Review Film</a></li>
                <li><a href="<?= $basePath ?>/review_series">Review Series</a></li>
                <li><a href="<?= $basePath ?>/daftar_film">Daftar Film</a></li>
                <li><a href="<?= $basePath ?>/daftar_series">Daftar Series</a></li>
                <li><a href="<?= $basePath ?>/komentar_rating">Komentar & Rating</a></li>
                <li><a href="<?= $basePath ?>/profile">Profile</a></li>
                <li><a href="<?= $basePath ?>/auth/logout">Logout</a></li>
            </ul>
        </nav>
    </header>