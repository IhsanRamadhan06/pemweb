<?php
// niflix_project/app/Views/series/index.php
// $series, $title, $message, $message_type akan tersedia dari SeriesController

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
    <div class="series-container articles-container">
        <h1><?= escape_html($title) ?></h1>

        <?php if (isset($message) && $message): ?>
            <div class="notification <?= escape_html($message_type ?? '') ?>"><?= escape_html($message) ?></div>
        <?php endif; ?>

        <?php if (!empty($series)): ?>
            <div class="series-list article-list">
                <?php foreach ($series as $s): ?>
                <div class="series-item article-item">
                    <h2><a href="<?= $basePath ?>/daftar_series/show/<?= escape_html($s['id']) ?>"><?= escape_html($s['title']) ?></a></h2>
                    <?php if (!empty($s['image_url'])): ?>
                        <img src="<?= escape_html($s['image_url']) ?>" alt="<?= escape_html($s['title']) ?>" class="series-thumbnail">
                    <?php endif; ?>
                    <p><?= escape_html(substr($s['description'], 0, 150)) ?>...</p>
                    <p class="series-meta">Tahun Rilis: <?= escape_html($s['release_year']) ?></p>
                    <a href="<?= $basePath ?>/daftar_series/show/<?= escape_html($s['id']) ?>" class="btn">Lihat Detail</a>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="info-message">Belum ada series yang ditambahkan.</p>
        <?php endif; ?>
    </div>
</main>

<?php
// Memuat footer
require_once APP_ROOT . '/app/Views/includes/footer.php';
?>