<?php
// karyawan/index.php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$stmt = $pdo->query("SELECT * FROM karyawan ORDER BY id DESC");
$karyawanList = $stmt->fetchAll();
$adminNama = $_SESSION['nama'] ?? 'Administrator';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Data Karyawan | Enterprise HRMS</title>
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
            <a href="../admin/index.php" class="nav-link">
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
            <a href="index.php" class="nav-link active">
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
    <div class="breadcrumb">
        <a href="../admin/index.php">Dashboard</a>
        <span>/</span>
        <span>Direktori Karyawan</span>
    </div>

    <!-- Header Section -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Direktori Anggota Karyawan</h1>
            <p class="page-desc">Kelola seluruh data identitas, jabatan, dan kontak karyawan perusahaan.</p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <a href="tambah.php" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Tambah Karyawan Baru
            </a>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
            <div style="font-size: 0.9rem; font-weight: 600; color: #475569;">
                Total: <span style="color: #2563eb;"><?= count($karyawanList) ?></span> Orang Terdaftar
            </div>
            <div style="position: relative;">
                <input type="text" id="karyawanSearch" placeholder="Cari NIK, nama, divisi..." class="form-control" style="padding: 7px 12px 7px 34px; font-size: 0.85rem; width: 260px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%);">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table-custom" id="karyawanTable">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Profil Karyawan</th>
                        <th>Jabatan / Divisi</th>
                        <th>WhatsApp Gateway</th>
                        <th>Email Resmi</th>
                        <th>Hak Akses</th>
                        <th style="text-align: right;">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($karyawanList)): ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <div style="font-weight: 600; color: #334155; margin-bottom: 4px;">Belum Ada Data Karyawan</div>
                                    <div style="font-size: 0.82rem;">Klik tombol "Tambah Karyawan Baru" di atas untuk mendaftarkan karyawan pertama.</div>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($karyawanList as $i => $row): ?>
                        <tr>
                            <td style="color: #94a3b8; font-weight: 600;"><?= $i + 1 ?></td>
                            <td>
                                <div class="user-cell">
                                    <div class="avatar-cell"><?= strtoupper(substr($row['nama'], 0, 2)) ?></div>
                                    <div>
                                        <div class="user-cell-name"><?= htmlspecialchars($row['nama']) ?></div>
                                        <div class="user-cell-sub">NIK: <code><?= htmlspecialchars($row['nik']) ?></code></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-neutral" style="font-weight: 600;">
                                    <?= htmlspecialchars($row['jabatan']) ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $cleanPhone = preg_replace('/[^0-9]/', '', $row['no_hp']);
                                if (strpos($cleanPhone, '0') === 0) {
                                    $waTarget = '62' . substr($cleanPhone, 1);
                                } else {
                                    $waTarget = $cleanPhone;
                                }
                                ?>
                                <a href="https://wa.me/<?= $waTarget ?>" target="_blank" class="wa-link" title="Buka chat WhatsApp">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.174.086.275.073.376-.044.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.203c.043.072.043.419-.101.824z"/>
                                    </svg>
                                    <?= htmlspecialchars($row['no_hp']) ?>
                                </a>
                            </td>
                            <td>
                                <span style="font-size: 0.83rem; color: #475569;"><?= htmlspecialchars($row['email']) ?></span>
                            </td>
                            <td>
                                <?php if ($row['role'] === 'admin'): ?>
                                    <span class="badge badge-primary">Admin HR</span>
                                <?php else: ?>
                                    <span class="badge badge-neutral">Karyawan</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right;">
                                <div class="action-group" style="justify-content: flex-end;">
                                    <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-secondary btn-sm" title="Edit data">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                        Edit
                                    </a>
                                    <a href="hapus.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus data karyawan <?= addslashes($row['nama']) ?>? Tindakan ini akan mengirimkan notifikasi WA ke yang bersangkutan.')" title="Hapus akun">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                        </svg>
                                        Hapus
                                    </a>
                                </div>
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
// Filter tabel karyawan secara instan
document.getElementById('karyawanSearch').addEventListener('input', function() {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('#karyawanTable tbody tr');
    
    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});
</script>
</body>
</html>

</html>