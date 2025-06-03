<?php
// niflix_project/app/Models/User.php

class User {
    private $pdo;
    private $table = 'user'; // Nama tabel di database

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Menemukan pengguna berdasarkan ID.
     * @param int $id
     * @return array|false
     */
    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Menemukan pengguna berdasarkan username atau email.
     * @param string $usernameOrEmail
     * @return array|false
     */
    public function findByUsernameOrEmail($usernameOrEmail) {
        // UBAH BARIS INI: Gunakan placeholder yang berbeda
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE username = :username_val OR email = :email_val");

        // DAN UBAH BARIS INI: Ikat nilai yang sama ke kedua placeholder
        $stmt->execute([
            ':username_val' => $usernameOrEmail,
            ':email_val' => $usernameOrEmail
        ]);

        return $stmt->fetch();
    }

    /**
     * Mengecek apakah username sudah ada.
     * @param string $username
     * @return bool
     */
    public function usernameExists($username) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM {$this->table} WHERE username = :username");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Mengecek apakah email sudah ada (kecuali untuk user tertentu saat update).
     * @param string $email
     * @param int|null $excludeId ID pengguna yang akan dikecualikan (untuk update profil)
     * @return bool
     */
    public function emailExists($email, $excludeId = null) {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE email = :email";
        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        if ($excludeId) {
            $stmt->bindParam(':exclude_id', $excludeId, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Membuat pengguna baru.
     * @param string $fullname
     * @param string $email
     * @param string $username
     * @param string $hashedPassword
     * @param string $photoPath
     * @param int $isAdmin
     * @return bool
     */
    public function create($fullname, $email, $username, $hashedPassword, $photoPath = 'default.png', $isAdmin = 0) {
        $stmt = $this->pdo->prepare("INSERT INTO {$this->table} (username, email, password, nama_lengkap, foto_pengguna, is_admin) VALUES (:username, :email, :password, :nama_lengkap, :foto_pengguna, :is_admin)");
        return $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':password' => $hashedPassword,
            ':nama_lengkap' => $fullname,
            ':foto_pengguna' => $photoPath,
            ':is_admin' => $isAdmin
        ]);
    }

    /**
     * Memperbarui data pengguna.
     * @param int $id
     * @param array $data Array asosiatif data yang akan diupdate (misal: ['username' => 'newuser'])
     * @return bool
     */
    public function update($id, array $data) {
        $set = [];
        foreach ($data as $key => $value) {
            $set[] = "`{$key}` = :{$key}";
        }
        $sql = "UPDATE {$this->table} SET " . implode(', ', $set) . " WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);

        foreach ($data as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }


    /**
     * Menghapus pengguna berdasarkan ID.
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Mengambil semua pengguna.
     * @return array
     */
    public function getAllUsers() {
        $stmt = $this->pdo->query("SELECT id, username, email, nama_lengkap, is_admin FROM {$this->table}");
        return $stmt->fetchAll();
    }
}