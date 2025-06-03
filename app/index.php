<?php
// niflix_project/public/index.php

// Mulai session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Definisikan path absolut ke folder aplikasi
define('APP_ROOT', dirname(__DIR__)); // Ini akan mengarah ke niflix_project/app
define('PUBLIC_PATH', __DIR__); // Ini akan mengarah ke niflix_project/public

// Muat helper functions
require_once APP_ROOT . '/app/Core/Functions.php';
require_once APP_ROOT . '/app/Core/Session.php';

// Muat konfigurasi database
$dbConfig = require APP_ROOT . '/app/config/database.php';

// Buat koneksi database
try {
    $pdo = new PDO(
        "mysql:host=" . $dbConfig['host'] . ";dbname=" . $dbConfig['database'] . ";charset=" . $dbConfig['charset'],
        $dbConfig['username'],
        $dbConfig['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// --- Sistem Routing Sederhana ---
$controllerName = 'DashboardController'; // Controller default (jika URI kosong)
$actionName = 'index'; // Action default
$params = [];

$requestUri = $_SERVER['REQUEST_URI'];
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '/') {
    $basePath = '';
} else {
    $basePath = rtrim($basePath, '/'); // Pastikan tidak ada trailing slash kecuali root
}

if (strpos($requestUri, $basePath) === 0) {
    $requestUri = substr($requestUri, strlen($basePath));
}
$uriSegments = explode('/', trim(parse_url($requestUri, PHP_URL_PATH), '/'));

$controllerFilePath = ''; // Inisialisasi

// Tentukan controller
if (!empty($uriSegments[0])) {
    $controllerCandidate = ucfirst($uriSegments[0]) . 'Controller';
    $tempControllerFilePath = APP_ROOT . '/app/Controllers/' . $controllerCandidate . '.php';

    if (file_exists($tempControllerFilePath)) {
        $controllerName = $controllerCandidate;
        $controllerFilePath = $tempControllerFilePath; // Set path jika ditemukan
        array_shift($uriSegments); // Hapus segmen controller
    } else {
        // Tambahkan logika routing untuk Article dan Comment
        switch ($uriSegments[0]) {
            case 'articles':
                $controllerName = 'ArticleController';
                $controllerFilePath = APP_ROOT . '/app/Controllers/ArticleController.php';
                array_shift($uriSegments); // Hapus segmen 'articles'
                break;
            case 'comment': // Untuk operasi komentar, terutama hapus
                $controllerName = 'CommentController';
                $controllerFilePath = APP_ROOT . '/app/Controllers/CommentController.php';
                array_shift($uriSegments); // Hapus segmen 'comment'
                break;
            case 'daftar_series': // NEW ROUTE FOR SERIES
                $controllerName = 'SeriesController';
                $controllerFilePath = APP_ROOT . '/app/Controllers/SeriesController.php';
                array_shift($uriSegments);
                break;
            case 'daftar_film': // NEW ROUTE FOR FILM
                $controllerName = 'FilmController';
                $controllerFilePath = APP_ROOT . '/app/Controllers/FilmController.php';
                array_shift($uriSegments);
                break;
            default:
                // Jika segmen pertama ada tapi bukan controller yang valid, biarkan controllerName default
                // dan anggap segmen pertama sebagai bagian dari path (misal: /login atau /dashboard)
                // yang akan ditangani oleh default controller (DashboardController) jika action-nya cocok.
                // Atau jika tidak, akan menghasilkan 404 nanti.
                break;
        }
    }
}

// Jika controllerFilePath belum diset (berarti menggunakan default controller)
if (empty($controllerFilePath)) {
    $controllerFilePath = APP_ROOT . '/app/Controllers/' . $controllerName . '.php';
}


// Validasi apakah file controller yang final benar-benar ada
if (!file_exists($controllerFilePath)) {
    header("HTTP/1.0 404 Not Found");
    echo "<h1>404 Not Found</h1><p>Controller file tidak ditemukan: " . htmlspecialchars($controllerName) . ".php</p>";
    exit();
}
require_once $controllerFilePath; // Muat controller

// Buat instance controller
$controller = new $controllerName($pdo); // Lewatkan koneksi PDO ke controller

// Tentukan action (jika tidak ada segmen tersisa, gunakan action 'index')
if (empty($uriSegments[0])) {
    $actionName = 'index';
} else {
    $actionName = $uriSegments[0];
    array_shift($uriSegments); // Hapus segmen action
}

$params = $uriSegments; // Sisa segmen adalah parameter

// Panggil action (method) dari controller
if (method_exists($controller, $actionName)) {
    call_user_func_array([$controller, $actionName], $params);
} else {
    // Tampilkan halaman 404 jika action tidak ditemukan
    header("HTTP/1.0 404 Not Found");
    echo "<h1>404 Not Found</h1><p>Halaman tidak ditemukan. Action '" . htmlspecialchars($actionName) . "' pada Controller '" . htmlspecialchars($controllerName) . "' tidak ditemukan.</p>";
}
