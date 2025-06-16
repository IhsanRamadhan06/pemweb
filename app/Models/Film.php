<?php
// niflix_project/app/Models/Film.php

class Film {
    private $pdo;
    private $table = 'films';

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Mengambil semua film dengan rata-rata rating (dari comments_rating), jumlah komentar, dan jumlah suka.
     * @return array
     */
    public function getAllFilms() {
        $stmt = $this->pdo->prepare("
            SELECT
                f.*,
                COALESCE(AVG(cr.rating_value), 0) AS average_rating,
                SUM(CASE WHEN cr.item_type = 'film' AND cr.rating_value IS NOT NULL THEN 1 ELSE 0 END) AS total_comments_ratings,
                COALESCE(SUM(CASE WHEN l.item_type = 'film' THEN 1 ELSE 0 END), 0) AS total_likes,
                u.username AS creator_username, 
                u.nama_lengkap AS creator_fullname 
            FROM
                {$this->table} f
            LEFT JOIN
                comments_rating cr ON f.id = cr.item_id AND cr.item_type = 'film'
            LEFT JOIN
                likes l ON f.id = l.item_id AND l.item_type = 'film'
            LEFT JOIN
                user u ON f.creator_id = u.id -- TAMBAHKAN JOIN INI
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
                COALESCE(SUM(CASE WHEN l.item_type = 'film' THEN 1 ELSE 0 END), 0) AS total_likes,
                u.username AS creator_username,
                u.nama_lengkap AS creator_fullname 
            FROM
                {$this->table} f
            LEFT JOIN
                comments_rating cr ON f.id = cr.item_id AND cr.item_type = 'film'
            LEFT JOIN
                likes l ON f.id = l.item_id AND l.item_type = 'film'
            LEFT JOIN
                user u ON f.creator_id = u.id -- TAMBAHKAN JOIN INI
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

    /**
     * Mengambil film berdasarkan is_popular status.
     * DITAMBAHKAN: Mengambil juga creator_username.
     * @return array
     */
    public function getPopularFilm() {
        $stmt = $this->pdo->prepare("
            SELECT
                f.*,
                COALESCE(AVG(cr.rating_value), 0) AS average_rating,
                SUM(CASE WHEN cr.item_type = 'film' AND cr.rating_value IS NOT NULL THEN 1 ELSE 0 END) AS total_comments_ratings,
                COALESCE(SUM(CASE WHEN l.item_type = 'film' THEN 1 ELSE 0 END), 0) AS total_likes,
                u.username AS creator_username,
                u.nama_lengkap AS creator_fullname 
            FROM
                {$this->table} f
            LEFT JOIN
                comments_rating cr ON f.id = cr.item_id AND cr.item_type = 'film'
            LEFT JOIN
                likes l ON f.id = l.item_id AND l.item_type = 'film'
            LEFT JOIN
                user u ON f.creator_id = u.id
            WHERE f.is_popular = 1
            GROUP BY
                f.id
            ORDER BY f.id ASC
        ");
        $stmt->execute();
        $popularFilm = $stmt->fetchAll();
        return $popularFilm;
    }

    /**
     * Membuat film baru.
     * DITAMBAHKAN: Parameter $creatorId.
     * @param string $title
     * @param string $description
     * @param int $releaseYear
     * @param string $imageUrl
     * @param int $isPopular
     * @param int $creatorId
     * @return bool
     */
    public function create($title, $description, $releaseYear, $imageUrl, $isPopular, $creatorId) {
        $stmt = $this->pdo->prepare("INSERT INTO {$this->table} (title, description, image_url, release_year, is_popular, creator_id) VALUES (:title, :description, :image_url, :release_year, :is_popular, :creator_id)");
        return $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':image_url' => $imageUrl,
            ':release_year' => $releaseYear,
            ':is_popular' => $isPopular,
            ':creator_id' => $creatorId // TAMBAHKAN INI
        ]);
    }

    /**
     * Memperbarui Film.
     * Perhatikan bahwa metode ini tidak akan mengupdate creator_id,
     * karena biasanya creator_id hanya diatur saat pembuatan.
     * Jika Anda ingin mengizinkan perubahan creator_id melalui edit,
     * Anda perlu menambahkan parameter dan logika di sini.
     * @param int $id
     * @param string $title
     * @param string $description
     * @param int $releaseYear
     * @param string $imageUrl
     * @param int $isPopular
     * @param int $editorId
     * @return bool
     */
    public function update($id, $title, $description, $releaseYear, $imageUrl, $isPopular, $editorId) {
        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET title = :title, description = :description, image_url = :image_url, release_year = :release_year, is_popular = :is_popular, creator_id = :creator_id WHERE id = :id");
        return $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':image_url' => $imageUrl,
            ':release_year' => $releaseYear,
            ':is_popular' => $isPopular,
            ':creator_id' => $editorId,
            ':id' => $id
        ]);
    }

    /**
     * Menghapus film.
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
