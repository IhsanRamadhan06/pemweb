<?php
// niflix_project/app/Views/articles/index.php
// $articles akan tersedia dari ArticleController
// $title akan tersedia dari ArticleController
// $message dan $message_type akan tersedia dari ArticleController (dari parameter URL)

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
    <div class="articles-container">
        <h1><?= escape_html($title) ?></h1>

        <?php if (isset($message) && $message): ?>
            <div class="notification <?= escape_html($message_type ?? '') ?>"><?= escape_html($message) ?></div>
        <?php endif; ?>

        <?php if (Session::has('user')): // Hanya tampilkan tombol buat artikel jika sudah login ?>
            <p><a href="<?= $basePath ?>/articles/create" class="btn btn-create-article">Buat Artikel Baru</a></p>
        <?php else: ?>
            <p class="info-message">Login untuk membuat artikel baru.</p>
        <?php endif; ?>


        <?php if (!empty($articles)): ?>
            <div class="article-list">
                <?php foreach ($articles as $article): ?>
                <div class="article-item">
                    <h2><a href="<?= $basePath ?>/articles/show/<?= escape_html($article['id']) ?>"><?= escape_html($article['title']) ?></a></h2>
                    <p class="article-meta">Oleh: <?= escape_html($article['author_fullname'] ?: $article['author_username']) ?> pada <?= date('d F Y', strtotime($article['created_at'])) ?></p>
                    <p><?= escape_html(substr($article['content'], 0, 200)) ?>...</p>
                    <a href="<?= $basePath ?>/articles/show/<?= escape_html($article['id']) ?>" class="btn">Baca Selengkapnya</a>
                    <?php
                        $currentUser = Session::get('user');
                        if (isset($currentUser) && ($currentUser['id'] == $article['user_id'] || $currentUser['is_admin'] == 1)) :
                    ?>
                        <div class="article-actions">
                            <a href="<?= $basePath ?>/articles/edit/<?= escape_html($article['id']) ?>" class="btn-edit">Edit</a>
                            <a href="<?= $basePath ?>/articles/delete/<?= escape_html($article['id']) ?>"
                            onclick="return confirm('Yakin ingin menghapus artikel ini?')">Hapus</a>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>Belum ada artikel yang dipublikasikan.</p>
        <?php endif; ?>
    </div>
</main>

<?php
// Memuat footer
require_once APP_ROOT . '/app/Views/includes/footer.php';
?>