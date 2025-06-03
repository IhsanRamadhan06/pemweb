<?php
// niflix_project/app/Controllers/CommentController.php

require_once APP_ROOT . '/app/Core/Session.php';
require_once APP_ROOT . '/app/Core/Functions.php';
require_once APP_ROOT . '/app/Models/Comment.php';
require_once APP_ROOT . '/app/Models/Article.php'; // Untuk redirect kembali ke artikel

class CommentController {
    private $pdo;
    private $commentModel;
    private $articleModel; // Untuk memverifikasi kepemilikan komentar/artikel

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->commentModel = new Comment($pdo);
        $this->articleModel = new Article($pdo); // Instance article model
    }

    /**
     * Menghapus komentar.
     * @param int $id ID komentar yang akan dihapus
     */
    public function delete($id) {
        if (!Session::has('user')) {
            redirect('/auth/login?message=' . urlencode('Anda harus login untuk menghapus komentar.') . '&type=error');
        }

        // Ambil komentar untuk mendapatkan article_id dan user_id_komentar
        $stmt = $this->pdo->prepare("SELECT article_id, user_id FROM comments WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $comment = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$comment) {
            redirect('/dashboard?message=' . urlencode('Komentar tidak ditemukan.') . '&type=error');
        }

        // Ambil artikel untuk mendapatkan user_id_artikel
        $article = $this->articleModel->findById($comment['article_id']);

        $message = '';
        $messageType = '';
        $currentUser = Session::get('user');

        // Hanya penulis komentar, penulis artikel, atau admin yang bisa menghapus komentar
        if ($currentUser['id'] == $comment['user_id'] || $currentUser['id'] == $article['user_id'] || $currentUser['is_admin'] == 1) {
            if ($this->commentModel->delete($id)) {
                $message = 'Komentar berhasil dihapus.';
                $messageType = 'success';
            } else {
                $message = 'Gagal menghapus komentar.';
                $messageType = 'error';
            }
        } else {
            $message = 'Anda tidak memiliki izin untuk menghapus komentar ini.';
            $messageType = 'error';
        }

        // Redirect kembali ke halaman detail artikel
        redirect('/articles/show/' . $comment['article_id'] . '?message=' . urlencode($message) . '&type=' . urlencode($messageType));
    }
}