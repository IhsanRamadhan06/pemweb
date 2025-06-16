<?php
// niflix_project/app/Views/komentar_rating/show.php

require_once APP_ROOT . '/app/Views/includes/header.php';

$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '/') {
    $basePath = '';
} else {
    $basePath = rtrim($basePath, '/');
}

// Ensure $item is available
if (!$item) {
    echo "<main><div class='articles-container'><p>Item tidak ditemukan.</p></div></main>";
    require_once APP_ROOT . '/app/Views/includes/footer.php';
    exit();
}
?>

<main>
    <div class="article-detail-container">
        <a href="<?= $basePath ?>/daftar_film" class="btn btn-back">← Kembali ke Daftar Film</a> <?php if (isset($message) && $message): ?>
            <div class="notification <?= escape_html($message_type ?? '') ?>"><?= escape_html($message) ?></div>
        <?php endif; ?>

        <article class="single-article">
            <h1><?= escape_html($item['title']) ?></h1>
            <?php if (!empty($item['image_url'])): ?>
                <img src="<?= escape_html($item['image_url']) ?>" alt="<?= escape_html($item['title']) ?>" class="<?= $itemType ?>-full-image">
            <?php endif; ?>
            <p class="<?= $itemType ?>-meta">Tahun Rilis: <?= escape_html($item['release_year']) ?></p>
            <p class="<?= $itemType ?>-meta">Deskripsi: <?= nl2br(escape_html($item['description'])) ?></p>
            <p class="<?= $itemType ?>-meta">Rating Rata-rata: <strong><?= number_format($item['average_rating'], 1) ?>/10</strong> (dari <?= escape_html($item['total_comments_ratings']) ?> ulasan)</p> <p class="<?= $itemType ?>-meta">Total Suka: <strong><?= escape_html($item['total_likes']) ?></strong></p>

            <div class="item-actions-bottom">
                <form action="<?= $basePath ?>/komentar_rating/detail/<?= escape_html($item_type) ?>/<?= escape_html($item['id']) ?>" method="POST" style="display:inline-block;">
                    <input type="hidden" name="action" value="toggle_like">
                    <button type="submit" class="btn btn-like">
                        <i class='bx <?= $isLiked ? 'bxs-heart' : 'bx-heart' ?>'></i> <?= $isLiked ? 'Disukai' : 'Suka' ?>
                    </button>
                </form>
                 <a href="<?= $basePath ?>/komentar_rating/detail/<?= escape_html($item_type) ?>/<?= escape_html($item['id']) ?>" class="btn">Lihat Komentar & Rating</a>
            </div>
        </article>

        </div>
</main>

<?php
require_once APP_ROOT . '/app/Views/includes/footer.php';
?>