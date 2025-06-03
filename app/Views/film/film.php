<?php
// niflix_project/app/Views/film/show.php
// $film, $title akan tersedia dari FilmController

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
    <div class="article-detail-container">
        <a href="<?= $basePath ?>/daftar_film" class="btn btn-back">← Kembali ke Daftar Film</a>

        <?php if ($film): ?>
            <article class="single-article">
                <h1><?= escape_html($film['title']) ?></h1>
                <?php if (!empty($film['image_url'])): ?>
                    <img src="<?= escape_html($film['image_url']) ?>" alt="<?= escape_html($film['title']) ?>" class="film-full-image">
                <?php endif; ?>
                <p class="film-meta">Tahun Rilis: <?= escape_html($film['release_year']) ?></p>
                <div class="article-content">
                    <?= nl2br(escape_html($film['description'])) ?>
                </div>
            </article>
        <?php else: ?>
            <p>Film tidak ditemukan.</p>
        <?php endif; ?>
    </div>
</main>

<?php
// Memuat footer
require_once APP_ROOT . '/app/Views/includes/footer.php';
?>