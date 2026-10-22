# Enterprise HRMS & Presensi Karyawan (Fonnte API WhatsApp Gateway)

Aplikasi Enterprise Human Resource Management System (HRMS) & Presensi Karyawan berbasis PHP Native dan MySQL dengan standar UI/UX modern (Enterprise Design System) dan integrasi WhatsApp Gateway Fonnte.

## ✨ Fitur Utama
- **Autentikasi Aman & Cerdas**:
  - Hashing kata sandi berbasis bcrypt (`password_hash` & `password_verify`).
  - Auto-fallback & migrasi otomatis kata sandi jika diubah langsung via database.
  - Password show/hide toggle & pintasan kredensial pengujian cepat.
- **Notifikasi WhatsApp Otomatis (Fonnte Gateway)**:
  - Notifikasi instan saat karyawan baru mendaftar atau didaftarkan admin.
  - Notifikasi saat data profil karyawan diperbarui atau dihapus.
  - Notifikasi konfirmasi absensi masuk dan absensi pulang beserta timestamp.
- **Executive Admin Dashboard**:
  - KPI Stat Cards: Total Tim, Hadir Hari Ini (% kehadiran), Belum Presensi, dan Telah Pulang.
  - Filter riwayat presensi berdasarkan tanggal kalender.
  - Filter pencarian instan (live search) karyawan pada tabel.
- **Employee Self-Service (ESS) Portal**:
  - Jam digital server real-time (WIB) dengan tanggal otomatis.
  - Status presensi dua langkah (Masuk & Pulang) dengan stepper interaktif.
  - Rekap riwayat presensi 7 hari terakhir karyawan bersangkutan.
- **Direktori Karyawan Terpadu**:
  - Pencarian live multi-kolom (NIK, Nama, Jabatan).
  - Tautan WhatsApp langsung (1-klik langsung membuka chat ke karyawan).
  - Manajemen Tambah, Edit, dan Hapus data karyawan dengan konfirmasi aman.

## 🔑 Kredensial Default untuk Pengujian
- **Administrator**:
  - NIK / Email: `admin@perusahaan.com` atau `000`
  - Password: `admin123`
- **Karyawan**:
  - NIK / Email: `rakakrispy@gmail.com` atau `1234567812345678`
  - Password: `12345678`
