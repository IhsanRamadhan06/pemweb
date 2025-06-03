<?php
// niflix_project/app/Controllers/FilmController.php

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
     * Menampilkan daftar semua series.
     */
    public function index() {
        $film = $this->filmModel->getAllFilms();

        // Tangani pesan dari parameter URL
        $message = $_GET['message'] ?? null;
        $messageType = $_GET['type'] ?? null;

        view('film/index', [
            'film' => $film,
            'title' => 'Daftar Film',
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
        $film = $this->filmModel->findById($id);

        if (!$film) {
            redirect('/daftar_film?message=' . urlencode('Film tidak ditemukan.') . '&type=error');
        }

        view('film', [
            'film' => $film,
            'title' => $film['title']
        ]);
    }

    // Methods for Create, Update, Delete series would go here if needed for full CRUD
    // public function create() { ... }
    // public function edit($id) { ... }
    // public function delete($id) { ... }
}