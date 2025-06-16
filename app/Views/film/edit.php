<?php
// niflix_project/app/Views/film/edit.php

// Memuat header
require_once APP_ROOT . '/app/Views/includes/header.php'; //

// Pastikan base Path tersedia untuk tautan
$basePath = dirname($_SERVER['SCRIPT_NAME']); //
if ($basePath === '/') { //
    $basePath = ''; //
} else {
    $basePath = rtrim($basePath, '/'); //
}

// Path lengkap ke gambar film - langsung dari database
$filmImageUrl = escape_html($film['image_url']); //
?>

<main>
    <div class="form-container">
        <h1><?= escape_html($title) ?>: <?= escape_html($film['title']) ?></h1>

        <?php if (isset($message) && $message): ?>
            <div class="notification <?= escape_html($message_type ?? '') ?>"><?= escape_html($message) ?></div>
        <?php endif; ?>

        <?php if ($film): ?>
            <form id="editFilmForm" action="<?= $basePath ?>/daftar_film/edit/<?= escape_html($film['id']) ?>" method="POST">
                <div class="input-group">
                    <label for="title">Judul Film:</label>
                    <input type="text" id="title" name="title" required value="<?= escape_html($_POST['title'] ?? $film['title']) ?>">
                    <?php if (isset($error['title'])): ?>
                        <span class="validation-message error"><?= escape_html($error['title']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="input-group">
                    <label for="description">Deskripsi Film:</label>
                    <textarea id="description" name="description" rows="10" required><?= escape_html($_POST['description'] ?? $film['description']) ?></textarea>
                    <?php if (isset($error['description'])): ?>
                        <span class="validation-message error"><?= escape_html($error['description']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="input-group">
                    <label for="release_year">Tahun Rilis:</label>
                    <input type="number" id="release_year" name="release_year" required value="<?= escape_html($_POST['release_year'] ?? $film['release_year']) ?>">
                    <?php if (isset($error['release_year'])): ?>
                        <span class="validation-message error"><?= escape_html($error['release_year']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="input-group">
                    <label for="image_url">URL Gambar Film:</label>
                    <?php if ($filmImageUrl): ?>
                        <img src="<?= $filmImageUrl ?>" alt="Current Film Image" style="max-width: 150px; height: auto; margin-bottom: 10px; border-radius: 5px;">
                        <br>
                    <?php endif; ?>
                    <input type="text" id="image_url" name="image_url" placeholder="http://example.com/image.jpg" value="<?= escape_html($_POST['image_url'] ?? $filmImageUrl) ?>">
                    <?php if (isset($error['image_url'])): ?>
                        <span class="validation-message error"><?= escape_html($error['image_url']) ?></span>
                    <?php endif; ?>
                    <small>Masukkan URL lengkap gambar film.</small>
                </div>

                <div class="input-group">
                    <label for="is_popular">Status Popular:</label>
                    <select id="is_popular" name="is_popular">
                        <option value="0" <?= (isset($film['is_popular']) ? $film['is_popular'] == 0 : true) ? 'selected' : '' ?>>Tidak</option>
                        <option value="1" <?= (isset($film['is_popular']) ? $film['is_popular'] == 1 : false) ? 'selected' : '' ?>>Ya</option>
                    </select>
                </div>

                <?php if (!empty($film['creator_username'])): ?>
                    <div class="input-group">
                        <label>Dibuat Oleh:</label>
                        <p><strong><?= escape_html($film['creator_fullname'] ?: $film['creator_username']) ?></strong></p>
                    </div>
                <?php endif; ?>

                <button type="submit" class="btn">Perbarui Film</button>
                <a href="<?= $basePath ?>/daftar_film/show/<?= escape_html($film['id']) ?>" class="btn btn-cancel">Batal</a>
            </form>
        <?php else: ?>
            <p>Film tidak ditemukan.</p>
        <?php endif; ?>
    </div>
</main>

<?php
// Memuat footer
require_once APP_ROOT . '/app/Views/includes/footer.php'; //
?>
