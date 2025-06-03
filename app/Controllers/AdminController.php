<?php
// niflix_project/app/Controllers/AdminController.php

require_once APP_ROOT . '/app/Core/Session.php';
require_once APP_ROOT . '/app/Core/Functions.php';
require_once APP_ROOT . '/app/Models/User.php';

class AdminController {
    private $pdo;
    private $userModel;
    private $uploadDir = PUBLIC_PATH . '/uploads/profile_photos/'; //

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->userModel = new User($pdo);
        $this->checkAdminAccess();

        // Pastikan direktori upload ada
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true); // Buat direktori dengan izin 0755 (atau 0777 jika 0755 gagal)
        }
    }

    private function checkAdminAccess() {
        if (!Session::has('user') || Session::get('user')['is_admin'] != 1) {
            redirect('/dashboard');
        }
    }

    /**
     * Menampilkan daftar semua pengguna.
     */
    public function index() {
        $users = $this->userModel->getAllUsers();

        // Tangani pesan dari parameter URL
        $message = $_GET['message'] ?? null;
        $messageType = $_GET['type'] ?? null;

        view('admin/manage_users', [
            'users' => $users,
            'title' => 'Kelola Akun',
            'message' => $message,       // Lewatkan pesan ke view
            'message_type' => $messageType // Lewatkan tipe pesan ke view
        ]);
    }

    /**
     * Menangani penghapusan pengguna.
     * @param int $id ID pengguna yang akan dihapus
     */
    public function delete($id) {
        $message = '';
        $messageType = '';

        if ($this->userModel->delete($id)) {
            $message = 'Akun berhasil dihapus!';
            $messageType = 'success';
        } else {
            $message = 'Gagal menghapus akun.';
            $messageType = 'error';
        }
        // Arahkan kembali dengan pesan di parameter URL
        redirect('/admin?message=' . urlencode($message) . '&type=' . urlencode($messageType));
    }

    /**
     * Menampilkan formulir untuk mengedit pengguna atau memproses update.
     * @param int $id ID pengguna yang akan diedit
     */
    public function edit_user($id) {
        $user = $this->userModel->findById($id);

        if (!$user) {
            redirect('/admin?message=' . urlencode('Pengguna tidak ditemukan.') . '&type=error');
        }

        $message = null;
        $messageType = null;
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $fullname = trim($_POST['fullname'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $is_admin = (int)($_POST['is_admin'] ?? 0);
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            $updateData = [];

            // Validasi username
            if (empty($username)) {
                $error = "Username tidak boleh kosong.";
            } else if ($username !== $user['username'] && $this->userModel->usernameExists($username)) {
                $error = "Username sudah digunakan!";
            } else {
                $updateData['username'] = $username;
            }

            // Validasi email
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Mohon masukkan alamat email yang valid.";
            } else if ($email !== $user['email'] && $this->userModel->emailExists($email, $id)) {
                $error = "Email sudah digunakan!";
            } else {
                $updateData['email'] = $email;
            }

            // Tambahkan nama lengkap dan status admin
            $updateData['nama_lengkap'] = $fullname;
            $updateData['is_admin'] = $is_admin;

            // Periksa perubahan password
            if (!empty($newPassword)) {
                if ($newPassword !== $confirmPassword) {
                    $error = "Password baru tidak cocok.";
                } else {
                    $updateData['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
                }
            }

            // Handle upload foto profil
            $photoPath = $user['foto_pengguna']; // Default ke foto yang sudah ada
            if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] == UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['profile_photo']['tmp_name'];
                $fileName = $_FILES['profile_photo']['name'];
                $fileSize = $_FILES['profile_photo']['size'];
                $fileType = $_FILES['profile_photo']['type'];
                $fileNameCmps = explode(".", $fileName);
                $fileExtension = strtolower(end($fileNameCmps));

                $allowedFileExtensions = ['jpg', 'gif', 'png', 'jpeg'];
                if (in_array($fileExtension, $allowedFileExtensions)) {
                    $newFileName = 'user_' . $id . '_' . time() . '.' . $fileExtension;
                    $destPath = $this->uploadDir . $newFileName;

                    if (move_uploaded_file($fileTmpPath, $destPath)) {
                        // Hapus foto lama jika bukan 'default.png' dan ada di server
                        if ($photoPath !== 'default.png' && file_exists($this->uploadDir . $photoPath)) {
                            unlink($this->uploadDir . $photoPath);
                        }
                        $updateData['foto_pengguna'] = $newFileName; // Simpan hanya nama file
                    } else {
                        $error = "Maaf, ada kesalahan saat mengunggah file Anda.";
                    }
                } else {
                    $error = "Jenis file tidak diizinkan. Hanya JPG, JPEG, PNG, GIF.";
                }
            }

            // Jika tidak ada error, lakukan update
            if (empty($error)) {
                if ($this->userModel->update($id, $updateData)) {
                    $message = "Profil pengguna berhasil diperbarui!";
                    $messageType = 'success';
                    // Update objek $user agar data yang ditampilkan di form tetap terbaru
                    $user = $this->userModel->findById($id);
                } else {
                    $error = "Terjadi kesalahan saat memperbarui pengguna: " . ($this->pdo->errorInfo()[2] ?? 'Unknown error');
                }
            }
        }

        view('admin/edit_user', [
            'user' => $user,
            'title' => 'Edit Pengguna',
            'message' => $message,
            'message_type' => $messageType,
            'error' => $error
        ]);
    }
}