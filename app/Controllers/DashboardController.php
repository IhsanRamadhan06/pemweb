<?php
// niflix_project/app/Controllers/DashboardController.php

require_once APP_ROOT . '/app/Core/Session.php';
require_once APP_ROOT . '/app/Core/Functions.php';

class DashboardController {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function index() {
        // Pastikan pengguna sudah login
        if (!Session::has('user')) {
            redirect('/auth/login');
        }

        // Data film dan series (sementara masih statis)
        $film = [
            ["title" => "Inception", "image" => "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRJyjBC4dx19LTH6CBmbDIpNCrelbYJSplrUA&s"],
            ["title" => "Interstellar", "image" => "https://upload.wikimedia.org/wikipedia/id/b/bc/Interstellar_film_poster.jpg"],
            ["title" => "The Dark Knight", "image" => "https://upload.wikimedia.org/wikipedia/id/8/8a/Dark_Knight.jpg"],
            ["title" => "Avatar", "image" => "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTpypb6nI7UrJtPHuHDnzAsO5_tP1uwd_raIw&s"],
            ["title" => "The Matrix", "image" => "https://images-cdn.ubuy.co.id/63497d4c524b6263e43a00ee-the-matrix-movie-poster-us-version-24x36.jpg"]
        ];

        $series = [
            ["title" => "Stranger Things", "image" => "https://awsimages.detik.net.id/community/media/visual/2017/10/23/d94f3168-b35d-4db2-844f-93b4d463261b.jpg?w=600&q=90"],
            ["title" => "Breaking Bad", "image" => "https://m.media-amazon.com/images/I/51fWOBx3agL.AC_UF894,1000_QL80.jpg"],
            ["title" => "Game of Thrones", "image" => "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTX60Jzu04x_G8OHcBGmy_GK6T4X1jLZgQ-JA&s"],
            ["title" => "The Walking Dead", "image" => "https://upload.wikimedia.org/wikipedia/id/thumb/0/0e/TheWalkingDeadPoster.jpg/220px-TheWalkingDeadPoster.jpg"],
            ["title" => "Sherlock", "image" => "https://m.media-amazon.com/images/I/51+LSKG5-FL.jpg"]
        ];

        $data = [
            'user_username' => Session::get('user')['username'] ?? 'Guest',
            'film' => $film,
            'series' => $series
        ];

        // Memanggil function view yang berada di Core/Functions.php
        view('dashboard', $data);
    }
}