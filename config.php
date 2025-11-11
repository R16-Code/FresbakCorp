<?php
// Mulai session hanya jika belum ada session aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Definisikan konstanta hanya jika belum terdefinisi
if (!defined('DB_HOST')) {
    // Konfigurasi Database
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root'); // Ganti dengan user DB Anda
    define('DB_PASS', '');     // Ganti dengan password DB Anda
    define('DB_NAME', 'fresbak');
    
    // Koneksi PDO (PHP Data Objects) - lebih aman
    try {
        $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        // Set mode error PDO ke exception
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
        die("ERROR: Tidak dapat terhubung. " . $e->getMessage());
    }
    
    // URL dasar (opsional, tapi membantu)
    define('BASE_URL', 'http://localhost/FresbakCorp/'); // Ganti sesuai path Anda
}
?>