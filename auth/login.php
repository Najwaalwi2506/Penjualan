<?php
include '../Koneksi.php';

// Jika sudah login, redirect
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'penjual') {
        header('Location: ../penjual/dashboard.php');
    } elseif ($_SESSION['role'] == 'admin') {
        header('Location: ../admin/index.php');
    } else {
        header('Location: ../pembeli/dashboard.php');
    }
    exit;
}

header('Location: ../index.php');
?>
