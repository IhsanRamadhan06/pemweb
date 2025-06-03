<?php
// niflix_project/app/Core/Session.php

class Session {

    /**
     * Mengatur nilai ke dalam session.
     * @param string $key Kunci session
     * @param mixed $value Nilai session
     */
    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    /**
     * Mengambil nilai dari session.
     * @param string $key Kunci session
     * @param mixed $default Nilai default jika kunci tidak ditemukan
     * @return mixed
     */
    public static function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Menghapus nilai dari session.
     * @param string $key Kunci session
     */
    public static function forget($key) {
        unset($_SESSION[$key]);
    }

    /**
     * Mengecek apakah kunci ada di session.
     * @param string $key Kunci session
     * @return bool
     */
    public static function has($key) {
        return isset($_SESSION[$key]);
    }

    /**
     * Mengatur flash message (pesan yang hanya muncul sekali).
     * @param string $key Kunci flash message
     * @param string $message Pesan
     */
    public static function flash($key, $message) {
        self::set('flash_' . $key, $message);
    }

    /**
     * Mengambil flash message dan menghapusnya.
     * @param string $key Kunci flash message
     * @param mixed $default Nilai default jika flash message tidak ditemukan
     * @return mixed
     */
    public static function getFlash($key, $default = null) {
        $flashKey = 'flash_' . $key;
        if (self::has($flashKey)) {
            $message = self::get($flashKey);
            self::forget($flashKey); // Hapus flash message setelah diambil
            return $message;
        }
        return $default;
    }

    /**
     * Menghancurkan seluruh sesi.
     */
    public static function destroy() {
        session_unset(); // Hapus semua variabel sesi
        session_destroy(); // Hancurkan sesi
        $_SESSION = []; // Pastikan $_SESSION kosong
    }
}


// heheehhe