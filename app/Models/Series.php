<?php
// niflix_project/app/Models/Series.php

class Series {
    private $pdo;
    private $table = 'series';

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Mengambil semua series.
     * @return array
     */
    public function getAllSeries() {
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
}