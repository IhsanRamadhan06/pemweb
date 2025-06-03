<?php
// niflix_project/app/Controllers/AuthController.php

// Pastikan model User dan Session tersedia
require_once APP_ROOT . '/app/Models/User.php';
require_once APP_ROOT . '/app/Core/Session.php';
require_once APP_ROOT . '/app/Core/Functions.php'; // Pastikan escape_html, redirect tersedia

class AuthController {
    private $userModel;

    public function __construct(PDO $pdo) {
        $this->userModel = new User($pdo);
    }

    /**
     * Menampilkan halaman login atau memproses login.
     */
    public function login() {
        // Mengecek apakah ada user di session, jika ada, menuju ke dashboard
        if (Session::has('user')) {
            redirect('/dashboard');
        }

        $error = null; // Inisialisasi error message

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usernameOrEmail = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            // Validasi input sederhana
            if (empty($usernameOrEmail) || empty($password)) {
                $error = "Username/Email dan password tidak boleh kosong.";
            } else {
                $user = $this->userModel->findByUsernameOrEmail($usernameOrEmail);

                if ($user && password_verify($password, $user['password'])) {
                    // Login berhasil
                    Session::set('user', [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'is_admin' => $user['is_admin'],
                        'photo' => $user['foto_pengguna'] ?? 'default.png' // Pastikan ada foto di session
                    ]);

                    // Memanggil function redirect yang berada di Core/Functions.php
                    redirect('/dashboard');
                } else {
                    // Error login tidak disimpan di session, langsung ditampilkan
                    $error = "Login gagal! Username/Email atau password salah.";
                }
            }
        }
        // Muat view login, lewati pesan error
        view('auth/login', ['error' => $error]);
    }

    /**
     * Menampilkan halaman registrasi atau memproses registrasi.
     */
    public function register() {
        // Jika sudah login, redirect ke dashboard
        if (Session::has('user')) {
            redirect('/dashboard');
        }

        $message = null; // Untuk pesan sukses atau error
        $messageType = null; // success atau error

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fullname = $_POST['fullname'] ?? '';
            $email = $_POST['email'] ?? '';
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm-password'] ?? '';

            // Validasi input
            if (empty($fullname) || empty($email) || empty($username) || empty($password) || empty($confirm_password)) {
                $message = 'Semua field wajib diisi!';
                $messageType = 'error';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message = 'Format email tidak valid!';
                $messageType = 'error';
            } elseif ($password !== $confirm_password) {
                $message = 'Password dan konfirmasi password tidak cocok!';
                $messageType = 'error';
            } elseif ($this->userModel->usernameExists($username)) {
                $message = 'Username sudah terdaftar!';
                $messageType = 'error';
            } elseif ($this->userModel->emailExists($email)) {
                $message = 'Email sudah terdaftar!';
                $messageType = 'error';
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Buat pengguna baru
                if ($this->userModel->create($fullname, $email, $username, $hashed_password)) {
                    $message = 'Registrasi berhasil! Silakan login.';
                    $messageType = 'success';
                    // Opsional: Redirect ke halaman login setelah registrasi sukses
                    // redirect('/login');
                } else {
                    $message = "Terjadi kesalahan saat registrasi.";
                    $messageType = 'error';
                }
            }
        }
        // Muat view register, lewati pesan
        view('auth/register', ['message' => $message, 'message_type' => $messageType]);
    }

    /**
     * Log out pengguna.
     */
    public function logout() {
        Session::destroy(); // Menggunakan helper Session
        redirect('/auth/login');
    }
}