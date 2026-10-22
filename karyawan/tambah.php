<?php
// karyawan/tambah.php
session_start();
require_once '../config/database.php';
require_once '../api/fonnte.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nik     = trim($_POST['nik'] ?? '');
    $nama    = trim($_POST['nama'] ?? '');
    $jabatan = trim($_POST['jabatan'] ?? '');
    $no_hp   = trim($_POST['no_hp'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $alamat  = trim($_POST['alamat'] ?? '');
    $rawPass = $_POST['password'] ?? '';

    if (empty($nik) || empty($nama) || empty($jabatan) || empty($no_hp) || empty($email) || empty($rawPass)) {
        $error = "Semua bidang bertanda bintang (*) wajib diisi.";
    } else {
        // Cek duplikasi NIK & Email
        $stmtCheck = $pdo->prepare("SELECT id FROM karyawan WHERE nik = ? OR email = ?");
        $stmtCheck->execute([$nik, $email]);
        if ($stmtCheck->fetch()) {
            $error = "NIK atau Email tersebut sudah digunakan oleh akun lain.";
        } else {
            $pass = password_hash($rawPass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO karyawan (nik, nama, jabatan, no_hp, email, password, alamat, role) VALUES (?, ?, ?, ?, ?, ?, ?, 'karyawan')");
            if ($stmt->execute([$nik, $nama, $jabatan, $no_hp, $email, $pass, $alamat])) {
                $msg = "*PENDAFTARAN KARYAWAN OLEH ADMINISTRATOR*\n\n"
                    . "Halo {$nama},\n"
                    . "Data Anda telah didaftarkan ke Sistem Presensi Perusahaan.\n"
                    . "NIK     : {$nik}\n"
                    . "Jabatan : {$jabatan}\n\n"
                    . "Silakan login menggunakan NIK atau Email terdaftar.\n"
                    . "Terima kasih.";
                kirimWA($no_hp, $msg);
                header("Location: index.php");
                exit;
            } else {
                $error = "Gagal menambahkan data ke dalam database.";
            }
        }
    }
}

$adminNama = $_SESSION['nama'] ?? 'Administrator';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Karyawan Baru | Enterprise HRMS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<!-- Enterprise Navbar -->
<nav class="navbar">
    <a href="../admin/index.php" class="nav-brand">
        <div class="brand-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
            </svg>
        </div>
        <div>
            <div class="brand-title">HRMS Enterprise</div>
            <div class="brand-subtitle">Management Console</div>
        </div>
    </a>

    <ul class="nav-links">
        <li>
            <a href="../admin/index.php" class="nav-link">Dashboard</a>
        </li>
        <li>
            <a href="index.php" class="nav-link active">Data Karyawan</a>
        </li>
    </ul>

    <div class="nav-user">
        <div class="user-badge">
            <div class="avatar-sm"><?= strtoupper(substr($adminNama, 0, 1)) ?></div>
            <div>
                <div class="user-meta-name"><?= htmlspecialchars($adminNama) ?></div>
                <div class="user-meta-role">HR Administrator</div>
            </div>
        </div>
        <a href="../auth/logout.php" class="btn-nav-logout">Keluar</a>
    </div>
</nav>

<div class="container" style="max-width: 800px;">
    <div class="breadcrumb">
        <a href="../admin/index.php">Dashboard</a>
        <span>/</span>
        <a href="index.php">Direktori Karyawan</a>
        <span>/</span>
        <span>Tambah Baru</span>
    </div>

    <div class="card">
        <div style="border-bottom: 1px solid var(--border-color); padding-bottom: 1rem; margin-bottom: 1.5rem;">
            <h2 style="font-size: 1.35rem; font-weight: 800; color: #0f172a;">Form Pendaftaran Karyawan Baru</h2>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 4px;">Informasi ini akan digunakan untuk login sistem dan notifikasi presensi WhatsApp.</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">Nomor Induk Karyawan (NIK) *</label>
                    <input type="text" name="nik" class="form-control" placeholder="Contoh: 20260012" value="<?= htmlspecialchars($_POST['nik'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Nama Lengkap Karyawan *</label>
                    <input type="text" name="nama" class="form-control" placeholder="Nama lengkap beserta gelar" value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">Jabatan / Divisi *</label>
                    <input type="text" name="jabatan" class="form-control" placeholder="Contoh: UI/UX Designer / Product" value="<?= htmlspecialchars($_POST['jabatan'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Nomor WhatsApp Aktif *</label>
                    <input type="text" name="no_hp" class="form-control" placeholder="Contoh: 081234567890" value="<?= htmlspecialchars($_POST['no_hp'] ?? '') ?>" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">Email Perusahaan / Pribadi *</label>
                    <input type="email" name="email" class="form-control" placeholder="karyawan@perusahaan.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Kata Sandi Akun *</label>
                    <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Alamat Lengkap</label>
                <input type="text" name="alamat" class="form-control" placeholder="Alamat domisili karyawan" value="<?= htmlspecialchars($_POST['alamat'] ?? '') ?>">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                <a href="index.php" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    Simpan Data Karyawan
                </button>
            </div>
        </form>
    </div>
</div>
</body>
</html>

</html>