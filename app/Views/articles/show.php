<?php
// niflix_project/app/Views/articles/show.php
// $article, $comments, $title, $message, $message_type akan tersedia dari ArticleController

// Memuat header
require_once APP_ROOT . '/app/Views/includes/header.php';

// Pastikan base Path tersedia untuk tautan dan gambar
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '/') {
    $basePath = '';
} else {
    $basePath = rtrim($basePath, '/');
}

// Path lengkap ke foto profil penulis
$authorPhotoUrl = $basePath . '/uploads/profile_photos/' . escape_html($article['author_photo'] ?? 'default.png');
// Jika default.png tidak ada di uploads/profile_photos, coba di assets/img
if (strpos($authorPhotoUrl, 'default.png') !== false && !file_exists(PUBLIC_PATH . '/uploads/profile_photos/default.png')) {
    $authorPhotoUrl = $basePath . '/assets/img/default.png';
}
?>

<main>
    <div class="article-detail-container">
        <a href="<?= $basePath ?>/articles" class="btn btn-back">← Kembali ke Daftar Artikel</a>

        <?php if (isset($message) && $message): ?>
            <div class="notification <?= escape_html($message_type ?? '') ?>"><?= escape_html($message) ?></div>
        <?php endif; ?>

        <?php if ($article): ?>
            <article class="single-article">
                <h1><?= escape_html($article['title']) ?></h1>
                <p class="article-meta">
                    Oleh: <img src="<?= $authorPhotoUrl ?>" alt="Author Photo" class="author-photo-thumb">
                    <strong><?= escape_html($article['author_fullname'] ?: $article['author_username']) ?></strong>
                    pada <?= date('d F Y H:i', strtotime($article['created_at'])) ?>
                    <?php if ($article['created_at'] != $article['updated_at']): ?>
                        (Terakhir diperbarui: <?= date('d F Y H:i', strtotime($article['updated_at'])) ?>)
                    <?php endif; ?>
                </p>
                <div class="article-content">
                    <?= nl2br(escape_html($article['content'])) ?>
                </div>

                <?php
                    $currentUser = Session::get('user');
                    if (isset($currentUser) && ($currentUser['id'] == $article['user_id'] || $currentUser['is_admin'] == 1)) :
                ?>
                    <div class="article-actions-bottom">
                        <a href="<?= $basePath ?>/articles/edit/<?= escape_html($article['id']) ?>" class="btn-edit">Edit Artikel</a>
                        <a href="<?= $basePath ?>/articles/delete/<?= escape_html($article['id']) ?>"
                           onclick="return confirm('Yakin ingin menghapus artikel ini? Semua komentar juga akan terhapus.')" class="btn-delete">Hapus Artikel</a>
                    </div>
                <?php endif; ?>
            </article>

            <section class="comments-section">
                <h2>Komentar (<?= count($comments) ?>)</h2>

                <?php if (Session::has('user')): ?>
                    <div class="comment-form">
                        <h3>Tambahkan Komentar</h3>
                        <form action="<?= $basePath ?>/articles/show/<?= escape_html($article['id']) ?>" method="POST">
                            <textarea name="comment_text" placeholder="Tulis komentar Anda di sini..." rows="5" required></textarea>
                            <button type="submit" class="btn">Kirim Komentar</button>
                        </form>
                    </div>
                <?php endif; ?>

                <div class="comments-list">
                    <?php if (!empty($comments)): ?>
                        <?php foreach ($comments as $comment):
                            // Path lengkap ke foto profil pengomentar
                            $commenterPhotoUrl = $basePath . '/uploads/profile_photos/' . escape_html($comment['commenter_photo'] ?? 'default.png');
                            // Jika default.png tidak ada di uploads/profile_photos, coba di assets/img
                            if (strpos($commenterPhotoUrl, 'default.png') !== false && !file_exists(PUBLIC_PATH . '/uploads/profile_photos/default.png')) {
                                $commenterPhotoUrl = $basePath . '/assets/img/default.png';
                            }
                        ?>
                            <div class="comment-item">
                                <div class="comment-header">
                                    <img src="<?= $commenterPhotoUrl ?>" alt="Commenter Photo" class="commenter-photo-thumb">
                                    <p class="comment-author"><strong><?= escape_html($comment['commenter_username']) ?></strong></p>
                                    <p class="comment-date"><?= date('d M Y, H:i', strtotime($comment['created_at'])) ?></p>
                                </div>
                                <p class="comment-text"><?= nl2br(escape_html($comment['comment_text'])) ?></p>
                                <?php
                                    // Izinkan hapus komentar jika:
                                    // 1. Pengguna saat ini adalah penulis komentar
                                    // 2. Pengguna saat ini adalah penulis artikel
                                    // 3. Pengguna saat ini adalah admin
                                    if (isset($currentUser) && ($currentUser['id'] == $comment['user_id'] || $currentUser['id'] == $article['user_id'] || $currentUser['is_admin'] == 1)) :
                                ?>
                                    <div class="comment-actions">
                                        <a href="<?= $basePath ?>/comment/delete/<?= escape_html($comment['id']) ?>"
                                        onclick="return confirm('Yakin ingin menghapus komentar ini?')" class="btn-delete-comment">Hapus</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Belum ada komentar untuk artikel ini.</p>
                    <?php endif; ?>
                </div>
            </section>

        <?php else: ?>
            <p>Artikel tidak ditemukan.</p>
        <?php endif; ?>
    </div>
</main>

<?php
// Memuat footer
require_once APP_ROOT . '/app/Views/includes/footer.php';
?>