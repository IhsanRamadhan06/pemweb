<?php
// niflix_project/app/Controllers/SeriesController.php

require_once APP_ROOT . '/app/Core/Session.php';
require_once APP_ROOT . '/app/Core/Functions.php';
require_once APP_ROOT . '/app/Models/Series.php';

class SeriesController {
    private $pdo;
    private $seriesModel;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->seriesModel = new Series($pdo);
    }

    /**
     * Menampilkan daftar semua series.
     */
    public function index() {
        // Pastikan pengguna sudah login
        if (!Session::has('user')) {
            redirect('/auth/login');
        }

        $series = $this->seriesModel->getAllSeries();

        // Tangani pesan dari parameter URL
        $message = $_GET['message'] ?? null;
        $messageType = $_GET['type'] ?? null;

        view('series/index', [
            'series' => $series,
            'title' => 'Series Populer',
            'message' => $message,
            'message_type' => $messageType
        ]);
    }

    /**
     * Menampilkan detail series tunggal.
     * Ini opsional jika Anda ingin halaman detail untuk setiap series.
     * @param int $id ID series
     */
    public function show($id) {
        $series = $this->seriesModel->findById($id);

        if (!$series) {
            redirect('/daftar_series?message=' . urlencode('Series tidak ditemukan.') . '&type=error');
        }

        view('series/show', [
            'series' => $series,
            'title' => $series['title']
        ]);
    }

    // Methods for Create, Update, Delete series would go here if needed for full CRUD
    // public function create() { ... }
    // public function edit($id) { ... }
    // public function delete($id) { ... }
}