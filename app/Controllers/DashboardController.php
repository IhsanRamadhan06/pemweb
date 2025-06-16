<?php
// niflix_project/app/Controllers/DashboardController.php

require_once APP_ROOT . '/app/Core/Session.php';
require_once APP_ROOT . '/app/Core/Functions.php';
require_once APP_ROOT . '/app/Models/Film.php';  
require_once APP_ROOT . '/app/Models/Series.php';

class DashboardController {
    private $pdo;
    private $filmModel;   // Tambahkan properti ini
    private $seriesModel; // Tambahkan properti ini

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->filmModel = new Film($pdo);     // Inisialisasi model Film
        $this->seriesModel = new Series($pdo); // Inisialisasi model Series
    }

    public function index() {
        // Pastikan pengguna sudah login
        if (!Session::has('user')) {
            redirect('/auth/login');
        }

        // Ambil data film dari database
        $dbFilms = $this->filmModel->getAllFilm(); // Mengambil semua film dari database

        // Ambil data series dari database
        $dbSeries = $this->seriesModel->getAllSeries(); // Mengambil semua series dari database

        // Ubah struktur data agar sesuai dengan yang diharapkan oleh view (misal: 'image_url' menjadi 'image')
        $film = [];
        foreach ($dbFilms as $f) {
            $film[] = [
                "title" => $f['title'],
                "image" => $f['image_url'] // Menggunakan 'image_url' dari database
            ];
        }

        $series = [];
        foreach ($dbSeries as $s) {
            $series[] = [
                "title" => $s['title'],
                "image" => $s['image_url'] // Menggunakan 'image_url' dari database
            ];
        }

        $data = [
            'user_username' => Session::get('user')['username'] ?? 'Guest',
            'film' => $film,
            'series' => $series
        ];

        // Memanggil function view yang berada di Core/Functions.php
        view('dashboard', $data);
    }
}
