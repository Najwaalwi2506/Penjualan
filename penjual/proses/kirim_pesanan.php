<?php
include '../../Koneksi.php';
check_login();
check_role(['penjual']);

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pesanan.php');
    exit;
}

$pesanan_id = isset($_POST['pesanan_id']) ? sanitize($koneksi, $_POST['pesanan_id']) : 0;

// Pastikan pesanan milik toko penjual ini
$cek = mysqli_query($koneksi, "SELECT p.* FROM pesanan p JOIN toko t ON p.toko_id = t.id WHERE p.id = $pesanan_id AND t.user_id = $user_id");
if (!$cek || mysqli_num_rows($cek) == 0) {
    header('Location: ../pesanan.php?error=Pesanan+tidak+ditemukan');
    exit;
}

$pesanan = mysqli_fetch_assoc($cek);

// Hanya izinkan transisi dari 'dikonfirmasi' -> 'dikirim'
if ($pesanan['status'] !== 'dikonfirmasi') {
    header('Location: ../pesanan_detail.php?id=' . $pesanan_id . '&error=Status+tidak+dapat+ditandai+dikirim');
    exit;
}

$update = mysqli_query($koneksi, "UPDATE pesanan SET status = 'dikirim', updated_at = NOW() WHERE id = $pesanan_id");
if (!$update) {
    header('Location: ../pesanan_detail.php?id=' . $pesanan_id . '&error=Gagal+menandai+dikirim');
    exit;
}

// Redirect kembali ke detail pesanan
header('Location: ../pesanan_detail.php?id=' . $pesanan_id . '&success=Pesanan+ditandai+dikirim');
exit;

?>