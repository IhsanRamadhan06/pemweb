<?php
// niflix_project/app/Views/komentar_rating/detail.php

require_once APP_ROOT . '/app/Views/includes/header.php';
require_once APP_ROOT . '/app/Models/CommentRating.php'; // Still needed for the rendering function

$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '/') {
    $basePath = '';
} else {
    $basePath = rtrim($basePath, '/');
}

// Ensure $item is available
if (!$item) {
    echo "<main><div class='articles-container'><p>Item tidak ditemukan.</p></div></main>";
    require_once APP_ROOT . '/app/Views/includes/footer.php';
    exit();
}

// Function to render comments and reviews recursively
function renderAllEntries($entries, $basePath, $item_type, $item_id, $currentUser, $pdo) {
    $commentRatingModel = new CommentRating($pdo); // Instantiate the model
    echo '<div class="comments-list">';
    if (empty($entries)) {
        echo '<p>Belum ada komentar atau ulasan untuk item ini.</p>';
    } else {
        foreach ($entries as $entry) {
            $commenterPhotoUrl = $basePath . '/uploads/profile_photos/' . escape_html($entry['commenter_photo'] ?? 'default.png');
            if (strpos($commenterPhotoUrl, 'default.png') !== false && !file_exists(PUBLIC_PATH . '/uploads/profile_photos/default.png')) {
                $commenterPhotoUrl = $basePath . '/assets/img/default.png';
            }
            $isCommentLiked = $commentRatingModel->hasUserLiked($currentUser['id'], $entry['id']);

            echo '<div class="comment-item">';
            echo '<div class="comment-header">';
            echo '<img src="' . $commenterPhotoUrl . '" alt="Commenter Photo" class="commenter-photo-thumb">';
            echo '<p class="comment-author"><strong>' . escape_html($entry['commenter_username']) . '</strong></p>';
            echo '<p class="comment-date">' . date('d M Y, H:i', strtotime($entry['created_at'])) . '</p>';

            // Display rating if it's a review
            if ($entry['rating_value'] !== null) {
                echo '<p class="comment-rating">Rating: <strong>' . escape_html($entry['rating_value']) . '/10</strong></p>';
            }
            // Display likes for comments (and reviews which can also be liked)
            echo '<p class="comment-likes"><i class="bx bxs-heart"></i> ' . escape_html($entry['total_likes']) . '</p>';

            echo '</div>'; // .comment-header
            echo '<p class="comment-text">' . nl2br(escape_html($entry['comment_text'])) . '</p>';

            echo '<div class="comment-actions">';
            // Like/Unlike comment button
            echo '<form action="' . $basePath . '/komentar_rating/detail/' . escape_html($item_type) . '/' . escape_html($item_id) . '" method="POST" style="display:inline-block;">';
            echo '<input type="hidden" name="action" value="toggle_comment_like">';
            echo '<input type="hidden" name="comment_id" value="' . escape_html($entry['id']) . '">';
            echo '<button type="submit" class="btn-like-comment ' . ($isCommentLiked ? 'liked' : '') . '">';
            echo '<i class="bx ' . ($isCommentLiked ? 'bxs-heart' : 'bx-heart') . '"></i>';
            echo '</button>';
            echo '</form>';

            // Reply button (only for pure comments, not reviews)
            if ($entry['rating_value'] === null) {
                echo '<button class="btn-reply" data-comment-id="' . escape_html($entry['id']) . '" data-comment-user="' . escape_html($entry['commenter_username']) . '">Balas</button>';
            }

            // Delete entry button (for author or admin)
            if (isset($currentUser) && ($currentUser['id'] == $entry['user_id'] || $currentUser['is_admin'] == 1)) {
                echo '<a href="' . $basePath . '/komentar_rating/deleteEntry/' . escape_html($item_type) . '/' . escape_html($entry['id']) . '" onclick="return confirm(\'Yakin ingin menghapus entri ini?\')" class="btn-delete-comment">Hapus</a>';
            }
            echo '</div>'; // .comment-actions

            // Render replies recursively
            if (!empty($entry['replies'])) {
                echo '<div class="comment-replies">';
                renderAllEntries($entry['replies'], $basePath, $item_type, $item_id, $currentUser, $pdo);
                echo '</div>'; // .comment-replies
            }

            echo '</div>'; // .comment-item
        }
    }
    echo '</div>'; // .comments-list
}

?>
<main>
    <div class="article-detail-container">
        <a href="<?= $basePath ?>/komentar_rating" class="btn btn-back">← Kembali ke Daftar Komentar & Rating</a>

        <?php if (isset($message) && $message): ?>
            <div class="notification <?= escape_html($message_type ?? '') ?>"><?= escape_html($message) ?></div>
        <?php endif; ?>

        <article class="single-article">
            <h1><?= escape_html($item['title']) ?></h1>
            <?php if (!empty($item['image_url'])): ?>
                <img src="<?= escape_html($item['image_url']) ?>" alt="<?= escape_html($item['title']) ?>" class="<?= $itemType ?>-full-image">
            <?php endif; ?>
            <p class="<?= $itemType ?>-meta">Tahun Rilis: <?= escape_html($item['release_year']) ?></p>
            <p class="<?= $itemType ?>-meta">Deskripsi: <?= nl2br(escape_html($item['description'])) ?></p>
            <p class="<?= $itemType ?>-meta">Rating Rata-rata: <strong><?= number_format($item['average_rating'], 1) ?>/10</strong> (dari <?= escape_html($item['total_comments_ratings']) ?> ulasan)</p>
            <p class="<?= $itemType ?>-meta">Total Suka: <strong><?= escape_html($item['total_likes']) ?></strong></p>

            <div class="item-actions-bottom">
                <form action="<?= $basePath ?>/komentar_rating/detail/<?= escape_html($item_type) ?>/<?= escape_html($item['id']) ?>" method="POST" style="display:inline-block;">
                    <input type="hidden" name="action" value="toggle_like">
                    <button type="submit" class="btn btn-like">
                        <i class='bx <?= $isLiked ? 'bxs-heart' : 'bx-heart' ?>'></i> <?= $isLiked ? 'Disukai' : 'Suka' ?>
                    </button>
                </form>
            </div>
        </article>

        <section class="comments-section">
            <h2>Komentar & Ulasan</h2>

            <?php
            // Determine initial values for the combined form
            $currentReviewText = $userReviewEntry['comment_text'] ?? '';
            $currentRating = $userReviewEntry['rating_value'] ?? 0;
            $hasUserReviewed = !empty($userReviewEntry);
            ?>

            <div class="comment-form">
                <h3><?= $hasUserReviewed ? 'Edit Ulasan atau Tambah Komentar' : 'Tambahkan Komentar atau Ulasan' ?></h3>
                <form id="comment-review-form" action="<?= $basePath ?>/komentar_rating/detail/<?= escape_html($item_type) ?>/<?= escape_html($item['id']) ?>" method="POST">
                    <input type="hidden" name="action" value="submit_comment_review">
                    <input type="hidden" name="parent_comment_id" id="parent-comment-id" value="">

                    <div class="input-group">
                        <label for="rating_value">Rating (1-10) (Opsional, kosongkan jika hanya komentar):</label>
                        <input type="number" id="rating_value" name="rating_value" min="1" max="10" value="<?= escape_html($currentRating) ?>">
                    </div>

                    <div class="input-group">
                        <label for="comment_text" id="comment-label">Komentar/Ulasan Anda:</label>
                        <textarea name="comment_text" id="comment_text" placeholder="Tulis komentar atau ulasan Anda di sini..." rows="5" required><?= escape_html($currentReviewText) ?></textarea>
                    </div>

                    <button type="submit" class="btn">Kirim</button>
                    <button type="button" id="cancel-reply" class="btn btn-cancel" style="display:none;">Batal Balasan</button>
                </form>
            </div>

            <?php
            // Render all entries (comments and reviews)
            renderAllEntries($allEntries, $basePath, $item_type, $item['id'], Session::get('user'), $pdo);
            ?>
        </section>
    </div>
</main>

<?php
require_once APP_ROOT . '/app/Views/includes/footer.php';
?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const replyButtons = document.querySelectorAll('.btn-reply');
    const commentForm = document.getElementById('comment-review-form');
    const parentCommentIdInput = document.getElementById('parent-comment-id');
    const commentTextInput = document.getElementById('comment_text');
    const commentLabel = document.getElementById('comment-label');
    const cancelReplyButton = document.getElementById('cancel-reply');
    const ratingValueInput = document.getElementById('rating_value');

    replyButtons.forEach(button => {
        button.addEventListener('click', () => {
            const commentId = button.dataset.commentId;
            const commentUser = button.dataset.commentUser;

            parentCommentIdInput.value = commentId;
            commentLabel.textContent = `Balas Komentar @${commentUser}:`;
            commentTextInput.placeholder = `Tulis balasan untuk @${commentUser} di sini...`;
            commentTextInput.focus();
            cancelReplyButton.style.display = 'inline-block';
            ratingValueInput.value = ''; // Clear rating when replying
            ratingValueInput.removeAttribute('required'); // Make rating optional for replies
        });
    });

    cancelReplyButton.addEventListener('click', () => {
        parentCommentIdInput.value = '';
        commentLabel.textContent = 'Komentar/Ulasan Anda:';
        commentTextInput.placeholder = 'Tulis komentar atau ulasan Anda di sini...';
        commentTextInput.value = '';
        cancelReplyButton.style.display = 'none';
        ratingValueInput.setAttribute('required', 'required'); // Make rating required again for top-level
        ratingValueInput.value = '<?= escape_html($currentRating) ?>'; // Restore initial rating if exists
    });

    // Optional: Add event listener to clear rating/parent_comment_id if user types in comment field after clearing it
    commentTextInput.addEventListener('input', () => {
        if (commentTextInput.value === '' && parentCommentIdInput.value !== '') {
            // If user deletes all text and it was a reply, reset to top-level comment mode
            // This is optional, but can improve UX
            // cancelReplyButton.click(); // Simulate click on cancel reply
        }
    });
});
</script>