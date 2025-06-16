<?php
// niflix_project/app/Models/Film.php

class Film {
    private $pdo;
    private $table = 'likes';

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Mengambil semua film dengan rata-rata rating (dari comments_rating), jumlah komentar, dan jumlah suka.
     * @return array
     */
    public function getAllFilm() {
        $stmt = $this->pdo->prepare("
            SELECT
                f.*,
                COALESCE(AVG(cr.rating_value), 0) AS average_rating,
                SUM(CASE WHEN cr.item_type = 'film' AND cr.rating_value IS NOT NULL THEN 1 ELSE 0 END) AS total_comments_ratings,
                COALESCE(SUM(CASE WHEN l.item_type = 'film' THEN 1 ELSE 0 END), 0) AS total_likes
            FROM
                {$this->table} f
            LEFT JOIN
                comments_rating cr ON f.id = cr.item_id AND cr.item_type = 'film'
            LEFT JOIN
                likes l ON f.id = l.item_id AND l.item_type = 'film'
            GROUP BY
                f.id
            ORDER BY
                f.title ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Menemukan film berdasarkan ID dengan rata-rata rating (dari comments_rating), jumlah komentar, dan jumlah suka.
     * @param int $id
     * @return array|false
     */
    public function findById($id) {
        $stmt = $this->pdo->prepare("
            SELECT
                f.*,
                COALESCE(AVG(cr.rating_value), 0) AS average_rating,
                SUM(CASE WHEN cr.item_type = 'film' AND cr.rating_value IS NOT NULL THEN 1 ELSE 0 END) AS total_comments_ratings,
                COALESCE(SUM(CASE WHEN l.item_type = 'film' THEN 1 ELSE 0 END), 0) AS total_likes
            FROM
                {$this->table} f
            LEFT JOIN
                comments_rating cr ON f.id = cr.item_id AND cr.item_type = 'film'
            LEFT JOIN
                likes l ON f.id = l.item_id AND l.item_type = 'film'
            WHERE
                f.id = :id
            GROUP BY
                f.id
        ");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Menambahkan/menghapus like pada film.
     * @param int $userId
     * @param int $filmId
     * @return bool True if successful, false otherwise.
     */
    public function toggleLike($userId, $filmId) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM likes WHERE user_id = :user_id AND item_id = :item_id AND item_type = 'film'");
        $stmt->execute([':user_id' => $userId, ':item_id' => $filmId]);
        $isLiked = $stmt->fetchColumn();

        if ($isLiked) {
            // Unlike
            $stmt = $this->pdo->prepare("DELETE FROM likes WHERE user_id = :user_id AND item_id = :item_id AND item_type = 'film'");
        } else {
            // Like
            $stmt = $this->pdo->prepare("INSERT INTO likes (user_id, item_id, item_type) VALUES (:user_id, :item_id, 'film')");
        }
        return $stmt->execute([':user_id' => $userId, ':item_id' => $filmId]);
    }

    /**
     * Mengecek apakah pengguna sudah menyukai film tertentu.
     * @param int $userId
     * @param int $filmId
     * @return bool
     */
    public function hasUserLiked($userId, $filmId) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM likes WHERE user_id = :user_id AND item_id = :item_id AND item_type = 'film'");
        $stmt->execute([':user_id' => $userId, ':item_id' => $filmId]);
        return $stmt->fetchColumn() > 0;
    }
}