<?php
// niflix_project/app/Controllers/ReviewSeriesController.php

require_once APP_ROOT . '/app/Core/Session.php';
require_once APP_ROOT . '/app/Core/Functions.php';
require_once APP_ROOT . '/app/Models/ReviewSeries.php';
require_once APP_ROOT . '/app/Models/Series.php';

class ReviewSeriesController {

    private $pdo;
    private $reviewSeriesModel;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->reviewSeriesModel = new ReviewSeries($pdo);
    }

    public function index() {
        $reviews = $this->reviewSeriesModel->all(); 
        view('review_series/index', [
            'reviews' => $reviews,
            'title' => 'Daftar Review Series'
        ]);
    }

    public function create() {
        if (!Session::has('user')) {
            redirect('/auth/login?message=' . urlencode('Anda harus login untuk menambahkan review.') . '&type=error');
        }

        $seriesModel = new Series($this->pdo);
        $series = $seriesModel->getAllSeries();

        view('review_series/create', [
            'title' => 'Tambah Review Series',
            'series' => $series
        ]);
    }

    /**
    * Mengarahkan kembali ke halaman sebelumnya.
    */
    public function back() {
        if (isset($_SERVER['HTTP_REFERER'])) {
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit();
        } else {
            // Fallback jika halaman sebelumnya tidak tersedia
            redirect('/dashboard');
        }
    }

    public function store() {
        if (!Session::has('user')) {
            redirect('/auth/login?message=' . urlencode('Anda harus login untuk menambahkan review.') . '&type=error');
        }

        $data = [
            'series_id' => $_POST['series_id'] ?? null,
            'user_id' => Session::get('user')['id'],
            'rating' => $_POST['rating'] ?? 0,
            'review_text' => trim($_POST['review_text'] ?? '')
        ];

        if (empty($data['series_id']) || empty($data['review_text'])) {
            Session::Flash('error', 'Semua field wajib diisi');
            $this->back();
            return;
        }

        if ($this->reviewSeriesModel->create($data)) {
            Session::Flash('success', 'Review berhasil ditambahkan!');
            redirect('/review_series');
        } else {
            Session::Flash('error', 'Gagal menambahkan review');
            $this->back();
        }
    }

    public function show($id) {
        $review = $this->reviewSeriesModel->find($id); 
        if (!$review) {
            redirect('/review_series?message=' . urlencode('Review tidak ditemukan.') . '&type=error');
        }
        view('review_series/show', [
            'review' => $review, 
            'title' => 'Detail Review Series'
        ]);
    }

    public function edit($id) {
        if (!Session::has('user')) {
            redirect('/auth/login?message=' . urlencode('Anda harus login untuk mengedit review.') . '&type=error');
        }

        $review = $this->reviewSeriesModel->find($id);
        if (!$review) {
            redirect('/review_series?message=' . urlencode('Review tidak ditemukan.') . '&type=error');
        }

        $currentUser = Session::get('user');
        if ($currentUser['id'] != $review['user_id'] && $currentUser['is_admin'] != 1) {
            redirect('/review_series/show/' . $id . '?message=' . urlencode('Anda tidak memiliki izin untuk mengedit review ini.') . '&type=error');
        }

        $message = null;
        $messageType = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $rating = $_POST['rating'] ?? 0;
            $reviewText = trim($_POST['review_text'] ?? '');

            if (empty($reviewText)) {
                $message = 'Ulasan tidak boleh kosong.';
                $messageType = 'error';
            } else {
                if ($this->reviewSeriesModel->update($id, $rating, $reviewText)) {
                    $message = 'Review berhasil diperbarui!';
                    $messageType = 'success';
                    redirect('/review_series/show/' . $id . '?message=' . urlencode($message) . '&type=' . urlencode($messageType));
                    exit();
                } else {
                    $message = 'Gagal memperbarui review.';
                    $messageType = 'error';
                }
            }
        }

        view('review_series/edit', [
            'title' => 'Edit Review Series',
            'review' => $review,
            'message' => $message,
            'message_type' => $messageType
        ]);
    }

    public function delete($id) {
        if (!Session::has('user')) {
            redirect('/auth/login?message=' . urlencode('Anda harus login untuk menghapus review.') . '&type=error');
        }

        $review = $this->reviewSeriesModel->find($id);
        if (!$review) {
            redirect('/review_series?message=' . urlencode('Review tidak ditemukan.') . '&type=error');
        }

        $currentUser = Session::get('user');
        if ($currentUser['id'] != $review['user_id'] && $currentUser['is_admin'] != 1) {
            redirect('/review_series?message=' . urlencode('Anda tidak memiliki izin untuk menghapus review ini.') . '&type=error');
        }

        $message = '';
        $messageType = '';

        if ($this->reviewSeriesModel->delete($id)) {
            $message = 'Review berhasil dihapus!';
            $messageType = 'success';
        } else {
            $message = 'Gagal menghapus review.';
            $messageType = 'error';
        }
        redirect('/review_series?message=' . urlencode($message) . '&type=' . urlencode($messageType));
    }
}