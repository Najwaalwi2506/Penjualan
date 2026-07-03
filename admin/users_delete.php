<?php
include '../Koneksi.php';
check_login();
check_role(['admin']);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: users.php");
    exit;
}

// Jangan biarkan admin menghapus dirinya sendiri
$admin_id = (int)$_SESSION['user_id'];
if ($id == $admin_id) {
    header("Location: users.php?status=self");
    exit;
}

// Cek apakah user memiliki riwayat pesanan
$cek = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM pesanan WHERE pembeli_id = $id");
$data = mysqli_fetch_assoc($cek);

if ($data['total'] > 0) {

    // User pernah bertransaksi, nonaktifkan akun
    mysqli_query($koneksi, "UPDATE users SET is_active = 0 WHERE id = $id");

    header("Location: users.php?status=nonaktif");
    exit;

} else {

    // User belum pernah transaksi, hapus permanen
    mysqli_query($koneksi, "DELETE FROM users WHERE id = $id");

    header("Location: users.php?status=hapus");
    exit;
}