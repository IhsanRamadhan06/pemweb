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
?>

<main>
    <div class="film-container articles-container">
        <h1><?= escape_html($title) ?></h1>

        <?php if (isset($message) && $message): ?>
            <div class="notification <?= escape_html($message_type ?? '') ?>"><?= escape_html($message) ?></div>
        <?php endif; ?>

        <?php if (!empty($film)): ?>
            <div class="film-list article-list">
                <?php foreach ($film as $s): ?>
                <div class="film-item article-item">
                    <h2><a href="<?= $basePath ?>/daftar_film/show/<?= escape_html($s['id']) ?>"><?= escape_html($s['title']) ?></a></h2>
                    <?php if (!empty($s['image_url'])): ?>
                        <img src="<?= escape_html($s['image_url']) ?>" alt="<?= escape_html($s['title']) ?>" class="film-thumbnail">
                    <?php endif; ?>
                    <p><?= escape_html(substr($s['description'], 0, 150)) ?>...</p>
                    <p class="film-meta">Tahun Rilis: <?= escape_html($s['release_year']) ?></p>
                    <a href="<?= $basePath ?>/daftar_film/show/<?= escape_html($s['id']) ?>" class="btn">Lihat Detail</a>
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