<?php
// index.php
session_start();
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/index.php");
    } else {
        header("Location: absensi/index.php");
    }
} else {
    header("Location: auth/login.php");
}
exit;