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

// Mengambil url saat ini
$requestUri = $_SERVER['REQUEST_URI'];
// basepath berisi /public karena dirname
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '/') {
    $basePath = '';
} else {
    $basePath = rtrim($basePath, '/');
}

// Membandingkan url di requestUri dan basepath, diambil yang berbeda
if (strpos($requestUri, $basePath) === 0) {
    $requestUri = substr($requestUri, strlen($basePath));
}
// Mengambil url yang berbeda, memisahkan menjadi array sehingga hanya ['auth', 'login']
$uriSegments = explode('/', trim(parse_url($requestUri, PHP_URL_PATH), '/'));

$controllerFilePath = '';

// Menentukan controller
// Mengambil array pertama yaitu auth
if (!empty($uriSegments[0])) {

    // Membentuk nama auth menjadi authController
    $controllerCandidate = ucfirst($uriSegments[0]) . 'Controller';
    // Mengecek apakah nama yang sudah dibentuk ada di app/Controllers?
    $tempControllerFilePath = APP_ROOT . '/app/Controllers/' . $controllerCandidate . '.php';

    // Jika ada maka array pertama yaitu auth dihapus dan didalam array hanya tersisa login
    if (file_exists($tempControllerFilePath)) {
        $controllerName = $controllerCandidate;
        $controllerFilePath = $tempControllerFilePath;
        array_shift($uriSegments);

    // Jika tidak ada maka
    } else {
        // Menggunakan switch untuk menjalankan file yang sesuai dengan url
        switch ($uriSegments[0]) {
            case 'articles':
                $controllerName = 'ArticleController';
                $controllerFilePath = APP_ROOT . '/app/Controllers/ArticleController.php';
                array_shift($uriSegments); // Hapus array 'articles'
                break;
            case 'comment':
                $controllerName = 'CommentController';
                $controllerFilePath = APP_ROOT . '/app/Controllers/CommentController.php';
                array_shift($uriSegments); // Hapus array 'comment'
                break;
            case 'daftar_series':
                $controllerName = 'SeriesController';
                $controllerFilePath = APP_ROOT . '/app/Controllers/SeriesController.php';
                array_shift($uriSegments); // Hapus array 'daftar_series'
                break;
            case 'daftar_film':
                $controllerName = 'FilmController';
                $controllerFilePath = APP_ROOT . '/app/Controllers/FilmController.php';
                array_shift($uriSegments); // Hapus array 'daftar_series'
                break;
            default:
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

// Mengakses controller
require_once $controllerFilePath;

// Buat instance controller
$controller = new $controllerName($pdo); // Lewatkan koneksi PDO ke controller

// Karena array auth sudah dihapus dan yang tersisa hanya login maka,
if (empty($uriSegments[0])) {
    $actionName = 'index';
} else {
    $actionName = $uriSegments[0]; // actionName berisi login
    array_shift($uriSegments); // Hapus array 
}

$params = $uriSegments; // Sisa segmen adalah parameter

// Panggil action (method) dari controller
if (method_exists($controller, $actionName)) {
    call_user_func_array([$controller, $actionName], $params); // memanggil controller authController dan action login
} else {
    // Tampilkan halaman 404 jika action tidak ditemukan
    header("HTTP/1.0 404 Not Found");
    echo "<h1>404 Not Found</h1><p>Halaman tidak ditemukan. Action '" . htmlspecialchars($actionName) . "' pada Controller '" . htmlspecialchars($controllerName) . "' tidak ditemukan.</p>";
}