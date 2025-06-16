<?php
// niflix_project/app/Models/CommentRating.php

class CommentRating {
    private $pdo;
    private $table = 'comments_rating';

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Mengambil semua entri (komentar dan rating) untuk sebuah item (film, series, atau artikel).
     * @param int $itemId
     * @param string $itemType 'film', 'series', 'article'
     * @return array Hierarchical array of comments and replies, with ratings included as top-level entries
     */
    public function getAllEntriesByItem($itemId, $itemType) {
        $sql = "
            SELECT
                cr.id,
                cr.user_id,
                cr.comment_text,
                cr.created_at,
                cr.parent_comment_id,
                cr.rating_value,
                u.username AS commenter_username,
                u.foto_pengguna AS commenter_photo,
                COALESCE(SUM(CASE WHEN l.item_type = 'comment' AND l.item_id = cr.id THEN 1 ELSE 0 END), 0) AS total_likes
            FROM
                {$this->table} cr
            JOIN
                user u ON cr.user_id = u.id
            LEFT JOIN
                likes l ON cr.id = l.item_id AND l.item_type = 'comment'
            WHERE
                cr.item_id = :item_id AND cr.item_type = :item_type
            GROUP BY
                cr.id
            ORDER BY
                cr.created_at ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':item_id' => $itemId,
            ':item_type' => $itemType
        ]);
        $allEntries = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Build a hierarchical array for comments and separate ratings
        $entriesById = [];
        foreach ($allEntries as $entry) {
            $entriesById[$entry['id']] = $entry;
            $entriesById[$entry['id']]['replies'] = [];
        }

        $rootEntries = [];
        foreach ($entriesById as $entry) {
            // A rating will have rating_value IS NOT NULL and parent_comment_id IS NULL
            // A top-level comment will have rating_value IS NULL and parent_comment_id IS NULL
            // A reply will have parent_comment_id IS NOT NULL
            if ($entry['parent_comment_id'] === null) {
                $rootEntries[] = &$entriesById[$entry['id']];
            } else {
                if (isset($entriesById[$entry['parent_comment_id']])) {
                    $entriesById[$entry['parent_comment_id']]['replies'][] = &$entriesById[$entry['id']];
                }
            }
        }
        return $rootEntries;
    }


    /**
     * Mengambil rating untuk sebuah item (film atau series).
     * @param int $itemId
     * @param string $itemType 'film' or 'series'
     * @return array List of ratings
     */
    public function getRatingsByItem($itemId, $itemType) {
        $stmt = $this->pdo->prepare("
            SELECT
                cr.id,
                cr.user_id,
                cr.comment_text,
                cr.created_at,
                cr.rating_value,
                u.username AS reviewer_username,
                u.foto_pengguna AS reviewer_photo
            FROM
                {$this->table} cr
            JOIN
                user u ON cr.user_id = u.id
            WHERE
                cr.item_id = :item_id AND cr.item_type = :item_type AND cr.rating_value IS NOT NULL
            ORDER BY
                cr.created_at DESC
        ");
        $stmt->execute([
            ':item_id' => $itemId,
            ':item_type' => $itemType
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Menemukan rating pengguna untuk item tertentu.
     * @param int $userId
     * @param int $itemId
     * @param string $itemType 'film' or 'series'
     * @return array|false
     */
    public function findUserRating($userId, $itemId, $itemType) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM {$this->table}
            WHERE user_id = :user_id AND item_id = :item_id AND item_type = :item_type AND rating_value IS NOT NULL
        ");
        $stmt->execute([
            ':user_id' => $userId,
            ':item_id' => $itemId,
            ':item_type' => $itemType
        ]);
        return $stmt->fetch();
    }

    /**
     * Menambahkan komentar baru atau balasan, atau rating baru.
     * @param int $itemId
     * @param string $itemType
     * @param int $userId
     * @param string $commentText
     * @param int|null $parentCommentId ID komentar yang dibalas (opsional)
     * @param int|null $ratingValue Rating (opsional, untuk rating)
     * @return bool
     */
    public function addEntry($itemId, $itemType, $userId, $commentText = null, $parentCommentId = null, $ratingValue = null) {
        $stmt = $this->pdo->prepare("
            INSERT INTO {$this->table} (item_id, item_type, user_id, comment_text, parent_comment_id, rating_value)
            VALUES (:item_id, :item_type, :user_id, :comment_text, :parent_comment_id, :rating_value)
        ");
        return $stmt->execute([
            ':item_id' => $itemId,
            ':item_type' => $itemType,
            ':user_id' => $userId,
            ':comment_text' => $commentText,
            ':parent_comment_id' => $parentCommentId,
            ':rating_value' => $ratingValue
        ]);
    }

    /**
     * Memperbarui entri (rating atau komentar).
     * @param int $id
     * @param string $commentText
     * @param int|null $ratingValue
     * @return bool
     */
    public function updateEntry($id, $commentText, $ratingValue = null) {
        $stmt = $this->pdo->prepare("
            UPDATE {$this->table} SET comment_text = :comment_text, rating_value = :rating_value
            WHERE id = :id
        ");
        return $stmt->execute([
            ':comment_text' => $commentText,
            ':rating_value' => $ratingValue,
            ':id' => $id
        ]);
    }

    /**
     * Menghapus entri (komentar atau rating).
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Finds an entry by ID.
     * @param int $id
     * @return array|false
     */
    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Menambahkan/menghapus like pada komentar.
     * @param int $userId
     * @param int $commentId
     * @return bool True if successful, false otherwise.
     */
    public function toggleLike($userId, $commentId) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM likes WHERE user_id = :user_id AND item_id = :item_id AND item_type = 'comment'");
        $stmt->execute([':user_id' => $userId, ':item_id' => $commentId]);
        $isLiked = $stmt->fetchColumn();

        if ($isLiked) {
            // Unlike
            $stmt = $this->pdo->prepare("DELETE FROM likes WHERE user_id = :user_id AND item_id = :item_id AND item_type = 'comment'");
        } else {
            // Like
            $stmt = $this->pdo->prepare("INSERT INTO likes (user_id, item_id, item_type) VALUES (:user_id, :item_id, 'comment')");
        }
        return $stmt->execute([':user_id' => $userId, ':item_id' => $commentId]);
    }

    /**
     * Mengecek apakah pengguna sudah menyukai komentar tertentu.
     * @param int $userId
     * @param int $commentId
     * @return bool
     */
    public function hasUserLiked($userId, $commentId) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM likes WHERE user_id = :user_id AND item_id = :item_id AND item_type = 'comment'");
        $stmt->execute([':user_id' => $userId, ':item_id' => $commentId]);
        return $stmt->fetchColumn() > 0;
    }
}