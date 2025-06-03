<?php
// niflix_project/app/Models/Film.php

class Film {
    private $pdo;
    private $table = 'films';

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Mengambil semua film.
     * @return array
     */
    public function getAllFilms() {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} ORDER BY title ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Menemukan series berdasarkan ID.
     * @param int $id
     * @return array|false
     */
    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Methods for Create, Update, Delete would go here if needed for full CRUD
    // For now, we only need read as per task description.
    /*
    public function create($title, $description, $imageUrl, $releaseYear) {
        $stmt = $this->pdo->prepare("INSERT INTO {$this->table} (title, description, image_url, release_year) VALUES (:title, :description, :image_url, :release_year)");
        return $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':image_url' => $imageUrl,
            ':release_year' => $releaseYear
        ]);
    }

    public function update($id, $title, $description, $imageUrl, $releaseYear) {
        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET title = :title, description = :description, image_url = :image_url, release_year = :release_year WHERE id = :id");
        return $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':image_url' => $imageUrl,
            ':release_year' => $releaseYear,
            ':id' => $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
    */
}