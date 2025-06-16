<?php
    require_once APP_ROOT . '/app/Views/includes/header.php';
?>

<div class="review-container">
    <h2>Tambah Review Series</h2>

    <form action="<?= $basePath ?>/review_series/store" method="POST">
        <label for="series_id">Judul Series:</label>
        <select id="series_id" name="series_id" required>
            <option value="">-- Pilih Series --</option>
            <?php foreach ($series as $s): ?>
                <option value="<?= htmlspecialchars($s['id']) ?>">
                    <?= htmlspecialchars($s['title']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="review_text">Ulasan:</label>
        <textarea name="review_text" id="review_text" rows="5" required></textarea>

        <button type="submit">Kirim Review</button>
    </form>
</div>

<?php
    require_once APP_ROOT . '/app/Views/includes/footer.php';
?>