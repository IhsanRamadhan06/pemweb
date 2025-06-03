<?php
// niflix_project/app/Models/Article.php

class Article {
    private $pdo;
    private $table = 'articles';

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Mengambil semua artikel, dengan informasi penulis.
     * @return array
     */
    public function getAllArticles() {
        $stmt = $this->pdo->prepare("
            SELECT
                a.id,
                a.user_id,
                a.title,
                a.content,
                a.created_at,
                a.updated_at,
                u.username AS author_username,
                u.nama_lengkap AS author_fullname
            FROM
                {$this->table} a
            JOIN
                user u ON a.user_id = u.id
            ORDER BY
                a.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Menemukan artikel berdasarkan ID, dengan informasi penulis.
     * @param int $id
     * @return array|false
     */
    public function findById($id) {
        $stmt = $this->pdo->prepare("
            SELECT
                a.id,
                a.user_id,
                a.title,
                a.content,
                a.created_at,
                a.updated_at,
                u.username AS author_username,
                u.nama_lengkap AS author_fullname,
                u.foto_pengguna AS author_photo
            FROM
                {$this->table} a
            JOIN
                user u ON a.user_id = u.id
            WHERE
                a.id = :id
        ");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Membuat artikel baru.
     * @param int $userId
     * @param string $title
     * @param string $content
     * @return bool
     */
    public function create($userId, $title, $content) {
        $stmt = $this->pdo->prepare("INSERT INTO {$this->table} (user_id, title, content) VALUES (:user_id, :title, :content)");
        return $stmt->execute([
            ':user_id' => $userId,
            ':title' => $title,
            ':content' => $content
        ]);
    }

    /**
     * Memperbarui artikel.
     * @param int $id
     * @param string $title
     * @param string $content
     * @return bool
     */
    public function update($id, $title, $content) {
        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET title = :title, content = :content WHERE id = :id");
        return $stmt->execute([
            ':title' => $title,
            ':content' => $content,
            ':id' => $id
        ]);
    }

    /**
     * Menghapus artikel.
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}