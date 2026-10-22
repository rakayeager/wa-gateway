<?php
// admin/index.php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$today = date('Y-m-d');
$filterTanggal = $_GET['tanggal'] ?? $today;

// 1. Data Ringkasan (Dashboard)
$countKaryawan = $pdo->query("SELECT COUNT(*) FROM karyawan WHERE role = 'karyawan'")->fetchColumn();

$stmtSudahAbsen = $pdo->prepare("SELECT COUNT(*) FROM absensi WHERE tanggal = ? AND jam_masuk IS NOT NULL");
$stmtSudahAbsen->execute([$today]);
$countSudahAbsen = $stmtSudahAbsen->fetchColumn();

$countBelumAbsen = max(0, $countKaryawan - $countSudahAbsen);

$stmtSudahPulang = $pdo->prepare("SELECT COUNT(*) FROM absensi WHERE tanggal = ? AND jam_pulang IS NOT NULL");
$stmtSudahPulang->execute([$today]);
$countSudahPulang = $stmtSudahPulang->fetchColumn();

// Hitung persentase kehadiran
$persenHadir = $countKaryawan > 0 ? round(($countSudahAbsen / $countKaryawan) * 100) : 0;

// 2. Data Laporan Absensi
$stmtLaporan = $pdo->prepare("
    SELECT a.*, k.nama, k.nik, k.jabatan 
    FROM absensi a 
    JOIN karyawan k ON a.id_karyawan = k.id 
    WHERE a.tanggal = ? 
    ORDER BY a.id DESC
");
$stmtLaporan->execute([$filterTanggal]);
$laporanAbsensi = $stmtLaporan->fetchAll();

$adminNama = $_SESSION['nama'] ?? 'Administrator';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enterprise Admin Dashboard | HRMS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
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
            <div class="brand-subtitle">Management Console</div>
        </div>
    </a>

    <ul class="nav-links">
        <li>
            <a href="index.php" class="nav-link active">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
                Dashboard
            </a>
        </li>
        <li>
            <a href="../karyawan/index.php" class="nav-link">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                Data Karyawan
            </a>
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
        <a href="../auth/logout.php" class="btn-nav-logout" title="Keluar dari sistem">
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
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Executive Dashboard</h1>
            <p class="page-desc">Monitoring operasional presensi dan utilisasi kehadiran seluruh karyawan perusahaan.</p>
        </div>
        <div style="display: flex; align-items: center; gap: 8px;">
            <span class="badge badge-neutral" style="padding: 8px 14px; font-size: 0.82rem;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                Zona Waktu: Asia/Jakarta (WIB)
            </span>
        </div>
    </div>

    <!-- Ringkasan Statistik KPI Cards -->
    <div class="stats-grid">
        <!-- Stat 1: Total Karyawan -->
        <div class="stat-card stat-primary">
            <div class="stat-info-box">
                <h4>Total Anggota Tim</h4>
                <div class="stat-number"><?= $countKaryawan ?></div>
                <div class="stat-sub">Karyawan aktif terdaftar</div>
            </div>
            <div class="stat-icon primary">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
        </div>

        <!-- Stat 2: Sudah Absen Hadir -->
        <div class="stat-card stat-success">
            <div class="stat-info-box">
                <h4>Hadir Hari Ini</h4>
                <div class="stat-number"><?= $countSudahAbsen ?></div>
                <div class="stat-sub" style="color: #16a34a; font-weight: 600;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                        <polyline points="17 6 23 6 23 12"></polyline>
                    </svg>
                    <?= $persenHadir ?>% tingkat kehadiran
                </div>
            </div>
            <div class="stat-icon success">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
        </div>

        <!-- Stat 3: Belum Hadir -->
        <div class="stat-card stat-warning">
            <div class="stat-info-box">
                <h4>Belum Presensi</h4>
                <div class="stat-number"><?= $countBelumAbsen ?></div>
                <div class="stat-sub">Menunggu konfirmasi masuk</div>
            </div>
            <div class="stat-icon warning">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
            </div>
        </div>

        <!-- Stat 4: Selesai / Pulang -->
        <div class="stat-card stat-info">
            <div class="stat-info-box">
                <h4>Telah Pulang</h4>
                <div class="stat-number"><?= $countSudahPulang ?></div>
                <div class="stat-sub">Menyelesaikan jam kerja</div>
            </div>
            <div class="stat-icon info">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                </svg>
            </div>
        </div>
    </div>

    <!-- Data Laporan Absensi -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h3 style="font-size: 1.15rem; font-weight: 700; color: #0f172a;">Jurnal Presensi Karyawan</h3>
                <p style="font-size: 0.82rem; color: #64748b; margin-top: 2px;">Daftar rekap log absensi harian yang tercatat di sistem.</p>
            </div>

            <!-- Toolbar Filter -->
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <form method="GET" style="display: flex; align-items: center; gap: 8px;">
                    <div style="display: flex; align-items: center; background: #fff; border: 1px solid #cbd5e1; border-radius: var(--radius-sm); padding: 2px 10px;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        <input type="date" name="tanggal" value="<?= htmlspecialchars($filterTanggal) ?>" style="border: none; outline: none; font-size: 0.85rem; padding: 6px 0; color: #334155; font-family: inherit;">
                    </div>
                    <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
                    <?php if ($filterTanggal !== $today): ?>
                        <a href="index.php" class="btn btn-sm btn-secondary" title="Kembali ke hari ini">Hari Ini</a>
                    <?php endif; ?>
                </form>

                <!-- Search box in table -->
                <div style="position: relative;">
                    <input type="text" id="tableSearch" placeholder="Cari nama atau NIK..." class="form-control" style="padding: 6px 12px 6px 32px; font-size: 0.82rem; width: 200px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%);">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table-custom" id="reportTable">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Karyawan</th>
                        <th>Jabatan</th>
                        <th>Tanggal Presensi</th>
                        <th>Jam Masuk</th>
                        <th>Jam Pulang</th>
                        <th>Status Verifikasi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($laporanAbsensi)): ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <svg class="empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                        <line x1="16" y1="2" x2="16" y2="6"></line>
                                        <line x1="8" y1="2" x2="8" y2="6"></line>
                                        <line x1="3" y1="10" x2="21" y2="10"></line>
                                    </svg>
                                    <div style="font-weight: 600; color: #334155; margin-bottom: 4px;">Belum Ada Riwayat Presensi</div>
                                    <div style="font-size: 0.82rem;">Tidak ada data log absensi karyawan untuk tanggal <?= date('d M Y', strtotime($filterTanggal)) ?>.</div>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($laporanAbsensi as $i => $lap): ?>
                        <tr>
                            <td style="color: #94a3b8; font-weight: 600;"><?= $i + 1 ?></td>
                            <td>
                                <div class="user-cell">
                                    <div class="avatar-cell"><?= strtoupper(substr($lap['nama'], 0, 2)) ?></div>
                                    <div>
                                        <div class="user-cell-name"><?= htmlspecialchars($lap['nama']) ?></div>
                                        <div class="user-cell-sub">NIK: <?= htmlspecialchars($lap['nik']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-neutral"><?= htmlspecialchars($lap['jabatan'] ?? 'Staff') ?></span>
                            </td>
                            <td style="font-weight: 500;">
                                <?= date('d M Y', strtotime($lap['tanggal'])) ?>
                            </td>
                            <td>
                                <?php if (!empty($lap['jam_masuk'])): ?>
                                    <span style="font-weight: 700; color: #16a34a; display: inline-flex; align-items: center; gap: 4px;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="20 6 9 17 4 12"></polyline>
                                        </svg>
                                        <?= htmlspecialchars($lap['jam_masuk']) ?> WIB
                                    </span>
                                <?php else: ?>
                                    <span style="color: #94a3b8;">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($lap['jam_pulang'])): ?>
                                    <span style="font-weight: 700; color: #0284c7; display: inline-flex; align-items: center; gap: 4px;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="20 6 9 17 4 12"></polyline>
                                        </svg>
                                        <?= htmlspecialchars($lap['jam_pulang']) ?> WIB
                                    </span>
                                <?php else: ?>
                                    <span style="color: #94a3b8; font-style: italic;">Sedang Bekerja</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($lap['status'] === 'Hadir'): ?>
                                    <span class="badge badge-success">
                                        <span class="badge-dot"></span> Hadir di Kantor
                                    </span>
                                <?php elseif ($lap['status'] === 'Pulang'): ?>
                                    <span class="badge badge-info">
                                        <span class="badge-dot"></span> Selesai (Pulang)
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-warning">
                                        <span class="badge-dot"></span> <?= htmlspecialchars($lap['status']) ?>
                                    </span>
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

<script>
// Filter pencarian instan pada tabel
document.getElementById('tableSearch').addEventListener('input', function() {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('#reportTable tbody tr');
    
    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});
</script>
</body>
</html>

</html>