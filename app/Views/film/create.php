<?php
// niflix_project/app/Views/film/create.php

// Memuat header
require_once APP_ROOT . '/app/Views/includes/header.php';

// Pastikan base Path tersedia untuk tautan
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '/') {
    $basePath = '';
} else {
    $basePath = rtrim($basePath, '/');
}

// Ambil data dari $film jika ada (untuk pre-fill form setelah validasi gagal)
$filmTitle = $film['title'] ?? '';
$filmDescription = $film['description'] ?? '';
$filmReleaseYear = $film['release_year'] ?? '';
$filmImageUrl = $film['image_url'] ?? ''; // Ambil URL gambar yang di-submit
$is_popular = $film['is_popular'] ?? '';
?>

<main>
    <div class="form-container">
        <h1><?= escape_html($title) ?></h1>

        <?php if (isset($message) && $message): ?>
            <div class="notification <?= escape_html($message_type ?? '') ?>"><?= escape_html($message) ?></div>
        <?php endif; ?>

        <form action="<?= $basePath ?>/daftar_film/create" method="POST"> <div class="input-group">
                <label for="title">Judul film:</label>
                <input type="text" id="title" name="title" required value="<?= escape_html($filmTitle) ?>">
            </div>

            <div class="input-group">
                <label for="description">Deskripsi Film:</label>
                <textarea id="description" name="description" rows="10" required><?= escape_html($filmDescription) ?></textarea>
            </div>

            <div class="input-group">
                <label for="release_year">Tahun Rilis:</label>
                <input type="number" id="release_year" name="release_year" required value="<?= escape_html($filmReleaseYear) ?>">
            </div>

            <div class="input-group">
                <label for="image_url">URL Gambar Film (Opsional):</label>
                <input type="text" id="image_url" name="image_url" placeholder="http://example.com/image.jpg" value="<?= escape_html($filmImageUrl) ?>">
                <small>Masukkan URL lengkap gambar film.</small>
            </div>

            <div class="input-group">
                    <label for="is_popular">Status Popular:</label>
                    <select id="is_popular" name="is_popular">
                        <option value="0" <?= (isset($film['is_popular']) ? $film['is_popular'] == 0 : true) ? 'selected' : '' ?>>Tidak</option>
                        <option value="1" <?= (isset($film['is_popular']) ? $film['is_popular'] == 1 : false) ? 'selected' : '' ?>>Ya</option>
                    </select>
            </div>

            <button type="submit" class="btn">Tambah Film</button>
            <a href="<?= $basePath ?>/daftar_film" class="btn btn-cancel">Batal</a>
        </form>
    </div>
</main>

<?php
// Memuat footer
require_once APP_ROOT . '/app/Views/includes/footer.php';
?>
