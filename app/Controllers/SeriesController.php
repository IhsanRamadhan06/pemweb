<?php
// niflix_project/app/Controllers/SeriesController.php

require_once APP_ROOT . '/app/Core/Session.php'; //
require_once APP_ROOT . '/app/Core/Functions.php'; //
require_once APP_ROOT . '/app/Models/Series.php'; //

class SeriesController {
    private $pdo;
    private $seriesModel;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->seriesModel = new Series($pdo);
    }

    /**
     * Memastikan hanya admin yang bisa mengakses fungsi tertentu.
     */
    private function checkAdminAccess() {
        if (!Session::has('user') || Session::get('user')['is_admin'] != 1) { //
            redirect('/dashboard?message=' . urlencode('Anda tidak memiliki izin untuk mengakses halaman ini.') . '&type=error'); //
        }
    }

    /**
     * Menampilkan daftar semua series (popular dan semua).
     */
    public function index() {
        // Pastikan pengguna sudah login
        if (!Session::has('user')) { //
            redirect('/auth/login'); //
        }

        $currentUserId = Session::get('user')['id']; // Ambil ID pengguna yang sedang login

        // Ambil series populer (ID 1-8)
        $popularSeries = $this->seriesModel->getSeriesByIdRange();
        // Ambil semua series
        $allSeries = $this->seriesModel->getAllSeries($currentUserId);

        // Tangani pesan dari parameter URL
        $message = $_GET['message'] ?? null;
        $messageType = $_GET['type'] ?? null;

        view('series/index', [
            'popularSeries' => $popularSeries, // Data untuk bagian slider (populer)
            'allSeries' => $allSeries,       // Data untuk bagian grid (semua)
            'title' => 'Daftar Series',     // Judul utama halaman (diperbarui)
            'message' => $message,
            'message_type' => $messageType
        ]);
    }

    /**
     * Endpoint AJAX untuk toggle like pada series.
     */
    public function toggleLikeAjax() {
        header('Content-Type: application/json'); // Pastikan respons dalam format JSON

        if (!Session::has('user')) { // Pastikan pengguna sudah login
            echo json_encode(['success' => false, 'message' => 'Anda harus login untuk menyukai series.', 'redirect' => '/auth/login']);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Metode request tidak valid.']);
            exit();
        }

        $seriesId = filter_var($_POST['series_id'] ?? null, FILTER_VALIDATE_INT);
        $userId = Session::get('user')['id']; //

        if (!$seriesId) {
            echo json_encode(['success' => false, 'message' => 'ID series tidak valid.']);
            exit();
        }

        $result = $this->seriesModel->toggleLike($userId, $seriesId);

        if ($result !== false) {
            echo json_encode([
                'success' => true,
                'total_likes' => $result['total_likes'],
                'is_liked_by_user' => $result['is_liked_by_user'],
                'message' => $result['is_liked_by_user'] ? 'Series disukai.' : 'Series tidak disukai.'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal memperbarui status like.']);
        }
        exit();
    }

    /**
     * Menampilkan detail series tunggal.
     * @param int $id ID series
     */
    public function show($id) {
        $series = $this->seriesModel->findById($id);

        if (!$series) {
            redirect('/daftar_series?message=' . urlencode('Series tidak ditemukan.') . '&type=error'); //
        }

        view('series/show', [
            'series' => $series,
            'title' => $series['title']
        ]);
    }

    /**
     * Menampilkan formulir untuk membuat series baru atau memproses submission.
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
        $error = []; // Initialize error array

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

            // Server-side validation
            if (empty($title)) {
                $error['title'] = 'Judul series tidak boleh kosong.';
            }

            if (empty($description)) {
                $error['description'] = 'Deskripsi series tidak boleh kosong.';
            }

            // Validation for release year
            if ($releaseYear === false || $releaseYear <= 0) {
                $error['release_year'] = 'Tahun rilis harus berupa angka valid.';
            } elseif ($releaseYear > date('Y')) { // Check if year is in the future
                $error['release_year'] = 'Tahun rilis tidak boleh lebih dari tahun sekarang (' . date('Y') . ').';
            } elseif ($releaseYear < 1888) { // Optional: minimum year
                $error['release_year'] = 'Tahun rilis terlalu lama.';
            }

            if (!empty($imageUrl) && !filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                $error['image_url'] = 'Format URL gambar tidak valid.';
            }

            if (empty($error)) {
                // Pass the converted integer value for is_popular and creatorId
                if ($this->seriesModel->create($title, $description, $releaseYear, $imageUrl, $is_popular, $creatorId)) {
                    $message = 'Series berhasil ditambahkan!';
                    $messageType = 'success';
                    redirect('/daftar_series?message=' . urlencode($message) . '&type=' . urlencode($messageType)); //
                    exit();
                } else {
                    $message = 'Gagal menambahkan series.';
                    $messageType = 'error';
                }
            } else {
                $message = 'Mohon perbaiki kesalahan dalam formulir.';
                $messageType = 'error';
            }
        }

        view('series/create', [
            'title' => 'Tambah Series Baru',
            'message' => $message,
            'message_type' => $messageType,
            'series' => [
                'title' => $title,
                'description' => $description,
                'release_year' => $releaseYear,
                'image_url' => $imageUrl,
                'is_popular' => $is_popular // Pass the value back for sticky form
            ],
            'error' => $error // Pass the error array to the view
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
                    $errorMessage = 'Judul series tidak boleh kosong.';
                }
                // You can add uniqueness check here if necessary
                break;
            case 'description':
                if (empty($value)) {
                    $isValid = false;
                    $errorMessage = 'Deskripsi series tidak boleh kosong.';
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
     * Menampilkan formulir untuk mengedit series yang sudah ada atau memproses submission.
     * @param int $id ID series
     */
    public function edit($id) {
        $this->checkAdminAccess(); // Hanya admin yang bisa mengakses

        $series = $this->seriesModel->findById($id);

        if (!$series) {
            redirect('/daftar_series?message=' . urlencode('Series tidak ditemukan.') . '&type=error'); //
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
            $editorId = Session::get('user')['id']; //

            // Server-side validation
            if (empty($title)) {
                $error['title'] = 'Judul series tidak boleh kosong.';
            } elseif (strlen($title) < 3) {
                $error['title'] = 'Judul series terlalu pendek.';
            }

            if (empty($description)) {
                $error['description'] = 'Deskripsi series tidak boleh kosong.';
            }

            if ($releaseYear === false || $releaseYear <= 0) {
                $error['release_year'] = 'Tahun rilis harus berupa angka valid.';
            } elseif ($releaseYear > date('Y')) { // Added year validation
                $error['release_year'] = 'Tahun rilis tidak boleh lebih dari tahun sekarang (' . date('Y') . ').';
            } elseif ($releaseYear < 1888) { // Optional: minimum year
                $error['release_year'] = 'Tahun rilis tidak valid (minimal 1888).';
            }

            if (!empty($imageUrl) && !filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                $error['image_url'] = 'Format URL gambar tidak valid.';
            }

            // Jika tidak ada error validasi
            if (empty($error)) {
                if ($this->seriesModel->update($id, $title, $description, $releaseYear, $imageUrl, $is_popular, $editorId)) {
                    $message = 'Series berhasil diperbarui!';
                    $messageType = 'success';
                    // $series = $this->seriesModel->findById($id); // Refresh data series
                    // redirect('/daftar_series/show/' . $id . '?message=' . urlencode($message) . '&type=' . urlencode($messageType)); //
                    // exit();
                    redirect('/daftar_series?message=' . urlencode($message) . '&type=' . urlencode($messageType));
                    exit();
                } else {
                    $message = 'Gagal memperbarui series.';
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

        view('series/edit', [
            'title' => 'Edit Series',
            'series' => $series,
            'message' => $message,
            'message_type' => $messageType,
            'error' => $error // Teruskan array error ke view
        ]);
    }

    /**
     * Menghapus series.
     * @param int $id ID series yang akan dihapus
     */
    public function delete($id) {
        $this->checkAdminAccess(); // Hanya admin yang bisa mengakses

        $series = $this->seriesModel->findById($id);

        if (!$series) {
            redirect('/daftar_series?message=' . urlencode('Series tidak ditemukan.') . '&type=error'); //
        }

        $message = '';
        $messageType = '';

        if ($this->seriesModel->delete($id)) {
            $message = 'Series berhasil dihapus!';
            $messageType = 'success';
        } else {
            $message = 'Gagal menghapus series.';
            $messageType = 'error';
        }
        redirect('/daftar_series?message=' . urlencode($message) . '&type=' . urlencode($messageType)); //
    }
}
