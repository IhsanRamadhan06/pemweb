<?php
// niflix_project/app/Controllers/ArticleController.php

//
require_once APP_ROOT . '/app/Core/Session.php';
require_once APP_ROOT . '/app/Core/Functions.php';
require_once APP_ROOT . '/app/Models/Article.php';
require_once APP_ROOT . '/app/Models/CommentRating.php'; // Renamed model

class ArticleController {
    private $pdo;
    private $articleModel;
    private $commentRatingModel; // Renamed property

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->articleModel = new Article($pdo);
        $this->commentRatingModel = new CommentRating($pdo); // Instantiate renamed model
    }

    /**
     * Menampilkan daftar semua artikel.
     */
    public function index() {
        // Pastikan pengguna sudah login
        if (!Session::has('user')) {
            redirect('/auth/login');
        }

        $articles = $this->articleModel->getAllArticles();
        view('articles/index', [
            'articles' => $articles,
            'title' => 'Daftar Artikel'
        ]);
    }

    /**
     * Menampilkan detail artikel tunggal dan komentar-komentarnya.
     * @param int $id ID artikel
     */
    public function show($id) {
        if (!Session::has('user')) {
            redirect('/auth/login');
        }

        $article = $this->articleModel->findById($id);

        if (!$article) {
            redirect('/articles?message=' . urlencode('Artikel tidak ditemukan.') . '&type=error');
        }

        // Get comments for the article using the generalized model
        $comments = $this->commentRatingModel->getAllEntriesByItem($id, 'article'); // Only get pure comments

        // Tangani penambahan komentar jika ada POST request
        $message = null;
        $messageType = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $commentText = trim($_POST['comment_text'] ?? '');
            $userId = Session::get('user')['id'];
            // Menggunakan FILTER_NULL_ON_FAILURE untuk memastikan $parentCommentId adalah NULL jika tidak valid
            $parentCommentId = filter_var($_POST['parent_comment_id'] ?? null, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);

            if (empty($commentText)) {
                $message = 'Komentar tidak boleh kosong!';
                $messageType = 'error';
            } else {
                // Use the generalized addEntry method with item_type 'article'
                if ($this->commentRatingModel->addEntry($id, 'article', $userId, $commentText, $parentCommentId, null)) { // null for rating_value
                    $message = 'Komentar berhasil ditambahkan.';
                    $messageType = 'success';
                } else {
                    $message = 'Gagal menambahkan komentar.';
                    $messageType = 'error';
                }
            }
            redirect('/articles/show/' . $id . '?message=' . urlencode($message) . '&type=' . urlencode($messageType));
            exit();
        }

        // Ambil pesan dari URL parameter jika ada
        if (isset($_GET['message'])) {
            $message = $_GET['message'];
            $messageType = $_GET['type'] ?? 'info';
        }


        view('articles/show', [
            'article' => $article,
            'comments' => $comments,
            'title' => $article['title'],
            'message' => $message,
            'message_type' => $messageType
        ]);
    }

    /**
     * Menampilkan formulir untuk membuat artikel baru.
     */
    public function create() {
        // Hanya pengguna yang login yang bisa membuat artikel
        if (!Session::has('user')) {
            redirect('/auth/login?message=' . urlencode('Anda harus login untuk membuat artikel.') . '&type=error');
        }

        $message = null;
        $messageType = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title'] ?? '');
            $content = trim($_POST['content'] ?? '');
            $userId = Session::get('user')['id'];

            if (empty($title) || empty($content)) {
                $message = 'Judul dan konten tidak boleh kosong.';
                $messageType = 'error';
            } else {
                try { // Tambahkan blok try-catch di sini
                    if ($this->articleModel->create($userId, $title, $content)) {
                        $message = 'Artikel berhasil dipublikasikan!';
                        $messageType = 'success';
                        redirect('/articles?message=' . urlencode($message) . '&type=' . urlencode($messageType));
                        exit();
                    } else {
                        $message = 'Gagal mempublikasikan artikel.';
                        $messageType = 'error';
                    }
                } catch (PDOException $e) { // Tangkap PDOException
                    if ($e->getCode() == '23000') { // Kode SQLSTATE untuk integrity constraint violation
                        $message = 'Judul artikel sudah ada. Mohon gunakan judul lain.';
                        $messageType = 'error';
                    } else {
                        $message = 'Terjadi kesalahan basis data: ' . $e->getMessage();
                        $messageType = 'error';
                    }
                }
            }
        }
        view('articles/create', [
            'title' => 'Buat Artikel Baru',
            'message' => $message,
            'message_type' => $messageType,
            // Pertahankan nilai input yang dikirimkan agar tidak hilang saat ada error
            'article' => [
                'title' => $_POST['title'] ?? '',
                'content' => $_POST['content'] ?? ''
            ]
        ]);
    }

    /**
     * Menampilkan formulir untuk mengedit artikel yang sudah ada.
     * @param int $id ID artikel
     */
    public function edit($id) {
        if (!Session::has('user')) {
            redirect('/auth/login?message=' . urlencode('Anda harus login untuk mengedit artikel.') . '&type=error');
        }

        $article = $this->articleModel->findById($id);

        if (!$article) {
            redirect('/articles?message=' . urlencode('Artikel tidak ditemukan.') . '&type=error');
        }

        // Pastikan hanya penulis atau admin yang bisa mengedit
        $currentUser = Session::get('user');
        if ($currentUser['id'] != $article['user_id'] && $currentUser['is_admin'] != 1) {
            redirect('/articles/show/' . $id . '?message=' . urlencode('Anda tidak memiliki izin untuk mengedit artikel ini.') . '&type=error');
        }

        $message = null;
        $messageType = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title'] ?? '');
            $content = trim($_POST['content'] ?? '');

            if (empty($title) || empty($content)) {
                $message = 'Judul dan konten tidak boleh kosong.';
                $messageType = 'error';
            } else {
                try { // Tambahkan blok try-catch di sini
                    if ($this->articleModel->update($id, $title, $content)) {
                        $message = 'Artikel berhasil diperbarui!';
                        $messageType = 'success';
                        redirect('/articles/show/' . $id . '?message=' . urlencode($message) . '&type=' . urlencode($messageType));
                        exit();
                    } else {
                        $message = 'Gagal memperbarui artikel.';
                        $messageType = 'error';
                    }
                } catch (PDOException $e) { // Tangkap PDOException
                    if ($e->getCode() == '23000') { // Kode SQLSTATE untuk integrity constraint violation
                        $message = 'Judul artikel sudah ada. Mohon gunakan judul lain.';
                        $messageType = 'error';
                    } else {
                        $message = 'Terjadi kesalahan basis data: ' . $e->getMessage();
                        $messageType = 'error';
                    }
                }
            }
        }
        view('articles/edit', [
            'title' => 'Edit Artikel',
            'article' => $article, // Pastikan ini tetap ada untuk mengisi form
            'message' => $message,
            'message_type' => $messageType
        ]);
    }

    /**
     * Menghapus artikel.
     * @param int $id ID artikel yang akan dihapus
     */
    public function delete($id) {
        if (!Session::has('user')) {
            redirect('/auth/login?message=' . urlencode('Anda harus login untuk menghapus artikel.') . '&type=error');
        }

        $article = $this->articleModel->findById($id);

        if (!$article) {
            redirect('/articles?message=' . urlencode('Artikel tidak ditemukan.') . '&type=error');
        }

        // Pastikan hanya penulis atau admin yang bisa menghapus
        $currentUser = Session::get('user');
        if ($currentUser['id'] != $article['user_id'] && $currentUser['is_admin'] != 1) {
            redirect('/articles?message=' . urlencode('Anda tidak memiliki izin untuk menghapus artikel ini.') . '&type=error');
        }

        $message = '';
        $messageType = '';

        if ($this->articleModel->delete($id)) {
            $message = 'Artikel berhasil dihapus!';
            $messageType = 'success';
        } else {
            $message = 'Gagal menghapus artikel.';
            $messageType = 'error';
        }
        redirect('/articles?message=' . urlencode($message) . '&type=' . urlencode($messageType));
    }
}