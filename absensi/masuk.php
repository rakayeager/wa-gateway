<?php
// absensi/masuk.php
session_start();
require_once '../config/database.php';
require_once '../api/fonnte.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$id_karyawan = $_SESSION['user_id'];
$today = date('Y-m-d');
$now   = date('H:i:s');

// Cek apakah sudah absen hari ini
$stmt = $pdo->prepare("SELECT * FROM absensi WHERE id_karyawan = ? AND tanggal = ?");
$stmt->execute([$id_karyawan, $today]);
$absen = $stmt->fetch();

if (!$absen) {
    // Simpan ke database
    $insert = $pdo->prepare("INSERT INTO absensi (id_karyawan, tanggal, jam_masuk, status) VALUES (?, ?, ?, 'Hadir')");
    $insert->execute([$id_karyawan, $today, $now]);

    // Ambil data karyawan untuk WA
    $userStmt = $pdo->prepare("SELECT * FROM karyawan WHERE id = ?");
    $userStmt->execute([$id_karyawan]);
    $user = $userStmt->fetch();

    $pesanWA = "*ABSENSI MASUK*\n\n"
        . "Nama    : {$user['nama']}\n"
        . "NIK     : {$user['nik']}\n"
        . "Tanggal : " . date('d F Y') . "\n"
        . "Jam     : {$now}\n"
        . "Status  : Hadir\n\n"
        . "Absensi masuk Anda telah berhasil dicatat.";
    kirimWA($user['no_hp'], $pesanWA);
}

header("Location: index.php");
exit;