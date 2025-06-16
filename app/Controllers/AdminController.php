<?php
// niflix_project/app/Controllers/AdminController.php

require_once APP_ROOT . '/app/Core/Session.php'; // cite: 1
require_once APP_ROOT . '/app/Core/Functions.php'; // cite: 1
require_once APP_ROOT . '/app/Models/User.php'; // cite: 1

class AdminController {
    private $pdo; // cite: 1
    private $userModel; // cite: 1
    private $uploadDir = PUBLIC_PATH . '/uploads/profile_photos/'; // cite: 1

    public function __construct(PDO $pdo) { // cite: 1
        $this->pdo = $pdo; // cite: 1
        $this->userModel = new User($pdo); // cite: 1
        $this->checkAdminAccess(); // cite: 1

        // Pastikan direktori upload ada // cite: 1
        if (!is_dir($this->uploadDir)) { // cite: 1
            mkdir($this->uploadDir, 0755, true); // Buat direktori dengan izin 0755 (atau 0777 jika 0755 gagal) // cite: 1
        }
    }

    private function checkAdminAccess() { // cite: 1
        // Periksa akses admin, jika tidak admin, redirect ke dashboard
        if (!Session::has('user') || Session::get('user')['is_admin'] != 1) { // cite: 1
            redirect('/dashboard'); // cite: 1
        }
    }

    /**
     * Menampilkan daftar semua pengguna.
     */
    public function index() { // cite: 1
        $users = $this->userModel->getAllUsers(); // cite: 1

        // Tangani pesan dari parameter URL // cite: 1
        $message = $_GET['message'] ?? null; // cite: 1
        $messageType = $_GET['type'] ?? null; // cite: 1

        view('admin/manage_users', [ // cite: 1
            'users' => $users, // cite: 1
            'title' => 'Kelola Akun', // cite: 1
            'message' => $message,       // Lewatkan pesan ke view // cite: 1
            'message_type' => $messageType // Lewatkan tipe pesan ke view // cite: 1
        ]);
    }

    /**
     * Menangani penghapusan pengguna.
     * @param int $id ID pengguna yang akan dihapus
     */
    public function delete($id) { // cite: 1
        $message = ''; // cite: 1
        $messageType = ''; // cite: 1

        $currentUser = Session::get('user'); // Dapatkan pengguna saat ini dari sesi

        if ($this->userModel->delete($id)) { // cite: 1
            $message = 'Akun berhasil dihapus!'; // cite: 1
            $messageType = 'success'; // cite: 1

            // Jika pengguna yang dihapus adalah pengguna yang sedang login
            if ($currentUser && $currentUser['id'] == $id) {
                Session::destroy(); // Hancurkan sesi
                redirect('/auth/login?message=' . urlencode('Akun Anda telah dihapus. Silakan login kembali.') . '&type=info');
                exit();
            }
        } else {
            $message = 'Gagal menghapus akun.'; // cite: 1
            $messageType = 'error'; // cite: 1
        }
        // Arahkan kembali dengan pesan di parameter URL // cite: 1
        redirect('/admin?message=' . urlencode($message) . '&type=' . urlencode($messageType)); // cite: 1
    }

    /**
     * Menampilkan formulir untuk mengedit pengguna atau memproses update.
     * @param int $id ID pengguna yang akan diedit
     */
    public function edit_user($id) { // cite: 1
        $user = $this->userModel->findById($id); // cite: 1

        if (!$user) { // cite: 1
            redirect('/admin?message=' . urlencode('Pengguna tidak ditemukan.') . '&type=error'); // cite: 1
        }

        $message = null; // cite: 1
        $messageType = null; // cite: 1
        $error = null; // cite: 1

        $currentUserInSession = Session::get('user'); // Dapatkan pengguna saat ini dari sesi

        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // cite: 1
            $username = trim($_POST['username'] ?? ''); // cite: 1
            $fullname = trim($_POST['fullname'] ?? ''); // cite: 1
            $email = trim($_POST['email'] ?? ''); // cite: 1
            $is_admin = (int)($_POST['is_admin'] ?? 0); // cite: 1
            $newPassword = $_POST['new_password'] ?? ''; // cite: 1
            $confirmPassword = $_POST['confirm_password'] ?? ''; // cite: 1

            $updateData = []; // cite: 1

            // Validasi username // cite: 1
            if (empty($username)) { // cite: 1
                $error = "Username tidak boleh kosong."; // cite: 1
            } else if ($username !== $user['username'] && $this->userModel->usernameExists($username)) { // cite: 1
                $error = "Username sudah digunakan!"; // cite: 1
            } else {
                $updateData['username'] = $username; // cite: 1
            }

            // Validasi email // cite: 1
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) { // cite: 1
                $error = "Mohon masukkan alamat email yang valid."; // cite: 1
            } else if ($email !== $user['email'] && $this->userModel->emailExists($email, $id)) { // cite: 1
                $error = "Email sudah digunakan!"; // cite: 1
            } else {
                $updateData['email'] = $email; // cite: 1
            }

            // Tambahkan nama lengkap dan status admin // cite: 1
            $updateData['nama_lengkap'] = $fullname; // cite: 1
            $updateData['is_admin'] = $is_admin; // cite: 1

            // Periksa perubahan password // cite: 1
            if (!empty($newPassword)) { // cite: 1
                if ($newPassword !== $confirmPassword) { // cite: 1
                    $error = "Password baru tidak cocok."; // cite: 1
                } else {
                    $updateData['password'] = password_hash($newPassword, PASSWORD_DEFAULT); // cite: 1
                }
            }

            // Handle upload foto profil // cite: 1
            $photoPath = $user['foto_pengguna']; // Default ke foto yang sudah ada // cite: 1
            if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] == UPLOAD_ERR_OK) { // cite: 1
                $fileTmpPath = $_FILES['profile_photo']['tmp_name']; // cite: 1
                $fileName = $_FILES['profile_photo']['name']; // cite: 1
                $fileSize = $_FILES['profile_photo']['size']; // cite: 1
                $fileType = $_FILES['profile_photo']['type']; // cite: 1
                $fileNameCmps = explode(".", $fileName); // cite: 1
                $fileExtension = strtolower(end($fileNameCmps)); // cite: 1

                $allowedFileExtensions = ['jpg', 'gif', 'png', 'jpeg']; // cite: 1
                if (in_array($fileExtension, $allowedFileExtensions)) { // cite: 1
                    $newFileName = 'user_' . $id . '_' . time() . '.' . $fileExtension; // cite: 1
                    $destPath = $this->uploadDir . $newFileName; // cite: 1

                    if (move_uploaded_file($fileTmpPath, $destPath)) { // cite: 1
                        // Hapus foto lama jika bukan 'default.png' dan ada di server // cite: 1
                        if ($photoPath !== 'default.png' && file_exists($this->uploadDir . $photoPath)) { // cite: 1
                            unlink($this->uploadDir . $photoPath); // cite: 1
                        }
                        $updateData['foto_pengguna'] = $newFileName; // Simpan hanya nama file // cite: 1
                    } else {
                        $error = "Maaf, ada kesalahan saat mengunggah file Anda."; // cite: 1
                    }
                } else {
                    $error = "Jenis file tidak diizinkan. Hanya JPG, JPEG, PNG, GIF."; // cite: 1
                }
            }

            // Jika tidak ada error, lakukan update // cite: 1
            if (empty($error)) { // cite: 1
                if ($this->userModel->update($id, $updateData)) { // cite: 1
                    $message = "Profil pengguna berhasil diperbarui!"; // cite: 1
                    $messageType = 'success'; // cite: 1
                    // Update objek $user agar data yang ditampilkan di form tetap terbaru // cite: 1
                    $user = $this->userModel->findById($id); // cite: 1

                    // Jika pengguna yang diedit adalah pengguna yang sedang login
                    if ($currentUserInSession && $currentUserInSession['id'] == $id) {
                        // Perbarui status admin di sesi
                        Session::set('user', [
                            'id' => $user['id'],
                            'username' => $user['username'],
                            'is_admin' => $user['is_admin'], // Perbarui status admin
                            'photo' => $user['foto_pengguna'],
                            'fullname' => $user['nama_lengkap']
                        ]);

                        // Jika admin mengubah dirinya menjadi non-admin, paksa refresh atau redirect
                        if ($currentUserInSession['is_admin'] == 1 && $user['is_admin'] == 0) {
                            redirect('/dashboard?message=' . urlencode('Status admin Anda telah diubah menjadi non-admin.') . '&type=info');
                            exit();
                        }
                    }
                } else {
                    $error = "Terjadi kesalahan saat memperbarui pengguna: " . ($this->pdo->errorInfo()[2] ?? 'Unknown error'); // cite: 1
                }
            }
        }

        view('admin/edit_user', [ // cite: 1
            'user' => $user, // cite: 1
            'title' => 'Edit Pengguna', // cite: 1
            'message' => $message, // cite: 1
            'message_type' => $messageType, // cite: 1
            'error' => $error // cite: 1
        ]);
    }
}