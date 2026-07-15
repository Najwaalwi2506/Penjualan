<?php
include '../../Koneksi.php';
check_login();
check_role(['penjual']);

$user_id = $_SESSION['user_id'];
$toko = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM toko WHERE user_id = $user_id"));
$toko_id = $toko['id'];

$pesanan_id = isset($_POST['pesanan_id']) ? sanitize($koneksi, $_POST['pesanan_id']) : 0;

$cek = mysqli_query($koneksi, "SELECT p.* FROM pesanan p WHERE p.id = $pesanan_id AND p.toko_id = $toko_id");
if (!$cek || mysqli_num_rows($cek) == 0) {
    header('Location: ../pesanan.php?error=Pesanan+tidak+ditemukan');
    exit;
}

$pesanan = mysqli_fetch_assoc($cek);
if ($pesanan['status'] !== 'menunggu_konfirmasi') {
    header('Location: ../pesanan_detail.php?id=' . $pesanan_id . '&error=Status+tidak+dapat+ditolak');
    exit;
}

// Update status pesanan hanya jika aksi pembatalan memang dikirim melalui form POST
$query = "UPDATE pesanan SET status = 'dibatalkan' WHERE id = $pesanan_id AND toko_id = $toko_id AND status = 'menunggu_konfirmasi'";
if (mysqli_query($koneksi, $query)) {
    // Kembalikan stok produk
    $detail = mysqli_query($koneksi, "SELECT * FROM detail_pesanan WHERE pesanan_id = $pesanan_id");
    while ($row = mysqli_fetch_assoc($detail)) {
        $produk_id = $row['produk_id'];
        $jumlah = $row['jumlah'];
        mysqli_query($koneksi, "UPDATE produk SET jumlah_stok = jumlah_stok + $jumlah WHERE id = $produk_id");
    }

    $subject = 'Pesanan Ditolak oleh Penjual';
    $message = '<p>Pesanan dengan ID ' . $pesanan_id . ' telah ditolak oleh penjual.</p>';
    send_notification_email($koneksi, $subject, $message);
    
    header('Location: ../pesanan.php?success=Pesanan berhasil ditolak');
} else {
    die('Error: ' . mysqli_error($koneksi));
}
?>
