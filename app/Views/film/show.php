<?php
// niflix_project/app/Views/film/show.php
// $film, $title akan tersedia dari FilmController

// Memuat header
require_once APP_ROOT . '/app/Views/includes/header.php';

// Pastikan base Path tersedia untuk tautan
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '/') {
    $basePath = '';
} else {
    $basePath = rtrim($basePath, '/');
}

// Cek apakah pengguna adalah admin
$currentUser = Session::get('user');
$isAdmin = isset($currentUser) && $currentUser['is_admin'] == 1;
?>

<main>
    <div class="article-detail-container">
        <a href="<?= $basePath ?>/daftar_film" class="btn btn-back">← Kembali ke Daftar Film</a>

        <?php if ($film): ?>
            <article class="single-article">
                <h1><?= escape_html($film['title']) ?></h1>
                <?php if (!empty($film['image_url'])): ?>
                    <img src="<?= escape_html($film['image_url']) ?>" alt="<?= escape_html($film['title']) ?>" class="series-full-image">
                <?php endif; ?>
                <p class="series-meta">Tahun Rilis: <?= escape_html($film['release_year']) ?></p>
                <div class="article-content">
                    <?= nl2br(escape_html($film['description'])) ?>
                </div>

                <?php if ($isAdmin): // Tampilkan tombol edit/hapus hanya untuk admin ?>
                    <div class="series-actions">
                        <a href="<?= $basePath ?>/daftar_film/edit/<?= escape_html($film['id']) ?>" class="btn-edit">Edit Film</a>
                        <a href="<?= $basePath ?>/daftar_film/delete/<?= escape_html($film['id']) ?>"
                           onclick="return confirm('Yakin ingin menghapus film ini?')" class="btn-delete">Hapus Film</a>
                    </div>
                <?php endif; ?>
            </article>
            <a href="<?= $basePath ?>/review_films/create" class="btn-link">Review </a>
            <a href="<?= $basePath ?>/komentar_rating/detail/film/<?= escape_html($film['id']) ?>" class="btn-link">Komentar Rating</a>
        <?php else: ?>
            <p>Film tidak ditemukan.</p>
        <?php endif; ?>
    </div>
</main>

<?php
// Memuat footer
require_once APP_ROOT . '/app/Views/includes/footer.php';
?>
