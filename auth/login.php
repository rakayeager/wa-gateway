<?php
// auth/login.php
session_start();
require_once '../config/database.php';

// Jika sudah login, redirect sesuai peran
if (isset($_SESSION['user_id'])) {
    if (($_SESSION['role'] ?? '') === 'admin') {
        header("Location: ../admin/index.php");
    } else {
        header("Location: ../absensi/index.php");
    }
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');
    $password   = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM karyawan WHERE nik = ? OR email = ?");
    $stmt->execute([$identifier, $identifier]);
    $user = $stmt->fetch();

    $isValid = false;
    if ($user) {
        if (password_verify($password, $user['password'])) {
            $isValid = true;
        } elseif ($password === $user['password']) {
            // Jika password tersimpan plain-text (misal diedit manual di database), migrasi otomatis ke bcrypt
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $up = $pdo->prepare("UPDATE karyawan SET password = ? WHERE id = ?");
            $up->execute([$newHash, $user['id']]);
            $isValid = true;
        }
    }

    if ($isValid) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nama']    = $user['nama'];
        $_SESSION['nik']     = $user['nik'];
        $_SESSION['role']    = $user['role'];

        if ($user['role'] === 'admin') {
            header("Location: ../admin/index.php");
        } else {
            header("Location: ../absensi/index.php");
        }
        exit;
    } else {
        $error = "Kombinasi NIK/Email atau password tidak sesuai!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enterprise Portal Login | HRMS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo-badge">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                </svg>
            </div>
            <h1 class="auth-title">HRMS Enterprise</h1>
            <p class="auth-subtitle">Sistem Terpadu Presensi & Manajemen Karyawan</p>
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

        <form method="POST" autocomplete="on">
            <div class="form-group">
                <label class="form-label" for="identifier">NIK atau Email Perusahaan</label>
                <div style="position: relative;">
                    <input 
                        type="text" 
                        id="identifier" 
                        name="identifier" 
                        class="form-control" 
                        placeholder="Contoh: 000 atau admin@perusahaan.com" 
                        value="<?= htmlspecialchars($_POST['identifier'] ?? '') ?>" 
                        required 
                        autofocus
                    >
                </div>
            </div>

            <div class="form-group">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <label class="form-label" for="password" style="margin-bottom: 0;">Kata Sandi</label>
                </div>
                <div class="password-field-wrapper">
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-control" 
                        placeholder="Masukkan kata sandi Anda" 
                        required
                    >
                    <button type="button" class="password-toggle-btn" id="togglePasswordBtn" title="Tampilkan/Sembunyikan password">
                        <svg id="eyeIcon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 0.5rem;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                    <polyline points="10 17 15 12 10 7"></polyline>
                    <line x1="15" y1="12" x2="3" y2="12"></line>
                </svg>
                Masuk ke Portal
            </button>
        </form>

        <!-- Akun Demo Cepat untuk Pengujian Pengguna -->
        <div class="demo-accounts">
            <div class="demo-title">Pintasan Kredensial Uji Coba:</div>
            <div class="demo-chips">
                <span class="demo-chip" onclick="fillCredential('admin@perusahaan.com', 'admin123')">🔑 Admin: admin123</span>
                <span class="demo-chip" onclick="fillCredential('rakakrispy@gmail.com', '12345678')">👤 Karyawan: 12345678</span>
            </div>
        </div>

        <div class="auth-footer">
            Belum terdaftar sebagai karyawan? <a href="register.php">Registrasi Akun Baru</a>
        </div>
    </div>
</div>

<script>
// Toggle Password Visibility
const toggleBtn = document.getElementById('togglePasswordBtn');
const passwordInput = document.getElementById('password');
const eyeIcon = document.getElementById('eyeIcon');

toggleBtn.addEventListener('click', function() {
    const isPassword = passwordInput.getAttribute('type') === 'password';
    passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
    if (isPassword) {
        eyeIcon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
    } else {
        eyeIcon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
    }
});

// Quick fill for testing
function fillCredential(user, pass) {
    document.getElementById('identifier').value = user;
    document.getElementById('password').value = pass;
}
</script>
</body>
</html>