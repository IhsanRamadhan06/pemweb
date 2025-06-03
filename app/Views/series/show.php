<?php
// niflix_project/app/Views/series/show.php
// $series, $title akan tersedia dari SeriesController

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
        <a href="<?= $basePath ?>/daftar_series" class="btn btn-back">← Kembali ke Daftar Series</a>

        <?php if ($series): ?>
            <article class="single-article">
                <h1><?= escape_html($series['title']) ?></h1>
                <?php if (!empty($series['image_url'])): ?>
                    <img src="<?= escape_html($series['image_url']) ?>" alt="<?= escape_html($series['title']) ?>" class="series-full-image">
                <?php endif; ?>
                <p class="series-meta">Tahun Rilis: <?= escape_html($series['release_year']) ?></p>
                <div class="article-content">
                    <?= nl2br(escape_html($series['description'])) ?>
                </div>
            </article>
        <?php else: ?>
            <p>Series tidak ditemukan.</p>
        <?php endif; ?>
    </div>
</main>

<?php
// Memuat footer
require_once APP_ROOT . '/app/Views/includes/footer.php';
?>