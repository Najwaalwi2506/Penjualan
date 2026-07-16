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
$catatan = isset($_POST['catatan']) ? sanitize($koneksi, $_POST['catatan']) : '';

if ($pesanan_id <= 0) {
    header('Location: ../pesanan.php?error=invalid');
    exit;
}

$pesanan = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT id, status, catatan FROM pesanan WHERE id = $pesanan_id AND pembeli_id = $user_id"));
if (!$pesanan) {
    header('Location: ../pesanan.php?error=not_found');
    exit;
}

if (!in_array($pesanan['status'], ['dikirim', 'siap_diterima'], true)) {
    header('Location: ../pesanan.php?error=status');
    exit;
}

$catatan_sql = $catatan !== '' ? "'" . mysqli_real_escape_string($koneksi, $catatan) . "'" : "'" . mysqli_real_escape_string($koneksi, $pesanan['catatan'] ?? '') . "'";
mysqli_query($koneksi, "UPDATE pesanan SET status = 'diterima', catatan = {$catatan_sql}, updated_at = NOW() WHERE id = $pesanan_id");
$subject = 'Pembeli Menyatakan Barang Diterima';
$message = '<p>Pesanan dengan ID ' . $pesanan_id . ' telah dinyatakan diterima oleh pembeli.</p>';
send_notification_email($koneksi, $subject, $message);
header('Location: ../pesanan_detail.php?id=' . $pesanan_id . '&success=terima');
exit;
