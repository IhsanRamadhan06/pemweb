<?php
// niflix_project/app/Models/ReviewFilm.php

class ReviewFilm {
    private $pdo; // Tambahkan properti pdo
    protected $table = 'review_films'; // Ubah static menjadi non-static jika model akan diinstansiasi

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo; // Inisialisasi pdo di konstruktor
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO {$this->table} (film_id, user_id, rating, review_text)
            VALUES (?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['film_id'],
            $data['user_id'],
            $data['rating'], // Add rating here
            $data['review_text']
        ]);
    }

    public function all() {
        $stmt = $this->pdo->query("
            SELECT fr.*, f.title AS film_title, u.username, u.foto_pengguna AS user_photo
            FROM {$this->table} fr
            JOIN films f ON fr.film_id = f.id
            JOIN user u ON fr.user_id = u.id
            ORDER BY fr.created_at DESC
        ");
        return $stmt->fetchAll();
    }

    public function find($id) {
        $stmt = $this->pdo->prepare("
            SELECT fr.*, f.title AS film_title, u.username, u.foto_pengguna AS user_photo
            FROM {$this->table} fr
            JOIN films f ON fr.film_id = f.id
            JOIN user u ON fr.user_id = u.id
            WHERE fr.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function update($id, $rating, $reviewText) {
        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET rating = ?, review_text = ? WHERE id = ?");
        return $stmt->execute([$rating, $reviewText, $id]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Get reviews for a specific film.
     * @param int $filmId
     * @return array
     */
    public function getReviewsByFilmId($filmId) {
        $stmt = $this->pdo->prepare("
            SELECT fr.*, u.username, u.foto_pengguna AS user_photo
            FROM {$this->table} fr
            JOIN user u ON fr.user_id = u.id
            WHERE fr.film_id = :film_id
            ORDER BY fr.created_at DESC
        ");
        $stmt->bindParam(':film_id', $filmId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}