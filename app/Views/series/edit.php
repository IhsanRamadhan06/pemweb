<?php
// niflix_project/app/Views/series/edit.php

// Memuat header
require_once APP_ROOT . '/app/Views/includes/header.php';

// Pastikan base Path tersedia untuk tautan
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '/') {
    $basePath = '';
} else {
    $basePath = rtrim($basePath, '/');
}

// Path lengkap ke gambar series - langsung dari database
$seriesImageUrl = escape_html($series['image_url']);
?>

<main>
    <div class="form-container">
        <h1><?= escape_html($title) ?>: <?= escape_html($series['title']) ?></h1>

        <?php if (isset($message) && $message): ?>
            <div class="notification <?= escape_html($message_type ?? '') ?>"><?= escape_html($message) ?></div>
        <?php endif; ?>

        <?php if ($series): ?>
            <form id="editSeriesForm" action="<?= $basePath ?>/daftar_series/edit/<?= escape_html($series['id']) ?>" method="POST">
                <div class="input-group">
                    <label for="title">Judul Series:</label>
                    <input type="text" id="title" name="title" required value="<?= escape_html($_POST['title'] ?? $series['title']) ?>" data-field="title">
                    <span class="validation-message error" id="title-error">
                        <?php echo isset($error['title']) ? escape_html($error['title']) : ''; ?>
                    </span>
                </div>

                <div class="input-group">
                    <label for="description">Deskripsi Series:</label>
                    <textarea id="description" name="description" rows="10" required data-field="description"><?= escape_html($_POST['description'] ?? $series['description']) ?></textarea>
                    <span class="validation-message error" id="description-error">
                        <?php echo isset($error['description']) ? escape_html($error['description']) : ''; ?>
                    </span>
                </div>

                <div class="input-group">
                    <label for="release_year">Tahun Rilis:</label>
                    <input type="number" id="release_year" name="release_year" required value="<?= escape_html($_POST['release_year'] ?? $series['release_year']) ?>" data-field="release_year">
                    <span class="validation-message error" id="release_year-error">
                        <?php echo isset($error['release_year']) ? escape_html($error['release_year']) : ''; ?>
                    </span>
                </div>

                <div class="input-group">
                    <label for="image_url">URL Gambar Series:</label>
                    <?php if ($seriesImageUrl): ?>
                        <img src="<?= $seriesImageUrl ?>" alt="Current Series Image" style="max-width: 150px; height: auto; margin-bottom: 10px; border-radius: 5px;">
                        <br>
                    <?php endif; ?>
                    <input type="text" id="image_url" name="image_url" placeholder="http://example.com/image.jpg" value="<?= escape_html($_POST['image_url'] ?? $seriesImageUrl) ?>" data-field="image_url">
                    <span class="validation-message error" id="image_url-error">
                        <?php echo isset($error['image_url']) ? escape_html($error['image_url']) : ''; ?>
                    </span>
                    <small>Masukkan URL lengkap gambar series.</small>
                </div>

                <div class="input-group">
                    <label for="is_popular">Status Popular:</label>
                    <select id="is_popular" name="is_popular">
                        <option value="0" <?= (isset($series['is_popular']) ? $series['is_popular'] == 0 : true) ? 'selected' : '' ?>>Tidak</option>
                        <option value="1" <?= (isset($series['is_popular']) ? $series['is_popular'] == 1 : false) ? 'selected' : '' ?>>Ya</option>
                    </select>
                </div>

                <?php if (!empty($series['creator_username'])): ?>
                    <div class="input-group">
                        <label>Dibuat Oleh:</label>
                        <p><strong><?= escape_html($series['creator_fullname'] ?: $series['creator_username']) ?></strong></p>
                    </div>
                <?php endif; ?>

                <button type="submit" class="btn">Perbarui Series</button>
                <a href="<?= $basePath ?>/daftar_series/show/<?= escape_html($series['id']) ?>" class="btn btn-cancel">Batal</a>
            </form>
        <?php else: ?>
            <p>Series tidak ditemukan.</p>
        <?php endif; ?>
    </div>
</main>

<?php
// Memuat footer
require_once APP_ROOT . '/app/Views/includes/footer.php';
?>
