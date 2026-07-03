<?php
include '../../Koneksi.php';
check_login();
check_role(['pembeli']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pesanan.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$pesanan_id = isset($_POST['pesanan_id']) ? (int) sanitize($koneksi, $_POST['pesanan_id']) : 0;

if ($pesanan_id <= 0) {
    header('Location: ../pesanan.php?error=invalid');
    exit;
}

$pesanan = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT id, status FROM pesanan WHERE id = $pesanan_id AND pembeli_id = $user_id"));
if (!$pesanan) {
    header('Location: ../pesanan.php?error=not_found');
    exit;
}

if ($pesanan['status'] !== 'dikirim') {
    header('Location: ../pesanan.php?error=status');
    exit;
}

mysqli_query($koneksi, "UPDATE pesanan SET status = 'selesai', updated_at = NOW() WHERE id = $pesanan_id");
header('Location: ../pesanan_detail.php?id=' . $pesanan_id . '&success=terima');
exit;
