<?php
// niflix_project/app/Views/articles/edit.php
// $article, $title, $message, $message_type akan tersedia dari ArticleController

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
    <div class="form-container">
        <h1><?= escape_html($title) ?>: <?= escape_html($article['title']) ?></h1>

        <?php if (isset($message) && $message): ?>
            <div class="notification <?= escape_html($message_type ?? '') ?>"><?= escape_html($message) ?></div>
        <?php endif; ?>

        <?php if ($article): ?>
            <form action="<?= $basePath ?>/articles/edit/<?= escape_html($article['id']) ?>" method="POST">
                <div class="input-group">
                    <label for="title">Judul Artikel:</label>
                    <input type="text" id="title" name="title" required value="<?= escape_html($article['title']) ?>">
                </div>

                <div class="input-group">
                    <label for="content">Konten Artikel:</label>
                    <textarea id="content" name="content" rows="15" required><?= escape_html($article['content']) ?></textarea>
                </div>

                <button type="submit" class="btn">Perbarui Artikel</button>
                <a href="<?= $basePath ?>/articles/show/<?= escape_html($article['id']) ?>" class="btn btn-cancel">Batal</a>
            </form>
        <?php else: ?>
            <p>Artikel tidak ditemukan.</p>
        <?php endif; ?>
    </div>
</main>

<?php
// Memuat footer
require_once APP_ROOT . '/app/Views/includes/footer.php';
?>