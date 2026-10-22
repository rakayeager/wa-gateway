-- Database: db_karyawan
CREATE DATABASE IF NOT EXISTS db_karyawan;
USE db_karyawan;

-- 1. Tabel Karyawan
CREATE TABLE IF NOT EXISTS karyawan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nik VARCHAR(30) UNIQUE NOT NULL,
    nama VARCHAR(100) NOT NULL,
    jabatan VARCHAR(100) NOT NULL,
    no_hp VARCHAR(20) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    alamat VARCHAR(255) DEFAULT NULL,
    role ENUM('karyawan', 'admin') DEFAULT 'karyawan',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 2. Tabel Absensi
CREATE TABLE IF NOT EXISTS absensi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_karyawan INT NOT NULL,
    tanggal DATE NOT NULL,
    jam_masuk TIME DEFAULT NULL,
    jam_pulang TIME DEFAULT NULL,
    status VARCHAR(30) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_karyawan) REFERENCES karyawan(id) ON DELETE CASCADE
);

-- Akun Default Admin untuk Login
-- NIK      : 000
-- Email    : admin@perusahaan.com
-- Password : admin123
INSERT INTO karyawan (nik, nama, jabatan, no_hp, email, password, role)
VALUES (
    '000',
    'Administrator',
    'Admin HRD',
    '081234567890',
    'admin@perusahaan.com',
    '$2y$10$bn.xXRktm1OvgC6t.9yJfuPTP0xiFiOSbc2mmQctRrLH3UA.2B8.m',
    'admin'
)
ON DUPLICATE KEY UPDATE 
    password = '$2y$10$bn.xXRktm1OvgC6t.9yJfuPTP0xiFiOSbc2mmQctRrLH3UA.2B8.m',
    role = 'admin';

