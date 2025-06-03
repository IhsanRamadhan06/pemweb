<?php
// niflix_project/app/Views/dashboard.php
// $user_username, $movies, $series akan tersedia dari DashboardController

// Memuat header
require_once APP_ROOT . '/app/Views/includes/header.php';
?>

<main>
    <div class="welcome-message">
        <h2>Welcome, <?= escape_html($user_username) ?>!</h2>
    </div>
    <section>
        <div class="slider-wrapper">
            <div class="slider-container">
                <?php foreach ($film as $movie): ?>
                    <div class="slider-item">
                        <img src="<?= escape_html($movie['image']) ?>" alt="<?= escape_html($movie['title']) ?>">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <section>
        <h1>Daftar Film</h1>
        <div class="grid-container">
            <?php foreach ($film as $film): ?>
                <div class="grid-item">
                    <img src="<?= escape_html($film['image']) ?>" alt="<?= escape_html($film['title']) ?>">
                    <h4><?= escape_html($film['title']) ?></h4>
                    <a href="<?= $basePath ?>/review_film?film=<?= urlencode($film['title']) ?>" class="btn">Review</a>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section>
        <h1>Daftar Series</h1>
        <div class="grid-container">
            <?php foreach ($series as $s): ?>
                <div class="grid-item">
                    <img src="<?= escape_html($s['image']) ?>" alt="<?= escape_html($s['title']) ?>">
                    <h4><?= escape_html($s['title']) ?></h4>
                    <a href="<?= $basePath ?>/review_series?series=<?= urlencode($s['title']) ?>" class="btn">Review</a>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

<?php
// Memuat footer (tag main akan ditutup di sini)
require_once APP_ROOT . '/app/Views/includes/footer.php';
?>