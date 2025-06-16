<?php
// niflix_project/app/Controllers/CommentRatingController.php

require_once APP_ROOT . '/app/Core/Session.php';
require_once APP_ROOT . '/app/Core/Functions.php';
require_once APP_ROOT . '/app/Models/Film.php';
require_once APP_ROOT . '/app/Models/Series.php';
require_once APP_ROOT . '/app/Models/CommentRating.php';

class CommentRatingController {
    private $pdo;
    private $filmModel;
    private $seriesModel;
    private $commentRatingModel;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->filmModel = new Film($pdo);
        $this->seriesModel = new Series($pdo);
        $this->commentRatingModel = new CommentRating($pdo);

        if (!Session::has('user')) {
            redirect('/auth/login?message=' . urlencode('Anda harus login untuk mengakses halaman Komentar & Rating.') . '&type=error');
        }
    }

    public function index() {
        $films = $this->filmModel->getAllFilms();
        $series = $this->seriesModel->getAllSeries();

        $message = $_GET['message'] ?? Session::getFlash('message');
        $messageType = $_GET['type'] ?? Session::getFlash('message_type');

        view('komentar_rating/index', [
            'films' => $films,
            'series' => $series,
            'title' => 'Komentar & Rating',
            'message' => $message,
            'message_type' => $messageType
        ]);
    }

    public function detail($itemType, $itemId) {
        $item = null;
        $allEntries = []; // Akan menampung komentar dan rating
        $userReviewEntry = null; // Akan menampung ulasan (rating + komentar) dari user yang sedang login
        $isLiked = false;
        $currentUserId = Session::get('user')['id'];

        if ($itemType === 'film') {
            $item = $this->filmModel->findById($itemId);
            if ($item) {
                // Mengambil semua komentar dan rating sekaligus
                $allEntries = $this->commentRatingModel->getAllEntriesByItem($itemId, 'film');
                $userReviewEntry = $this->commentRatingModel->findUserRating($currentUserId, $itemId, 'film'); // Untuk form ulasan pengguna
                $isLiked = $this->filmModel->hasUserLiked($currentUserId, $itemId);
            }
        } elseif ($itemType === 'series') {
            $item = $this->seriesModel->findById($itemId);
            if ($item) {
                // Mengambil semua komentar dan rating sekaligus
                $allEntries = $this->commentRatingModel->getAllEntriesByItem($itemId, 'series');
                $userReviewEntry = $this->commentRatingModel->findUserRating($currentUserId, $itemId, 'series'); // Untuk form ulasan pengguna
                $isLiked = $this->seriesModel->hasUserLiked($currentUserId, $itemId);
            }
        } else {
            redirect('/dashboard?message=' . urlencode('Tipe item tidak didukung di sini.') . '&type=error');
        }

        if (!$item) {
            redirect('/komentar_rating?message=' . urlencode('Film/Series tidak ditemukan.') . '&type=error');
        }

        $message = null;
        $messageType = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            if ($action === 'submit_comment_review') { // Handle submission of combined comment/review
                $commentText = trim($_POST['comment_text'] ?? '');
                $ratingValue = filter_var($_POST['rating_value'] ?? null, FILTER_VALIDATE_INT);
                $userId = Session::get('user')['id'];
                $parentCommentId = filter_var($_POST['parent_comment_id'] ?? null, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);

                // Validasi input
                if (empty($commentText) && ($ratingValue === false || $ratingValue < 1 || $ratingValue > 10)) {
                    $message = 'Komentar atau rating harus diisi, dan rating harus antara 1-10!';
                    $messageType = 'error';
                } else if (!empty($commentText) && ($ratingValue !== false && ($ratingValue < 1 || $ratingValue > 10))) {
                     $message = 'Rating harus antara 1-10!';
                     $messageType = 'error';
                } else {
                    // Cek apakah ini update rating atau penambahan baru
                    // Jika ada ratingValue dan ini bukan balasan komentar, cari rating yang sudah ada dari user ini.
                    if ($ratingValue !== null && $parentCommentId === null) {
                         $existingUserRating = $this->commentRatingModel->findUserRating($userId, $itemId, $itemType);
                        if ($existingUserRating) {
                            if ($this->commentRatingModel->updateEntry($existingUserRating['id'], $commentText, $ratingValue)) {
                                $message = 'Ulasan dan rating berhasil diperbarui!';
                                $messageType = 'success';
                            } else {
                                $message = 'Gagal memperbarui ulasan dan rating.';
                                $messageType = 'error';
                            }
                        } else {
                            // Ini adalah ulasan baru (rating + komentar)
                            if ($this->commentRatingModel->addEntry($itemId, $itemType, $userId, $commentText, null, $ratingValue)) {
                                $message = 'Ulasan dan rating berhasil ditambahkan!';
                                $messageType = 'success';
                            } else {
                                $message = 'Gagal menambahkan ulasan dan rating.';
                                $messageType = 'error';
                            }
                        }
                    } else {
                        // Ini adalah komentar biasa (bisa jadi balasan)
                        if ($this->commentRatingModel->addEntry($itemId, $itemType, $userId, $commentText, $parentCommentId, null)) {
                            $message = 'Komentar berhasil ditambahkan.';
                            $messageType = 'success';
                        } else {
                            $message = 'Gagal menambahkan komentar.';
                            $messageType = 'error';
                        }
                    }
                }
                redirect('/komentar_rating/detail/' . $itemType . '/' . $itemId . '?message=' . urlencode($message) . '&type=' . urlencode($messageType));
                exit();

            } elseif ($action === 'toggle_like') {
                if ($itemType === 'film') {
                    $this->filmModel->toggleLike($currentUserId, $itemId);
                } elseif ($itemType === 'series') {
                    $this->seriesModel->toggleLike($currentUserId, $itemId);
                }
                redirect('/komentar_rating/detail/' . $itemType . '/' . $itemId);
                exit();
            } elseif ($action === 'toggle_comment_like') {
                $commentId = filter_var($_POST['comment_id'] ?? null, FILTER_VALIDATE_INT);
                if ($commentId) {
                    if ($this->commentRatingModel->toggleLike($currentUserId, $commentId)) {
                        // Success, no specific message needed, just refresh
                    } else {
                        // Error toggling like, handle as needed
                    }
                }
                redirect('/komentar_rating/detail/' . $itemType . '/' . $itemId);
                exit();
            }
        }

        // Re-fetch all entries after potential submission to display the new ones
        $allEntries = $this->commentRatingModel->getAllEntriesByItem($itemId, $itemType);
        $userReviewEntry = $this->commentRatingModel->findUserRating($currentUserId, $itemId, $itemType); // Re-fetch user's review/rating

        if (isset($_GET['message'])) {
            $message = $_GET['message'];
            $messageType = $_GET['type'] ?? 'info';
        }


        view('komentar_rating/detail', [
            'item' => $item,
            'item_type' => $itemType,
            'allEntries' => $allEntries, // Mengirim semua entri
            'title' => $item['title'],
            'message' => $message,
            'message_type' => $messageType,
            'userReviewEntry' => $userReviewEntry, // Ulasan (rating+komentar) user yang sedang login
            'isLiked' => $isLiked,
            'pdo' => $this->pdo // Kirim objek PDO agar bisa digunakan di view (jika perlu)
        ]);
    }

    public function deleteEntry($entryId) {
        if (!Session::has('user')) {
            redirect('/auth/login?message=' . urlencode('Anda harus login untuk menghapus entri ini.') . '&type=error');
        }

        $entry = $this->commentRatingModel->findById($entryId);

        if (!$entry) {
            redirect('/komentar_rating?message=' . urlencode('Entri tidak ditemukan.') . '&type=error');
        }

        $currentUser = Session::get('user');
        // Hanya penulis entri, atau admin yang bisa menghapus
        if ($currentUser['id'] != $entry['user_id'] && $currentUser['is_admin'] != 1) {
            redirect('/komentar_rating/detail/' . $entry['item_type'] . '/' . $entry['item_id'] . '?message=' . urlencode('Anda tidak memiliki izin untuk menghapus entri ini.') . '&type=error');
        }

        $message = '';
        $messageType = '';

        // Capture item_type and item_id before deletion for redirect
        $redirectItemType = $entry['item_type'];
        $redirectItemId = $entry['item_id'];

        if ($this->commentRatingModel->delete($entryId)) {
            $message = 'Entri berhasil dihapus!';
            $messageType = 'success';
        } else {
            $message = 'Gagal menghapus entri.';
            $messageType = 'error';
        }

        // --- Perubahan di sini: Arahkan ke halaman artikel jika item_type adalah 'article' ---
        if ($redirectItemType === 'article') {
            redirect('/articles/show/' . $redirectItemId . '?message=' . urlencode($message) . '&type=' . urlencode($messageType));
        } else {
            redirect('/komentar_rating/detail/' . $redirectItemType . '/' . $redirectItemId . '?message=' . urlencode($message) . '&type=' . urlencode($messageType));
        }
    }
}
