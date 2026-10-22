<?php
// absensi/pulang.php
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

// Cek absensi masuk hari ini
$stmt = $pdo->prepare("SELECT * FROM absensi WHERE id_karyawan = ? AND tanggal = ?");
$stmt->execute([$id_karyawan, $today]);
$absen = $stmt->fetch();

// Hanya izinkan jika sudah absen masuk dan belum absen pulang
if ($absen && empty($absen['jam_pulang'])) {
    $update = $pdo->prepare("UPDATE absensi SET jam_pulang = ?, status = 'Pulang' WHERE id = ?");
    $update->execute([$now, $absen['id']]);

    // Ambil data profil karyawan
    $userStmt = $pdo->prepare("SELECT * FROM karyawan WHERE id = ?");
    $userStmt->execute([$id_karyawan]);
    $user = $userStmt->fetch();

    $pesanWA = "*ABSENSI PULANG*\n\n"
        . "Nama       : {$user['nama']}\n"
        . "NIK        : {$user['nik']}\n"
        . "Tanggal    : " . date('d F Y') . "\n"
        . "Jam Masuk  : {$absen['jam_masuk']}\n"
        . "Jam Pulang : {$now}\n"
        . "Status     : Pulang\n\n"
        . "Terima kasih.\nAbsensi pulang Anda telah berhasil dicatat.";
    kirimWA($user['no_hp'], $pesanWA);
}

header("Location: index.php");
exit;