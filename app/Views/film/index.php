<?php
// niflix_project/app/Views/film/index.php
// $film, $title, $message, $message_type akan tersedia dari FilmController

// Memuat header
require_once APP_ROOT . '/app/Views/includes/header.php';

// Pastikan base Path tersedia untuk tautan
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '/') {
    $basePath = '';
} else {
    $basePath = rtrim($basePath, '/');
}

// Cek apakah pengguna adalah admin
$currentUser = Session::get('user');
$isAdmin = isset($currentUser) && $currentUser['is_admin'] == 1;
?>

<main>
    <div class="series-container articles-container">
        <h1>Film Populer</h1>

        <?php if (isset($message) && $message): ?>
            <div class="notification <?= escape_html($message_type ?? '') ?>"><?= escape_html($message) ?></div>
        <?php endif; ?>

        <?php if ($isAdmin): // Tampilkan tombol tambah film hanya untuk admin ?>
            <p><a href="<?= $basePath ?>/daftar_film/create" class="btn btn-create-article">Tambah Film Baru</a></p>
        <?php endif; ?>

        <?php if (!empty($popularFilm)): // Gunakan $popularFilm untuk slider ?>
            <div class="slider-wrapper">
                <button class="slider-arrow left-arrow">&#10094;</button>
                <div class="slider-container">
                    <?php foreach ($popularFilm as $s): ?>
                        <div class="slider-item">
                            <a href="<?= $basePath ?>/daftar_film/show/<?= escape_html($s['id']) ?>">
                                <?php if (!empty($s['image_url'])): ?>
                                    <img src="<?= escape_html($s['image_url']) ?>" alt="<?= escape_html($s['title']) ?>" class="series-thumbnail">
                                <?php else: ?>
                                    <img src="<?= $basePath ?>/assets/img/default_series_thumb.png" alt="No Image" class="series-thumbnail">
                                <?php endif; ?>
                                <div class="series-hover-info">
                                    <h3><?= escape_html($s['title']) ?> (<?= escape_html($s['release_year']) ?>)</h3>
                                </div>
                            </a>
                            <div class="slider-stats">
                                <span class="stat-item"><i class='bx bxs-heart'></i> <?= escape_html($s['total_likes']) ?></span>
                                <span class="stat-item"><i class='bx bxs-check-circle'></i> <?= escape_html($s['total_comments_ratings']) ?></span> 
                                <span class="stat-item"><i class='bx bxs-message-dots'></i> <?= escape_html($s['total_comments_ratings']) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button class="slider-arrow right-arrow">&#10095;</button>
            </div>
        <?php else: ?>
            <p class="info-message">Belum ada film populer yang ditambahkan.</p>
        <?php endif; ?>

        <h1>Daftar Film</h1>

        <?php if (!empty($allFilm)): ?>
            <div class="series-list">
                <?php foreach ($allFilm as $s): ?>
                        <div class="series-item">
                            <a href="<?= $basePath ?>/daftar_film/show/<?= escape_html($s['id']) ?>">
                                <?php if (!empty($s['image_url'])): ?>
                                    <img src="<?= escape_html($s['image_url']) ?>" alt="<?= escape_html($s['title']) ?>" class="series-thumbnail">
                                <?php else: ?>
                                    <img src="<?= $basePath ?>/assets/img/default_series_thumb.png" alt="No Image" class="series-thumbnail">
                                <?php endif; ?>
                                <div class="series-hover-info">
                                    <h3><?= escape_html($s['title']) ?> (<?= escape_html($s['release_year']) ?>)</h3>
                                </div>
                            </a>
                            <div class="series-stats">
                                <span class="stat-item"><i class='bx bxs-heart'></i> <?= escape_html($s['total_likes']) ?></span>
                                <span class="stat-item"><i class='bx bxs-check-circle'></i> <?= escape_html($s['total_comments_ratings']) ?></span> 
                                <span class="stat-item"><i class='bx bxs-message-dots'></i> <?= escape_html($s['total_comments_ratings']) ?></span>
                            </div>
                        </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="info-message">Belum ada film yang ditambahkan.</p>
        <?php endif; ?>
    </div>
</main>

<?php
// Memuat footer
require_once APP_ROOT . '/app/Views/includes/footer.php';
?>