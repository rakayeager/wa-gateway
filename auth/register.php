<?php
// auth/register.php
session_start();
require_once '../config/database.php';
require_once '../api/fonnte.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nik        = trim($_POST['nik'] ?? '');
    $nama       = trim($_POST['nama'] ?? '');
    $jabatan    = trim($_POST['jabatan'] ?? '');
    $no_hp      = trim($_POST['no_hp'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $alamat     = trim($_POST['alamat'] ?? '');
    $password   = $_POST['password'] ?? '';
    $konfirmasi = $_POST['konfirmasi'] ?? '';

    // Validasi
    if (empty($nik) || empty($nama) || empty($jabatan) || empty($no_hp) || empty($email) || empty($password)) {
        $error = "Mohon lengkapi semua kolom bertanda bintang (*) dengan benar.";
    } elseif ($password !== $konfirmasi) {
        $error = "Konfirmasi kata sandi tidak cocok dengan kata sandi utama!";
    } elseif (strlen($password) < 6) {
        $error = "Kata sandi minimal harus terdiri dari 6 karakter demi keamanan akun.";
    } else {
        // Cek duplikasi NIK & Email
        $stmt = $pdo->prepare("SELECT id FROM karyawan WHERE nik = ? OR email = ?");
        $stmt->execute([$nik, $email]);
        if ($stmt->fetch()) {
            $error = "NIK atau Email ini sudah terdaftar dalam basis data sistem!";
        } else {
            // Hash Password
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO karyawan (nik, nama, jabatan, no_hp, email, password, alamat, role) VALUES (?, ?, ?, ?, ?, ?, ?, 'karyawan')");
            if ($stmt->execute([$nik, $nama, $jabatan, $no_hp, $email, $hash, $alamat])) {
                // Notifikasi ke Karyawan
                $msgKaryawan = "*REGISTRASI KARYAWAN BERHASIL*\n\n"
                    . "Halo {$nama},\n"
                    . "Akun Anda telah berhasil terdaftar pada Sistem Enterprise HRMS.\n"
                    . "NIK     : {$nik}\n"
                    . "Jabatan : {$jabatan}\n\n"
                    . "Silakan login menggunakan NIK atau Email terdaftar.\n"
                    . "Terima kasih.";
                kirimWA($no_hp, $msgKaryawan);

                // Notifikasi ke Admin
                $msgAdmin = "*NOTIFIKASI PENDAFTARAN KARYAWAN BARU*\n\n"
                    . "Telah terdaftar karyawan baru:\n"
                    . "Nama    : {$nama}\n"
                    . "NIK     : {$nik}\n"
                    . "Jabatan : {$jabatan}\n"
                    . "No. HP  : {$no_hp}\n"
                    . "Email   : {$email}\n\n"
                    . "Data telah tersinkronisasi ke dashboard manajemen.";
                kirimWA(ADMIN_PHONE, $msgAdmin);

                $success = "Pendaftaran berhasil! Akun Anda aktif dan dapat langsung digunakan untuk presensi.";
            } else {
                $error = "Terjadi kesalahan internal saat menyimpan data registrasi.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Karyawan Baru | Enterprise HRMS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .form-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        @media (max-width: 640px) {
            .form-grid-2 {
                grid-template-columns: 1fr;
            }
        }
        .section-divider {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 1.25rem 0 1rem;
            color: #64748b;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .section-divider::before, .section-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e2e8f0;
        }
    </style>
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card auth-card-wide">
        <div class="auth-header">
            <div class="auth-logo-badge">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="8.5" cy="7.5" r="4"></circle>
                    <line x1="20" y1="8" x2="20" y2="14"></line>
                    <line x1="23" y1="11" x2="17" y2="11"></line>
                </svg>
            </div>
            <h1 class="auth-title">Pendaftaran Akun Karyawan</h1>
            <p class="auth-subtitle">Lengkapi profil identitas untuk akses sistem presensi perusahaan</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                <div>
                    <div><strong><?= htmlspecialchars($success) ?></strong></div>
                    <div style="margin-top: 6px;"><a href="login.php" class="btn btn-sm btn-primary" style="display: inline-block;">Lanjut ke Halaman Login →</a></div>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="section-divider">1. Identitas Karyawan</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Nomor Induk Karyawan (NIK) *</label>
                    <input type="text" name="nik" class="form-control" placeholder="Contoh: 19870102" value="<?= htmlspecialchars($_POST['nik'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Nama Lengkap & Gelar *</label>
                    <input type="text" name="nama" class="form-control" placeholder="Contoh: Ahmad Wijaya" value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Jabatan / Divisi *</label>
                <input type="text" name="jabatan" class="form-control" placeholder="Contoh: Senior Software Engineer / IT" value="<?= htmlspecialchars($_POST['jabatan'] ?? '') ?>" required>
            </div>

            <div class="section-divider">2. Informasi Kontak & Domisili</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Nomor WhatsApp Aktif *</label>
                    <input type="text" name="no_hp" class="form-control" placeholder="Contoh: 081234567890" value="<?= htmlspecialchars($_POST['no_hp'] ?? '') ?>" required>
                    <small style="color: #64748b; font-size: 0.72rem;">*Notifikasi presensi akan dikirim otomatis ke nomor ini.</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Alamat Email *</label>
                    <input type="email" name="email" class="form-control" placeholder="nama@perusahaan.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Alamat Tempat Tinggal</label>
                <input type="text" name="alamat" class="form-control" placeholder="Jl. Sudirman No. 45, Jakarta Pusat" value="<?= htmlspecialchars($_POST['alamat'] ?? '') ?>">
            </div>

            <div class="section-divider">3. Kredensial Keamanan Akun</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Kata Sandi Baru *</label>
                    <input type="password" id="regPassword" name="password" class="form-control" placeholder="Minimal 6 karakter" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Konfirmasi Kata Sandi *</label>
                    <input type="password" id="regKonfirmasi" name="konfirmasi" class="form-control" placeholder="Ulangi kata sandi" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 1rem;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="8.5" cy="7.5" r="4"></circle>
                    <polyline points="17 11 19 13 23 9"></polyline>
                </svg>
                Daftarkan Akun Karyawan
            </button>
        </form>

        <div class="auth-footer">
            Sudah memiliki akun terdaftar? <a href="login.php">Masuk ke Portal</a>
        </div>
    </div>
</div>
</body>
</html>

</html>