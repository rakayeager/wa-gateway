<?php
// config/database.php
date_default_timezone_set('Asia/Jakarta');

$host = "localhost";
$db   = "db_karyawan";
$user = "root";
$pass = "";
$charset = "utf8mb4";

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// Konfigurasi WhatsApp Admin & Token Fonnte
define('FONNTE_TOKEN', 'z19NUVTDw7mmrpPF7NaF'); 
define('ADMIN_PHONE', '085934386273'); // Ganti dengan nomor WhatsApp Admin