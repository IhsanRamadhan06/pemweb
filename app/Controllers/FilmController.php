<?php
// niflix_project/app/Controllers/FilmsController.php

require_once APP_ROOT . '/app/Core/Session.php';
require_once APP_ROOT . '/app/Core/Functions.php';
require_once APP_ROOT . '/app/Models/Film.php';

class FilmController {
    private $pdo;
    private $filmModel;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->filmModel = new Film($pdo);
    }

    /**
     * Memastikan hanya admin yang bisa mengakses fungsi tertentu.
     */
    private function checkAdminAccess() {
        if (!Session::has('user') || Session::get('user')['is_admin'] != 1) {
            redirect('/dashboard?message=' . urlencode('Anda tidak memiliki izin untuk mengakses halaman ini.') . '&type=error');
        }
    }

    /**
     * Menampilkan daftar semua film.
     */
    public function index() {
        // Pastikan pengguna sudah login
        if (!Session::has('user')) {
            redirect('/auth/login');
        }

        $currentUserId = Session::get('user')['id']; // Ambil ID pengguna yang sedang login

        // Ambil film populer (ID 1-8)
        $popularFilm = $this->filmModel->getPopularFilm(); // Teruskan userId
        // Ambil semua film
        $allFilm = $this->filmModel->getAllFilm($currentUserId); // Teruskan userId

        // Tangani pesan dari parameter URL
        $message = $_GET['message'] ?? null;
        $messageType = $_GET['type'] ?? null;

        view('Film/index', [
            'popularFilm' => $popularFilm, // Data untuk bagian slider (populer)
            'allFilm' => $allFilm,       // Data untuk bagian grid (semua)
            'title' => 'Daftar Film',     // Judul utama halaman (diperbarui)
            'message' => $message,
            'message_type' => $messageType
        ]);
    }

    public function toggleLikeAjax() {
        header('Content-Type: application/json'); // Pastikan respons dalam format JSON

        if (!Session::has('user')) { // Pastikan pengguna sudah login
            echo json_encode(['success' => false, 'message' => 'Anda harus login untuk menyukai film.', 'redirect' => '/auth/login']);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Metode request tidak valid.']);
            exit();
        }

        $filmId = filter_var($_POST['film_id'] ?? null, FILTER_VALIDATE_INT);
        $userId = Session::get('user')['id'];

        if (!$filmId) {
            echo json_encode(['success' => false, 'message' => 'ID film tidak valid.']);
            exit();
        }

        $result = $this->filmModel->toggleLike($userId, $filmId);

        if ($result !== false) {
            echo json_encode([
                'success' => true,
                'total_likes' => $result['total_likes'],
                'is_liked_by_user' => $result['is_liked_by_user'],
                'message' => $result['is_liked_by_user'] ? 'Film disukai.' : 'Film tidak disukai.'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal memperbarui status like.']);
        }
        exit();
    }

    /**
     * Menampilkan detail film tunggal.
     * Ini opsional jika Anda ingin halaman detail untuk setiap film.
     * @param int $id ID film
     */
    public function show($id) {
        $film = $this->filmModel->findById($id);

        if (!$film) {
            redirect('/daftar_film?message=' . urlencode('Film tidak ditemukan.') . '&type=error');
        }

        view('film/show', [
            'film' => $film,
            'title' => $film['title']
        ]);
    }

        /**
     * Menampilkan formulir untuk membuat film baru atau memproses submission.
     */
    public function create() {
        $this->checkAdminAccess(); // Hanya admin yang bisa mengakses

        $message = null;
        $messageType = null;
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $releaseYear = $_POST['release_year'] ?? '';
        $imageUrl = $_POST['image_url'] ?? '';
        // Initialize $is_popular with a default value for GET requests
        $is_popular = 0; // Default to not popular (0)

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $releaseYear = filter_var($_POST['release_year'] ?? '', FILTER_VALIDATE_INT);
            $imageUrl = trim($_POST['image_url'] ?? '');
            // $is_popular_str = $_POST['is_popular'] ?? 'NO'; // Re-capture for POST
            // $is_popular = ($is_popular_str === 'YES') ? 1 : 0; // Convert to integer (0 or 1)
            // Capture integer from POST directly
            $is_popular = filter_var($_POST['is_popular'] ?? 0, FILTER_VALIDATE_INT);
            // Pastikan nilai hanya 0 atau 1
            $is_popular = ($is_popular === 1) ? 1 : 0;
            $creatorId = Session::get('user')['id']; // AMBIL ID PENGGUNA DARI SESI

            if (empty($title) || empty($description) || empty($releaseYear)) {
                $message = 'Judul, deskripsi, dan tahun rilis tidak boleh kosong.';
                $messageType = 'error';
            } elseif ($releaseYear === false || $releaseYear <= 0) {
                $message = 'Tahun rilis harus berupa angka valid.';
                $messageType = 'error';
            } else {
                // Pass the converted integer value for is_popular and creatorId
                if ($this->filmModel->create($title, $description, $releaseYear, $imageUrl, $is_popular, $creatorId)) {
                    $message = 'Film berhasil ditambahkan!';
                    $messageType = 'success';
                    redirect('/daftar_film?message=' . urlencode($message) . '&type=' . urlencode($messageType));
                    exit();
                } else {
                    $message = 'Gagal menambahkan film.';
                    $messageType = 'error';
                }
            }
        }

        
        view('film/create', [
            'title' => 'Tambah Film Baru',
            'message' => $message,
            'message_type' => $messageType,
            'film' => [
                'title' => $title,
                'description' => $description,
                'release_year' => $releaseYear,
                'image_url' => $imageUrl,
                'is_popular' => $is_popular
            ]
        ]);
    }

    /**
     * Endpoint AJAX untuk validasi field (misal: tahun rilis).
     */
    public function validateFieldAjax() {
        header('Content-Type: application/json');
        $field = $_POST['field'] ?? '';
        $value = $_POST['value'] ?? '';
        $isValid = true;
        $errorMessage = '';

        switch ($field) {
            case 'release_year':
                $year = filter_var($value, FILTER_VALIDATE_INT);
                if ($value === '') { // Check for empty string specifically
                    $isValid = false;
                    $errorMessage = 'Tahun rilis tidak boleh kosong.';
                } elseif ($year === false || $year <= 0) {
                    $isValid = false;
                    $errorMessage = 'Tahun rilis harus berupa angka valid.';
                } elseif ($year > date('Y')) {
                    $isValid = false;
                    $errorMessage = 'Tahun rilis tidak boleh lebih dari tahun sekarang (' . date('Y') . ').';
                } elseif ($year < 1888) { // Optional: minimum year
                    $isValid = false;
                    $errorMessage = 'Tahun rilis terlalu lama.';
                }
                break;
            case 'title':
                if (empty($value)) {
                    $isValid = false;
                    $errorMessage = 'Judul film tidak boleh kosong.';
                }
                // You can add uniqueness check here if necessary
                break;
            case 'description':
                if (empty($value)) {
                    $isValid = false;
                    $errorMessage = 'Deskripsi film tidak boleh kosong.';
                }
                break;
            case 'image_url':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
                    $isValid = false;
                    $errorMessage = 'Format URL gambar tidak valid.';
                }
                break;
        }

        echo json_encode(['isValid' => $isValid, 'message' => $errorMessage]);
        exit();
    }

    /**
     * Menampilkan formulir untuk mengedit film yang sudah ada atau memproses submission.
     * @param int $id ID film
     */
    public function edit($id) {
        $this->checkAdminAccess(); // Hanya admin yang bisa mengakses

        $film = $this->filmModel->findById($id);

        if (!$film) {
            redirect('/daftar_film?message=' . urlencode('Film tidak ditemukan.') . '&type=error');
        }

        $message = null;
        $messageType = null;
        $error = []; // Array untuk menyimpan pesan error validasi

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $releaseYear = filter_var($_POST['release_year'] ?? '', FILTER_VALIDATE_INT);
            $imageUrl = trim($_POST['image_url'] ?? '');
            // $is_popular_str = $_POST['is_popular'] ?? 'NO'; // Capture string from POST
            // $is_popular = ($is_popular_str === 'YES') ? 1 : 0; // Convert to integer
            // Capture integer from POST directly
            $is_popular = filter_var($_POST['is_popular'] ?? 0, FILTER_VALIDATE_INT);
            // Pastikan nilai hanya 0 atau 1
            $is_popular = ($is_popular === 1) ? 1 : 0;
            $editorId = Session::get('user')['id'];

            // Server-side validation
            if (empty($title)) {
                $error['title'] = 'Judul film tidak boleh kosong.';
            } elseif (strlen($title) < 3) {
                $error['title'] = 'Judul film terlalu pendek.';
            }

            if (empty($description)) {
                $error['description'] = 'Deskripsi film tidak boleh kosong.';
            }

            if ($releaseYear === false || $releaseYear <= 0) {
                $error['release_year'] = 'Tahun rilis harus berupa angka valid.';
            } elseif ($releaseYear < 1888 || $releaseYear > date('Y') + 5) {
                $error['release_year'] = 'Tahun rilis tidak valid (contoh: 1888 - ' . (date('Y') + 5) . ').';
            }

            if (!empty($imageUrl) && !filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                $error['image_url'] = 'Format URL gambar tidak valid.';
            }

            // Jika tidak ada error validasi
            if (empty($error)) {
                if ($this->filmModel->update($id, $title, $description, $releaseYear, $imageUrl, $is_popular, $editorId)) {
                    $message = 'Film berhasil diperbarui!';
                    $messageType = 'success';
                    // $film = $this->filmModel->findById($id); // Refresh data film
                    // redirect('/daftar_film/show/' . $id . '?message=' . urlencode($message) . '&type=' . urlencode($messageType));
                    // exit();
                    redirect('/daftar_film?message=' . urlencode($message) . '&type=' . urlencode($messageType));
                    exit();
                } else {
                    $message = 'Gagal memperbarui film.';
                    $messageType = 'error';
                }
            } else {
                // Jika ada error, tampilkan pesan error umum dan biarkan error array yang mengisi form
                $message = 'Mohon perbaiki kesalahan dalam formulir.';
                $messageType = 'error';
            }
        }

        // Ambil pesan dari URL parameter jika ada (setelah redirect sukses/gagal)
        if (isset($_GET['message'])) {
            $message = $_GET['message'];
            $messageType = $_GET['type'] ?? 'info';
        }

        view('film/edit', [
            'title' => 'Edit film',
            'film' => $film,
            'message' => $message,
            'message_type' => $messageType,
            'error' => $error // Teruskan array error ke view
        ]);
    }

    /**
     * Menghapus film.
     * @param int $id ID film yang akan dihapus
     */
    public function delete($id) {
        $this->checkAdminAccess(); // Hanya admin yang bisa mengakses

        $film = $this->filmModel->findById($id);

        if (!$film) {
            redirect('/daftar_film?message=' . urlencode('Film tidak ditemukan.') . '&type=error');
        }

        $message = '';
        $messageType = '';

        if ($this->filmModel->delete($id)) {
            $message = 'Film berhasil dihapus!';
            $messageType = 'success';
        } else {
            $message = 'Gagal menghapus film.';
            $messageType = 'error';
        }
        redirect('/daftar_film?message=' . urlencode($message) . '&type=' . urlencode($messageType));
    }
}