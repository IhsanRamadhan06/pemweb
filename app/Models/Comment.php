<?php
// niflix_project/app/Models/Comment.php

class Comment {
    private $pdo;
    private $table = 'comments';

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Mengambil semua komentar untuk sebuah artikel, dengan informasi penulis.
     * @param int $articleId
     * @return array
     */
    public function getCommentsByArticleId($articleId) {
        $stmt = $this->pdo->prepare("
            SELECT
                c.id,
                c.article_id,
                c.user_id,
                c.comment_text,
                c.created_at,
                u.username AS commenter_username,
                u.foto_pengguna AS commenter_photo
            FROM
                {$this->table} c
            JOIN
                user u ON c.user_id = u.id
            WHERE
                c.article_id = :article_id
            ORDER BY
                c.created_at ASC
        ");
        $stmt->bindParam(':article_id', $articleId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Menambahkan komentar baru.
     * @param int $articleId
     * @param int $userId
     * @param string $commentText
     * @return bool
     */
    public function addComment($articleId, $userId, $commentText) {
        $stmt = $this->pdo->prepare("INSERT INTO {$this->table} (article_id, user_id, comment_text) VALUES (:article_id, :user_id, :comment_text)");
        return $stmt->execute([
            ':article_id' => $articleId,
            ':user_id' => $userId,
            ':comment_text' => $commentText
        ]);
    }

    /**
     * Menghapus komentar.
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}