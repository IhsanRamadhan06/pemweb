<?php
// niflix_project/app/Views/series/create.php

// Memuat header
require_once APP_ROOT . '/app/Views/includes/header.php';

// Pastikan base Path tersedia untuk tautan
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '/') {
    $basePath = '';
} else {
    $basePath = rtrim($basePath, '/');
}

// Ambil data dari $series jika ada (untuk pre-fill form setelah validasi gagal)
$seriesTitle = $_POST['title'] ?? ($series['title'] ?? '');
$seriesDescription = $_POST['description'] ?? ($series['description'] ?? '');
$seriesReleaseYear = $_POST['release_year'] ?? ($series['release_year'] ?? '');
$seriesImageUrl = $_POST['image_url'] ?? ($series['image_url'] ?? ''); // Ambil URL gambar yang di-submit
$is_popular = $_POST['is_popular'] ?? ($series['is_popular'] ?? 0); // Handle 'is_popular'
?>

<main>
    <div class="form-container">
        <h1><?= escape_html($title) ?></h1>

        <?php if (isset($message) && $message): ?>
            <div class="notification <?= escape_html($message_type ?? '') ?>"><?= escape_html($message) ?></div>
        <?php endif; ?>

        <form action="<?= $basePath ?>/daftar_series/create" method="POST">
            <div class="input-group">
                <label for="title">Judul Series:</label>
                <input type="text" id="title" name="title" required value="<?= escape_html($seriesTitle) ?>" data-field="title">
                <span class="validation-message error" id="title-error">
                    <?php echo isset($error['title']) ? escape_html($error['title']) : ''; ?>
                </span>
            </div>

            <div class="input-group">
                <label for="description">Deskripsi Series:</label>
                <textarea id="description" name="description" rows="10" required data-field="description"><?= escape_html($seriesDescription) ?></textarea>
                <span class="validation-message error" id="description-error">
                    <?php echo isset($error['description']) ? escape_html($error['description']) : ''; ?>
                </span>
            </div>

            <div class="input-group">
                <label for="release_year">Tahun Rilis:</label>
                <input type="number" id="release_year" name="release_year" required value="<?= escape_html($seriesReleaseYear) ?>" data-field="release_year">
                <span class="validation-message error" id="release_year-error">
                    <?php echo isset($error['release_year']) ? escape_html($error['release_year']) : ''; ?>
                </span>
            </div>

            <div class="input-group">
                <label for="image_url">URL Gambar Series (Opsional):</label>
                <input type="text" id="image_url" name="image_url" placeholder="http://example.com/image.jpg" value="<?= escape_html($seriesImageUrl) ?>" data-field="image_url">
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

            <button type="submit" class="btn">Tambah Series</button>
            <a href="<?= $basePath ?>/daftar_series" class="btn btn-cancel">Batal</a>
        </form>
    </div>
</main>

<?php
// Memuat footer
require_once APP_ROOT . '/app/Views/includes/footer.php';
?>
