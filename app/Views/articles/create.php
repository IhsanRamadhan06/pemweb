<?php
// niflix_project/app/Views/articles/create.php
// $title, $message, $message_type akan tersedia dari ArticleController

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
        <h1><?= escape_html($title) ?></h1>

        <?php if (isset($message) && $message): ?>
            <div class="notification <?= escape_html($message_type ?? '') ?>"><?= escape_html($message) ?></div>
        <?php endif; ?>

        <form action="<?= $basePath ?>/articles/create" method="POST">
            <div class="input-group">
                <label for="title">Judul Artikel:</label>
                <input type="text" id="title" name="title" required value="<?= escape_html($_POST['title'] ?? '') ?>">
            </div>

            <div class="input-group">
                <label for="content">Konten Artikel:</label>
                <textarea id="content" name="content" rows="15" required><?= escape_html($_POST['content'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn">Publikasikan Artikel</button>
            <a href="<?= $basePath ?>/articles" class="btn btn-cancel">Batal</a>
        </form>
    </div>
</main>

<?php
// Memuat footer
require_once APP_ROOT . '/app/Views/includes/footer.php';
?>