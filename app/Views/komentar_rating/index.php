<?php
// niflix_project/app/Views/komentar_rating/index.php

require_once APP_ROOT . '/app/Views/includes/header.php';

$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '/') {
    $basePath = '';
} else {
    $basePath = rtrim($basePath, '/');
}
?>

<main>
    <div class="articles-container">
        <h1><?= escape_html($title) ?></h1>

        <?php if (isset($message) && $message): ?>
            <div class="notification <?= escape_html($message_type ?? '') ?>"><?= escape_html($message) ?></div>
        <?php endif; ?>

        <section class="item-list-section">
            <h2>Film</h2>
            <?php if (!empty($films)): ?>
                <div class="film-list article-list">
                    <?php foreach ($films as $film): ?>
                        <div class="film-item article-item">
                            <h2><a href="<?= $basePath ?>/komentar_rating/detail/film/<?= escape_html($film['id']) ?>"><?= escape_html($film['title']) ?></a></h2>
                            <?php if (!empty($film['image_url'])): ?>
                                <img src="<?= escape_html($film['image_url']) ?>" alt="<?= escape_html($film['title']) ?>" class="film-thumbnail">
                            <?php endif; ?>
                            <p class="film-meta">Tahun Rilis: <?= escape_html($film['release_year']) ?></p>
                            <p class="film-meta">Rating Rata-rata: <strong><?= number_format($film['average_rating'], 1) ?>/10</strong></p>
                            <p class="film-meta">Jumlah Komentar & Rating: <?= escape_html($film['total_comments_ratings']) ?></p>
                            <p class="film-meta">Total Suka: <?= escape_html($film['total_likes']) ?></p>
                            <a href="<?= $basePath ?>/komentar_rating/detail/film/<?= escape_html($film['id']) ?>" class="btn">Lihat Detail</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="info-message">Belum ada film yang ditambahkan.</p>
            <?php endif; ?>
        </section>

        <section class="item-list-section">
            <h2>Series</h2>
            <?php if (!empty($series)): ?>
                <div class="series-list">
                    <?php foreach ($series as $s): ?>
                        <div class="series-item">
                            <h2><a href="<?= $basePath ?>/komentar_rating/detail/series/<?= escape_html($s['id']) ?>"><?= escape_html($s['title']) ?></a></h2>
                            <?php if (!empty($s['image_url'])): ?>
                                <img src="<?= escape_html($s['image_url']) ?>" alt="<?= escape_html($s['title']) ?>" class="series-thumbnail">
                            <?php else: ?>
                                <img src="<?= $basePath ?>/assets/img/default_series_thumb.png" alt="No Image" class="series-thumbnail">
                            <?php endif; ?>
                            <p class="series-meta">Tahun Rilis: <?= escape_html($s['release_year']) ?></p>
                            <p class="series-meta">Rating Rata-rata: <strong><?= number_format($s['average_rating'], 1) ?>/10</strong></p>
                            <p class="series-meta">Jumlah Komentar & Rating: <?= escape_html($s['total_comments_ratings']) ?></p>
                            <!-- <p class="series-meta">Jumlah Komentar: <?php //  escape_html($s['total_comments']) ?></p> -->
                            <p class="series-meta">Total Suka: <?= escape_html($s['total_likes']) ?></p>
                            <a href="<?= $basePath ?>/komentar_rating/detail/series/<?= escape_html($s['id']) ?>" class="btn">Lihat Detail</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="info-message">Belum ada series yang ditambahkan.</p>
            <?php endif; ?>
        </section>
    </div>
</main>

<?php
require_once APP_ROOT . '/app/Views/includes/footer.php';
?>