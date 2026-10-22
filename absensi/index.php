<?php
// absensi/index.php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$id_karyawan = $_SESSION['user_id'];
$today = date('Y-m-d');

// Ambil status absen hari ini
$stmt = $pdo->prepare("SELECT * FROM absensi WHERE id_karyawan = ? AND tanggal = ?");
$stmt->execute([$id_karyawan, $today]);
$absen = $stmt->fetch();

// Ambil detail data karyawan
$userStmt = $pdo->prepare("SELECT * FROM karyawan WHERE id = ?");
$userStmt->execute([$id_karyawan]);
$user = $userStmt->fetch();

// Ambil riwayat presensi 7 hari terakhir karyawan ini
$histStmt = $pdo->prepare("SELECT * FROM absensi WHERE id_karyawan = ? ORDER BY tanggal DESC LIMIT 7");
$histStmt->execute([$id_karyawan]);
$riwayatAbsensi = $histStmt->fetchAll();

$employeeName = $user['nama'] ?? 'Karyawan';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Presensi Karyawan | Enterprise HRMS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .kiosk-container {
            max-width: 600px;
            margin: 0 auto;
        }
        .user-profile-header {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 1.25rem;
            background: #ffffff;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-xs);
            margin-bottom: 1.5rem;
        }
        .user-big-avatar {
            width: 54px;
            height: 54px;
            border-radius: 50%;
            background: linear-gradient(135deg, #2563eb 0%, #4f46e5 100%);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            font-weight: 800;
            box-shadow: 0 4px 10px rgba(37, 99, 235, 0.25);
            flex-shrink: 0;
        }
        .btn-attendance-action {
            width: 100%;
            padding: 16px 24px;
            font-size: 1.05rem;
            font-weight: 800;
            border-radius: var(--radius-md);
            letter-spacing: 0.02em;
            text-transform: uppercase;
            box-shadow: 0 4px 14px 0 rgba(0, 0, 0, 0.12);
        }
    </style>
</head>
<body>

<!-- Enterprise Navbar -->
<nav class="navbar">
    <a href="index.php" class="nav-brand">
        <div class="brand-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
            </svg>
        </div>
        <div>
            <div class="brand-title">HRMS Enterprise</div>
            <div class="brand-subtitle">Employee Self-Service (ESS)</div>
        </div>
    </a>

    <div class="nav-user">
        <div class="user-badge">
            <div class="avatar-sm"><?= strtoupper(substr($employeeName, 0, 1)) ?></div>
            <div>
                <div class="user-meta-name"><?= htmlspecialchars($employeeName) ?></div>
                <div class="user-meta-role">Karyawan</div>
            </div>
        </div>
        <a href="../auth/logout.php" class="btn-nav-logout" title="Keluar dari akun">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <polyline points="16 17 21 12 16 7"></polyline>
                <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
            Keluar
        </a>
    </div>
</nav>

<div class="container">
    <div class="kiosk-container">

        <!-- User Profile Card -->
        <div class="user-profile-header">
            <div class="user-big-avatar"><?= strtoupper(substr($employeeName, 0, 2)) ?></div>
            <div style="flex: 1;">
                <div style="font-size: 1.15rem; font-weight: 800; color: #0f172a;"><?= htmlspecialchars($user['nama']) ?></div>
                <div style="font-size: 0.82rem; color: #64748b; margin-top: 2px;">
                    NIK: <strong><?= htmlspecialchars($user['nik']) ?></strong> &bull; <?= htmlspecialchars($user['jabatan']) ?>
                </div>
            </div>
            <span class="badge badge-success">
                <span class="badge-dot"></span> Aktif
            </span>
        </div>

        <!-- Live Digital Clock Widget -->
        <div class="live-clock-box">
            <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; color: #94a3b8; font-weight: 700; margin-bottom: 4px;">
                Waktu Server Presensi Real-Time
            </div>
            <div class="live-time" id="digitalClock"><?= date('H:i:s') ?></div>
            <div class="live-date" id="digitalDate"><?= date('l, d F Y') ?></div>
        </div>

        <!-- Attendance Stepper Status -->
        <div class="card" style="margin-bottom: 1.5rem;">
            <div style="text-align: center; margin-bottom: 1.25rem;">
                <h3 style="font-size: 1.1rem; font-weight: 800; color: #0f172a;">Status Presensi Hari Ini</h3>
                <p style="font-size: 0.82rem; color: #64748b; margin-top: 2px;">Konfirmasi jam masuk dan kepulangan kerja Anda</p>
            </div>

            <div class="attendance-steps">
                <!-- Step 1: Jam Masuk -->
                <div class="step-card <?= !empty($absen['jam_masuk']) ? 'active' : '' ?>">
                    <div class="step-title">Presensi Masuk</div>
                    <?php if (!empty($absen['jam_masuk'])): ?>
                        <div class="step-time"><?= htmlspecialchars($absen['jam_masuk']) ?> WIB</div>
                        <span class="badge badge-success" style="margin-top: 6px;">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            Tercatat
                        </span>
                    <?php else: ?>
                        <div class="step-time" style="color: #94a3b8;">-- : -- : --</div>
                        <span class="badge badge-warning" style="margin-top: 6px;">Belum Masuk</span>
                    <?php endif; ?>
                </div>

                <!-- Step 2: Jam Pulang -->
                <div class="step-card <?= !empty($absen['jam_pulang']) ? 'active' : '' ?>">
                    <div class="step-title">Presensi Pulang</div>
                    <?php if (!empty($absen['jam_pulang'])): ?>
                        <div class="step-time"><?= htmlspecialchars($absen['jam_pulang']) ?> WIB</div>
                        <span class="badge badge-info" style="margin-top: 6px;">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            Selesai
                        </span>
                    <?php else: ?>
                        <div class="step-time" style="color: #94a3b8;">-- : -- : --</div>
                        <span class="badge badge-neutral" style="margin-top: 6px;">Menunggu</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Action CTA Button -->
            <div style="margin-top: 1rem;">
                <?php if (!$absen): ?>
                    <a href="masuk.php" class="btn btn-primary btn-attendance-action" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        Catat Presensi Masuk Sekarang
                    </a>
                <?php elseif ($absen && empty($absen['jam_pulang'])): ?>
                    <a href="pulang.php" class="btn btn-primary btn-attendance-action" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18.36 6.64a9 9 0 1 1-12.73 0"></path>
                            <line x1="12" y1="2" x2="12" y2="12"></line>
                        </svg>
                        Catat Presensi Pulang
                    </a>
                <?php else: ?>
                    <div style="background: #f8fafc; border: 2px dashed #cbd5e1; border-radius: var(--radius-md); padding: 18px; text-align: center;">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin: 0 auto 8px;">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                        <div style="font-weight: 800; font-size: 1rem; color: #166534;">Presensi Hari Ini Selesai</div>
                        <div style="font-size: 0.82rem; color: #64748b; margin-top: 2px;">Terima kasih atas dedikasi kerja Anda hari ini. Selamat beristirahat!</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Riwayat Presensi Singkat Karyawan -->
        <div class="card">
            <h4 style="font-size: 0.95rem; font-weight: 700; color: #0f172a; margin-bottom: 1rem; display: flex; align-items: center; gap: 8px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                Riwayat Presensi Saya (7 Hari Terakhir)
            </h4>

            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Masuk</th>
                            <th>Pulang</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($riwayatAbsensi)): ?>
                            <tr>
                                <td colspan="4" style="text-align: center; color: #94a3b8; padding: 20px;">
                                    Belum ada catatan riwayat presensi.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($riwayatAbsensi as $r): ?>
                            <tr>
                                <td style="font-weight: 600; color: #334155;">
                                    <?= date('d M Y', strtotime($r['tanggal'])) ?>
                                </td>
                                <td>
                                    <?= !empty($r['jam_masuk']) ? htmlspecialchars($r['jam_masuk']) . ' WIB' : '-' ?>
                                </td>
                                <td>
                                    <?= !empty($r['jam_pulang']) ? htmlspecialchars($r['jam_pulang']) . ' WIB' : '-' ?>
                                </td>
                                <td>
                                    <?php if ($r['status'] === 'Pulang'): ?>
                                        <span class="badge badge-info">Selesai</span>
                                    <?php elseif ($r['status'] === 'Hadir'): ?>
                                        <span class="badge badge-success">Hadir</span>
                                    <?php else: ?>
                                        <span class="badge badge-neutral"><?= htmlspecialchars($r['status']) ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script>
// Jam Digital Real-Time
function updateClock() {
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    document.getElementById('digitalClock').textContent = `${hours}:${minutes}:${seconds}`;
}
setInterval(updateClock, 1000);
</script>
</body>
</html>

</html>