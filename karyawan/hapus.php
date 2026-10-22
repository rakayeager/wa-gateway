<?php
// karyawan/hapus.php
session_start();
require_once '../config/database.php';
require_once '../api/fonnte.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$id = $_GET['id'] ?? null;
if ($id) {
    // 1. AMBIL DATA TERLEBIH DAHULU (PENTING!)
    $stmt = $pdo->prepare("SELECT * FROM karyawan WHERE id = ?");
    $stmt->execute([$id]);
    $karyawan = $stmt->fetch();

    if ($karyawan) {
        // 2. KIRIM WHATSAPP
        $pesanWA = "*NOTIFIKASI DATA KARYAWAN*\n\n"
            . "Halo {$karyawan['nama']},\n"
            . "Data Anda pada sistem karyawan telah dihapus oleh administrator.\n"
            . "Jika Anda merasa tidak melakukan permintaan tersebut, silakan hubungi administrator.";
        kirimWA($karyawan['no_hp'], $pesanWA);

        // 3. DELETE DARI DATABASE
        $del = $pdo->prepare("DELETE FROM karyawan WHERE id = ?");
        $del->execute([$id]);
    }
}

header("Location: index.php");
exit;